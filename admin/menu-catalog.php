<?php
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once 'includes/auth.php';
require_once 'includes/menu-editor-security.php';

$page_title = 'Menu Catalog';
$admin_stylesheet = 'assets/css/admin-catalog.css';

function catalogEscape($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function catalogPostValue($key)
{
    return isset($_POST[$key]) && is_string($_POST[$key]) ? trim($_POST[$key]) : '';
}

if (empty($_SESSION['menu_catalog_csrf'])) {
    $_SESSION['menu_catalog_csrf'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['menu_catalog_csrf'];
$message = '';
$message_type = 'danger';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $action = catalogPostValue('action');
    $submitted_token = catalogPostValue('csrf_token');

    if (!hash_equals($csrf_token, $submitted_token)) {
        $message = 'Your form expired. Refresh the page and try again.';
    } else {
        $uploaded = null;
        try {
            if ($action === 'add_category') {
                $name = catalogPostValue('name');
                $description = catalogPostValue('description');

                if ($name === '' || strlen($name) > 100) {
                    throw new InvalidArgumentException('Enter a category name of up to 100 characters.');
                }
                if (strlen($description) > 4000) {
                    throw new InvalidArgumentException('Keep the category description under 4000 characters.');
                }

                $check = $conn->prepare('SELECT id FROM menu_categories WHERE LOWER(name) = LOWER(?) LIMIT 1');
                $check->execute([$name]);
                if ($check->fetch()) {
                    throw new InvalidArgumentException('That category already exists. Choose a different name.');
                }

                $insert = $conn->prepare("INSERT INTO menu_categories (name, description, image, status) VALUES (?, ?, '', 'active')");
                $insert->execute([$name, $description]);
                $_SESSION['menu_catalog_flash'] = 'Category added. You can add dishes to it below.';
            } elseif ($action === 'add_item') {
                $category_id = filter_var(catalogPostValue('category_id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
                $name = catalogPostValue('name');
                $description = catalogPostValue('description');
                $price = catalogPostValue('price');

                if ($category_id === false) {
                    throw new InvalidArgumentException('Choose a valid category for the dish.');
                }
                if ($name === '' || strlen($name) > 100) {
                    throw new InvalidArgumentException('Enter a dish name of up to 100 characters.');
                }
                if (strlen($description) > 4000) {
                    throw new InvalidArgumentException('Keep the dish description under 4000 characters.');
                }
                if (!preg_match('/^\d{1,8}(?:\.\d{1,2})?$/', $price) || (float)$price <= 0) {
                    throw new InvalidArgumentException('Enter a price above zero with up to two decimal places.');
                }

                $check = $conn->prepare("SELECT id FROM menu_categories WHERE id = ? AND status = 'active' LIMIT 1");
                $check->execute([$category_id]);
                if (!$check->fetch()) {
                    throw new InvalidArgumentException('That category is unavailable. Select an active category.');
                }

                $uploaded = menuEditorUploadImage('menu');
                $image = $uploaded ?? '';
                $insert = $conn->prepare('INSERT INTO menu_items (category_id, name, description, price, image, is_available, is_featured) VALUES (?, ?, ?, ?, ?, 1, 0)');
                $insert->execute([$category_id, $name, $description, $price, $image]);
                $uploaded = null;
                $_SESSION['menu_catalog_flash'] = 'Dish added to the live menu catalog.';
            } elseif ($action === 'update_item') {
                $item_id = filter_var(catalogPostValue('item_id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
                $price = catalogPostValue('price');
                $availability = catalogPostValue('is_available');

                if ($item_id === false) {
                    throw new InvalidArgumentException('The dish could not be found.');
                }
                if (!preg_match('/^\d{1,8}(?:\.\d{1,2})?$/', $price) || (float)$price <= 0) {
                    throw new InvalidArgumentException('Enter a price above zero with up to two decimal places.');
                }
                if ($availability !== '0' && $availability !== '1') {
                    throw new InvalidArgumentException('Choose a valid availability setting.');
                }

                $check = $conn->prepare('SELECT id FROM menu_items WHERE id = ? LIMIT 1');
                $check->execute([$item_id]);
                if (!$check->fetch()) {
                    throw new InvalidArgumentException('That dish no longer exists. Refresh the page.');
                }

                $update = $conn->prepare('UPDATE menu_items SET price = ?, is_available = ? WHERE id = ?');
                $update->execute([$price, (int)$availability, $item_id]);
                $_SESSION['menu_catalog_flash'] = 'Dish price and availability updated.';
            } else {
                throw new InvalidArgumentException('Choose a valid catalog action.');
            }

            header('Location: menu-catalog.php');
            exit();
        } catch (InvalidArgumentException $exception) {
            menuEditorDeleteUpload('menu', $uploaded);
            $message = $exception->getMessage();
        } catch (Throwable $exception) {
            menuEditorDeleteUpload('menu', $uploaded);
            error_log('Menu catalog save failed: ' . $exception->getMessage());
            $message = 'The catalog could not be saved. Please try again.';
        }
    }
}

if ($message === '' && isset($_SESSION['menu_catalog_flash'])) {
    $message = $_SESSION['menu_catalog_flash'];
    $message_type = 'success';
    unset($_SESSION['menu_catalog_flash']);
}

$category_query = $conn->query('SELECT id, name, description, status FROM menu_categories ORDER BY name');
$categories = $category_query->fetchAll();
$item_query = $conn->query('SELECT mi.id, mi.category_id, mi.name, mi.description, mi.price, mi.image, mi.is_available, mc.name AS category_name, mc.status AS category_status FROM menu_items mi JOIN menu_categories mc ON mc.id = mi.category_id ORDER BY mc.name, mi.name');
$items = $item_query->fetchAll();
$items_by_category = [];
$active_categories = [];
foreach ($categories as $category) {
    $items_by_category[(int)$category['id']] = [];
    if ($category['status'] === 'active') {
        $active_categories[] = $category;
    }
}
foreach ($items as $item) {
    $items_by_category[(int)$item['category_id']][] = $item;
}
$available_count = count(array_filter($items, static function ($item) {
    return (int)$item['is_available'] === 1 && $item['category_status'] === 'active';
}));

require_once 'includes/header.php';
?>

<div class="catalog-admin">
    <header class="catalog-admin__hero">
        <div>
            <span class="catalog-admin__eyebrow"><i class="bi bi-book-half" aria-hidden="true"></i> Kitchen editor</span>
            <h1>Menu catalog</h1>
            <p>Add a category or dish, then keep its price and availability current. Active dishes appear in the guest menu book.</p>
        </div>
        <a href="<?php echo SITE_URL; ?>/menu.php" class="catalog-admin__view" target="_blank" rel="noopener noreferrer"><i class="bi bi-box-arrow-up-right" aria-hidden="true"></i> View guest menu</a>
    </header>

    <div class="catalog-admin__stats" aria-label="Catalog summary">
        <div class="catalog-admin__stat"><span>Categories</span><strong><?php echo count($categories); ?></strong></div>
        <div class="catalog-admin__stat"><span>Total dishes</span><strong><?php echo count($items); ?></strong></div>
        <div class="catalog-admin__stat"><span>Available dishes</span><strong><?php echo $available_count; ?></strong></div>
    </div>

    <?php if ($message !== ''): ?>
    <div class="alert alert-<?php echo $message_type; ?>" role="alert"><?php echo catalogEscape($message); ?></div>
    <?php endif; ?>

    <section class="catalog-admin__quick" aria-label="Add to the menu">
        <div class="catalog-admin__panel">
            <div class="catalog-admin__panel-heading">
                <span class="catalog-admin__panel-icon"><i class="bi bi-tags" aria-hidden="true"></i></span>
                <div><span class="catalog-admin__kicker">Organize the book</span><h2>Add a category</h2></div>
            </div>
            <form method="post" action="menu-catalog.php" class="catalog-admin__form">
                <input type="hidden" name="csrf_token" value="<?php echo catalogEscape($csrf_token); ?>">
                <input type="hidden" name="action" value="add_category">
                <label for="catalog-category-name">Category name</label>
                <input class="form-control" id="catalog-category-name" name="name" type="text" maxlength="100" placeholder="e.g. Drinks or Momos" required>
                <label for="catalog-category-description">Short description <span>(optional)</span></label>
                <textarea class="form-control" id="catalog-category-description" name="description" rows="2" maxlength="4000" placeholder="A few words to introduce this section"></textarea>
                <button class="btn btn-admin-primary" type="submit"><i class="bi bi-plus-lg" aria-hidden="true"></i> Add category</button>
            </form>
        </div>

        <div class="catalog-admin__panel">
            <div class="catalog-admin__panel-heading">
                <span class="catalog-admin__panel-icon"><i class="bi bi-cup-hot" aria-hidden="true"></i></span>
                <div><span class="catalog-admin__kicker">Add something delicious</span><h2>Add a dish</h2></div>
            </div>
            <?php if ($active_categories): ?>
            <form method="post" action="menu-catalog.php" class="catalog-admin__form" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo catalogEscape($csrf_token); ?>">
                <input type="hidden" name="action" value="add_item">
                <div class="catalog-admin__form-grid">
                    <div>
                        <label for="catalog-item-name">Dish name</label>
                        <input class="form-control" id="catalog-item-name" name="name" type="text" maxlength="100" placeholder="e.g. Steamed momos" required>
                    </div>
                    <div>
                        <label for="catalog-item-price">Price (Rs.)</label>
                        <input class="form-control" id="catalog-item-price" name="price" type="number" min="0.01" max="99999999.99" step="0.01" placeholder="250.00" required>
                    </div>
                </div>
                <label for="catalog-item-category">Category</label>
                <select class="form-select" id="catalog-item-category" name="category_id" required>
                    <option value="">Choose a category</option>
                    <?php foreach ($active_categories as $category): ?>
                    <option value="<?php echo (int)$category['id']; ?>"><?php echo catalogEscape($category['name']); ?></option>
                    <?php endforeach; ?>
                </select>
                <label for="catalog-item-description">Short description <span>(optional)</span></label>
                <textarea class="form-control" id="catalog-item-description" name="description" rows="2" maxlength="4000" placeholder="What makes this dish special?"></textarea>
                <label for="catalog-item-image">Dish photo <span>(optional, JPG, PNG, or WebP; up to 5 MB)</span></label>
                <input class="form-control" id="catalog-item-image" name="image" type="file" accept="image/jpeg,image/png,image/webp">
                <button class="btn btn-admin-primary" type="submit"><i class="bi bi-plus-lg" aria-hidden="true"></i> Add dish</button>
            </form>
            <?php else: ?>
            <p class="catalog-admin__empty-intro">Add an active category first, then your dishes can go inside it.</p>
            <?php endif; ?>
        </div>
    </section>

    <div class="catalog-admin__section-heading">
        <div><span class="catalog-admin__kicker">Everything in one place</span><h2>Browse &amp; update</h2><p>Prices and availability can be changed here. Use the full editors for descriptions, photos, and other details.</p></div>
        <div class="catalog-admin__editor-links"><a href="categories.php"><i class="bi bi-tags" aria-hidden="true"></i> Category editor</a><a href="menu-items.php"><i class="bi bi-images" aria-hidden="true"></i> Dish &amp; photo editor</a></div>
    </div>

    <?php if ($categories): ?>
    <nav class="catalog-admin__jump" aria-label="Jump to a category">
        <?php foreach ($categories as $category): ?>
        <a href="#category-<?php echo (int)$category['id']; ?>"><?php echo catalogEscape($category['name']); ?></a>
        <?php endforeach; ?>
    </nav>

    <div class="catalog-admin__categories">
        <?php foreach ($categories as $category): ?>
        <?php $category_items = $items_by_category[(int)$category['id']]; ?>
        <section class="catalog-admin__category" id="category-<?php echo (int)$category['id']; ?>" aria-labelledby="category-title-<?php echo (int)$category['id']; ?>">
            <header class="catalog-admin__category-heading">
                <div>
                    <span class="catalog-admin__kicker"><?php echo count($category_items); ?> <?php echo count($category_items) === 1 ? 'dish' : 'dishes'; ?></span>
                    <h3 id="category-title-<?php echo (int)$category['id']; ?>"><?php echo catalogEscape($category['name']); ?></h3>
                    <?php if (trim((string)$category['description']) !== ''): ?><p><?php echo catalogEscape($category['description']); ?></p><?php endif; ?>
                </div>
                <div class="catalog-admin__category-actions">
                    <span class="catalog-admin__status <?php echo $category['status'] === 'active' ? 'is-active' : 'is-inactive'; ?>"><?php echo $category['status'] === 'active' ? 'Active' : 'Hidden'; ?></span>
                    <a href="categories.php" aria-label="Edit <?php echo catalogEscape($category['name']); ?> category"><i class="bi bi-pencil-square" aria-hidden="true"></i> Edit category</a>
                </div>
            </header>
            <?php if ($category_items): ?>
            <div class="catalog-admin__items">
                <?php foreach ($category_items as $item): ?>
                <article class="catalog-admin__item">
                    <div class="catalog-admin__item-image">
                        <img src="<?php echo catalogEscape(menuItemImageUrl($item)); ?>" alt="" loading="lazy">
                    </div>
                    <div class="catalog-admin__item-main">
                        <h4><?php echo catalogEscape($item['name']); ?></h4>
                        <?php if (trim((string)$item['description']) !== ''): ?><p><?php echo catalogEscape($item['description']); ?></p><?php endif; ?>
                        <a href="menu-items.php?category=<?php echo (int)$category['id']; ?>">Edit details &amp; photo <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
                    </div>
                    <form method="post" action="menu-catalog.php" class="catalog-admin__inline-form" aria-label="Update <?php echo catalogEscape($item['name']); ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo catalogEscape($csrf_token); ?>">
                        <input type="hidden" name="action" value="update_item">
                        <input type="hidden" name="item_id" value="<?php echo (int)$item['id']; ?>">
                        <div><label for="item-price-<?php echo (int)$item['id']; ?>">Price (Rs.)</label><input id="item-price-<?php echo (int)$item['id']; ?>" class="form-control" type="number" name="price" min="0.01" max="99999999.99" step="0.01" value="<?php echo catalogEscape(number_format((float)$item['price'], 2, '.', '')); ?>" required></div>
                        <div><label for="item-availability-<?php echo (int)$item['id']; ?>">Availability</label><select id="item-availability-<?php echo (int)$item['id']; ?>" class="form-select" name="is_available"><option value="1"<?php echo (int)$item['is_available'] === 1 ? ' selected' : ''; ?>>Available</option><option value="0"<?php echo (int)$item['is_available'] === 0 ? ' selected' : ''; ?>>Hidden</option></select></div>
                        <button class="btn btn-admin-primary" type="submit">Save</button>
                    </form>
                </article>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="catalog-admin__empty-category"><i class="bi bi-journal-plus" aria-hidden="true"></i> No dishes yet. Use the add dish form above to start this section.</div>
            <?php endif; ?>
        </section>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="catalog-admin__empty-all"><i class="bi bi-book" aria-hidden="true"></i><h3>Your catalog starts here</h3><p>Add your first category above, then add dishes and prices.</p></div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>

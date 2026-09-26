<?php
$page_title = 'Menu';
$page_stylesheet = 'assets/css/kids-menu.css';
$page_stylesheets = ['assets/css/menu-catalog.css'];
require_once 'config/config.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';

$stmt = $conn->prepare("SELECT * FROM menu_categories WHERE status = 'active' ORDER BY CASE WHEN name = 'Kids'' Favorites' THEN 0 ELSE 1 END, name");
$stmt->execute();
$categories = $stmt->fetchAll();
$catalog_stmt = $conn->prepare("SELECT mi.* FROM menu_items mi JOIN menu_categories mc ON mi.category_id = mc.id WHERE mi.is_available = 1 AND mc.status = 'active' ORDER BY mi.name");
$catalog_stmt->execute();
$catalog_items = [];
foreach ($catalog_stmt->fetchAll() as $catalog_item) {
    $catalog_items[(int)$catalog_item['category_id']][] = $catalog_item;
}
$kids_category = null;
foreach ($categories as $category) {
    if (strtolower($category['name']) === "kids' favorites") {
        $kids_category = $category;
        break;
    }
}

$selected_category = strtolower(trim((string)($_GET['category'] ?? 'all')));
$category_slugs = array_map(function ($category) { return strtolower($category['name']); }, $categories);
if ($selected_category !== 'all' && !in_array($selected_category, $category_slugs, true)) {
    $selected_category = 'all';
}
$search_term = trim((string)($_GET['search'] ?? ''));
$sql = "SELECT mi.*, mc.name AS category_name FROM menu_items mi JOIN menu_categories mc ON mi.category_id = mc.id WHERE mi.is_available = 1 AND mc.status = 'active'";
$parameters = [];
if ($selected_category !== 'all' && $search_term === '') {
    $sql .= " AND LOWER(mc.name) = ?";
    $parameters[] = $selected_category;
}
if ($search_term !== '') {
    $sql .= " AND (mi.name LIKE ? OR mi.description LIKE ?)";
    $parameters[] = '%' . $search_term . '%';
    $parameters[] = '%' . $search_term . '%';
}
$sql .= " ORDER BY mc.name, mi.name";
$stmt = $conn->prepare($sql);
$stmt->execute($parameters);
$menu_items = $stmt->fetchAll();
?>

<section class="section menu-section">
    <div class="container">
        <div class="section-title">
            <span class="eyebrow">From our kitchen</span>
            <h1>Explore the menu.</h1>
            <p>Find something for the table, and something just for you.</p>
        </div>

        <section class="catalog-invitation" aria-labelledby="catalog-invitation-title">
            <div class="catalog-invitation__copy">
                <span class="catalog-invitation__eyebrow">The 4 TO 9 collection</span>
                <h2 id="catalog-invitation-title">A menu worth <em>opening.</em></h2>
                <p>Turn the pages to discover every dish and drink, with current prices straight from our kitchen.</p>
                <button class="catalog-invitation__button" type="button" data-catalog-open>
                    Open menu catalog <i class="bi bi-arrow-up-right" aria-hidden="true"></i>
                </button>
                <span class="catalog-invitation__hint"><?php echo count($categories); ?> <?php echo count($categories) === 1 ? 'chapter' : 'chapters'; ?> to explore</span>
            </div>
            <button class="catalog-cover" type="button" data-catalog-open aria-label="Open the menu catalog">
                <span class="catalog-cover__spine" aria-hidden="true"></span>
                <span class="catalog-cover__edge" aria-hidden="true"></span>
                <span class="catalog-cover__content">
                    <span class="catalog-cover__overline">EST. AT THE TABLE</span>
                    <span class="catalog-cover__monogram">4 <span>TO</span> 9</span>
                    <span class="catalog-cover__rule" aria-hidden="true"></span>
                    <span class="catalog-cover__title">The Menu</span>
                    <span class="catalog-cover__footer">FOOD &amp; DRINKS &nbsp;&bull;&nbsp; <?php echo date('Y'); ?></span>
                </span>
            </button>
        </section>

        <dialog class="menu-catalog-dialog" id="menu-catalog-dialog" aria-labelledby="menu-catalog-title" aria-describedby="menu-catalog-description">
            <div class="menu-catalog-dialog__shell">
                <header class="menu-catalog-dialog__header">
                    <div>
                        <span class="menu-catalog-dialog__eyebrow">4 TO 9 RESTAURANT</span>
                        <h2 id="menu-catalog-title">Menu catalog</h2>
                        <p id="menu-catalog-description">Browse our menu by chapter. Prices are in rupees.</p>
                    </div>
                    <button class="menu-catalog-dialog__close" type="button" data-catalog-close aria-label="Close menu catalog"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
                </header>

                <div class="menu-catalog-dialog__mobile-select">
                    <label for="menu-catalog-chapter-select">Choose a chapter</label>
                    <select id="menu-catalog-chapter-select" data-catalog-select>
                        <?php foreach ($categories as $index => $category): ?>
                        <option value="<?php echo $index; ?>"><?php echo htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="menu-catalog-book" data-catalog-book>
                    <div class="menu-catalog-book__left">
                        <div class="menu-catalog-book__left-top"><span>THE COLLECTION</span><span>4 TO 9</span></div>
                        <?php foreach ($categories as $index => $category):
                            $chapter_items = $catalog_items[(int)$category['id']] ?? [];
                            $chapter_image = count($chapter_items) ? menuItemImageUrl($chapter_items[0]) : '';
                        ?>
                        <div class="menu-catalog-chapter" data-catalog-chapter="<?php echo $index; ?>" <?php echo $index === 0 ? '' : 'hidden'; ?>>
                            <span class="menu-catalog-chapter__number">CHAPTER <?php echo str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT); ?></span>
                            <?php if ($chapter_image !== ''): ?>
                            <div class="menu-catalog-chapter__image"><img src="<?php echo htmlspecialchars($chapter_image, ENT_QUOTES, 'UTF-8'); ?>" alt="" loading="lazy" decoding="async"></div>
                            <?php else: ?>
                            <div class="menu-catalog-chapter__image menu-catalog-chapter__image--empty" aria-hidden="true"><i class="bi bi-cup-hot"></i></div>
                            <?php endif; ?>
                            <h3><?php echo htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8'); ?></h3>
                            <?php if (!empty($category['description'])): ?>
                            <p><?php echo htmlspecialchars($category['description'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <?php endif; ?>
                            <span class="menu-catalog-chapter__count"><?php echo count($chapter_items); ?> <?php echo count($chapter_items) === 1 ? 'dish' : 'dishes'; ?></span>
                        </div>
                        <?php endforeach; ?>
                        <nav class="menu-catalog-index" aria-label="Menu chapters">
                            <?php foreach ($categories as $index => $category): ?>
                            <button type="button" data-catalog-go="<?php echo $index; ?>" aria-current="<?php echo $index === 0 ? 'page' : 'false'; ?>"><span><?php echo str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT); ?></span><?php echo htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8'); ?></button>
                            <?php endforeach; ?>
                        </nav>
                    </div>

                    <div class="menu-catalog-book__right">
                        <?php foreach ($categories as $index => $category):
                            $chapter_items = $catalog_items[(int)$category['id']] ?? [];
                        ?>
                        <section class="menu-catalog-page" data-catalog-page="<?php echo $index; ?>" aria-labelledby="menu-catalog-page-title-<?php echo $index; ?>" <?php echo $index === 0 ? '' : 'hidden'; ?>>
                            <div class="menu-catalog-page__heading">
                                <div><span class="menu-catalog-page__kicker">CHAPTER <?php echo str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT); ?></span><h3 id="menu-catalog-page-title-<?php echo $index; ?>"><?php echo htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8'); ?></h3></div>
                                <span class="menu-catalog-page__count"><?php echo count($chapter_items); ?> <?php echo count($chapter_items) === 1 ? 'item' : 'items'; ?></span>
                            </div>
                            <?php if ($chapter_items): ?>
                            <div class="menu-catalog-page__items">
                                <?php foreach ($chapter_items as $item): ?>
                                <article class="menu-catalog-dish">
                                    <img src="<?php echo htmlspecialchars(menuItemImageUrl($item), ENT_QUOTES, 'UTF-8'); ?>" alt="" loading="lazy" decoding="async">
                                    <div class="menu-catalog-dish__copy">
                                        <h4><?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?></h4>
                                        <?php if (!empty($item['description'])): ?><p><?php echo htmlspecialchars($item['description'], ENT_QUOTES, 'UTF-8'); ?></p><?php endif; ?>
                                    </div>
                                    <span class="menu-catalog-dish__price">Rs. <?php echo number_format((float)$item['price'], 2); ?></span>
                                </article>
                                <?php endforeach; ?>
                            </div>
                            <?php else: ?>
                            <div class="menu-catalog-page__empty"><i class="bi bi-journal-richtext" aria-hidden="true"></i><h4>New flavours are coming</h4><p>We are preparing dishes for this chapter. Browse another one for now.</p></div>
                            <?php endif; ?>
                            <span class="menu-catalog-page__folio"><?php echo str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT); ?> / <?php echo str_pad((string)count($categories), 2, '0', STR_PAD_LEFT); ?></span>
                        </section>
                        <?php endforeach; ?>
                    </div>
                    <span class="menu-catalog-book__turn-sheet" aria-hidden="true"></span>
                </div>

                <footer class="menu-catalog-dialog__footer">
                    <button type="button" data-catalog-prev><i class="bi bi-arrow-left" aria-hidden="true"></i> Previous</button>
                    <span data-catalog-status role="status" aria-live="polite">Chapter 1 of <?php echo count($categories); ?></span>
                    <button type="button" data-catalog-next>Next <i class="bi bi-arrow-right" aria-hidden="true"></i></button>
                </footer>
            </div>
        </dialog>

        <div class="search-container">
            <form method="GET" class="search-form">
                <div class="search-input-wrapper">
                    <i class="bi bi-search search-icon"></i>
                    <input type="search" class="form-control" name="search" aria-label="Search menu items" placeholder="Search dishes or ingredients" value="<?php echo htmlspecialchars($search_term); ?>">
                </div>
                <button type="submit" class="btn-search">Search</button>
                <?php if ($search_term): ?>
                <a href="menu.php" class="btn-clear">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <?php if (!$search_term && $kids_category): ?>
        <section class="kids-feature" id="kids" aria-labelledby="kids-feature-title">
            <div class="kids-feature__copy">
                <span class="kids-feature__eyebrow"><i class="bi bi-stars" aria-hidden="true"></i> For the little ones</span>
                <h2 id="kids-feature-title">Little plates.<br><em>Big smiles.</em></h2>
                <p>Familiar favourites in smaller portions, made for the whole family to enjoy together.</p>
                <div class="kids-feature__actions">
                    <a class="kids-feature__button" href="menu.php?category=<?php echo rawurlencode(strtolower($kids_category['name'])); ?>#kids">Explore kids' dishes <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                    <a class="kids-feature__secondary" href="tables.php?guests=4">Reserve a family table</a>
                </div>
            </div>
            <div class="kids-feature__photos" aria-hidden="true">
                <img class="kids-feature__photo kids-feature__photo--pizza" src="<?php echo SITE_URL; ?>/assets/images/photos/kids-mini-pizza.jpg" alt="" loading="lazy" decoding="async">
                <img class="kids-feature__photo kids-feature__photo--burger" src="<?php echo SITE_URL; ?>/assets/images/photos/kids-mini-burger.jpg" alt="" loading="lazy" decoding="async">
                <span class="kids-feature__spark kids-feature__spark--one"></span>
                <span class="kids-feature__spark kids-feature__spark--two"></span>
            </div>
        </section>
        <?php endif; ?>

        <?php if (!$search_term): ?>
        <div class="menu-categories">
            <button type="button" class="category-btn <?php echo $selected_category === 'all' ? 'active' : ''; ?>" data-category="all">
                <i class="bi bi-grid"></i> All
            </button>
            <?php foreach ($categories as $category): ?>
            <?php $is_kids_category = strtolower($category['name']) === "kids' favorites"; ?>
            <button type="button" class="category-btn <?php echo $selected_category === strtolower($category['name']) ? 'active' : ''; ?> <?php echo $is_kids_category ? 'category-btn--kids' : ''; ?>" data-category="<?php echo htmlspecialchars(strtolower($category['name']), ENT_QUOTES); ?>">
                <i class="bi <?php echo $is_kids_category ? 'bi-stars' : 'bi-tag'; ?>" aria-hidden="true"></i> <?php echo htmlspecialchars($category['name']); ?>
            </button>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="menu-grid">
            <?php if (count($menu_items) > 0): ?>
                <?php foreach ($menu_items as $item):
                    $image_url = menuItemImageUrl($item);
                    $is_kids_item = strtolower($item['category_name']) === "kids' favorites";
                ?>
                <article class="menu-item <?php echo $is_kids_item ? 'menu-item--kids' : ''; ?>" data-category="<?php echo htmlspecialchars(strtolower($item['category_name']), ENT_QUOTES); ?>">
                    <div class="menu-item-image">
                        <img src="<?php echo htmlspecialchars($image_url, ENT_QUOTES); ?>" alt="<?php echo htmlspecialchars($item['name'], ENT_QUOTES); ?>" loading="lazy" decoding="async">
                        <?php if ($is_kids_item): ?>
                        <span class="kids-dish-badge"><i class="bi bi-stars" aria-hidden="true"></i> Little favourite</span>
                        <?php endif; ?>
                        <?php if ($item['is_featured']): ?>
                        <div class="featured-badge">
                            <i class="bi bi-star-fill"></i> Featured
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="menu-item-content">
                        <div class="menu-item-category">
                            <?php echo htmlspecialchars($item['category_name']); ?>
                        </div>
                        <h2><?php echo htmlspecialchars($item['name']); ?></h2>
                        <p><?php echo htmlspecialchars($item['description']); ?></p>
                        <div class="menu-item-meta">
                            <span class="menu-item-price">Rs. <?php echo number_format($item['price'], 2); ?></span>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="bi bi-search empty-state-icon" aria-hidden="true"></i>
                    <h4>No Menu Items Found</h4>
                    <p>Try adjusting your search or category filter</p>
                </div>
            <?php endif; ?>
        </div>

        <?php if (!$search_term): ?>
        <div class="text-center mt-5">
            <a href="tables.php" class="btn-hero">Book a Table</a>
        </div>
        <?php endif; ?>
    </div>
</section>

<style>
.search-container {
    max-width: 700px;
    margin: 0 auto 3rem;
}

.search-form {
    display: flex;
    gap: 1rem;
    align-items: center;
}

.search-input-wrapper {
    position: relative;
    flex: 1;
}

.search-icon {
    position: absolute;
    left: 1.2rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--primary-teal);
    font-size: 1.2rem;
}

.search-input-wrapper .form-control {
    padding-left: 3rem;
}

.btn-search {
    background: linear-gradient(135deg, var(--primary-teal), var(--dark-teal));
    color: white;
    padding: 1rem 2rem;
    border: none;
    border-radius: var(--radius-md);
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: var(--shadow-md);
}

.btn-search:hover {
    background: linear-gradient(135deg, var(--dark-teal), var(--primary-teal));
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

.btn-clear {
    background: transparent;
    color: var(--text-gray);
    padding: 1rem 1.5rem;
    border: 2px solid var(--border-color);
    border-radius: var(--radius-md);
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-clear:hover {
    border-color: var(--primary-teal);
    color: var(--primary-teal);
}

.menu-item {
    background: linear-gradient(135deg, var(--card-background), var(--soft-green));
    border-radius: var(--radius-xl);
    overflow: hidden;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    border: 1px solid var(--border-color);
    box-shadow: var(--shadow-md);
}

.menu-item:hover {
    transform: translateY(-12px);
    box-shadow: var(--shadow-xl);
    border-color: var(--primary-teal);
}

.menu-item-image {
    height: 260px;
    overflow: hidden;
    position: relative;
}

.menu-item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}

.menu-item:hover .menu-item-image img {
    transform: scale(1.12);
}

.featured-badge {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: linear-gradient(135deg, var(--warning), #D97706);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: var(--radius-pill);
    font-size: 0.8rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.3rem;
    box-shadow: var(--shadow-md);
}

.menu-item-content {
    padding: 2rem;
}

.menu-item-category {
    color: var(--primary-teal);
    font-size: 0.85rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 0.8rem;
}

.menu-item-content h4 {
    font-size: 1.6rem;
    margin-bottom: 1rem;
    color: var(--dark-teal);
    font-weight: 700;
}

.menu-item-content p {
    color: var(--text-gray);
    font-size: 1rem;
    margin-bottom: 1.5rem;
    line-height: 1.7;
}

.menu-item-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid var(--border-color);
}

.menu-item-price {
    font-size: 1.9rem;
    color: var(--primary-teal);
    font-weight: 700;
    background: linear-gradient(135deg, var(--primary-teal), var(--dark-teal));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

@media (max-width: 768px) {
    .search-form {
        flex-direction: column;
    }

    .menu-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
(function () {
    function showKidsFromLink() {
        if (window.location.hash !== '#kids') return;
        const kidsCategory = document.querySelector('.menu-categories .category-btn--kids');
        if (kidsCategory && !kidsCategory.classList.contains('active')) kidsCategory.click();
    }
    window.addEventListener('load', showKidsFromLink);
    window.addEventListener('hashchange', showKidsFromLink);
})();
</script>
<script src="<?php echo SITE_URL; ?>/assets/js/menu-catalog.js?v=<?php echo filemtime(__DIR__ . '/assets/js/menu-catalog.js'); ?>" defer></script>

<?php require_once 'includes/footer.php'; ?>

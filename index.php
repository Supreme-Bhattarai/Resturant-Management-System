<?php
$page_title = 'Home';
$page_stylesheet = 'assets/css/kids-home.css';
require_once 'config/config.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';

$home_settings = getRestaurantSettings($conn) ?: [];
$home_booking_window = getBookingTimeWindow($home_settings);
$home_max_guests = max(1, (int)($home_settings['max_booking_guests'] ?? 20));
$home_opening = !empty($home_settings['opening_time']) ? formatTime($home_settings['opening_time']) : '';
$home_closing = !empty($home_settings['closing_time']) ? formatTime($home_settings['closing_time']) : '';

$stmt = $conn->prepare("SELECT mi.*, mc.name AS category_name FROM menu_items mi JOIN menu_categories mc ON mi.category_id = mc.id WHERE mi.is_available = 1 AND mc.status = 'active' ORDER BY mi.is_featured DESC, mi.id DESC LIMIT 3");
$stmt->execute();
$featured_items = $stmt->fetchAll();

$stmt = $conn->prepare("SELECT mi.*, mc.name AS category_name FROM menu_items mi JOIN menu_categories mc ON mi.category_id = mc.id WHERE mc.name = ? AND mc.status = 'active' AND mi.is_available = 1 ORDER BY mi.id ASC LIMIT 4");
$stmt->execute(["Kids' Favorites"]);
$kids_items = $stmt->fetchAll();

$stmt = $conn->prepare("SELECT * FROM gallery WHERE status = 'active' ORDER BY created_at DESC LIMIT 4");
$stmt->execute();
$gallery_images = array_values(array_filter($stmt->fetchAll(), function ($image) {
    return !empty($image['image']) && is_file(__DIR__ . '/uploads/gallery/' . basename($image['image']));
}));
?>

    <section class="hero" aria-labelledby="hero-title">
        <div class="container hero-content">
            <div class="hero-text">
                <span class="eyebrow">4 TO 9 · A place to gather</span>
                <h1 id="hero-title">Come hungry.<br><em>Leave closer.</em></h1>
                <p>Nepali flavors, generous plates, and the kind of welcome that makes you stay a little longer.</p>
                <div class="hero-buttons">
                    <a href="tables.php" class="btn-hero">Reserve a table <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
                    <a href="menu.php" class="btn-hero-outline">Explore the menu</a>
                </div>
                <?php if ($home_opening !== '' && $home_closing !== ''): ?>
                <p class="hero-detail"><i class="bi bi-clock" aria-hidden="true"></i> Open daily · <?php echo htmlspecialchars($home_opening, ENT_QUOTES); ?>–<?php echo htmlspecialchars($home_closing, ENT_QUOTES); ?></p>
                <?php endif; ?>
            </div>
            <div class="hero-image">
                <img src="<?php echo SITE_URL; ?>/assets/images/photos/restaurant-hero.jpg" alt="Restaurant dining room ready for guests" fetchpriority="high">
                <div class="hero-image-caption"><span class="caption-mark">4—9</span><span>Good food tastes better together.</span></div>
            </div>
        </div>
    </section>

    <div class="experience-strip" aria-label="Our restaurant at a glance">
        <div class="container experience-strip-inner">
            <span><i class="bi bi-heart" aria-hidden="true"></i> Made with care</span>
            <span><i class="bi bi-people" aria-hidden="true"></i> Better shared</span>
            <span><i class="bi bi-cup-hot" aria-hidden="true"></i> Warmly served</span>
        </div>
    </div>

    <section class="section about-section" aria-labelledby="home-about-title">
        <div class="container about-content">
            <div class="about-image">
                <img src="<?php echo SITE_URL; ?>/assets/images/photos/food-sharing.jpg" alt="Freshly prepared food served at the table" loading="lazy" decoding="async">
                <span class="image-kicker">A seat for everyone</span>
            </div>
            <div class="about-text">
                <span class="eyebrow">Our story</span>
                <h2 id="home-about-title">Where food brings people together.</h2>
                <p>At 4 TO 9, we draw inspiration from the warmth of Nepali hospitality: a table full of food, familiar faces, and room for one more.</p>
                <p>Come for a quick meal or settle in for a long conversation. We make space for both.</p>
                <a href="about.php" class="text-link">Get to know us <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
            </div>
        </div>
    </section>

    <section class="section menu-section" aria-labelledby="home-menu-title">
        <div class="container">
            <div class="section-heading-row">
                <div class="section-title"><span class="eyebrow">From our kitchen</span><h2 id="home-menu-title">Something worth sharing.</h2><p>Discover a few favorites, then explore the full menu.</p></div>
                <a href="menu.php" class="text-link">View full menu <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
            </div>
            <?php if ($featured_items): ?>
            <div class="menu-grid">
                <?php foreach ($featured_items as $item):
                    $image_url = menuItemImageUrl($item);
                ?>
                <article class="menu-item">
                    <div class="menu-item-image"><img src="<?php echo htmlspecialchars($image_url, ENT_QUOTES); ?>" alt="<?php echo htmlspecialchars($item['name'], ENT_QUOTES); ?>" loading="lazy" decoding="async"></div>
                    <div class="menu-item-content">
                        <span class="menu-item-category"><?php echo htmlspecialchars($item['category_name']); ?></span>
                        <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                        <p><?php echo htmlspecialchars($item['description']); ?></p>
                        <div class="menu-item-meta"><span class="menu-item-price">Rs. <?php echo number_format($item['price'], 2); ?></span></div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="empty-state"><i class="bi bi-journal-text empty-state-icon" aria-hidden="true"></i><h3>Our menu is being prepared</h3><p>Please check back soon, or get in touch for today's dishes.</p></div>
            <?php endif; ?>
        </div>
    </section>

    <?php if ($kids_items): ?>
    <section class="section kids-section" id="kids" aria-labelledby="home-kids-title">
        <div class="container kids-layout">
            <div class="kids-intro">
                <span class="eyebrow"><i class="bi bi-stars" aria-hidden="true"></i> For our younger guests</span>
                <h2 id="home-kids-title">Little plates.<br><em>Big smiles.</em></h2>
                <p>Give everyone at the table something to look forward to. Our kids' favorites include mini mains and a sweet finish.</p>
                <div class="kids-highlights" aria-label="Kids menu highlights">
                    <span><i class="bi bi-check2-circle" aria-hidden="true"></i> Familiar favorites</span>
                    <span><i class="bi bi-check2-circle" aria-hidden="true"></i> Little portions</span>
                </div>
                <a href="menu.php#kids" class="btn-hero">Explore the kids menu <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
            </div>
            <div class="kids-showcase">
                <div class="kids-showcase__image">
                    <img src="<?php echo htmlspecialchars(menuItemImageUrl($kids_items[0]), ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($kids_items[0]['name'], ENT_QUOTES, 'UTF-8'); ?>" loading="lazy" decoding="async">
                    <span class="kids-showcase__badge"><i class="bi bi-heart-fill" aria-hidden="true"></i> Little favorites</span>
                </div>
                <div class="kids-showcase__list" aria-label="Kids menu preview">
                    <?php foreach ($kids_items as $item): ?>
                    <a class="kids-dish" href="menu.php#kids">
                        <img src="<?php echo htmlspecialchars(menuItemImageUrl($item), ENT_QUOTES, 'UTF-8'); ?>" alt="" loading="lazy" decoding="async">
                        <span class="kids-dish__name"><?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <span class="kids-dish__price">Rs. <?php echo number_format((float)$item['price'], 0); ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <section class="section reservation-section" aria-labelledby="home-reservation-title">
        <div class="container reservation-layout">
            <div class="reservation-intro">
                <span class="eyebrow">Make yourself at home</span>
                <h2 id="home-reservation-title">There’s always room at the table.</h2>
                <p>Tell us when you’re coming and how many people are joining you. We’ll help you find a table that fits.</p>
                <div class="reservation-note"><i class="bi bi-stars" aria-hidden="true"></i> Celebrations, catch-ups, and everything in between.</div>
            </div>
            <div class="reservation-form">
                <h3>Find your table</h3>
                <form action="tables.php" method="GET">
                    <div class="form-group"><label class="form-label" for="home-date">Date</label><input id="home-date" type="date" class="form-control" name="date" min="<?php echo date('Y-m-d'); ?>" required></div>
                    <div class="form-group"><label class="form-label" for="home-time">Time</label><input id="home-time" type="time" class="form-control" name="time" <?php if (!$home_booking_window['overnight']): ?>min="<?php echo htmlspecialchars($home_booking_window['opening'], ENT_QUOTES, 'UTF-8'); ?>" max="<?php echo htmlspecialchars($home_booking_window['latest_start'], ENT_QUOTES, 'UTF-8'); ?>"<?php endif; ?> required></div>
                    <div class="form-group"><label class="form-label" for="home-guests">Guests</label><input id="home-guests" type="number" class="form-control" name="guests" min="1" max="<?php echo $home_max_guests; ?>" value="<?php echo min(2, $home_max_guests); ?>" required></div>
                    <button type="submit" class="btn-reserve">Find a table <i class="bi bi-arrow-right" aria-hidden="true"></i></button>
                    <p class="form-hint">Booking times: <?php echo formatTime($home_booking_window['opening']); ?>&ndash;<?php echo formatTime($home_booking_window['latest_start']); ?>. Availability is confirmed when you send your request.</p>
                </form>
            </div>
        </div>
    </section>

    <?php if ($gallery_images): ?>
    <section class="section gallery-section" aria-labelledby="home-gallery-title">
        <div class="container">
            <div class="section-heading-row">
                <div class="section-title"><span class="eyebrow">A little inspiration</span><h2 id="home-gallery-title">Around the table.</h2><p>Food and dining moments to look forward to. Photos are illustrative.</p></div>
                <a href="gallery.php" class="text-link">Explore the gallery <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
            </div>
            <div class="gallery-grid">
                <?php foreach ($gallery_images as $image): ?>
                <figure class="gallery-item"><img src="<?php echo SITE_URL; ?>/uploads/gallery/<?php echo rawurlencode(basename($image['image'])); ?>" alt="<?php echo htmlspecialchars($image['title']); ?>" loading="lazy" decoding="async"><figcaption class="gallery-overlay"><span><?php echo htmlspecialchars($image['title']); ?></span></figcaption></figure>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <section class="visit-section" aria-labelledby="home-visit-title"><div class="container visit-content"><div><span class="eyebrow">See you soon</span><h2 id="home-visit-title">Pull up a chair.</h2><p>Your next good meal could start here.</p></div><a href="tables.php" class="btn-hero">Book your visit <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a></div></section>
<?php require_once 'includes/footer.php'; ?>

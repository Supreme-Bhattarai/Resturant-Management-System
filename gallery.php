<?php
$page_title = 'Gallery';
require_once 'config/config.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';

$stmt = $conn->prepare("SELECT * FROM gallery WHERE status = 'active' ORDER BY created_at DESC");
$stmt->execute();
$gallery_images = array_values(array_filter($stmt->fetchAll(), function ($image) {
    return !empty($image['image']) && is_file(__DIR__ . '/uploads/gallery/' . basename($image['image']));
}));
?>

<section class="section gallery-section">
    <div class="container">
        <div class="section-title">
            <span class="eyebrow">A look inside</span>
            <h1>Life around the table.</h1>
            <p>Food, cooking, and dining inspiration. Photos are illustrative.</p>
        </div>

        <?php if (count($gallery_images) > 0): ?>
        <div class="gallery-grid">
            <?php foreach ($gallery_images as $image): ?>
            <figure class="gallery-item">
                <img src="<?php echo SITE_URL; ?>/uploads/gallery/<?php echo rawurlencode(basename($image['image'])); ?>" alt="<?php echo htmlspecialchars($image['title']); ?>" loading="lazy" decoding="async">
                <figcaption class="gallery-overlay"><span><?php echo htmlspecialchars($image['title']); ?></span></figcaption>
            </figure>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <i class="bi bi-images empty-state-icon" aria-hidden="true"></i>
            <h4>No Gallery Images Available</h4>
            <p>We're getting our photos ready. Come back soon for a closer look.</p>
        </div>
        <?php endif; ?>

        <div class="gallery-cta">
            <div class="cta-content">
                <h3>Come see for yourself.</h3>
                <p>A seat at the table is the best way to experience 4 TO 9.</p>
                <a href="tables.php" class="btn-hero">Book a Table</a>
            </div>
        </div>
    </div>
</section>

<style>
.gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 2rem;
    margin-bottom: 4rem;
}

.gallery-item {
    position: relative;
    overflow: hidden;
    border-radius: var(--radius-xl);
    height: 320px;
    box-shadow: var(--shadow-md);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.gallery-item:hover {
    transform: translateY(-10px);
    box-shadow: var(--shadow-xl);
}

.gallery-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}

.gallery-item:hover img {
    transform: scale(1.15);
}

.gallery-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(20, 43, 89, 0.84), rgba(65, 105, 225, 0.9));
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.4s ease;
}

.gallery-item:hover .gallery-overlay {
    opacity: 1;
}

.gallery-overlay h4 {
    color: white;
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 1rem;
    text-align: center;
    padding: 0 1rem;
}

.gallery-overlay-content {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(10px);
}

.gallery-overlay-content i {
    color: white;
    font-size: 1.5rem;
}

.gallery-cta {
    background: linear-gradient(135deg, var(--soft-green), var(--light-green));
    padding: 4rem;
    border-radius: var(--radius-2xl);
    margin-top: 4rem;
}

.cta-content {
    text-align: center;
}

.cta-content h3 {
    color: var(--dark-teal);
    font-size: 2rem;
    margin-bottom: 1rem;
}

.cta-content p {
    color: var(--text-gray);
    font-size: 1.1rem;
    margin-bottom: 2rem;
}

@media (max-width: 768px) {
    .gallery-grid {
        grid-template-columns: 1fr;
    }

    .gallery-item {
        height: 250px;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?>

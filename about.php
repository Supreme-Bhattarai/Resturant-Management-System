<?php
$page_title = 'About Us';
require_once 'config/config.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';
?>

<section class="section about-section">
    <div class="container">
        <div class="section-title">
            <span class="eyebrow">The story behind our table</span>
            <h1>Food is better together.</h1>
            <p>A warm welcome, a good meal, and room for everyone.</p>
        </div>

        <div class="about-hero">
            <div class="about-hero-text">
                <h2>Welcome to 4 TO 9.</h2>
                <p>We started with a simple idea: a restaurant should feel like being welcomed into someone’s home. The table is a place to slow down, share food, and reconnect.</p>
                <p>Our kitchen takes inspiration from Nepali hospitality and the pleasure of eating together. Whether you visit with family, friends, or someone new, we hope you feel at ease here.</p>
            </div>
            <div class="about-hero-image">
                <img src="<?php echo SITE_URL; ?>/assets/images/photos/food-sharing.jpg" alt="Assorted dumplings and vegetables served on a table" loading="lazy" decoding="async">
            </div>
        </div>

        <div class="philosophy-section">
            <div class="philosophy-content">
                <div class="philosophy-text">
                    <h3>Our Philosophy</h3>
                    <p>Good food starts with care. We make room for familiar comforts and new favorites, served in a space where you can take your time.</p>
                    <p>Hospitality is the part that brings it all together. We want every visit to feel easy, generous, and worth sharing.</p>
                </div>
                <div class="philosophy-image">
                    <img src="<?php echo SITE_URL; ?>/assets/images/photos/restaurant-hero.jpg" alt="A welcoming restaurant dining room" loading="lazy" decoding="async">
                </div>
            </div>
        </div>

        <div class="section-title">
            <h2>Our Values</h2>
            <p>What drives us every day</p>
        </div>

        <div class="values-grid">
            <div class="value-card">
                <div class="value-icon"><i class="bi bi-patch-check" aria-hidden="true"></i></div>
                <h4>Authenticity</h4>
                <p>We respect the flavors and traditions behind Nepali cuisine. Authenticity means respecting the character of the dish - understanding its ingredients, techniques, and why people love it.</p>
            </div>
            <div class="value-card">
                <div class="value-icon"><i class="bi bi-star" aria-hidden="true"></i></div>
                <h4>Quality</h4>
                <p>We care about ingredients, preparation, cooking, and presentation. Every dish should taste great every time you order it.</p>
            </div>
            <div class="value-card">
                <div class="value-icon"><i class="bi bi-heart" aria-hidden="true"></i></div>
                <h4>Hospitality</h4>
                <p>We want every guest to feel welcomed. In Nepali culture, guests are treated with warmth. Food is offered generously. Tea is shared. People sit together.</p>
            </div>
            <div class="value-card">
                <div class="value-icon"><i class="bi bi-arrow-repeat" aria-hidden="true"></i></div>
                <h4>Consistency</h4>
                <p>A favorite dish should taste great every time you order it. We maintain consistent quality in every meal we serve.</p>
            </div>
            <div class="value-card">
                <div class="value-icon"><i class="bi bi-shield-check" aria-hidden="true"></i></div>
                <h4>Cleanliness</h4>
                <p>A professional restaurant begins with a clean kitchen and dining environment. We maintain the highest standards of hygiene.</p>
            </div>
            <div class="value-card">
                <div class="value-icon"><i class="bi bi-people" aria-hidden="true"></i></div>
                <h4>Community</h4>
                <p>Restaurants are places where people connect. We want 4 TO 9 to be part of those connections, bringing people together around great Nepali food.</p>
            </div>
        </div>

        <div class="cta-section">
            <div class="cta-content">
                <h3>Experience 4 TO 9</h3>
                <p>Our story is best understood around a table. Join us for authentic Nepali food and warm hospitality.</p>
                <div class="cta-buttons">
                    <a href="tables.php" class="btn-hero">Book Your Table</a>
                    <a href="menu.php" class="btn-hero-outline">View Our Menu</a>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.about-hero {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4rem;
    align-items: center;
    margin-bottom: 4rem;
}

.about-hero-text h3 {
    font-size: 2.5rem;
    margin-bottom: 2rem;
    color: var(--dark-blue);
}

.about-hero-text p {
    color: var(--text-gray);
    font-size: 1.1rem;
    line-height: 1.8;
    margin-bottom: 1.5rem;
}

.about-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 2rem;
    margin-top: 3rem;
}

.stat-item {
    text-align: center;
    padding: 2rem;
    background: linear-gradient(135deg, var(--light-purple), var(--light-blue));
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-md);
}

.stat-number {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--primary-blue);
    background: linear-gradient(135deg, var(--primary-blue), var(--royal-purple));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.stat-label {
    color: var(--dark-blue);
    font-weight: 600;
    font-size: 1rem;
}

.about-hero-image img {
    width: 100%;
    border-radius: var(--radius-2xl);
    box-shadow: var(--shadow-xl);
}

.philosophy-section {
    background: linear-gradient(135deg, var(--soft-purple), var(--light-purple));
    padding: 4rem;
    border-radius: var(--radius-2xl);
    margin-bottom: 4rem;
}

.philosophy-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4rem;
    align-items: center;
}

.philosophy-text h3 {
    font-size: 2.2rem;
    margin-bottom: 2rem;
    color: var(--dark-blue);
}

.philosophy-text p {
    color: var(--text-gray);
    font-size: 1.1rem;
    line-height: 1.8;
    margin-bottom: 1.5rem;
}

.philosophy-image img {
    width: 100%;
    border-radius: var(--radius-2xl);
    box-shadow: var(--shadow-xl);
}

.team-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    margin-bottom: 4rem;
}

.team-card {
    background: linear-gradient(135deg, var(--card-background), var(--soft-purple));
    padding: 2.5rem;
    border-radius: var(--radius-xl);
    border: 1px solid var(--border-color);
    text-align: center;
    box-shadow: var(--shadow-md);
    transition: all 0.3s ease;
}

.team-card:hover {
    transform: translateY(-8px);
    box-shadow: var(--shadow-lg);
}

.team-avatar {
    width: 120px;
    height: 120px;
    margin: 0 auto 1.5rem;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary-blue), var(--royal-purple));
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: var(--shadow-lg);
}

.avatar-emoji {
    font-size: 3rem;
}

.team-card h4 {
    color: var(--dark-blue);
    margin-bottom: 1rem;
    font-size: 1.3rem;
}

.team-card p {
    color: var(--text-gray);
    font-size: 1rem;
    margin-bottom: 1.5rem;
    line-height: 1.7;
}

.team-social {
    display: flex;
    justify-content: center;
    gap: 1rem;
}

.team-social a {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary-blue), var(--royal-purple));
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.team-social a:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-md);
}

.values-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 2rem;
    margin-bottom: 4rem;
}

.value-card {
    background: var(--card-background);
    padding: 2.5rem;
    border-radius: var(--radius-xl);
    border: 1px solid var(--border-color);
    text-align: center;
    box-shadow: var(--shadow-md);
    transition: all 0.3s ease;
}

.value-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg);
}

.value-icon {
    font-size: 3rem;
    margin-bottom: 1.5rem;
}

.value-card h4 {
    color: var(--dark-blue);
    margin-bottom: 1rem;
    font-size: 1.3rem;
}

.value-card p {
    color: var(--text-gray);
    font-size: 1rem;
    line-height: 1.7;
}

.cta-section {
    background: linear-gradient(135deg, var(--dark-blue), var(--primary-blue));
    padding: 4rem;
    border-radius: var(--radius-2xl);
    margin-top: 4rem;
}

.cta-content {
    text-align: center;
    color: white;
}

.cta-content h3 {
    font-size: 2.5rem;
    margin-bottom: 1.5rem;
}

.cta-content p {
    font-size: 1.2rem;
    margin-bottom: 2.5rem;
    opacity: 0.9;
}

.cta-buttons {
    display: flex;
    gap: 1.5rem;
    justify-content: center;
}

.cta-buttons .btn-hero {
    background: white;
    color: var(--primary-blue);
}

.cta-buttons .btn-hero-outline {
    border-color: white;
    color: white;
}

.cta-buttons .btn-hero-outline:hover {
    background: white;
    color: var(--primary-blue);
}

@media (max-width: 768px) {
    .about-hero {
        grid-template-columns: 1fr;
    }

    .about-stats {
        grid-template-columns: 1fr;
    }

    .philosophy-content {
        grid-template-columns: 1fr;
    }

    .team-grid {
        grid-template-columns: 1fr;
    }

    .values-grid {
        grid-template-columns: 1fr;
    }

    .cta-buttons {
        flex-direction: column;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?>

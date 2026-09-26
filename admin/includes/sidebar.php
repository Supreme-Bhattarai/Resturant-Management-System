<?php
$current_page = basename($_SERVER['PHP_SELF']);
$admin_nav = [
    ['dashboard.php', 'Dashboard', 'bi-grid-1x2', ['dashboard.php']],
    ['bookings.php', 'Bookings', 'bi-calendar-check', ['bookings.php', 'booking-details.php']],
    ['tables.php', 'Tables', 'bi-layout-three-columns', ['tables.php']],
    ['menu-catalog.php', 'Menu Catalog', 'bi-book-half', ['menu-catalog.php']],
    ['categories.php', 'Categories', 'bi-tags', ['categories.php']],
    ['menu-items.php', 'Menu items', 'bi-journal-text', ['menu-items.php']],
    ['customers.php', 'Customers', 'bi-people', ['customers.php']],
    ['gallery.php', 'Gallery', 'bi-images', ['gallery.php']],
    ['settings.php', 'Settings', 'bi-gear', ['settings.php']],
    ['backup.php', 'Backup', 'bi-database', ['backup.php']],
    ['profile.php', 'Profile', 'bi-person-circle', ['profile.php']],
];
?>
            <aside class="admin-sidebar" id="admin-sidebar" aria-label="Admin navigation">
                <div class="sidebar-header">
                    <a href="dashboard.php" class="sidebar-brand">4 <span>TO</span> 9</a>
                    <p>Restaurant workspace</p>
                </div>
                <nav aria-label="Admin sections">
                    <p class="sidebar-label">Manage</p>
                    <ul class="sidebar-menu">
                        <?php foreach ($admin_nav as [$href, $label, $icon, $active_pages]): ?>
                        <?php $is_active = in_array($current_page, $active_pages, true); ?>
                        <li>
                            <a class="<?php echo $is_active ? 'active' : ''; ?>" href="<?php echo $href; ?>"<?php echo $is_active ? ' aria-current="page"' : ''; ?>>
                                <i class="bi <?php echo $icon; ?>" aria-hidden="true"></i><span><?php echo $label; ?></span>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </nav>
                <div class="sidebar-bottom">
                    <a href="logout.php"><i class="bi bi-box-arrow-right" aria-hidden="true"></i><span>Sign out</span></a>
                </div>
            </aside>

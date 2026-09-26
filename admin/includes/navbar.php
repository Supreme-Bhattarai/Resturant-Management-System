            <header class="admin-navbar">
                <div class="navbar-content">
                    <button class="btn-toggle-sidebar" type="button" aria-label="Toggle admin menu" aria-controls="admin-sidebar" aria-expanded="false">
                        <i class="bi bi-list" aria-hidden="true"></i>
                    </button>
                    <div class="navbar-heading">
                        <span class="navbar-eyebrow">4 TO 9 / Workspace</span>
                        <span class="navbar-page"><?php echo htmlspecialchars($page_title ?? 'Dashboard'); ?></span>
                    </div>
                    <div class="navbar-right">
                        <a class="navbar-site-link" href="<?php echo SITE_URL; ?>/index.php" target="_blank" rel="noopener noreferrer">
                            <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i><span>View website</span>
                        </a>
                        <div class="admin-info">
                            <span class="admin-avatar" aria-hidden="true"><?php echo htmlspecialchars(strtoupper(substr($_SESSION['user_name'] ?? 'A', 0, 1))); ?></span>
                            <span class="admin-identity">
                            <span class="admin-name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?></span>
                            <span class="admin-role">Administrator</span>
                            </span>
                        </div>
                    </div>
                </div>
            </header>

            <main class="admin-content" id="admin-content">

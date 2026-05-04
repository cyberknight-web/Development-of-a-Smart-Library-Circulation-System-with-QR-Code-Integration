<?php
// includes/admin_layout.php
// Shared layout helpers for admin pages (header and footer with EVSU colors).

declare(strict_types=1);

function admin_render_header(string $page_title = 'Admin'): void
{
    $full_title = 'Smart Library Admin | ' . $page_title;
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($full_title, ENT_QUOTES, 'UTF-8'); ?></title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
        <style>
            :root {
                --sl-primary: <?php echo COLOR_PRIMARY; ?>;
                --sl-accent: <?php echo COLOR_ACCENT; ?>;
                --sl-light: <?php echo COLOR_LIGHT; ?>;
            }
            body {
                background-color: #f5f5f5;
                font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            }
            .sl-navbar {
                background: linear-gradient(90deg, #FF6A00, #FF8C00, #FFA500);
            }
            .sl-navbar .navbar-brand, .sl-navbar .nav-link, .sl-navbar .navbar-text {
                color: var(--sl-light) !important;
            }
            .sl-navbar .nav-link.active {
                border-bottom: 2px solid var(--sl-accent);
            }
            .sl-card {
                border-radius: 12px;
                border: none;
                box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            }
            .sl-badge {
                background-color: var(--sl-accent);
                color: #000;
            }
            .btn-sl-primary {
                background-color: #FF8C00;
                color: var(--sl-light);
                border: none;
            }
            .btn-sl-primary:hover {
                background-color: #FF6A00;
                color: var(--sl-light);
            }
            .btn-sl-accent {
                background-color: var(--sl-accent);
                color: #000;
                border: none;
            }
            .btn-sl-accent:hover {
                background-color: #c19b2f;
                color: #000;
            }
            .sl-page-header {
                border-left: 4px solid var(--sl-accent);
                padding-left: 0.75rem;
            }
            .notification-badge {
                display: inline-block;
                background-color: #FF6A00;
                color: white;
                border-radius: 50%;
                width: 20px;
                height: 20px;
                line-height: 20px;
                text-align: center;
                font-size: 0.75rem;
                font-weight: bold;
                margin-left: 0.3rem;
                animation: pulse 2s infinite;
            }
            @keyframes pulse {
                0%, 100% {
                    box-shadow: 0 0 0 0 rgba(255, 106, 0, 0.7);
                }
                50% {
                    box-shadow: 0 0 0 8px rgba(255, 106, 0, 0);
                }
            }
        </style>
    </head>
    <body>
    <nav class="navbar navbar-expand-lg sl-navbar mb-4">
        <div class="container-fluid">
            <a class="navbar-brand fw-semibold" href="<?php echo BASE_URL; ?>/admin/dashboard.php">
                EVSU Smart Library — Admin
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="adminNavbar">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/admin/dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/admin/students_create.php">Students</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/admin/books.php">Books</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/admin/borrow_requests.php">
                            Borrow Requests
                            <?php 
                                $pdo_badge = db_connect();
                                $pending_count = (int) $pdo_badge->query("SELECT COUNT(*) FROM borrow_requests WHERE status = 'pending'")->fetchColumn();
                                if ($pending_count > 0): 
                            ?>
                                <span class="notification-badge"><?php echo $pending_count; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/admin/approved.php">Approved</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/admin/qr_scan.php">QR Scan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/admin/returns.php">Returned</a>
                    </li>
                    <li class="nav-item dropdown ms-lg-3">
                        <a class="nav-link dropdown-toggle navbar-text" href="#" id="adminUserDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin', ENT_QUOTES, 'UTF-8'); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="adminUserDropdown">
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/admin/change_password.php">Change Password</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/admin/logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <main class="container mb-5">
    <?php
}

function admin_render_footer(): void
{
    ?>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
    <?php
}


<?php
// includes/student_layout.php
// Shared layout for student pages (header and footer with EVSU theme).

declare(strict_types=1);

function student_render_header(string $page_title = 'Student'): void
{
    $full_title = 'Smart Library | ' . $page_title;
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($full_title, ENT_QUOTES, 'UTF-8'); ?></title>
        <link rel="icon" type="image/png" href="<?php echo BASE_URL; ?>/assets/images/favicon.png">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
        <style>
            :root {
                --sl-primary: <?php echo COLOR_PRIMARY; ?>;
                --sl-accent: <?php echo COLOR_ACCENT; ?>;
                --sl-light: <?php echo COLOR_LIGHT; ?>;
            }
            body { background-color: #f5f5f5; font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
            .sl-navbar { background: linear-gradient(135deg, var(--sl-primary), #4a0000); }
            .sl-navbar .navbar-brand, .sl-navbar .nav-link, .sl-navbar .navbar-text { color: var(--sl-light) !important; }
            .sl-navbar .nav-link.active { border-bottom: 2px solid var(--sl-accent); }
            .sl-card { border-radius: 12px; border: none; box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08); }
            .btn-sl-primary { background-color: var(--sl-primary); color: var(--sl-light); border: none; }
            .btn-sl-primary:hover { background-color: #5c0000; color: var(--sl-light); }
            .btn-sl-accent { background-color: var(--sl-accent); color: #000; border: none; }
            .btn-sl-accent:hover { background-color: #c19b2f; color: #000; }
            .sl-page-header { border-left: 4px solid var(--sl-accent); padding-left: 0.75rem; }
        </style>
    </head>
    <body>
    <nav class="navbar navbar-expand-lg sl-navbar mb-4">
        <div class="container-fluid">
            <a class="navbar-brand fw-semibold" href="<?php echo BASE_URL; ?>/student/dashboard.php">EVSU Smart Library</a>
            <button class="navbar-toggler navbar-dark" type="button" data-bs-toggle="collapse" data-bs-target="#studentNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="studentNavbar">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/student/dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/student/choose_books.php">Borrow Books</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/student/shelves.php">My Shelves</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/student/my_borrow_books.php">My Borrow Books</a>
                    </li>
                    <li class="nav-item dropdown ms-lg-3">
                        <a class="nav-link dropdown-toggle navbar-text" href="#" id="studentUserDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <?php echo htmlspecialchars($_SESSION['student_name'] ?? 'Student', ENT_QUOTES, 'UTF-8'); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="studentUserDropdown">
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/student/profile.php">My Profile</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/student/change_password.php">Change Password</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/student/logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <main class="container mb-5">
    <?php
}

function student_render_footer(): void
{
    ?>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
    <?php
}


<?php
// includes/student_layout.php
// Shared layout for student pages (header and footer with EVSU theme).

declare(strict_types=1);

function student_render_header(string $page_title = 'Student'): void
{
    $full_title = 'Smart Library | ' . $page_title;
    $student_name = (string)($_SESSION['student_name'] ?? 'Student');
    $student_initial = strtoupper(substr($student_name, 0, 1));
    $student_profile_picture = null;

    if (!empty($_SESSION['student_id'])) {
        try {
            $pdo_avatar = db_connect();
            $stmt_avatar = $pdo_avatar->prepare('SELECT profile_picture FROM students WHERE id = :id LIMIT 1');
            $stmt_avatar->execute([':id' => (int)$_SESSION['student_id']]);
            $profile_picture = $stmt_avatar->fetchColumn();

            if (is_string($profile_picture) && $profile_picture !== '') {
                $student_profile_picture = basename($profile_picture);
            }
        } catch (Throwable $e) {
            error_log('student navbar profile picture lookup failed: ' . $e->getMessage());
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($full_title, ENT_QUOTES, 'UTF-8'); ?></title>
        <link rel="icon" type="image/png" href="<?php echo BASE_URL; ?>/assets/images/favicon.png">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
        <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/responsive.css">
        <style>
            :root {
                --sl-primary: <?php echo COLOR_PRIMARY; ?>;
                --sl-accent: <?php echo COLOR_ACCENT; ?>;
                --sl-light: <?php echo COLOR_LIGHT; ?>;
            }
            body { background-color: #f5f5f5; font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
            .sl-navbar { background: linear-gradient(135deg, #720000, #4d0000); }
            .sl-navbar .navbar-brand, .sl-navbar .nav-link, .sl-navbar .navbar-text { color: var(--sl-light) !important; }
            .sl-navbar .nav-link.active { border-bottom: 2px solid var(--sl-accent); }
            .sl-brand-logo { width: 36px; height: 36px; object-fit: contain; background: #fff; border-radius: 50%; padding: 2px; }
            .sl-user-avatar {
                width: 28px;
                height: 28px;
                border-radius: 50%;
                object-fit: cover;
                border: 2px solid rgba(246, 198, 0, 0.75);
                background: #fff;
                color: #720000;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-size: 0.78rem;
                font-weight: 800;
                flex: 0 0 auto;
            }
            .sl-card { border-radius: 12px; border: none; box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08); }
            .btn-sl-primary { background-color: var(--sl-primary); color: var(--sl-light); border: none; }
            .btn-sl-primary:hover { background-color: #520000; color: var(--sl-light); }
            .btn-sl-accent { background-color: var(--sl-accent); color: #000; border: none; }
            .btn-sl-accent:hover { background-color: #d8ad00; color: #000; }
            .sl-page-header { border-left: 4px solid var(--sl-accent); padding-left: 0.75rem; }
        </style>
        <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/modern-minimal.css">
        <style>
            .sl-student .sl-navbar .navbar-brand::before {
                content: none !important;
                display: none !important;
            }
        </style>
    </head>
    <body class="sl-student">
    <nav class="navbar navbar-expand-lg sl-navbar mb-4">
        <div class="container-fluid">
            <a class="navbar-brand fw-semibold d-flex align-items-center" href="<?php echo BASE_URL; ?>/student/dashboard.php">
                <img src="<?php echo BASE_URL; ?>/assets/images/evsu-logo.png" alt="EVSU logo" class="sl-brand-logo me-2">
                <span>EVSU Smart Library</span>
            </a>
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
                        <a class="nav-link dropdown-toggle navbar-text d-inline-flex align-items-center gap-2" href="#" id="studentUserDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <?php if ($student_profile_picture !== null): ?>
                                <img
                                    src="<?php echo BASE_URL; ?>/uploads/profiles/<?php echo htmlspecialchars($student_profile_picture, ENT_QUOTES, 'UTF-8'); ?>"
                                    alt="Profile picture"
                                    class="sl-user-avatar"
                                >
                            <?php else: ?>
                                <span class="sl-user-avatar" aria-hidden="true">
                                    <?php echo htmlspecialchars($student_initial, ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            <?php endif; ?>
                            <span><?php echo htmlspecialchars($student_name, ENT_QUOTES, 'UTF-8'); ?></span>
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
    <script>
        document.querySelectorAll('main table').forEach(function (table) {
            if (!table.closest('.table-responsive')) {
                const wrapper = document.createElement('div');
                wrapper.className = 'table-responsive';
                table.parentNode.insertBefore(wrapper, table);
                wrapper.appendChild(table);
            }

            const wrapper = table.closest('.table-responsive');
            if (wrapper) {
                wrapper.classList.add('sl-card-table');
            }

            const headers = Array.from(table.querySelectorAll('thead th')).map(function (header) {
                return header.textContent.replace(/\s+/g, ' ').trim();
            });

            table.querySelectorAll('tbody tr').forEach(function (row) {
                const cells = Array.from(row.children).filter(function (cell) {
                    return cell.tagName && cell.tagName.toLowerCase() === 'td';
                });

                if (cells.length === 1 && Number(cells[0].getAttribute('colspan') || 1) > 1) {
                    cells[0].setAttribute('data-label', '');
                    return;
                }

                cells.forEach(function (cell, index) {
                    if (!cell.hasAttribute('data-label')) {
                        cell.setAttribute('data-label', headers[index] || '');
                    }
                });
            });
        });
    </script>
    </body>
    </html>
    <?php
}


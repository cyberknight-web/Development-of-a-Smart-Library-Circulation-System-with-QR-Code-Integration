<?php
// index.php
// Landing page: links to Admin and Student portals.

declare(strict_types=1);

require_once __DIR__ . '/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EVSU Smart Library</title>

    <link rel="icon" type="image/png" href="assets/images/favicon.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/responsive.css">

    <style>
        :root {
            --sl-primary: <?php echo COLOR_PRIMARY; ?>;
            --sl-accent: <?php echo COLOR_ACCENT; ?>;
            --sl-light: <?php echo COLOR_LIGHT; ?>;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(circle at top, #ffe8c2 0, #f5f5f5 50%);
            font-family: system-ui, -apple-system, sans-serif;
        }

        .portal-card {
            max-width: 320px;
            border-radius: 16px;
            border: none;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.12);
            overflow: hidden;
            text-decoration: none;
            color: inherit;
            display: block;
            transition: transform 0.2s;
        }

        .portal-card:hover {
            transform: translateY(-4px);
            color: inherit;
        }

        .portal-card .card-body {
            padding: 1.5rem;
        }

        .btn-sl {
            background-color: var(--sl-primary);
            color: var(--sl-light);
            border: none;
        }

        .btn-sl:hover {
            background-color: #5c0000;
            color: var(--sl-light);
        }
    </style>
</head>
<body>
    <div class="container text-center">
        <h1 class="mb-2">EVSU Smart Library</h1>
        <p class="text-muted mb-4">Choose your portal</p>

        <div class="row g-4 justify-content-center">
            <div class="col-sm-6 col-md-5">
                <a href="<?php echo BASE_URL; ?>/admin/login.php" class="portal-card card">
                    <div class="card-body">
                        <h5 class="card-title">Administrator</h5>
                        <p class="text-muted small">Manage books, students, and borrow requests.</p>
                        <span class="btn btn-sl">Admin Login</span>
                    </div>
                </a>
            </div>

            <div class="col-sm-6 col-md-5">
                <a href="<?php echo BASE_URL; ?>/student/login.php" class="portal-card card">
                    <div class="card-body">
                        <h5 class="card-title">Student</h5>
                        <p class="text-muted small">Borrow books and get your QR code.</p>
                        <span class="btn btn-sl">Student Login</span>
                    </div>
                </a>
            </div>
        </div>
    </div>
</body>
</html>

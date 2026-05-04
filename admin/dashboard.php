<?php

declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/admin_auth.php';
require_once __DIR__ . '/../includes/admin_layout.php';

require_admin_login();

$pdo = db_connect();

// Count unique books (titles), not copies
$total_books = (int) $pdo->query("SELECT COUNT(*) FROM books")->fetchColumn();
$total_books_copies = (int) $pdo->query("SELECT COALESCE(SUM(copies_total), 0) FROM books")->fetchColumn();
$available_books = (int) $pdo->query("SELECT COUNT(*) FROM books WHERE copies_available > 0")->fetchColumn();
$available_books_copies = (int) $pdo->query("SELECT COALESCE(SUM(copies_available), 0) FROM books")->fetchColumn();
$borrowed_books = (int) $pdo->query("SELECT COUNT(*) FROM books WHERE copies_available = 0")->fetchColumn();

$total_students = (int) $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$pending_requests = (int) $pdo->query("SELECT COUNT(*) FROM borrow_requests WHERE status = 'pending'")->fetchColumn();
$approved_requests = (int) $pdo->query("SELECT COUNT(*) FROM borrow_requests WHERE status IN ('approved', 'claimed')")->fetchColumn();
$request_rate_base = $pending_requests + $approved_requests;
// Total book copies returned (sum of quantities in returned requests)
$returned_count = (int) $pdo->query(
    "SELECT COALESCE(SUM(bri.quantity), 0)
     FROM borrow_request_items bri
     JOIN borrow_requests br ON br.id = bri.borrow_request_id
     WHERE br.status = 'returned'"
)->fetchColumn();


$stmt_daily = $pdo->prepare(
    "SELECT DATE(requested_at) AS d, COUNT(*) AS cnt
     FROM borrow_requests
     WHERE requested_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
     GROUP BY DATE(requested_at)
     ORDER BY d ASC"
);
$stmt_daily->execute();
$borrows_by_day = $stmt_daily->fetchAll(PDO::FETCH_KEY_PAIR);

$stmt_monthly = $pdo->prepare(
    "SELECT DATE_FORMAT(requested_at, '%Y-%m') AS m, COUNT(*) AS cnt
     FROM borrow_requests
     WHERE requested_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
     GROUP BY DATE_FORMAT(requested_at, '%Y-%m')
     ORDER BY m ASC"
);
$stmt_monthly->execute();
$borrows_by_month = $stmt_monthly->fetchAll(PDO::FETCH_KEY_PAIR);

// Build last 30 days labels and values (fill missing days with 0)
$chart_days_labels = [];
$chart_days_data = [];
for ($i = 29; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i days"));
    $chart_days_labels[] = $d;
    $chart_days_data[] = (int) ($borrows_by_day[$d] ?? 0);
}

$chart_months_labels = [];
$chart_months_data = [];
for ($i = 11; $i >= 0; $i--) {
    $m = date('Y-m', strtotime("-$i months"));
    $chart_months_labels[] = $m;
    $chart_months_data[] = (int) ($borrows_by_month[$m] ?? 0);
}


$book_status_available = $available_books;
$book_status_borrowed = $borrowed_books;
$book_status_reserved = 0;
$book_status_overdue = 0;

$availability_rate = $total_books > 0 ? (int) round(($available_books / $total_books) * 100) : 0;
$borrowed_rate = $total_books > 0 ? (int) round(($borrowed_books / $total_books) * 100) : 0;
$pending_rate = $request_rate_base > 0 ? (int) round(($pending_requests / $request_rate_base) * 100) : 0;
$approved_rate = $request_rate_base > 0 ? (int) round(($approved_requests / $request_rate_base) * 100) : 0;

$stmt_top = $pdo->query(
    "SELECT b.title, COALESCE(SUM(bri.quantity), 0) AS times_borrowed
     FROM borrow_request_items bri
     JOIN borrow_requests br ON br.id = bri.borrow_request_id
     JOIN books b ON b.id = bri.book_id
     WHERE br.status IN ('claimed', 'returned')
     GROUP BY b.id, b.title
     ORDER BY times_borrowed DESC
     LIMIT 5"
);
$most_borrowed_books = $stmt_top->fetchAll();

admin_render_header('Dashboard');
?>

<style>
    .sl-dashboard-hero {
        background: linear-gradient(90deg, #FF6A00, #FF8C00, #FFA500);
        border-radius: 14px;
        color: #fff;
        padding: 1.2rem 1.25rem;
        box-shadow: 0 10px 24px rgba(255, 106, 0, 0.2);
    }
    .sl-dashboard-hero .sl-kpi {
        border-left: 1px solid rgba(255, 255, 255, 0.2);
        padding-left: 1rem;
    }
    .sl-dashboard-hero .sl-kpi:first-child {
        border-left: 0;
        padding-left: 0;
    }
    .sl-hero-subtitle {
        color: rgba(255, 255, 255, 0.9);
    }
    .sl-hero-kpi-label {
        color: rgba(255, 255, 255, 0.88);
    }
    .sl-hero-kpi-value {
        color: #ffffff;
    }
    .sl-stat-card .label {
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #6c757d;
    }
    .sl-stat-card .value {
        font-size: 1.55rem;
        font-weight: 700;
        line-height: 1.1;
        color: #212529;
    }
    .sl-stat-card .meta {
        font-size: 0.82rem;
        color: #6c757d;
    }
    .sl-section-title {
        font-size: 1rem;
        font-weight: 600;
        color: #212529;
        margin-bottom: 0.85rem;
    }
    .sl-chart-shell {
        border: 1px solid #edf0f4;
        border-radius: 12px;
        padding: 0.6rem;
        background: #fff;
    }
    .sl-analytics-card {
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(255, 106, 0, 0.12);
        background: linear-gradient(145deg, #ffffff, #fff8f3);
        box-shadow: 0 14px 32px rgba(255, 106, 0, 0.08);
    }
    .sl-analytics-card::before {
        content: "";
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at top right, rgba(255, 165, 0, 0.1), transparent 45%);
        pointer-events: none;
    }
    .sl-analytics-header {
        position: relative;
        z-index: 1;
    }
    .sl-analytics-title {
        display: flex;
        align-items: center;
        gap: 0.45rem;
        font-size: 1rem;
        letter-spacing: 0.02em;
    }
    .sl-analytics-title .dot {
        width: 9px;
        height: 9px;
        border-radius: 50%;
        background: #FF8C00;
        box-shadow: 0 0 0 4px rgba(255, 106, 0, 0.12);
    }
    .sl-analytics-sub {
        font-size: 0.8rem;
        color: #6c757d;
    }
    .sl-analytics-tabs {
        border-bottom: 1px solid rgba(255, 106, 0, 0.14);
    }
    .sl-analytics-tabs .nav-link {
        border: 0;
        color: #6c757d;
        font-weight: 600;
        padding: 0.45rem 0.85rem;
        border-radius: 999px;
        margin-right: 0.35rem;
        transition: all 0.2s ease;
    }
    .sl-analytics-tabs .nav-link.active {
        background: rgba(255, 106, 0, 0.12);
        color: #FF8C00;
        box-shadow: inset 0 0 0 1px rgba(255, 106, 0, 0.18);
    }
    .sl-chart-shell.sl-analytics-shell {
        border: 1px solid rgba(255, 106, 0, 0.12);
        background:
            linear-gradient(180deg, rgba(255, 106, 0, 0.03), rgba(255, 255, 255, 0.7)),
            #fff;
        padding: 0.85rem;
    }
    .sl-chart-shell.sl-analytics-shell canvas {
        filter: drop-shadow(0 8px 16px rgba(255, 106, 0, 0.12));
    }
    .sl-status-legend {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.5rem;
        margin-top: 0.75rem;
    }
    .sl-status-pill {
        border: 1px solid #edf0f4;
        border-radius: 999px;
        padding: 0.3rem 0.6rem;
        font-size: 0.76rem;
        color: #495057;
        background: #fff;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }
    .sl-status-pill .swatch {
        width: 9px;
        height: 9px;
        border-radius: 50%;
    }
    .sl-soft-progress {
        height: 7px;
        background: #f0f2f5;
    }
    .sl-table-clean thead th {
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        color: #6c757d;
        border-bottom-width: 1px;
    }
</style>

<div class="row mb-4">
    <div class="col-12">
        <div class="sl-page-header">
            <h2 class="mb-1">Admin Dashboard</h2>
            <p class="text-muted mb-0">Overview of Smart Library circulation activities.</p>
        </div>
    </div>
</div>

<section class="mb-4">
    <div class="sl-dashboard-hero">
        <div class="row g-3 align-items-center">
            <div class="col-lg-6">
                <h4 class="mb-1 fw-semibold">Welcome to Smart Library Admin</h4>
                <p class="mb-0 small sl-hero-subtitle">Monitor circulation, review requests, and track collection health in one view.</p>
            </div>
            <div class="col-lg-6">
                <div class="row g-3 text-center">
                    <div class="col-4">
                        <div class="sl-kpi">
                            <div class="small sl-hero-kpi-label">Books</div>
                            <div class="h5 mb-0 fw-bold sl-hero-kpi-value"><?php echo (int) $available_books; ?></div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="sl-kpi">
                            <div class="small sl-hero-kpi-label">Students</div>
                            <div class="h5 mb-0 fw-bold sl-hero-kpi-value"><?php echo (int) $total_students; ?></div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="sl-kpi">
                            <div class="small sl-hero-kpi-label">Approved Requests</div>
                            <div class="h5 mb-0 fw-bold sl-hero-kpi-value"><?php echo (int) $approved_requests; ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="mb-4">
    <h5 class="sl-section-title">Summary Metrics</h5>
    <div class="row g-3">
        <div class="col-6 col-md-4 col-lg-3">
            <div class="card sl-card h-100">
                <div class="card-body py-3 sl-stat-card">
                    <div class="label">Total Books Copies</div>
                    <div class="value"><?php echo (int) $total_books_copies; ?></div>
                    <div class="meta">All copies in inventory</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-3">
            <div class="card sl-card h-100">
                <div class="card-body py-3 sl-stat-card">
                    <div class="label">Books Available</div>
                    <div class="value text-success"><?php echo (int) $available_books_copies; ?></div>
                    <div class="progress sl-soft-progress mt-2">
                        <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $availability_rate; ?>%;"></div>
                    </div>
                    <div class="meta mt-2"><?php echo $availability_rate; ?>% in stock</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-3">
            <div class="card sl-card h-100">
                <div class="card-body py-3 sl-stat-card">
                    <div class="label">Books Fully Borrowed</div>
                    <div class="value text-primary"><?php echo (int) $borrowed_books; ?></div>
                    <div class="progress sl-soft-progress mt-2">
                        <div class="progress-bar" role="progressbar" style="width: <?php echo $borrowed_rate; ?>%; background-color: var(--sl-primary);"></div>
                    </div>
                    <div class="meta mt-2"><?php echo $borrowed_rate; ?>% out of stock</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-3">
            <div class="card sl-card h-100">
                <div class="card-body py-3 sl-stat-card">
                    <div class="label">Total Students</div>
                    <div class="value"><?php echo (int) $total_students; ?></div>
                    <div class="meta">Registered borrowers</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-3">
            <div class="card sl-card h-100">
                <div class="card-body py-3 sl-stat-card">
                    <div class="label">Pending Requests</div>
                    <div class="value text-warning"><?php echo (int) $pending_requests; ?></div>
                    <div class="meta"><?php echo $pending_rate; ?>% of all requests</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-3">
            <div class="card sl-card h-100">
                <div class="card-body py-3 sl-stat-card">
                    <div class="label">Approved / Claimed</div>
                    <div class="value text-info"><?php echo (int) $approved_requests; ?></div>
                    <div class="meta"><?php echo $approved_rate; ?>% fulfillment rate</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-3">
            <div class="card sl-card h-100">
                <div class="card-body py-3 sl-stat-card">
                    <div class="label">Returned Copies</div>
                    <div class="value text-secondary"><?php echo (int) $returned_count; ?></div>
                    <div class="meta">Completed circulation</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-3">
            <div class="card sl-card h-100">
                <div class="card-body py-3 sl-stat-card">
                    <div class="label">Quick Actions</div>
                    <div class="d-flex gap-2 flex-wrap mt-2">
                        <a href="<?php echo BASE_URL; ?>/admin/books.php" class="btn btn-outline-secondary btn-sm">Books</a>
                        <a href="<?php echo BASE_URL; ?>/admin/borrow_requests.php" class="btn btn-outline-secondary btn-sm">Requests</a>
                        <a href="<?php echo BASE_URL; ?>/admin/qr_scan.php" class="btn btn-outline-secondary btn-sm">QR Scan</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Quick links (existing cards) -->
<div class="row g-3 mb-4">
    <div class="col-md-6 col-lg-3">
        <div class="card sl-card h-100">
            <div class="card-body">
                <h5 class="card-title fw-semibold">Books</h5>
                <p class="card-text text-muted small mb-3">Import and manage all library titles.</p>
                <a href="<?php echo BASE_URL; ?>/admin/books.php" class="btn btn-sl-primary btn-sm">Manage Books</a>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card sl-card h-100">
            <div class="card-body">
                <h5 class="card-title fw-semibold">Borrow Requests</h5>
                <p class="card-text text-muted small mb-3">Review student borrow requests and approve via email + QR.</p>
                <a href="<?php echo BASE_URL; ?>/admin/borrow_requests.php" class="btn btn-sl-primary btn-sm">View Requests</a>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card sl-card h-100">
            <div class="card-body">
                <h5 class="card-title fw-semibold">QR Scanning</h5>
                <p class="card-text text-muted small mb-3">Scan student QR codes for claiming and returning books.</p>
                <a href="<?php echo BASE_URL; ?>/admin/qr_scan.php" class="btn btn-sl-primary btn-sm">Open Scanner</a>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card sl-card h-100">
            <div class="card-body">
                <h5 class="card-title fw-semibold">Returned Book Copies</h5>
                <p class="card-text text-muted small mb-3">View all borrow transactions that have been returned.</p>
                <a href="<?php echo BASE_URL; ?>/admin/returns.php" class="btn btn-sl-primary btn-sm">View All Returned</a>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- 2. Borrowing Activity Chart -->
    <div class="col-lg-8">
        <div class="card sl-card sl-analytics-card h-100">
            <div class="card-header bg-transparent border-0 pt-3 pb-0 sl-analytics-header">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h5 class="mb-0 fw-semibold sl-analytics-title"><span class="dot"></span>Borrowing Activity</h5>
                    <span class="sl-analytics-sub">Live circulation trend</span>
                </div>
                <ul class="nav nav-tabs card-header-tabs mt-2 sl-analytics-tabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" id="tab-day" data-bs-toggle="tab" data-bs-target="#chart-day" type="button">By Day (30 days)</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="tab-month" data-bs-toggle="tab" data-bs-target="#chart-month" type="button">By Month (12 months)</button>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content sl-chart-shell sl-analytics-shell">
                    <div class="tab-pane fade show active" id="chart-day">
                        <div class="chart-container" style="height: 280px;">
                            <canvas id="chartBorrowsDay"></canvas>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="chart-month">
                        <div class="chart-container" style="height: 280px;">
                            <canvas id="chartBorrowsMonth"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Book Status Pie Chart -->
    <div class="col-lg-4">
        <div class="card sl-card sl-analytics-card h-100">
            <div class="card-header bg-transparent border-0 pt-3 pb-0 sl-analytics-header">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h5 class="mb-0 fw-semibold sl-analytics-title"><span class="dot"></span>Book Status</h5>
                    <span class="sl-analytics-sub">Availability snapshot</span>
                </div>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                <div class="chart-container sl-chart-shell sl-analytics-shell" style="height: 260px; width: 100%; max-width: 300px;">
                    <canvas id="chartBookStatus"></canvas>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 pt-0 pb-3 px-3">
                <div class="sl-status-legend">
                    <span class="sl-status-pill"><span class="swatch" style="background:#198754;"></span>Available</span>
                    <span class="sl-status-pill"><span class="swatch" style="background:#0d6efd;"></span>Borrowed</span>
                    <span class="sl-status-pill"><span class="swatch" style="background:#ffc107;"></span>Reserved</span>
                    <span class="sl-status-pill"><span class="swatch" style="background:#dc3545;"></span>Overdue</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 4. Most Borrowed Books -->
<div class="row mt-2">
    <div class="col-12">
        <div class="card sl-card">
            <div class="card-header bg-transparent border-0 pt-3 pb-0">
                <h5 class="mb-0 fw-semibold">Most Borrowed Books</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 sl-table-clean">
                        <thead>
                            <tr>
                                <th style="width: 72px;">Rank</th>
                                <th>Book Title</th>
                                <th class="text-end">Times Borrowed</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($most_borrowed_books)): ?>
                                <?php $rank = 1; ?>
                                <?php foreach ($most_borrowed_books as $row): ?>
                                    <tr>
                                        <td>
                                            <span class="badge rounded-pill text-bg-light border">#<?php echo $rank; ?></span>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="text-end fw-semibold"><?php echo (int) $row['times_borrowed']; ?></td>
                                    </tr>
                                    <?php $rank++; ?>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-muted text-center py-4">No borrow history yet.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
    'use strict';

    var daysLabels = <?php echo json_encode($chart_days_labels); ?>;
    var daysData = <?php echo json_encode($chart_days_data); ?>;
    var monthsLabels = <?php echo json_encode($chart_months_labels); ?>;
    var monthsData = <?php echo json_encode($chart_months_data); ?>;

    var chartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: 'rgba(33, 37, 41, 0.95)',
                borderColor: 'rgba(128, 0, 0, 0.35)',
                borderWidth: 1,
                displayColors: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { stepSize: 1, color: '#6c757d' },
                grid: { color: 'rgba(128, 0, 0, 0.08)' }
            },
            x: {
                ticks: { color: '#6c757d', maxRotation: 0, autoSkip: true, maxTicksLimit: 8 },
                grid: { display: false }
            }
        }
    };

    new Chart(document.getElementById('chartBorrowsDay'), {
        type: 'bar',
        data: {
            labels: daysLabels,
            datasets: [{
                label: 'Borrow requests',
                data: daysData,
                backgroundColor: 'rgba(128, 0, 0, 0.6)',
                borderColor: 'rgba(128, 0, 0, 1)',
                borderWidth: 1
            }]
        },
        options: chartOptions
    });

    new Chart(document.getElementById('chartBorrowsMonth'), {
        type: 'bar',
        data: {
            labels: monthsLabels,
            datasets: [{
                label: 'Borrow requests',
                data: monthsData,
                backgroundColor: 'rgba(212, 175, 55, 0.6)',
                borderColor: 'rgba(212, 175, 55, 1)',
                borderWidth: 1
            }]
        },
        options: chartOptions
    });

    var pieData = {
        labels: ['Available', 'Borrowed', 'Reserved', 'Overdue'],
        datasets: [{
            data: [
                <?php echo (int) $book_status_available; ?>,
                <?php echo (int) $book_status_borrowed; ?>,
                <?php echo (int) $book_status_reserved; ?>,
                <?php echo (int) $book_status_overdue; ?>
            ],
            backgroundColor: ['#198754', '#0d6efd', '#ffc107', '#dc3545'],
            borderWidth: 1
        }]
    };
    new Chart(document.getElementById('chartBookStatus'), {
        type: 'pie',
        data: pieData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            }
        }
    });
})();
</script>

<?php
admin_render_footer();

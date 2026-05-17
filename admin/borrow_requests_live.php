<?php

declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/admin_auth.php';
require_once __DIR__ . '/../includes/borrow_requests_helper.php';

require_admin_login();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$pdo = db_connect();

if (isset($_GET['count_only'])) {
    echo json_encode([
        'pending_count' => sl_count_pending_borrow_requests($pdo),
    ], JSON_THROW_ON_ERROR);
    exit;
}

$requests = sl_get_pending_borrow_requests($pdo);
$request_book_availability = sl_get_request_book_availability($pdo, $requests);

echo json_encode([
    'pending_count' => count($requests),
    'rows_html' => sl_render_borrow_request_rows_html($requests, $request_book_availability),
], JSON_THROW_ON_ERROR);

<?php

declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/student_auth.php';
require_once __DIR__ . '/../includes/student_requests_helper.php';

require_student_login();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$pdo = db_connect();
$student_id = (int)$_SESSION['student_id'];
$requests = sl_get_student_active_requests($pdo, $student_id);

echo json_encode([
    'active_count' => count($requests),
    'rows_html' => sl_render_student_active_request_rows_html($requests),
], JSON_THROW_ON_ERROR);

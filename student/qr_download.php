<?php
// student/qr_download.php
// Proxies QR image from API and forces download as PNG (same-origin so download attribute works).

declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/student_auth.php';

require_student_login();

$token = trim($_GET['token'] ?? '');
if ($token === '') {
    header('HTTP/1.1 400 Bad Request');
    exit('Missing token');
}

$pdo = db_connect();
$student_id = (int)$_SESSION['student_id'];

$stmt = $pdo->prepare(
    "SELECT br.qr_token FROM borrow_requests br WHERE br.qr_token = :token AND br.student_id = :sid LIMIT 1"
);
$stmt->execute([':token' => $token, ':sid' => $student_id]);
$record = $stmt->fetch();
if (!$record) {
    header('HTTP/1.1 403 Forbidden');
    exit('Invalid or unauthorized token');
}

$qr_api_url = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&format=png&data=' . urlencode($token);

$image = @file_get_contents($qr_api_url);
if ($image === false) {
    header('HTTP/1.1 502 Bad Gateway');
    exit('Could not generate QR image');
}

$filename = 'borrow-qr-' . preg_replace('/[^a-zA-Z0-9_-]/', '-', $token) . '.png';

header('Content-Type: image/png');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . strlen($image));
header('Cache-Control: private, no-cache');
echo $image;

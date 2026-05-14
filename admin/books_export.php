<?php
// admin/books_export.php
// Export books data (all/available) as CSV that can be opened in Excel.

declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/admin_auth.php';

require_admin_login();

$pdo = db_connect();

$scope = $_GET['scope'] ?? 'available'; // 'available' | 'all'
$format = $_GET['format'] ?? 'csv';     // currently only 'csv'
$search = trim($_GET['q'] ?? '');

if (!in_array($scope, ['available', 'all'], true)) {
    http_response_code(400);
    echo 'Invalid scope.';
    exit;
}
if ($format !== 'csv') {
    http_response_code(400);
    echo 'Invalid format.';
    exit;
}

$where = [];
$params = [];

if ($scope === 'available') {
    $where[] = "status = 'available'";
}

if ($search !== '') {
    $where[] = '(title LIKE :q OR author LIKE :q OR category LIKE :q OR accession_no LIKE :q OR isbn LIKE :q)';
    $params[':q'] = '%' . $search . '%';
}

$sql = 'SELECT * FROM books';
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ($scope === 'available') ? ' ORDER BY title ASC' : ' ORDER BY created_at DESC';

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('Books export DB error: ' . $e->getMessage());
    http_response_code(500);
    echo 'Export failed. Please try again later.';
    exit;
}

// Column order for export (covers all columns in books table).
$columns = [
    'id',
    'accession_no',
    'isbn',
    'title',
    'author',
    'publisher',
    'publication_year',
    'category',
    'location',
    'copies_total',
    'copies_available',
    'status',
    'imported_from_excel',
    'created_at',
    'updated_at',
];

$date = (new DateTimeImmutable('now'))->format('Y-m-d');
$filename = 'books_' . $scope . '_' . $date . '.csv';

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('X-Content-Type-Options: nosniff');

$out = fopen('php://output', 'w');
if ($out === false) {
    http_response_code(500);
    echo 'Export failed.';
    exit;
}

// Excel-friendly UTF-8 BOM.
fwrite($out, "\xEF\xBB\xBF");

// Header row.
fputcsv($out, $columns);

foreach ($rows as $row) {
    $line = [];
    foreach ($columns as $col) {
        $line[] = $row[$col] ?? '';
    }
    fputcsv($out, $line);
}

fclose($out);
exit;


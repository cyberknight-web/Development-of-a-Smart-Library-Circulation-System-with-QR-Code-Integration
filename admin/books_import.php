<?php

declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/admin_auth.php';

require_admin_login();


function download_books_template(): void
{
    $filename = 'books_import_template.csv';

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');
    if ($output === false) {
        http_response_code(500);
        echo 'Unable to generate template file.';
        exit;
    }

    fputcsv(
        $output,
        [
            'accession_no',
            'isbn',
            'title',
            'author',
            'publisher',
            'publication_year',
            'category',
            'location',
            'copies',
        ]
    );
    fclose($output);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['download_template'])) {
    download_books_template();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/admin/books.php');
    exit;
}

if (!isset($_FILES['books_excel']) || $_FILES['books_excel']['error'] !== UPLOAD_ERR_OK) {
    header('Location: ' . BASE_URL . '/admin/books.php?status=import_error');
    exit;
}

$tmp_path = $_FILES['books_excel']['tmp_name'] ?? null;
$original_name = $_FILES['books_excel']['name'] ?? '';

if (!$tmp_path || !is_uploaded_file($tmp_path)) {
    header('Location: ' . BASE_URL . '/admin/books.php?status=import_error');
    exit;
}

$extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
if ($extension !== 'csv') {
    header('Location: ' . BASE_URL . '/admin/books.php?status=import_error');
    exit;
}

$handle = fopen($tmp_path, 'r');
if ($handle === false) {
    header('Location: ' . BASE_URL . '/admin/books.php?status=import_error');
    exit;
}

fgetcsv($handle);

$pdo = db_connect();
$pdo->beginTransaction();

$inserted = 0;

$stmt = $pdo->prepare(
    'INSERT INTO books (
        accession_no,
        isbn,
        title,
        author,
        publisher,
        publication_year,
        category,
        location,
        copies_total,
        copies_available,
        status,
        imported_from_excel
     ) VALUES (
        :accession_no,
        :isbn,
        :title,
        :author,
        :publisher,
        :publication_year,
        :category,
        :location,
        :copies_total,
        :copies_available,
        :status,
        1
     )'
);

try {
    while (($row = fgetcsv($handle)) !== false) {
        if (!$row) {
            continue;
        }

        $accession_no = isset($row[0]) ? trim((string)$row[0]) : '';
        $isbn = isset($row[1]) ? trim((string)$row[1]) : '';
        $title = isset($row[2]) ? trim((string)$row[2]) : '';
        $author = isset($row[3]) ? trim((string)$row[3]) : '';
        $publisher = isset($row[4]) ? trim((string)$row[4]) : '';
        $publication_year_raw = isset($row[5]) ? trim((string)$row[5]) : '';
        $category = isset($row[6]) ? trim((string)$row[6]) : '';
        $location = isset($row[7]) ? trim((string)$row[7]) : '';
        $copies_raw = isset($row[8]) ? trim((string)$row[8]) : '0';

        if ($title === '') {
            continue;
        }

        $copies = (int)$copies_raw;

        $publication_year = $publication_year_raw !== '' ? (int)$publication_year_raw : null;

        $status = $copies > 0 ? 'available' : 'unavailable';

        $stmt->execute([
            ':accession_no'     => $accession_no,
            ':isbn'             => $isbn,
            ':title'            => $title,
            ':author'           => $author,
            ':publisher'        => $publisher,
            ':publication_year' => $publication_year,
            ':category'         => $category,
            ':location'         => $location,
            ':copies_total'     => max(0, $copies),
            ':copies_available' => max(0, $copies),
            ':status'           => $status,
        ]);

        $inserted++;
    }

    fclose($handle);

    if ($inserted > 0) {
        $pdo->commit();
        header('Location: ' . BASE_URL . '/admin/books.php?status=import_success');
        exit;
    }

    $pdo->rollBack();
    header('Location: ' . BASE_URL . '/admin/books.php?status=import_error');
    exit;
} catch (Throwable $e) {
    fclose($handle);
    $pdo->rollBack();
    error_log('Books import failed: ' . $e->getMessage());
    header('Location: ' . BASE_URL . '/admin/books.php?status=import_error');
    exit;
}


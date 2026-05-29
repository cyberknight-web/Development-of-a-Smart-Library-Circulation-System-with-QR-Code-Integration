<?php
// Utilities for book cover uploads.

const SMARTLIBRARY_BOOK_COVER_MAX_BYTES = 5242880;

class SmartlibraryBookCoverUploadException extends RuntimeException
{
}

function smartlibrary_ensure_book_cover_columns(PDO $pdo): void
{
    $stmt = $pdo->query("SHOW COLUMNS FROM books LIKE 'cover_image'");
    if (!$stmt || !$stmt->fetch()) {
        $pdo->exec("ALTER TABLE books ADD cover_image varchar(255) DEFAULT NULL AFTER location");
    }

    $table_check = $pdo->query("SHOW TABLES LIKE 'books_restore_bin'");
    if ($table_check && $table_check->fetchColumn()) {
        $stmt = $pdo->query("SHOW COLUMNS FROM books_restore_bin LIKE 'cover_image'");
        if (!$stmt || !$stmt->fetch()) {
            $pdo->exec("ALTER TABLE books_restore_bin ADD cover_image varchar(255) DEFAULT NULL AFTER location");
        }
    }
}

function smartlibrary_book_cover_upload_dir(): string
{
    return dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'book_covers';
}

function smartlibrary_book_cover_url(?string $filename): ?string
{
    $filename = trim((string)$filename);
    if ($filename === '') {
        return null;
    }

    return BASE_URL . '/uploads/book_covers/' . rawurlencode(basename($filename));
}

function smartlibrary_save_book_cover_upload(array $file, int $book_id): ?string
{
    $error = (int)($file['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($error === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($error !== UPLOAD_ERR_OK) {
        throw new SmartlibraryBookCoverUploadException('Book cover upload failed.');
    }
    if ((int)($file['size'] ?? 0) > SMARTLIBRARY_BOOK_COVER_MAX_BYTES) {
        throw new SmartlibraryBookCoverUploadException('Book cover file is too large.');
    }

    $tmp_name = (string)($file['tmp_name'] ?? '');
    if ($tmp_name === '' || !is_uploaded_file($tmp_name)) {
        throw new SmartlibraryBookCoverUploadException('Invalid book cover upload.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($tmp_name);
    $extensions = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];
    if (!isset($extensions[$mime])) {
        throw new SmartlibraryBookCoverUploadException('Invalid book cover file type.');
    }

    $upload_dir = smartlibrary_book_cover_upload_dir();
    if (!is_dir($upload_dir) && !mkdir($upload_dir, 0755, true)) {
        throw new SmartlibraryBookCoverUploadException('Book cover upload directory could not be created.');
    }

    $filename = sprintf(
        'book_%d_%s_%s.%s',
        $book_id,
        date('YmdHis'),
        bin2hex(random_bytes(4)),
        $extensions[$mime]
    );
    $destination = $upload_dir . DIRECTORY_SEPARATOR . $filename;

    if (!move_uploaded_file($tmp_name, $destination)) {
        throw new SmartlibraryBookCoverUploadException('Book cover file could not be saved.');
    }

    return $filename;
}

function smartlibrary_delete_book_cover_file(?string $filename): void
{
    $filename = basename(trim((string)$filename));
    if ($filename === '') {
        return;
    }

    $path = smartlibrary_book_cover_upload_dir() . DIRECTORY_SEPARATOR . $filename;
    if (is_file($path)) {
        unlink($path);
    }
}

<?php
// config.php
// Global configuration for Smart Library system (database, app constants).


declare(strict_types=1);

const DB_HOST = 'localhost';
const DB_NAME = 'u173310863_smartlibrarydb';
const DB_USER = 'u173310863_encrypted'; // your Hostinger DB username
const DB_PASS = 'SystemProject23';
const DB_CHARSET = 'utf8mb4';

// Base URL is built from the current request so it works from both:
// - Laptop: http://localhost/smartlibrary
// - Phone (LAN): http://192.168.x.x/smartlibrary
// Redirects and links will use the same host you used to open the page.
$base_path = '';

// Detect if running inside XAMPP (htdocs)
if (strpos(__DIR__, 'htdocs') !== false) {
    $base_path = '/smartlibrary';
}

if (!defined('BASE_URL')) {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    define('BASE_URL', $scheme . '://' . $host . $base_path);
}
// Application timezone for all PHP and DB timestamps (avoids approved_at/returned_at being off by hours).
// Use a valid PHP timezone, e.g. Asia/Manila, America/New_York.
const APP_TIMEZONE = 'Asia/Manila';
date_default_timezone_set(APP_TIMEZONE);

// EVSU theme colors (maroon, white, gold).
const COLOR_PRIMARY = '#800000'; // maroon
const COLOR_ACCENT = '#d4af37';  // gold
const COLOR_LIGHT = '#ffffff';   // white

// Mail (for forgot password). Install PHPMailer: composer require phpmailer/phpmailer
const MAIL_FROM_EMAIL = 'noreply@example.com';
const MAIL_FROM_NAME = 'EVSU Smart Library';
const MAIL_SMTP_HOST = 'smtp.gmail.com';       // e.g. 'smtp.gmail.com'
const MAIL_SMTP_PORT = 587;
const MAIL_SMTP_USER = 'your_email@gmail.com';
const MAIL_SMTP_PASS = 'your_app_password';
const MAIL_SMTP_SECURE = 'tls';  // tls or ssl

function db_connect(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        // Use app timezone for CURRENT_TIMESTAMP and date functions in MySQL.
        $tz = new DateTimeZone(APP_TIMEZONE);
        $now = new DateTime('now', $tz);
        $offset = $now->format('P'); // e.g. +08:00
        $pdo->exec("SET time_zone = '" . $offset . "'");
    } catch (PDOException $e) {
        http_response_code(500);
        echo 'Database connection failed. Please contact the administrator.';
        error_log('DB connection error: ' . $e->getMessage());
        exit;
    }

    return $pdo;
}


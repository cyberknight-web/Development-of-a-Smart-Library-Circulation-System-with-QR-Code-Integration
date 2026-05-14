<?php
// includes/student_auth.php
// Helpers for student authentication and session handling.

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

const STUDENT_REMEMBER_COOKIE = 'student_remember_token';
const STUDENT_REMEMBER_DAYS = 30;

function ensure_student_remember_tokens_table(PDO $pdo): void
{
    $sql = "
        CREATE TABLE IF NOT EXISTS student_remember_tokens (
          id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
          student_id INT UNSIGNED NOT NULL,
          token_hash CHAR(64) NOT NULL UNIQUE,
          expires_at DATETIME NOT NULL,
          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          CONSTRAINT fk_student_remember_tokens_student
            FOREIGN KEY (student_id) REFERENCES students(id)
            ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";

    $pdo->exec($sql);
}

function student_remember_cookie_options(int $expires): array
{
    return [
        'expires' => $expires,
        'path' => '/',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => 'Lax',
    ];
}

function student_set_remember_cookie(string $token, int $expires): void
{
    setcookie(STUDENT_REMEMBER_COOKIE, $token, student_remember_cookie_options($expires));
}

function student_delete_remember_cookie(): void
{
    setcookie(STUDENT_REMEMBER_COOKIE, '', student_remember_cookie_options(time() - 3600));
    unset($_COOKIE[STUDENT_REMEMBER_COOKIE]);
}

function student_store_remember_token(PDO $pdo, int $student_id): void
{
    ensure_student_remember_tokens_table($pdo);

    $token = bin2hex(random_bytes(32));
    $token_hash = hash('sha256', $token);
    $expires = time() + (STUDENT_REMEMBER_DAYS * 24 * 60 * 60);
    $expires_at = date('Y-m-d H:i:s', $expires);

    $delete = $pdo->prepare('DELETE FROM student_remember_tokens WHERE student_id = :student_id');
    $delete->execute([':student_id' => $student_id]);

    $insert = $pdo->prepare(
        'INSERT INTO student_remember_tokens (student_id, token_hash, expires_at)
         VALUES (:student_id, :token_hash, :expires_at)'
    );
    $insert->execute([
        ':student_id' => $student_id,
        ':token_hash' => $token_hash,
        ':expires_at' => $expires_at,
    ]);

    student_set_remember_cookie($token, $expires);
}

function student_delete_current_remember_token(PDO $pdo): void
{
    $token = $_COOKIE[STUDENT_REMEMBER_COOKIE] ?? '';

    if (is_string($token) && $token !== '') {
        ensure_student_remember_tokens_table($pdo);

        $delete = $pdo->prepare('DELETE FROM student_remember_tokens WHERE token_hash = :token_hash');
        $delete->execute([':token_hash' => hash('sha256', $token)]);
    }

    student_delete_remember_cookie();
}

function student_start_session_from_row(array $student): void
{
    student_login(
        (int)$student['id'],
        (string)$student['name'],
        (string)$student['student_id'],
        (string)$student['course'],
        (string)$student['section'],
        (string)$student['email'],
        (string)$student['username']
    );
}

function student_try_remember_login(): bool
{
    if (!empty($_SESSION['student_id'])) {
        return true;
    }

    $token = $_COOKIE[STUDENT_REMEMBER_COOKIE] ?? '';

    if (!is_string($token) || $token === '') {
        return false;
    }

    try {
        $pdo = db_connect();
        ensure_student_remember_tokens_table($pdo);

        $stmt = $pdo->prepare(
            "SELECT s.id, s.name, s.student_id, s.course, s.section, s.email, s.username
             FROM student_remember_tokens srt
             JOIN students s ON s.id = srt.student_id
             WHERE srt.token_hash = :token_hash AND srt.expires_at > NOW()
             LIMIT 1"
        );
        $stmt->execute([':token_hash' => hash('sha256', $token)]);
        $student = $stmt->fetch();

        if (!$student) {
            student_delete_current_remember_token($pdo);
            return false;
        }

        session_regenerate_id(true);
        student_start_session_from_row($student);
        return true;
    } catch (Throwable $e) {
        error_log('student_try_remember_login failed: ' . $e->getMessage());
        return false;
    }
}

function student_is_logged_in(): bool
{
    return isset($_SESSION['student_id']) || student_try_remember_login();
}

function require_student_login(): void
{
    if (!student_is_logged_in()) {
        header('Location: ' . BASE_URL . '/student/login.php');
        exit;
    }
}

function student_login(
    int $student_id,
    string $name,
    string $student_code,
    string $course,
    string $section,
    string $email,
    string $username
): void {
    $_SESSION['student_id'] = $student_id;
    $_SESSION['student_name'] = $name;
    $_SESSION['student_code'] = $student_code;
    $_SESSION['student_course'] = $course;
    $_SESSION['student_section'] = $section;
    $_SESSION['student_email'] = $email;
    $_SESSION['student_username'] = $username;
    $_SESSION['student_session'] = [
        'id' => $student_id,
        'name' => $name,
        'student_id' => $student_code,
        'course' => $course,
        'section' => $section,
        'email' => $email,
        'username' => $username,
        'logged_in_at' => time(),
    ];
}

function student_logout(): void
{
    try {
        student_delete_current_remember_token(db_connect());
    } catch (Throwable $e) {
        error_log('student_logout remember token cleanup failed: ' . $e->getMessage());
        student_delete_remember_cookie();
    }

    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

/** Get current borrow cart (book ids). Max 3. */
function student_get_cart(): array
{
    $cart = $_SESSION['borrow_cart'] ?? [];
    return is_array($cart) ? array_slice(array_values($cart), 0, 3) : [];
}

function student_sync_cart_with_books(PDO $pdo): array
{
    $cart = student_get_cart();

    if (empty($cart)) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($cart), '?'));
    $stmt = $pdo->prepare("SELECT id FROM books WHERE id IN ($placeholders)");
    $stmt->execute($cart);
    $valid_ids = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    $valid_lookup = array_fill_keys($valid_ids, true);

    $synced_cart = array_values(array_filter(
        $cart,
        fn($book_id) => isset($valid_lookup[(int)$book_id])
    ));

    if (count($synced_cart) !== count($cart)) {
        student_set_cart($synced_cart);
    }

    return $synced_cart;
}

function student_set_cart(array $book_ids): void
{
    $_SESSION['borrow_cart'] = array_slice($book_ids, 0, 3);
}

function student_add_to_cart(int $book_id): bool
{
    $cart = student_get_cart();
    if (in_array($book_id, $cart, true) || count($cart) >= 3) {
        return false;
    }
    $cart[] = $book_id;
    student_set_cart($cart);
    return true;
}

function student_remove_from_cart(int $book_id): void
{
    $cart = array_values(array_filter(student_get_cart(), fn($id) => (int)$id !== $book_id));
    student_set_cart($cart);
}

function student_clear_cart(): void
{
    $_SESSION['borrow_cart'] = [];
}

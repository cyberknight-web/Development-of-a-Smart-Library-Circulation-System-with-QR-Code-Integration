<?php
// includes/student_auth.php
// Helpers for student authentication and session handling.

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function student_is_logged_in(): bool
{
    return isset($_SESSION['student_id']);
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
}

function student_logout(): void
{
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

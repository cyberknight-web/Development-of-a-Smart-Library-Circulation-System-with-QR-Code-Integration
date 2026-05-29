<?php
// student/logout.php
// End student session and redirect to login.

declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/student_auth.php';

$timed_out = isset($_GET['timeout']);

student_logout(!$timed_out);
header('Location: ' . BASE_URL . '/student/login.php' . ($timed_out ? '?timeout=1' : ''));
exit;

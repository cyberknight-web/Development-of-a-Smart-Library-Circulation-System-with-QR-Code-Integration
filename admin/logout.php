<?php

declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/admin_auth.php';

admin_logout();

header('Location: ' . BASE_URL . '/admin/login.php');
exit;


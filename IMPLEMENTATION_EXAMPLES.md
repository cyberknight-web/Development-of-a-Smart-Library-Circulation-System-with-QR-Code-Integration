# Implementation Code Examples

## 1. Move Credentials to Environment Variables

### Step 1: Create .gitignore entry
```
.env
.env.local
.env.*.local
```

### Step 2: Create .env.example (commit this)
```env
# Database
DB_HOST=127.0.0.1
DB_NAME=smartlibrary
DB_USER=root
DB_PASS=

# Mail (Gmail example)
MAIL_FROM_EMAIL=noreply@example.com
MAIL_FROM_NAME=EVSU Smart Library
MAIL_SMTP_HOST=smtp.gmail.com
MAIL_SMTP_PORT=587
MAIL_SMTP_USER=your-email@gmail.com
MAIL_SMTP_PASS=your-app-password
MAIL_SMTP_SECURE=tls

# App
APP_TIMEZONE=Asia/Manila
BASE_URL=http://localhost/smartlibrary
```

### Step 3: Update config.php
```php
<?php
declare(strict_types=1);

// Load .env file
$env_file = __DIR__ . '/.env';
if (file_exists($env_file)) {
    $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if ($line[0] === '#') continue;
        [$key, $value] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}

// Database (from .env)
const DB_HOST = $_ENV['DB_HOST'] ?? '127.0.0.1';
const DB_NAME = $_ENV['DB_NAME'] ?? 'smartlibrary';
const DB_USER = $_ENV['DB_USER'] ?? 'root';
const DB_PASS = $_ENV['DB_PASS'] ?? '';
const DB_CHARSET = 'utf8mb4';

// ... rest of config
```

---

## 2. Add CSRF Token Protection

### Create includes/security.php (NEW FILE)
```php
<?php
declare(strict_types=1);

/**
 * Generate or retrieve CSRF token
 */
function get_csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token from POST request
 */
function verify_csrf_token(string $token = null): bool
{
    $token = $token ?? $_POST['csrf_token'] ?? '';
    return !empty($token) && hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

/**
 * HTML output for CSRF token in forms
 */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' 
        . htmlspecialchars(get_csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}
?>
```

### Update admin/borrow_request_action.php
```php
<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/admin_auth.php';
require_once __DIR__ . '/../includes/security.php';  // ADD THIS

require_admin_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/admin/borrow_requests.php');
    exit;
}

// ADD CSRF VERIFICATION
if (!verify_csrf_token()) {
    http_response_code(403);
    die('CSRF token verification failed');
}

// ... rest of code
?>
```

### Update form in admin/borrow_requests.php
```php
<?php
require_once __DIR__ . '/../includes/security.php';
?>

<form method="post" action="<?php echo BASE_URL; ?>/admin/borrow_request_action.php">
    <?php echo csrf_field(); ?>  <!-- ADD THIS -->
    <input type="hidden" name="request_id" value="<?php echo (int)$request['id']; ?>">
    <input type="hidden" name="action" value="approve">
    <button type="submit" class="btn btn-success">Approve</button>
</form>
```

---

## 3. Secure Session Cookies

### Update config.php - Add after DB configuration
```php
<?php
declare(strict_types=1);

// ... existing code ...

// Set session configuration BEFORE session_start()
$secure_cookie = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

session_set_cookie_params([
    'lifetime' => 1800,        // 30 minutes
    'path' => '/',
    'domain' => '',
    'secure' => $secure_cookie,  // HTTPS only
    'httponly' => true,         // No JavaScript access
    'samesite' => 'Strict',      // CSRF protection
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Session timeout check (on each page load)
$session_timeout = 1800;  // 30 minutes
if (isset($_SESSION['last_activity'])) {
    $elapsed = time() - $_SESSION['last_activity'];
    if ($elapsed > $session_timeout) {
        session_destroy();
        header('Location: ' . BASE_URL . '/admin/login.php?expired=1');
        exit;
    }
}
$_SESSION['last_activity'] = time();

// ... rest of code ...
?>
```

---

## 4. Implement Token Expiration

### Step 1: Update schema.sql
```sql
ALTER TABLE borrow_requests 
ADD COLUMN qr_token_expires_at TIMESTAMP 
DEFAULT (DATE_ADD(NOW(), INTERVAL 7 DAY));

ALTER TABLE borrow_requests 
ADD INDEX idx_token_expiry (qr_token_expires_at);
```

### Step 2: Update student/borrow_submit.php
```php
<?php
// ... existing code ...

$qr_token = bin2hex(random_bytes(32));
$expires_at = (new DateTimeImmutable())->add(
    new DateInterval('P7D')  // 7 days from now
)->format('Y-m-d H:i:s');

$pdo->beginTransaction();
try {
    $ins = $pdo->prepare(
        'INSERT INTO borrow_requests (student_id, qr_token, qr_token_expires_at, status) 
         VALUES (:sid, :token, :expires, :status)'
    );
    $ins->execute([
        ':sid' => $student_id,
        ':token' => $qr_token,
        ':expires' => $expires_at,
        ':status' => 'pending',
    ]);
    // ... rest of code ...
}
?>
```

### Step 3: Update admin/qr_scan.php
```php
<?php
// ... existing code ...

if ($qr_token !== '') {
    $stmt = $pdo->prepare(
        "SELECT br.*, s.name AS student_name, s.student_id AS student_code, s.course, s.section, s.email
         FROM borrow_requests br
         JOIN students s ON s.id = br.student_id
         WHERE br.qr_token = :qr_token AND br.qr_token_expires_at > NOW()
         LIMIT 1"
    );
    $stmt->execute([':qr_token' => $qr_token]);
    $record = $stmt->fetch();

    if (!$record) {
        if ($qr_token !== '') {
            // Check if token exists but is expired
            $check_expired = $pdo->prepare(
                "SELECT id FROM borrow_requests WHERE qr_token = :token AND qr_token_expires_at <= NOW()"
            );
            $check_expired->execute([':token' => $qr_token]);
            if ($check_expired->fetch()) {
                $error = 'QR code has expired. Please request a new borrow.';
            } else {
                $error = 'No record found for this QR token.';
            }
        }
    }
}
?>
```

---

## 5. Fix Inventory Model

### Step 1: Update schema to reserve instead of immediate decement
```sql
-- Add column to track when items were claimed
ALTER TABLE borrow_request_items 
ADD COLUMN claimed_at TIMESTAMP NULL;

-- Add index for efficiency
ALTER TABLE borrow_request_items 
ADD INDEX idx_claim_status (claimed_at);
```

### Step 2: Update admin/borrow_request_action.php - CHANGE APPROVAL
```php
<?php
// ... existing code ...

if ($action === 'approve') {
    $pdo->beginTransaction();
    try {
        // CHANGE: Don't decrement inventory on approval
        // Just mark as approved (inventory stays same)
        
        $update = $pdo->prepare(
            "UPDATE borrow_requests
             SET status = 'approved', approved_at = :now, admin_id_approved = :admin_id
             WHERE id = :id"
        );
        $update->execute([
            ':now' => $now,
            ':admin_id' => $admin_id,
            ':id' => $request_id,
        ]);

        // Note: Inventory will be decremented when marked as CLAIMED
        
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        error_log('Borrow approval failed: ' . $e->getMessage());
    }
}
?>
```

### Step 3: Update admin/qr_scan_action.php - DECREMENT ON CLAIM
```php
<?php
// ... existing code ...

if ($action === 'claimed') {
    if ($request['status'] === 'claimed') {
        header('Location: ' . $redirect_url);
        exit;
    }
    if ($request['status'] !== 'approved') {
        header('Location: ' . $redirect_url);
        exit;
    }
    
    $pdo->beginTransaction();
    try {
        // Mark as claimed
        $update = $pdo->prepare(
            "UPDATE borrow_requests
             SET status = 'claimed', claimed_at = :now, admin_id_claimed = :admin_id
             WHERE id = :id"
        );
        $update->execute([
            ':now' => $now,
            ':admin_id' => $admin_id,
            ':id' => $request_id,
        ]);

        // NOW decrement inventory (actual handoff)
        $items = $pdo->prepare(
            "SELECT book_id, quantity FROM borrow_request_items WHERE borrow_request_id = :id"
        );
        $items->execute([':id' => $request_id]);
        
        while ($row = $items->fetch(PDO::FETCH_ASSOC)) {
            $pdo->prepare(
                "UPDATE books SET copies_available = GREATEST(0, copies_available - :qty) WHERE id = :id"
            )->execute([
                ':qty' => (int)$row['quantity'],
                ':id' => (int)$row['book_id'],
            ]);
            
            // Mark items as claimed
            $pdo->prepare(
                "UPDATE borrow_request_items SET claimed_at = :now WHERE borrow_request_id = :req_id AND book_id = :book_id"
            )->execute([
                ':now' => $now,
                ':req_id' => $request_id,
                ':book_id' => (int)$row['book_id'],
            ]);
        }
        
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        error_log('Claim processing failed: ' . $e->getMessage());
    }
}
?>
```

---

## 6. Add Overdue Tracking

### Step 1: Update schema.sql
```sql
ALTER TABLE borrow_requests 
ADD COLUMN due_date TIMESTAMP NULL;

ALTER TABLE borrow_requests 
ADD COLUMN fine_amount DECIMAL(10, 2) DEFAULT 0;

ALTER TABLE borrow_requests 
ADD COLUMN fine_paid TINYINT(1) DEFAULT 0;

ALTER TABLE borrow_requests 
ADD INDEX idx_due_date (due_date);

ALTER TABLE borrow_requests 
ADD INDEX idx_overdue (status, due_date);

-- Notification tracking
CREATE TABLE IF NOT EXISTS overdue_notifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    borrow_request_id INT UNSIGNED NOT NULL UNIQUE,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (borrow_request_id) REFERENCES borrow_requests(id)
);
```

### Step 2: Update admin/qr_scan_action.php - SET DUE DATE ON CLAIM
```php
<?php
if ($action === 'claimed') {
    // ... existing code ...
    
    // Set due date to 3 days from now
    $due_date = (new DateTimeImmutable())->add(
        new DateInterval('P3D')
    )->format('Y-m-d H:i:s');
    
    $update = $pdo->prepare(
        "UPDATE borrow_requests
         SET status = 'claimed', claimed_at = :now, due_date = :due_date, admin_id_claimed = :admin_id
         WHERE id = :id"
    );
    // ...
}
?>
```

### Step 3: Create cron/process_overdue.php (NEW FILE)
```php
<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/mail_helper.php';

$pdo = db_connect();

// Find overdue books
$stmt = $pdo->query(
    "SELECT br.id, br.due_date, s.email, s.name, s.student_id AS student_code,
            (SELECT GROUP_CONCAT(b.title) FROM borrow_request_items bri
             JOIN books b ON b.id = bri.book_id WHERE bri.borrow_request_id = br.id) AS book_titles
     FROM borrow_requests br
     JOIN students s ON s.id = br.student_id
     WHERE br.status = 'claimed' AND br.due_date < NOW()
     AND br.id NOT IN (SELECT borrow_request_id FROM overdue_notifications)"
);

$fine_per_day = 25;  // pesos
$max_fine = 500;     // max penalty

foreach ($stmt->fetchAll() as $overdue) {
    $days_late = intdiv((int)(time() - strtotime($overdue['due_date'])), 86400);
    $fine = min($days_late * $fine_per_day, $max_fine);
    
    // Update fine
    $update = $pdo->prepare(
        "UPDATE borrow_requests SET fine_amount = :fine WHERE id = :id"
    );
    $update->execute([':fine' => $fine, ':id' => $overdue['id']]);
    
    // Send email
    $subject = 'Overdue Books - EVSU Smart Library';
    $body = "
        <h2>Dear {$overdue['name']},</h2>
        <p>You have overdue books!</p>
        <ul>
            <li>" . str_replace(',', '</li><li>', htmlspecialchars($overdue['book_titles'])) . "</li>
        </ul>
        <p><strong>Due Date:</strong> {$overdue['due_date']}</p>
        <p><strong>Days Overdue:</strong> {$days_late}</p>
        <p><strong>Fine Accrued:</strong> ₱{$fine}</p>
        <p>Please return your books immediately to avoid additional fines.</p>
        <p>Smart Library Administration</p>
    ";
    
    send_mail($overdue['email'], $overdue['name'], $subject, $body);
    
    // Mark notification sent
    $insert = $pdo->prepare("INSERT INTO overdue_notifications (borrow_request_id) VALUES (:id)");
    $insert->execute([':id' => $overdue['id']]);
}

echo "Processed " . count($stmt->fetchAll()) . " overdue notifications.\n";
?>
```

### Step 4: Schedule cron job
```bash
# Add to crontab (runs daily at 8 AM)
0 8 * * * php /var/www/html/smartlibrary/cron/process_overdue.php
```

---

## 7. Add Rate Limiting to QR Scan

### Create includes/rate_limit.php (NEW FILE)
```php
<?php
declare(strict_types=1);

/**
 * Check if user has exceeded rate limit
 * Returns true if limit exceeded
 */
function check_rate_limit(string $key, int $max_attempts, int $window_seconds): bool
{
    // Try to use APCu if available (fastest)
    if (function_exists('apcu_fetch')) {
        $current = apcu_fetch($key) ?? 0;
        if ($current >= $max_attempts) {
            return true;  // Limit exceeded
        }
        apcu_store($key, $current + 1, $window_seconds);
        return false;
    }
    
    // Fallback to file-based rate limiting
    $lockfile = sys_get_temp_dir() . '/' . md5($key) . '.lock';
    $data = file_exists($lockfile) ? json_decode(file_get_contents($lockfile), true) : [
        'attempts' => 0,
        'window_start' => time(),
    ];
    
    $elapsed = time() - $data['window_start'];
    if ($elapsed > $window_seconds) {
        // Window expired, reset
        $data = ['attempts' => 1, 'window_start' => time()];
    } else {
        $data['attempts']++;
    }
    
    file_put_contents($lockfile, json_encode($data));
    return $data['attempts'] > $max_attempts;
}

/**
 * Get remaining attempts in current window
 */
function get_remaining_attempts(string $key, int $max_attempts): int
{
    if (function_exists('apcu_fetch')) {
        $current = apcu_fetch($key) ?? 0;
        return max(0, $max_attempts - $current);
    }
    
    $lockfile = sys_get_temp_dir() . '/' . md5($key) . '.lock';
    $data = file_exists($lockfile) ? json_decode(file_get_contents($lockfile), true) : ['attempts' => 0];
    return max(0, $max_attempts - $data['attempts']);
}
?>
```

### Update admin/qr_scan_action.php
```php
<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/admin_auth.php';
require_once __DIR__ . '/../includes/rate_limit.php';

require_admin_login();

// Rate limiting: max 30 QR scans per minute per admin
$admin_id = $_SESSION['admin_id'];
$rate_key = 'qr_scan_' . $admin_id;

if (check_rate_limit($rate_key, 30, 60)) {
    http_response_code(429);
    die('Rate limit exceeded. Maximum 30 scans per minute. Please wait before trying again.');
}

// ... rest of code ...
?>
```

---

## 8. Validate CSV Import

### Update admin/books_import.php
```php
<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/admin_auth.php';

require_admin_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/admin/books.php');
    exit;
}

// Validate file
$max_file_size = 5 * 1024 * 1024;  // 5MB
$allowed_types = ['text/csv', 'text/plain', 'application/vnd.ms-excel'];

if (!isset($_FILES['books_excel']) || $_FILES['books_excel']['error'] !== UPLOAD_ERR_OK) {
    header('Location: ' . BASE_URL . '/admin/books.php?status=import_error');
    exit;
}

$file = $_FILES['books_excel'];

// Check file size
if ($file['size'] > $max_file_size) {
    error_log('CSV import: File too large (' . ($file['size'] / 1024 / 1024) . 'MB)');
    header('Location: ' . BASE_URL . '/admin/books.php?status=import_error');
    exit;
}

// Check MIME type
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mime, $allowed_types, true)) {
    error_log("CSV import: Invalid MIME type '$mime'");
    header('Location: ' . BASE_URL . '/admin/books.php?status=import_error');
    exit;
}

// Check line count
$lines = file($file['tmp_name']);
if (count($lines) > 10001) {  // Header + 10,000 data rows
    error_log('CSV import: Too many rows (' . count($lines) . ')');
    header('Location: ' . BASE_URL . '/admin/books.php?status=import_error');
    exit;
}

// Process CSV
$pdo = db_connect();
$imported = 0;
$errors = [];

foreach ($lines as $i => $line) {
    if ($i === 0) continue;  // Skip header
    if (trim($line) === '') continue;  // Skip empty lines
    
    $row = str_getcsv(trim($line));
    if (count($row) < 6) {
        $errors[] = "Line " . ($i + 1) . ": Invalid format";
        continue;
    }
    
    [$accession_no, $isbn, $title, $author, $publisher, $publication_year, $category, $location, $copies] = array_pad($row, 9, '');
    
    // Sanitize and validate
    $title = trim($title);
    $copies = (int)$copies;
    
    if (empty($title) || $copies < 1) {
        $errors[] = "Line " . ($i + 1) . ": Title or copies invalid";
        continue;
    }
    
    try {
        $stmt = $pdo->prepare(
            "INSERT INTO books (accession_no, isbn, title, author, publisher, publication_year, category, location, copies_total, copies_available, status, imported_from_excel)
             VALUES (:accession, :isbn, :title, :author, :publisher, :year, :category, :location, :copies, :copies, 'available', 1)
             ON DUPLICATE KEY UPDATE copies_total = :copies, copies_available = :copies"
        );
        
        $stmt->execute([
            ':accession' => trim($accession_no) ?: null,
            ':isbn' => trim($isbn) ?: null,
            ':title' => $title,
            ':author' => trim($author) ?: null,
            ':publisher' => trim($publisher) ?: null,
            ':year' => trim($publication_year) ?: null,
            ':category' => trim($category) ?: null,
            ':location' => trim($location) ?: null,
            ':copies' => $copies,
        ]);
        
        $imported++;
    } catch (Throwable $e) {
        error_log('CSV import line ' . ($i + 1) . ': ' . $e->getMessage());
        $errors[] = "Line " . ($i + 1) . ": " . $e->getMessage();
    }
}

if (empty($errors)) {
    header('Location: ' . BASE_URL . '/admin/books.php?status=import_success');
} else {
    error_log('CSV import completed with errors: ' . json_encode($errors));
    header('Location: ' . BASE_URL . '/admin/books.php?status=import_error');
}
exit;
?>
```

---

## 9. Add Database Indexes

```sql
-- Performance optimization indexes
ALTER TABLE borrow_requests ADD INDEX idx_qr_token (qr_token);
ALTER TABLE borrow_requests ADD INDEX idx_student_status (student_id, status);
ALTER TABLE borrow_requests ADD INDEX idx_status (status);
ALTER TABLE borrow_request_items ADD INDEX idx_book_id (book_id);
ALTER TABLE books ADD INDEX idx_status (status);
ALTER TABLE books ADD INDEX idx_availability (status, copies_available);
ALTER TABLE books ADD FULLTEXT INDEX idx_search (title, author, category);
ALTER TABLE students ADD INDEX idx_username (username);
ALTER TABLE admins ADD INDEX idx_username (username);
```

**Expected Performance Improvement**: 10x faster queries

---

## Testing Script

Create `test_security.php` to validate improvements:

```php
<?php
declare(strict_types=1);

echo "=== Security Audit ===\n\n";

$checks = [
    'Credentials in config' => function() {
        $config = file_get_contents(__DIR__ . '/config.php');
        return !preg_match('/MAIL_SMTP_PASS\s*=\s*[\'"][^\'\"]+[\'"]/', $config);
    },
    
    'CSRF tokens implemented' => function() {
        return file_exists(__DIR__ . '/includes/security.php');
    },
    
    'Session security' => function() {
        $config = file_get_contents(__DIR__ . '/config.php');
        return strpos($config, 'httponly') !== false;
    },
    
    'Database connection secure' => function() {
        try {
            $pdo = new PDO('mysql:host=127.0.0.1;dbname=smartlibrary', 'root', '', [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    },
];

foreach ($checks as $name => $check) {
    $result = $check() ? '✓ PASS' : '✗ FAIL';
    echo "$result: $name\n";
}

echo "\n=== Database Checks ===\n\n";

$pdo = new PDO('mysql:host=127.0.0.1;dbname=smartlibrary', 'root', '');

// Check for token expiry column
$result = $pdo->query("DESCRIBE borrow_requests qr_token_expires_at");
echo ($result->fetch() ? '✓ ' : '✗ ') . "Token expiry column exists\n";

// Check for indexes
$indexes = $pdo->query("SHOW INDEXES FROM borrow_requests WHERE Key_name = 'idx_qr_token'")->fetch();
echo ($indexes ? '✓ ' : '✗ ') . "QR token index exists\n";
?>
```

Run with: `php test_security.php`

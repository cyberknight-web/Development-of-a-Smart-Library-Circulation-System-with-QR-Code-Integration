<?php

declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/admin_auth.php';
require_once __DIR__ . '/../includes/admin_layout.php';
require_once __DIR__ . '/../includes/mail_helper.php';
require_once __DIR__ . '/../includes/password_policy.php';

require_admin_login();

$errors = [];
$field_errors = [
    'student_id' => '',
    'email' => '',
];
$success_message = null;
$warning_message = null;

$first_name = '';
$middle_name = '';
$last_name = '';
$student_id = '';
$course = '';
$section = '';
$email = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $middle_name = trim($_POST['middle_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $name = trim(implode(' ', array_filter([$first_name, $middle_name, $last_name], static fn($part) => $part !== '')));
    $student_id = trim($_POST['student_id'] ?? '');
    $course = trim($_POST['course'] ?? '');
    $section = trim($_POST['section'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($first_name === '') {
        $errors[] = 'First name is required.';
    }
    if ($last_name === '') {
        $errors[] = 'Last name is required.';
    }
    if ($student_id === '') {
        $errors[] = 'Student ID is required.';
    } elseif (!preg_match('/^[A-Za-z0-9]{4}-[A-Za-z0-9]{5}$/', $student_id)) {
        $errors[] = 'Student ID must use the format xxxx-xxxxx.';
    }
    if ($course === '') {
        $errors[] = 'Course is required.';
    }
    if ($section === '') {
        $errors[] = 'Year / Section is required.';
    }
    if ($email === '') {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email format is invalid.';
    } elseif (!preg_match('/^[A-Z0-9._%+\-]+@evsu\.edu\.ph$/i', $email)) {
        $errors[] = 'Student email must be an EVSU email address, for example jasonkinth.arcillas@evsu.edu.ph.';
    }
    if ($username === '') {
        $errors[] = 'Username is required.';
    }
    if ($password === '') {
        $errors[] = 'Password is required.';
    } elseif (!smartlibrary_is_valid_password($password)) {
        $errors[] = smartlibrary_password_policy_message();
    }

    if (!$errors) {
        $pdo = db_connect();

        // Check uniqueness for each field separately so we can report all duplicates.
        $stmt_id = $pdo->prepare('SELECT 1 FROM students WHERE student_id = :student_id LIMIT 1');
        $stmt_id->execute([':student_id' => $student_id]);
        if ($stmt_id->fetch()) {
            $field_errors['student_id'] = 'Student ID must be unique.';
            $errors[] = 'Duplicate Student ID. System blocks submission.';
        }

        $stmt_email = $pdo->prepare('SELECT 1 FROM students WHERE email = :email LIMIT 1');
        $stmt_email->execute([':email' => $email]);
        if ($stmt_email->fetch()) {
            $field_errors['email'] = 'Email must be unique.';
            $errors[] = 'Duplicate Email. System displays validation error.';
        }

        $stmt_username = $pdo->prepare('SELECT 1 FROM students WHERE username = :username LIMIT 1');
        $stmt_username->execute([':username' => $username]);
        if ($stmt_username->fetch()) {
            $errors[] = 'Username already exists. Please use a unique username.';
        }
    }

    if (!$errors) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $insert_stmt = $pdo->prepare(
            'INSERT INTO students (name, student_id, course, section, email, username, password_hash)
             VALUES (:name, :student_id, :course, :section, :email, :username, :password_hash)'
        );

        try {
            $insert_stmt->execute([
                ':name' => $name,
                ':student_id' => $student_id,
                ':course' => $course,
                ':section' => $section,
                ':email' => $email,
                ':username' => $username,
                ':password_hash' => $password_hash,
            ]);

            $subject = 'Smart Library - Student Account Created';
            $body = '<p>Hello ' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . ',</p>';
            $body .= '<p>Your Smart Library student account has been created.</p>';
            $body .= '<p><strong>Login credentials:</strong></p>';
            $body .= '<ul>';
            $body .= '<li><strong>Student ID:</strong> ' . htmlspecialchars($student_id, ENT_QUOTES, 'UTF-8') . '</li>';
            $body .= '<li><strong>Username:</strong> ' . htmlspecialchars($username, ENT_QUOTES, 'UTF-8') . '</li>';
            $body .= '<li><strong>Password:</strong> ' . htmlspecialchars($password, ENT_QUOTES, 'UTF-8') . '</li>';
            $body .= '</ul>';
            $body .= '<p>Please keep these credentials secure and do not share them with anyone.</p>';
            $body .= '<p>Thank you,<br>Smart Library</p>';

            $success_message = 'Student account created successfully.';
            if (send_mail($email, $name, $subject, $body)) {
                $success_message .= ' Login credentials were sent to the student email.';
            } else {
                $warning_message = 'The student account was created, but the credentials email could not be sent. Please check the mail settings and try notifying the student manually.';
                error_log('Student credentials email failed for student #' . $student_id . ' to ' . $email);
            }

            $first_name = $middle_name = $last_name = $student_id = $course = $section = $email = $username = '';
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $message = $e->getMessage();
                if (stripos($message, 'student_id') !== false) {
                    $field_errors['student_id'] = 'Student ID must be unique.';
                    $errors[] = 'Duplicate Student ID. System blocks submission.';
                } elseif (stripos($message, 'email') !== false) {
                    $field_errors['email'] = 'Email must be unique.';
                    $errors[] = 'Duplicate Email. System displays validation error.';
                } else {
                    $errors[] = 'Duplicate account details. Please check the form.';
                }
            } else {
                $errors[] = 'Failed to create student account. Please try again.';
            }
            error_log('Create student error: ' . $e->getMessage());
        }
    }
}

// Fetch recent students for display (Name, Student ID, Course). Optional search by name or student_id.
$pdo = $pdo ?? db_connect();
$search_students = trim($_GET['q'] ?? '');
if ($search_students !== '') {
    $like_students = '%' . $search_students . '%';
    $recent_stmt = $pdo->prepare(
        'SELECT name, student_id, course FROM students WHERE name LIKE :q OR student_id LIKE :q ORDER BY created_at DESC LIMIT 50'
    );
    $recent_stmt->execute([':q' => $like_students]);
} else {
    $recent_stmt = $pdo->prepare(
        'SELECT name, student_id, course FROM students ORDER BY created_at DESC LIMIT 15'
    );
    $recent_stmt->execute();
}
$recent_students = $recent_stmt->fetchAll(PDO::FETCH_ASSOC);

admin_render_header('Create Student Account');
?>

<div class="row mb-4">
    <div class="col-12">
        <div class="sl-page-header">
            <h2 class="mb-1">
                <i class="bi bi-person-plus me-2"></i>Create Student Account
            </h2>
            <p class="text-muted mb-0">
                Register new student accounts. Only administrators can create accounts—students cannot self-register.
            </p>
        </div>
    </div>
</div>

        <div class="row g-4">
    <!-- Form Card -->
    <div class="col-12 col-md-7">
        <div class="card sl-card h-100 shadow-sm">
            <div class="card-header bg-light border-bottom-0 pt-4 pb-3">
                <h5 class="card-title fw-semibold mb-0">
                    <i class="bi bi-form-check text-primary me-2"></i>Student Information
                </h5>
            </div>
            <div class="card-body">
                <?php if ($success_message): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle me-2"></i>
                        <strong>Success!</strong> <?php echo htmlspecialchars($success_message, ENT_QUOTES, 'UTF-8'); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if ($warning_message): ?>
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Warning!</strong> <?php echo htmlspecialchars($warning_message, ENT_QUOTES, 'UTF-8'); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if ($errors): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-circle me-2"></i>
                        <strong>Error!</strong> Please fix the following:
                        <ul class="mb-0 mt-2">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form method="post" novalidate>
                    <!-- Personal Information Section -->
                    <div class="mb-4">
                        <h6 class="text-uppercase text-muted fw-semibold small mb-3">Personal Information</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="first_name" class="form-label fw-semibold">First Name <span class="text-danger">*</span></label>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="first_name"
                                    name="first_name"
                                    placeholder="e.g., John"
                                    value="<?php echo htmlspecialchars($first_name, ENT_QUOTES, 'UTF-8'); ?>"
                                    required
                                >
                            </div>
                            <div class="col-md-4">
                                <label for="middle_name" class="form-label fw-semibold">Middle Name</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="middle_name"
                                    name="middle_name"
                                    placeholder="e.g., Santos"
                                    value="<?php echo htmlspecialchars($middle_name, ENT_QUOTES, 'UTF-8'); ?>"
                                >
                            </div>
                            <div class="col-md-4">
                                <label for="last_name" class="form-label fw-semibold">Last Name <span class="text-danger">*</span></label>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="last_name"
                                    name="last_name"
                                    placeholder="e.g., Dela Cruz"
                                    value="<?php echo htmlspecialchars($last_name, ENT_QUOTES, 'UTF-8'); ?>"
                                    required
                                >
                            </div>
                            <div class="col-md-6">
                                <label for="student_id" class="form-label fw-semibold">Student ID <span class="text-danger">*</span></label>
                                <input
                                    type="text"
                                    class="form-control<?php echo $field_errors['student_id'] ? ' is-invalid' : ''; ?>"
                                    id="student_id"
                                    name="student_id"
                                    placeholder="e.g., 2024-00001"
                                    pattern="^[A-Za-z0-9]{4}-[A-Za-z0-9]{5}$"
                                    title="Use the format xxxx-xxxxx, for example 2024-00001"
                                    value="<?php echo htmlspecialchars($student_id, ENT_QUOTES, 'UTF-8'); ?>"
                                    required
                                >
                                <?php if ($field_errors['student_id']): ?>
                                    <div class="invalid-feedback"><?php echo htmlspecialchars($field_errors['student_id'], ENT_QUOTES, 'UTF-8'); ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-4">
                                <label for="course" class="form-label fw-semibold">Course <span class="text-danger">*</span></label>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="course"
                                    name="course"
                                    placeholder="e.g., BSCS"
                                    value="<?php echo htmlspecialchars($course, ENT_QUOTES, 'UTF-8'); ?>"
                                    required
                                >
                            </div>
                            <div class="col-md-4">
                                <label for="section" class="form-label fw-semibold">Year / Section <span class="text-danger">*</span></label>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="section"
                                    name="section"
                                    placeholder="e.g., 1st Year A"
                                    value="<?php echo htmlspecialchars($section, ENT_QUOTES, 'UTF-8'); ?>"
                                    required
                                >
                            </div>
                            <div class="col-md-4">
                                <label for="email" class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                                <input
                                    type="email"
                                    class="form-control<?php echo $field_errors['email'] ? ' is-invalid' : ''; ?>"
                                    id="email"
                                    name="email"
                                    placeholder="e.g., jasonkinth.arcillas@evsu.edu.ph"
                                    pattern="^[A-Za-z0-9._%+\-]+@evsu\.edu\.ph$"
                                    title="Use an EVSU email address, for example jasonkinth.arcillas@evsu.edu.ph"
                                    value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>"
                                    required
                                >
                                <?php if ($field_errors['email']): ?>
                                    <div class="invalid-feedback"><?php echo htmlspecialchars($field_errors['email'], ENT_QUOTES, 'UTF-8'); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Account Information Section -->
                    <div class="mb-4">
                        <h6 class="text-uppercase text-muted fw-semibold small mb-3">Account Credentials</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="username" class="form-label fw-semibold">Username <span class="text-danger">*</span></label>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="username"
                                    name="username"
                                    placeholder="e.g., johndoe"
                                    value="<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>"
                                    required
                                >
                            </div>
                            <div class="col-md-6">
                                <label for="password" class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input
                                        type="password"
                                        class="form-control"
                                        id="password"
                                        name="password"
                                        placeholder="Enter a secure password"
                                        minlength="8"
                                        pattern="(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{8,}"
                                        title="<?php echo htmlspecialchars(smartlibrary_password_policy_message(), ENT_QUOTES, 'UTF-8'); ?>"
                                        required
                                    >
                                </div>
                                <div class="form-check mt-2">
                                    <input type="checkbox" class="form-check-input" id="showPasswordCheck" onclick="myFunction()">
                                    <label class="form-check-label" for="showPasswordCheck">
                                        Show Password
                                    </label>
                                </div>
                            </div>
                            <div class="col-12">
                                <small class="text-muted d-block mt-2">
                                    <i class="bi bi-info-circle me-1"></i>
                                    <?php echo htmlspecialchars(smartlibrary_password_policy_message(), ENT_QUOTES, 'UTF-8'); ?>
                                </small>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Action Buttons -->
                    <div class="d-flex gap-2 justify-content-end">
                        <button type="reset" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-clockwise me-2"></i>Clear Form
                        </button>
                        <button type="submit" class="btn btn-sl-primary">
                            <i class="bi bi-check-lg me-2"></i>Save Student Account
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Recent Students Card -->
    <div class="col-12 col-md-5">
        <div class="card sl-card h-100 shadow-sm">
            <div class="card-header bg-light border-bottom-0 pt-4 pb-3">
                <h5 class="card-title fw-semibold mb-0">
                    <i class="bi bi-clock-history text-secondary me-2"></i>Recent Accounts Created
                </h5>
            </div>
            <div class="card-body d-flex flex-column">
                <form method="get" action="<?php echo BASE_URL; ?>/admin/students_create.php" class="mb-3">
                    <div class="input-group input-group-sm">
                        <input
                            type="search"
                            class="form-control"
                            name="q"
                            placeholder="Search by name or ID..."
                            value="<?php echo htmlspecialchars($search_students, ENT_QUOTES, 'UTF-8'); ?>"
                            aria-label="Search students"
                        >
                        <button type="submit" class="btn btn-outline-secondary" title="Search">
                            <i class="bi bi-search"></i>
                        </button>
                        <?php if ($search_students !== ''): ?>
                            <a href="<?php echo BASE_URL; ?>/admin/students_create.php" class="btn btn-outline-secondary" title="Clear search">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
                <p class="text-muted small mb-3">
                    <i class="bi bi-info-circle me-1"></i>
                    Showing latest students added to the system.
                </p>
                <div class="table-responsive flex-grow-1">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="fw-semibold">Name</th>
                                <th class="fw-semibold">Student ID</th>
                                <th class="fw-semibold">Course</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($recent_students): ?>
                                <?php foreach ($recent_students as $s): ?>
                                    <tr>
                                        <td>
                                            <i class="bi bi-person-circle text-primary me-2" style="opacity: 0.6;"></i>
                                            <?php echo htmlspecialchars($s['name'], ENT_QUOTES, 'UTF-8'); ?>
                                        </td>
                                        <td><code class="text-primary"><?php echo htmlspecialchars($s['student_id'], ENT_QUOTES, 'UTF-8'); ?></code></td>
                                        <td><span class="badge bg-light text-dark"><?php echo htmlspecialchars($s['course'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">
                                        <div class="d-flex flex-column align-items-center justify-content-center h-100">
                                            <i class="bi bi-inbox" style="font-size: 2rem; opacity: 0.3;"></i>
                                            <div class="mt-2">
                                                <?php echo $search_students !== '' ? 'No students match your search.' : 'No students created yet.'; ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function myFunction() {
    var passwordInput = document.getElementById('password');
    var checkbox = document.getElementById('showPasswordCheck');
    if (passwordInput && checkbox) {
        passwordInput.type = checkbox.checked ? 'text' : 'password';
    }
}
</script>

<?php
admin_render_footer();


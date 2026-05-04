<?php
// student/profile.php
// Display and edit student profile (name, course, section, profile picture).

declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/student_auth.php';
require_once __DIR__ . '/../includes/student_layout.php';

require_student_login();

$pdo = db_connect();
$student_id = (int)($_SESSION['student_id'] ?? 0);

$stmt = $pdo->prepare('SELECT id, name, student_id, course, section, email, profile_picture FROM students WHERE id = :id LIMIT 1');
$stmt->execute([':id' => $student_id]);
$student = $stmt->fetch();

if (!$student) {
    header('Location: ' . BASE_URL . '/student/dashboard.php');
    exit;
}

$status = $_GET['status'] ?? null;
$success_message = null;
$error_message = null;

if ($status === 'updated') {
    $success_message = 'Profile updated successfully.';
} elseif ($status === 'invalid_file') {
    $error_message = 'Invalid file type. Please upload JPG, PNG, or GIF only.';
} elseif ($status === 'file_error') {
    $error_message = 'File upload failed. Please try again.';
} elseif ($status === 'size_error') {
    $error_message = 'File is too large. Maximum 5MB allowed.';
} elseif ($status === 'error') {
    $error_message = 'An error occurred while updating your profile.';
}

$profile_picture_url = null;
if ($student['profile_picture']) {
    $profile_picture_url = BASE_URL . '/uploads/profiles/' . htmlspecialchars($student['profile_picture'], ENT_QUOTES, 'UTF-8');
}

student_render_header('My Profile');
?>

<style>
    .sl-profile-hero {
        background: linear-gradient(135deg, rgba(128, 0, 0, 0.98), rgba(74, 0, 0, 0.95));
        border-radius: 14px;
        color: #fff;
        padding: 1.2rem 1.25rem;
        box-shadow: 0 10px 24px rgba(74, 0, 0, 0.18);
    }
    .sl-profile-subtext {
        color: rgba(255, 255, 255, 0.9);
        margin-bottom: 0;
    }
    .sl-avatar {
        width: 150px;
        height: 150px;
        object-fit: cover;
        border-radius: 50%;
        border: 4px solid #fff;
        box-shadow: 0 8px 22px rgba(0, 0, 0, 0.16);
    }
    .sl-avatar-fallback {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        background: linear-gradient(135deg, #f7f7f7, #eceff3);
        color: #7c8796;
        font-size: 2.4rem;
        font-weight: 700;
        margin: 0 auto;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #e8ecf1;
    }
    .sl-profile-card-title {
        font-weight: 700;
        margin-bottom: 0.3rem;
    }
    .sl-profile-meta {
        font-size: 0.85rem;
        color: #6c757d;
    }
    .sl-form-label {
        font-size: 0.85rem;
        font-weight: 600;
        color: #495057;
    }
    .sl-readonly-wrap {
        background: #f8f9fb;
        border: 1px solid #edf0f5;
        border-radius: 10px;
        padding: 0.85rem;
        margin-bottom: 1rem;
    }
    .sl-readonly-note {
        font-size: 0.75rem;
        color: #6c757d;
    }
</style>

<div class="row mb-4">
    <div class="col-12">
        <div class="sl-profile-hero">
            <h2 class="mb-1">My Profile</h2>
            <p class="sl-profile-subtext">Update your personal details and keep your account information up to date.</p>
        </div>
    </div>
</div>

<?php if ($success_message): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($success_message, ENT_QUOTES, 'UTF-8'); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($error_message): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card sl-card">
            <div class="card-body text-center">
                <h5 class="sl-profile-card-title">Profile Picture</h5>
                <p class="sl-profile-meta mb-3">This photo appears in your student account.</p>
                <div class="mb-3 pt-1">
                    <?php if ($profile_picture_url): ?>
                        <img src="<?php echo $profile_picture_url; ?>" alt="Profile Picture" class="sl-avatar">
                    <?php else: ?>
                        <div class="sl-avatar-fallback">
                            <?php echo htmlspecialchars(strtoupper(substr((string)$student['name'], 0, 1)), ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    <?php endif; ?>
                </div>
                <form method="post" action="<?php echo BASE_URL; ?>/student/profile_action.php" enctype="multipart/form-data" id="uploadForm">
                    <div class="mb-3">
                        <label for="profile_picture" class="form-label sl-form-label">Choose Image</label>
                        <input type="file" class="form-control" id="profile_picture" name="profile_picture" accept=".jpg,.jpeg,.png,.gif" required>
                        <small class="form-text text-muted">JPG, PNG, or GIF • Max 5MB</small>
                    </div>
                    <button type="submit" class="btn btn-sl-primary w-100">Upload Picture</button>
                </form>
                <?php if ($profile_picture_url): ?>
                    <form method="post" action="<?php echo BASE_URL; ?>/student/profile_action.php" style="margin-top: 0.5rem;">
                        <input type="hidden" name="action" value="delete_picture">
                        <button type="submit" class="btn btn-outline-danger w-100 btn-sm">Remove Picture</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card sl-card">
            <div class="card-header bg-transparent border-0 pt-3">
                <h5 class="mb-0 fw-semibold">Edit Profile Information</h5>
            </div>
            <div class="card-body">
                <form method="post" action="<?php echo BASE_URL; ?>/student/profile_action.php">
                    <input type="hidden" name="action" value="update_profile">

                    <div class="sl-readonly-wrap">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="student_id" class="form-label sl-form-label">Student ID</label>
                                <input type="text" class="form-control" id="student_id" value="<?php echo htmlspecialchars($student['student_id'], ENT_QUOTES, 'UTF-8'); ?>" readonly>
                                <div class="sl-readonly-note mt-1">Cannot be changed</div>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label sl-form-label">Email</label>
                                <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($student['email'], ENT_QUOTES, 'UTF-8'); ?>" readonly>
                                <div class="sl-readonly-note mt-1">Cannot be changed</div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="name" class="form-label sl-form-label">Full Name *</label>
                        <input
                            type="text"
                            class="form-control"
                            id="name"
                            name="name"
                            value="<?php echo htmlspecialchars($student['name'], ENT_QUOTES, 'UTF-8'); ?>"
                            required
                            maxlength="100"
                        >
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="course" class="form-label sl-form-label">Course *</label>
                            <input
                                type="text"
                                class="form-control"
                                id="course"
                                name="course"
                                value="<?php echo htmlspecialchars($student['course'], ENT_QUOTES, 'UTF-8'); ?>"
                                required
                                maxlength="100"
                            >
                        </div>
                        <div class="col-md-6">
                            <label for="section" class="form-label sl-form-label">Year / Section *</label>
                            <input
                                type="text"
                                class="form-control"
                                id="section"
                                name="section"
                                value="<?php echo htmlspecialchars($student['section'], ENT_QUOTES, 'UTF-8'); ?>"
                                required
                                maxlength="50"
                            >
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-sm-flex justify-content-sm-end">
                        <a href="<?php echo BASE_URL; ?>/student/dashboard.php" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-sl-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
student_render_footer();
?>

<?php
// student/change_password.php
// Form for student to update their own password (current password required).

declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/student_auth.php';
require_once __DIR__ . '/../includes/student_layout.php';

require_student_login();

$status = $_GET['status'] ?? null;
$error_message = null;
if ($status === 'wrong_current') {
    $error_message = 'Current password is incorrect.';
} elseif ($status === 'mismatch') {
    $error_message = 'New password and confirmation do not match.';
} elseif ($status === 'short') {
    $error_message = 'New password must be at least 8 characters.';
} elseif ($status === 'error') {
    $error_message = 'Password could not be updated. Please try again.';
}

student_render_header('Change Password');
?>

<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-5">
        <div class="card sl-card">
            <div class="card-header bg-transparent border-0 pt-3">
                <h5 class="mb-0 fw-semibold">Change Password</h5>
            </div>
            <div class="card-body">
                <?php if ($error_message): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>
                <?php if ($status === 'updated'): ?>
                    <div class="alert alert-success">Your password has been updated successfully.</div>
                <?php endif; ?>

                <form method="post" action="<?php echo BASE_URL; ?>/student/change_password_action.php">
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current password</label>
                        <div class="input-group">
                            <input type="password" class="form-control js-password-field" id="current_password" name="current_password" required autocomplete="current-password">
                            <button type="button" class="btn btn-outline-secondary js-password-toggle" data-target="current_password" data-state="hidden" aria-label="Show current password" title="Show password">
                                <span class="js-password-icon" aria-hidden="true">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.12 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z"/>
                                        <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5"/>
                                    </svg>
                                </span>
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New password</label>
                        <div class="input-group">
                            <input type="password" class="form-control js-password-field" id="new_password" name="new_password" required minlength="8" autocomplete="new-password">
                            <button type="button" class="btn btn-outline-secondary js-password-toggle" data-target="new_password" data-state="hidden" aria-label="Show new password" title="Show password">
                                <span class="js-password-icon" aria-hidden="true">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.12 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z"/>
                                        <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5"/>
                                    </svg>
                                </span>
                            </button>
                        </div>
                        <div class="form-text">At least 8 characters.</div>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm new password</label>
                        <div class="input-group">
                            <input type="password" class="form-control js-password-field" id="confirm_password" name="confirm_password" required minlength="8" autocomplete="new-password">
                            <button type="button" class="btn btn-outline-secondary js-password-toggle" data-target="confirm_password" data-state="hidden" aria-label="Show confirm password" title="Show password">
                                <span class="js-password-icon" aria-hidden="true">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.12 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z"/>
                                        <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5"/>
                                    </svg>
                                </span>
                            </button>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-sl-primary">Update password</button>
                        <a href="<?php echo BASE_URL; ?>/student/dashboard.php" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php student_render_footer(); ?>
<script>
(function () {
    var eyeOpenIcon = '' +
        '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">' +
        '<path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.12 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z"/>' +
        '<path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5"/>' +
        '</svg>';
    var eyeClosedIcon = '' +
        '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">' +
        '<path d="M13.359 11.238C14.746 10.17 15.53 8.912 16 8c-.87-1.697-3.49-5.5-8-5.5a7.1 7.1 0 0 0-2.79.588l.77.771A6.1 6.1 0 0 1 8 3.5c3.497 0 5.758 2.005 6.995 4.5a12 12 0 0 1-2.1 2.746z"/>' +
        '<path d="M11.297 9.176a3.5 3.5 0 0 0-4.473-4.473l.823.823a2.5 2.5 0 0 1 2.827 2.827z"/>' +
        '<path d="m2.379 5.378.84.84A12 12 0 0 0 1.005 8c1.236 2.495 3.498 4.5 6.995 4.5a6.1 6.1 0 0 0 2.021-.359l.84.84A7.1 7.1 0 0 1 8 13.5C3.49 13.5.87 9.697 0 8c.5-.968 1.326-2.251 2.379-3.622"/>' +
        '<path d="m3.35 6.35 1.522 1.522a3.5 3.5 0 0 0 3.756 3.756l1.522 1.522c-.622.226-1.34.35-2.15.35-4.51 0-7.13-3.803-8-5.5a13 13 0 0 1 2.385-3.093z"/>' +
        '<path d="M2.646 1.646a.5.5 0 1 0-.708.708l10 10a.5.5 0 0 0 .708-.708z"/>' +
        '</svg>';

    var toggleButtons = document.querySelectorAll('.js-password-toggle');
    toggleButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            var targetId = button.getAttribute('data-target');
            var input = document.getElementById(targetId);
            if (!input) {
                return;
            }

            var isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            button.setAttribute('data-state', isHidden ? 'shown' : 'hidden');
            button.innerHTML = '<span class="js-password-icon" aria-hidden="true">' + (isHidden ? eyeClosedIcon : eyeOpenIcon) + '</span>';
            button.setAttribute('title', isHidden ? 'Hide password' : 'Show password');
            button.setAttribute('aria-label', (isHidden ? 'Hide ' : 'Show ') + targetId.replace('_', ' '));
        });
    });
})();
</script>

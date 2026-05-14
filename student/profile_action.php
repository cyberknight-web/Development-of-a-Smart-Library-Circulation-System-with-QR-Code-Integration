<?php
// student/profile_action.php
// Backend: handle profile picture upload and profile information updates.

declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/student_auth.php';

require_student_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/student/profile.php');
    exit;
}

$student_id = (int)($_SESSION['student_id'] ?? 0);
$action = $_POST['action'] ?? 'update_profile';
$pdo = db_connect();

// Handle picture upload
if ($action === 'upload_picture' || (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] !== UPLOAD_ERR_NO_FILE)) {
    $redirect_status = 'error';
    
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profile_picture'];
        $max_size = 5 * 1024 * 1024;  // 5MB
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        
        // Validate file size
        if ($file['size'] > $max_size) {
            $redirect_status = 'size_error';
        } else {
            // Validate MIME type
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($mime, $allowed_types, true)) {
                $redirect_status = 'invalid_file';
            } else {
                // Create uploads directory if not exists
                $upload_dir = __DIR__ . '/../uploads/profiles';
                if (!is_dir($upload_dir)) {
                    if (!mkdir($upload_dir, 0755, true)) {
                        $redirect_status = 'file_error';
                    }
                }
                
                if ($redirect_status === 'error') {
                    // Delete old picture if exists
                    $stmt = $pdo->prepare('SELECT profile_picture FROM students WHERE id = :id');
                    $stmt->execute([':id' => $student_id]);
                    $old_pic = $stmt->fetch();
                    
                    if ($old_pic && $old_pic['profile_picture']) {
                        $old_path = $upload_dir . '/' . $old_pic['profile_picture'];
                        if (file_exists($old_path)) {
                            @unlink($old_path);
                        }
                    }
                    
                    // Generate unique filename
                    $ext = match($mime) {
                        'image/jpeg' => 'jpg',
                        'image/png' => 'png',
                        'image/gif' => 'gif',
                        default => 'jpg'
                    };
                    
                    $filename = 'student_' . $student_id . '_' . time() . '.' . $ext;
                    $filepath = $upload_dir . '/' . $filename;
                    
                    if (move_uploaded_file($file['tmp_name'], $filepath)) {
                        // Update database
                        $update = $pdo->prepare(
                            'UPDATE students SET profile_picture = :pic WHERE id = :id'
                        );
                        $update->execute([':pic' => $filename, ':id' => $student_id]);
                        $redirect_status = 'updated';
                    } else {
                        $redirect_status = 'file_error';
                    }
                }
            }
        }
    } elseif (
        isset($_FILES['profile_picture'])
        && in_array((int)$_FILES['profile_picture']['error'], [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)
    ) {
        $redirect_status = 'size_error';
    } else {
        $redirect_status = 'file_error';
    }
    
    header('Location: ' . BASE_URL . '/student/profile.php?status=' . $redirect_status);
    exit;
}

// Handle profile information update
if ($action === 'update_profile') {
    $name = trim($_POST['name'] ?? '');
    $course = trim($_POST['course'] ?? '');
    $section = trim($_POST['section'] ?? '');
    
    if (empty($name) || empty($course) || empty($section)) {
        header('Location: ' . BASE_URL . '/student/profile.php?status=error');
        exit;
    }
    
    if (strlen($name) > 100 || strlen($course) > 100 || strlen($section) > 50) {
        header('Location: ' . BASE_URL . '/student/profile.php?status=error');
        exit;
    }
    
    try {
        $update = $pdo->prepare(
            'UPDATE students SET name = :name, course = :course, section = :section WHERE id = :id'
        );
        $update->execute([
            ':name' => $name,
            ':course' => $course,
            ':section' => $section,
            ':id' => $student_id,
        ]);
        
        // Update session data
        $_SESSION['student_name'] = $name;
        $_SESSION['student_course'] = $course;
        $_SESSION['student_section'] = $section;
        
        header('Location: ' . BASE_URL . '/student/profile.php?status=updated');
        exit;
    } catch (Throwable $e) {
        error_log('Profile update error: ' . $e->getMessage());
        header('Location: ' . BASE_URL . '/student/profile.php?status=error');
        exit;
    }
}

// Handle picture deletion
if ($action === 'delete_picture') {
    try {
        $stmt = $pdo->prepare('SELECT profile_picture FROM students WHERE id = :id');
        $stmt->execute([':id' => $student_id]);
        $student = $stmt->fetch();
        
        if ($student && $student['profile_picture']) {
            $upload_dir = __DIR__ . '/../uploads/profiles';
            $filepath = $upload_dir . '/' . $student['profile_picture'];
            
            if (file_exists($filepath)) {
                @unlink($filepath);
            }
            
            // Clear from database
            $update = $pdo->prepare('UPDATE students SET profile_picture = NULL WHERE id = :id');
            $update->execute([':id' => $student_id]);
        }
        
        header('Location: ' . BASE_URL . '/student/profile.php?status=updated');
        exit;
    } catch (Throwable $e) {
        error_log('Profile picture deletion error: ' . $e->getMessage());
        header('Location: ' . BASE_URL . '/student/profile.php?status=error');
        exit;
    }
}

header('Location: ' . BASE_URL . '/student/profile.php');
exit;
?>

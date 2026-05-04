<?php
// includes/password_reset_tokens.php
// Utilities for the student password reset token table (creation + maintenance).

declare(strict_types=1);

/**
 * Ensures the `password_reset_tokens` table exists.
 *
 * This prevents fatal errors if the database was imported without the latest schema.sql.
 */
function ensure_password_reset_tokens_table(PDO $pdo): void
{
    // Keep this DDL aligned with schema.sql.
    $sql = "
        CREATE TABLE IF NOT EXISTS password_reset_tokens (
          id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
          student_id INT UNSIGNED NOT NULL,
          token VARCHAR(64) NOT NULL UNIQUE,
          expires_at TIMESTAMP NOT NULL,
          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          CONSTRAINT fk_reset_tokens_student
            FOREIGN KEY (student_id) REFERENCES students(id)
            ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";

    try {
        $pdo->exec($sql);
    } catch (PDOException $e) {
        // If this fails, we don't want to crash the page; callers should show a friendly message.
        error_log('ensure_password_reset_tokens_table failed: ' . $e->getMessage());
        throw $e;
    }
}


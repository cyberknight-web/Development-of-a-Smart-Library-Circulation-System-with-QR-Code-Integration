<?php
// includes/mail_helper.php
// Send email using PHPMailer when available (e.g. for forgot password).

declare(strict_types=1);

/**
 * Send an email. Uses PHPMailer if vendor/autoload.php exists; otherwise falls back to mail().
 * Returns true on success, false on failure.
 */
function send_mail(string $to_email, string $to_name, string $subject, string $body_html): bool
{
    $vendor_paths = [
        dirname(__DIR__) . '/vendor/autoload.php',
        dirname(__DIR__) . '/backend/vendor/autoload.php',
    ];

    $vendor = null;
    foreach ($vendor_paths as $path) {
        if (file_exists($path)) {
            $vendor = $path;
            break;
        }
    }

    if ($vendor !== null) {
        require_once $vendor;
        try {
            $mailer_class = '\\PHPMailer\\PHPMailer\\PHPMailer';
            if (!class_exists($mailer_class)) {
                error_log('PHPMailer is not available (missing dependency).');
                return false;
            }

            /** @var object $mail */
            $mail = new $mailer_class(true);

            // Use SMTP only when configured; otherwise fall back to PHP's mail transport.
            // On typical XAMPP installs, an SMTP server is not running on localhost.
            if (MAIL_SMTP_HOST !== '') {
                $mail->isSMTP();
                $mail->Host       = MAIL_SMTP_HOST;
                $mail->SMTPAuth   = (MAIL_SMTP_USER !== '');
                $mail->Username   = MAIL_SMTP_USER;
                $mail->Password   = MAIL_SMTP_PASS;
                if (MAIL_SMTP_SECURE !== '') {
                    $mail->SMTPSecure = MAIL_SMTP_SECURE; // 'tls' or 'ssl'
                }
                $mail->Port       = MAIL_SMTP_PORT;
            } else {
                $mail->isMail();
            }

            $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
            $mail->addAddress($to_email, $to_name);
            $mail->Subject = $subject;
            $mail->isHTML(true);
            $mail->Body    = $body_html;
            $mail->CharSet = 'UTF-8';
            $mail->send();
            return true;
        } catch (\Throwable $e) {
            $error_info = isset($mail) ? $mail->ErrorInfo : '';
            error_log('PHPMailer send failed: ' . $e->getMessage() . ($error_info !== '' ? ' | ErrorInfo: ' . $error_info : ''));
            return false;
        }
    }
    $headers = "MIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\n";
    return @mail($to_email, $subject, $body_html, $headers);
}

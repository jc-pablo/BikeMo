<?php
use PHPMailer\PHPMailer\PHPMailer;

require_once __DIR__ . '/../vendor/autoload.php';

function sendBikeMoEmail(
    string $email,
    string $name,
    string $subject,
    string $message,
    ?string $attachmentPath = null,
    ?string $attachmentName = null
): bool {
    $config = require __DIR__ . '/mail_config.php';
    if ($config['username'] === '' || $config['password'] === '') {
        error_log('BikeMo email not sent: Mailtrap SMTP credentials are not configured.');
        return false;
    }

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = $config['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['username'];
        $mail->Password = $config['password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $config['port'];
        $mail->setFrom($config['from_email'], $config['from_name']);
        $mail->addAddress($email, $name);
        $mail->Subject = $subject;
        $mail->isHTML(false);
        $mail->Body = $message;
        $mail->CharSet = 'UTF-8';
        if ($attachmentPath !== null && is_file($attachmentPath)) {
            $mail->addAttachment($attachmentPath, $attachmentName ?: basename($attachmentPath));
        }
        $mail->send();
        return true;
    } catch (Throwable $e) {
        error_log('BikeMo Email Error: ' . $e->getMessage());
        return false;
    }
}

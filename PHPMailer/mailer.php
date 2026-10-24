<?php

use PHPMailer\PHPMailer\PHPMailer;

require_once __DIR__ . '/../vendor/autoload.php';

function sendBikeMoEmail(
    string $email,
    string $name,
    string $subject,
    string $message
): bool {

    $config = require __DIR__ . '/mail_config.php';

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();

        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;

        $mail->Username = $config['email'];
        $mail->Password = $config['password'];

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom($config['email'], 'BikeMo');

        $mail->addAddress($email, $name);

        $mail->Subject = $subject;

        $mail->isHTML(false);
        $mail->Body = $message;
        $mail->CharSet = 'UTF-8';

        $mail->send();

        return true;

    } catch (Throwable $e) {

        error_log('BikeMo Email Error: ' . $e->getMessage());

        return false;
    }
}

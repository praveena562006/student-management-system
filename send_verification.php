<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/mail_config.php';
require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

function sendVerificationEmail($email, $name, $token)
{
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;

        $mail->Username = MAIL_USERNAME;
        $mail->Password = MAIL_PASSWORD;

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom(MAIL_USERNAME, 'EduTrack');
        $mail->addAddress($email, $name);

        $verificationLink =
            'http://localhost/Student%20Management%20System/verify_email.php?token='
            . urlencode($token);

        $mail->isHTML(true);
        $mail->Subject = 'Verify your EduTrack account';

        $mail->Body = "
            <h2>Welcome to EduTrack</h2>
            <p>Hello " . htmlspecialchars($name) . ",</p>

            <p>Your EduTrack student account has been created.</p>

            <p>Please click the button below to verify your email:</p>

            <p>
                <a href='$verificationLink'>
                    Verify Email
                </a>
            </p>

            <p>After verification, you can log in to your account.</p>
        ";

        $mail->AltBody =
            "Verify your EduTrack account using this link: "
            . $verificationLink;

        $mail->send();

        return true;

    } catch (Exception $e) {
    die(
        "<h2>PHPMailer failed</h2>" .
        "<p><b>Mailer Error:</b> " . htmlspecialchars($mail->ErrorInfo) . "</p>" .
        "<p><b>Exception:</b> " . htmlspecialchars($e->getMessage()) . "</p>"
    );
}
}
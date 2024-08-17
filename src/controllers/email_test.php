Email Test Script

<?php
require __DIR__ . '/../../build/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../build');
$dotenv->load();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = $_ENV['USERNAME_MAIL'];
    $mail->Password = $_ENV['PASSWORD_MAIL'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = $_ENV['PORT_MAIL'];

    $mail->setFrom($_ENV['USERNAME_MAIL'], 'Test Sender');
    $mail->addAddress('your-email@example.com', 'Test Recipient');
    $mail->Subject = 'Test Email';
    $mail->Body = 'This is a test email from PHPMailer.';

    $mail->send();
    echo 'Email has been sent';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
?>
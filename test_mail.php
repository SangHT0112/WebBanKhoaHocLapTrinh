<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$mail = new PHPMailer(true);

try {
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'huynhtsang2004@gmail.com';
    $mail->Password   = 'xtrgjliokmzruehr';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->setFrom('huynhtsang2004@gmail.com', 'Test');
    $mail->addAddress('sangdzvn007@gmail.com');
    $mail->Subject = 'Test gửi Gmail SMTP';
    $mail->Body    = 'Nếu bạn thấy email này thì SMTP hoạt động OK!';

    $mail->send();
    echo "Đã gửi email thành công!";
} catch (Exception $e) {
    echo "Lỗi: {$mail->ErrorInfo}";
}

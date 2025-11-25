<?php
/**
 * Contact Form Handler
 * Handles contact form submissions with reCAPTCHA v3 verification
 */

// Load configuration
require_once __DIR__ . '/../config.php';

// Load PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Validate POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError("Invalid request method", 405);
}

// Get and sanitize form data
$name    = isset($_POST['name']) ? trim($_POST['name']) : '';
$email   = isset($_POST['email']) ? trim($_POST['email']) : '';
$subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';
$privacy = isset($_POST['privacy']) ? trim($_POST['privacy']) : '';
$recaptchaToken = isset($_POST['g-recaptcha-response']) ? trim($_POST['g-recaptcha-response']) : '';

// Validate required fields
if (empty($name) || empty($email) || empty($subject) || empty($message)) {
    sendError("Vui lòng điền đầy đủ các thông tin bắt buộc.");
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendError("Vui lòng nhập địa chỉ email hợp lệ.");
}

// Validate privacy acceptance
if ($privacy !== 'accept') {
    sendError("Bạn cần chấp nhận các điều khoản dịch vụ và chính sách bảo mật.");
}

// Verify reCAPTCHA v3
$recaptchaResult = verifyRecaptcha($recaptchaToken, 'contact');
if (!$recaptchaResult['success']) {
    error_log("reCAPTCHA verification failed: " . $recaptchaResult['message'] . " (Score: " . $recaptchaResult['score'] . ")");
    sendError("Xác minh bảo mật không thành công. Vui lòng thử lại.");
}

// Send email
$mail = new PHPMailer(true);

try {
    // SMTP Configuration
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USERNAME;
    $mail->Password   = SMTP_PASSWORD;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = SMTP_PORT;
    $mail->CharSet    = 'UTF-8';

    // Sender information
    $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
    $mail->addReplyTo($email, $name);

    // Recipient (restaurant manager)
    $mail->addAddress(SMTP_TO_EMAIL, SMTP_TO_NAME);

    // Email content
    $mail->isHTML(true);
    $mail->Subject = 'Liên hệ từ website ZapFood: ' . $subject;
    $mail->Body    = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #ce1212; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background-color: #f9f9f9; }
                .info-row { padding: 10px 0; border-bottom: 1px solid #ddd; }
                .label { font-weight: bold; color: #ce1212; }
                .message-box { margin-top: 20px; padding: 15px; background-color: white; border-left: 4px solid #ce1212; }
                .footer { padding: 20px; text-align: center; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Tin Nhắn Liên Hệ Mới</h2>
                </div>
                <div class='content'>
                    <div class='info-row'>
                        <span class='label'>Từ:</span> " . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . "
                    </div>
                    <div class='info-row'>
                        <span class='label'>Email:</span> " . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . "
                    </div>
                    <div class='info-row'>
                        <span class='label'>Chủ đề:</span> " . htmlspecialchars($subject, ENT_QUOTES, 'UTF-8') . "
                    </div>
                    <div class='message-box'>
                        <strong>Nội dung tin nhắn:</strong><br><br>
                        " . nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8')) . "
                    </div>
                </div>
                <div class='footer'>
                    <p><em>Email này được gửi tự động từ form liên hệ của ZapFood.</em></p>
                </div>
            </div>
        </body>
        </html>
    ";
    
    // Plain text alternative
    $mail->AltBody = "
        TIN NHẮN LIÊN HỆ MỚI
        
        Từ: {$name}
        Email: {$email}
        Chủ đề: {$subject}
        
        Nội dung:
        {$message}
        
        ---
        Email này được gửi tự động từ form liên hệ của ZapFood.
    ";

    $mail->send();
    sendSuccess('OK');

} catch (Exception $e) {
    error_log("Email sending failed: {$mail->ErrorInfo}. Exception: {$e->getMessage()}");
    sendError("Rất tiếc, đã có lỗi xảy ra khi gửi tin nhắn của bạn. Vui lòng thử lại hoặc liên hệ trực tiếp với chúng tôi.");
}


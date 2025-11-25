<?php
/**
 * Book a Table Form Handler
 * Handles table booking submissions with reCAPTCHA v3 verification
 */

// Load configuration
require_once __DIR__ . '/../config.php';

// Load PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Initialize database connection and create table if needed
try {
    $pdo = getDbConnection();
    
    // Check if 'bookings' table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'bookings'");
    if ($stmt->rowCount() == 0) {
        // Create table if it doesn't exist
        $sqlCreateTable = "CREATE TABLE bookings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            phone VARCHAR(20),
            date DATE NOT NULL,
            time TIME NOT NULL,
            people INT NOT NULL,
            message TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_date_time (date, time),
            INDEX idx_email (email)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        $pdo->exec($sqlCreateTable);
    }
} catch (PDOException $e) {
    sendError("Database initialization failed");
}

// Validate POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError("Invalid request method", 405);
}

// Get and sanitize form data
$name    = isset($_POST['name']) ? trim($_POST['name']) : '';
$email   = isset($_POST['email']) ? trim($_POST['email']) : '';
$phone   = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$date    = isset($_POST['date']) ? trim($_POST['date']) : '';
$time    = isset($_POST['time']) ? trim($_POST['time']) : '';
$people  = isset($_POST['people']) ? intval($_POST['people']) : 0;
$message = isset($_POST['message']) ? trim($_POST['message']) : '';
$privacy = isset($_POST['privacy']) ? trim($_POST['privacy']) : '';
$recaptchaToken = isset($_POST['g-recaptcha-response']) ? trim($_POST['g-recaptcha-response']) : '';

// Validate required fields
if (empty($name) || empty($email) || empty($phone) || empty($date) || empty($time) || $people <= 0) {
    sendError("Vui lòng điền đầy đủ các thông tin bắt buộc.");
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendError("Vui lòng nhập địa chỉ email hợp lệ.");
}

// Validate phone number (basic Vietnamese phone format)
if (!preg_match('/^[0-9]{10,11}$/', $phone)) {
    sendError("Vui lòng nhập số điện thoại hợp lệ.");
}

// Validate privacy acceptance
if ($privacy !== 'accept') {
    sendError("Bạn cần chấp nhận các điều khoản dịch vụ và chính sách bảo mật.");
}

// Verify reCAPTCHA v3
$recaptchaResult = verifyRecaptcha($recaptchaToken, 'book_table');
if (!$recaptchaResult['success']) {
    error_log("reCAPTCHA verification failed: " . $recaptchaResult['message'] . " (Score: " . $recaptchaResult['score'] . ")");
    sendError("Xác minh bảo mật không thành công. Vui lòng thử lại.");
}


// Save booking to database
$sql = "INSERT INTO bookings (name, email, phone, date, time, people, message) 
        VALUES (:name, :email, :phone, :date, :time, :people, :message)";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':name' => $name,
        ':email' => $email,
        ':phone' => $phone,
        ':date' => $date,
        ':time' => $time,
        ':people' => $people,
        ':message' => $message
    ]);
} catch (PDOException $e) {
    error_log("Database insert failed: " . $e->getMessage());
    sendError("Không thể lưu thông tin đặt bàn. Vui lòng thử lại.");
}


// Send email notification
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

    // Primary recipient (customer who made the booking)
    $mail->addAddress($email, $name);

    // Add restaurant manager as CC to receive notification
    $mail->addCC(SMTP_TO_EMAIL, SMTP_TO_NAME);

    // Email content
    $mail->isHTML(true);
    $mail->Subject = 'Thông báo đặt bàn mới từ website ZapFood';
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
                .footer { padding: 20px; text-align: center; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Yêu Cầu Đặt Bàn Mới</h2>
                </div>
                <div class='content'>
                    <div class='info-row'>
                        <span class='label'>Tên khách hàng:</span> " . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . "
                    </div>
                    <div class='info-row'>
                        <span class='label'>Email:</span> " . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . "
                    </div>
                    <div class='info-row'>
                        <span class='label'>Số điện thoại:</span> " . htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') . "
                    </div>
                    <div class='info-row'>
                        <span class='label'>Ngày đặt:</span> " . htmlspecialchars($date, ENT_QUOTES, 'UTF-8') . "
                    </div>
                    <div class='info-row'>
                        <span class='label'>Thời gian:</span> " . htmlspecialchars($time, ENT_QUOTES, 'UTF-8') . "
                    </div>
                    <div class='info-row'>
                        <span class='label'>Số người:</span> " . htmlspecialchars($people, ENT_QUOTES, 'UTF-8') . "
                    </div>
                    <div class='info-row'>
                        <span class='label'>Lời nhắn:</span><br>" . nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8')) . "
                    </div>
                </div>
                <div class='footer'>
                    <p><em>Email này được gửi tự động từ hệ thống đặt bàn của ZapFood.</em></p>
                </div>
            </div>
        </body>
        </html>
    ";
    
    // Plain text alternative
    $mail->AltBody = "
        YÊU CẦU ĐẶT BÀN MỚI
        
        Tên khách hàng: {$name}
        Email: {$email}
        Số điện thoại: {$phone}
        Ngày đặt: {$date}
        Thời gian: {$time}
        Số người: {$people}
        Lời nhắn:
        {$message}
        
        ---
        Email này được gửi tự động từ hệ thống đặt bàn của ZapFood.
    ";

    $mail->send();
    sendSuccess('OK');

} catch (Exception $e) {
    // Log detailed error
    error_log("Email sending failed: {$mail->ErrorInfo}. Exception: {$e->getMessage()}");
    
    // Still send success to user since booking was saved
    // But log the email failure for admin review
    sendSuccess('OK');
}

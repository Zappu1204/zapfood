<?php
// Bao gồm các tệp cần thiết của PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

// Cấu hình kết nối cơ sở dữ liệu với PDO
$host     = '172.200.234.169';
$dbname   = 'zapfood';
$db_user  = 'zap';
$db_pass  = 'Meobeo123@';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Kết nối cơ sở dữ liệu thất bại: " . $e->getMessage());
}

// Nhận dữ liệu từ form thông qua phương thức POST
$name    = isset($_POST['name']) ? trim($_POST['name']) : '';
$email   = isset($_POST['email']) ? trim($_POST['email']) : '';
$phone   = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$date    = isset($_POST['date']) ? trim($_POST['date']) : '';
$time    = isset($_POST['time']) ? trim($_POST['time']) : '';
$people  = isset($_POST['people']) ? intval($_POST['people']) : 0;
$message = isset($_POST['message']) ? trim($_POST['message']) : '';
$privacy = isset($_POST['privacy']) ? trim($_POST['privacy']) : '';

// Kiểm tra và làm sạch dữ liệu
if (empty($name) || empty($email) || empty($phone) || empty($date) || empty($time) || $people <= 0) {
    die("Vui lòng điền đầy đủ các thông tin bắt buộc.");
}

// Thêm kiểm tra định dạng email cơ bản phía server (nên làm)
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
     die("Vui lòng nhập địa chỉ email hợp lệ.");
}


if ($privacy !== 'accept') {
    die("Bạn cần chấp nhận các điều khoản dịch vụ và chính sách bảo mật.");
}

// Lưu trữ dữ liệu vào cơ sở dữ liệu
$sql = "INSERT INTO bookings (name, email, phone, date, time, people, message) 
        VALUES (:name, :email, :phone, :date, :time, :people, :message)";
$stmt = $pdo->prepare($sql);

$stmt->bindParam(':name', $name);
$stmt->bindParam(':email', $email);
$stmt->bindParam(':phone', $phone);
$stmt->bindParam(':date', $date);
$stmt->bindParam(':time', $time);
$stmt->bindParam(':people', $people, PDO::PARAM_INT);
$stmt->bindParam(':message', $message);

try {
    $stmt->execute();
} catch (PDOException $e) {
    die("Lỗi lưu trữ dữ liệu: " . $e->getMessage());
}

// Gửi email thông báo đến chủ nhà hàng
$mail = new PHPMailer(true);

try {
    // Cấu hình SMTP
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com'; // Máy chủ SMTP của Gmail
    $mail->SMTPAuth   = true;
    $mail->Username   = 'win10pro.2004+zapfood@gmail.com'; // Địa chỉ email của bạn
    $mail->Password   = 'umgi wqlq fkpf rvay'; // Mật khẩu ứng dụng hoặc mật khẩu email
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Hoặc PHPMailer::ENCRYPTION_SMTPS
    $mail->Port       = 587;                      // Hoặc 465 cho SMTPS
    $mail->CharSet    = 'UTF-8';                  // Đảm bảo tiếng Việt hiển thị đúng

    // Thông tin người gửi (Nhà hàng)
    $mail->setFrom('win10pro.2004+zapfood@gmail.com', 'ZapFood'); // Email và tên hiển thị của người gửi

    // Thông tin người nhận chính (Chủ nhà hàng / Quản lý)
    $mail->addAddress('win10pro.2004+zapfood@gmail.com', 'Zap'); // Email và tên người nhận chính

    // ----- THAY ĐỔI Ở ĐÂY -----
    // Thêm người gửi form (khách hàng) làm người nhận BCC
    // Kiểm tra $email không rỗng để tránh lỗi nếu có vấn đề khi lấy dữ liệu
    if (!empty($email)) {
        $mail->addBCC($email, $name); // Email và tên của khách hàng
    }
    // -------------------------

    // Nội dung email
    $mail->isHTML(true); // Đặt định dạng email là HTML
    $mail->Subject = 'Thông báo đặt bàn mới từ website ZapFood'; // Tiêu đề email
    $mail->Body    = "
        <h2>Có một yêu cầu đặt bàn mới:</h2>
        <p><strong>Tên khách hàng:</strong> " . htmlspecialchars($name) . "</p>
        <p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>
        <p><strong>Số điện thoại:</strong> " . htmlspecialchars($phone) . "</p>
        <p><strong>Ngày đặt:</strong> " . htmlspecialchars($date) . "</p>
        <p><strong>Thời gian:</strong> " . htmlspecialchars($time) . "</p>
        <p><strong>Số người:</strong> " . htmlspecialchars($people) . "</p>
        <p><strong>Lời nhắn:</strong><br>" . nl2br(htmlspecialchars($message)) . "</p>
        <hr>
        <p><em>Email này được gửi tự động từ hệ thống đặt bàn của ZapFood.</em></p>
    ";
    // Nội dung thay thế cho các trình đọc email không hỗ trợ HTML
    $mail->AltBody = "
        Có một yêu cầu đặt bàn mới:\n
        Tên khách hàng: " . $name . "\n" .
        "Email: " . $email . "\n" .
        "Số điện thoại: " . $phone . "\n" .
        "Ngày đặt: " . $date . "\n" .
        "Thời gian: " . $time . "\n" .
        "Số người: " . $people . "\n" .
        "Lời nhắn:\n" . $message . "\n\n" .
        "Email này được gửi tự động từ hệ thống đặt bàn của ZapFood.";

    $mail->send();
    // Trả về 'OK' hoặc một thông báo thành công dạng JSON cho form xử lý
    echo 'OK';
    exit;

} catch (Exception $e) {
    // Ghi log lỗi chi tiết thay vì hiển thị trực tiếp cho người dùng
    error_log("Lỗi gửi email PHPMailer: {$mail->ErrorInfo}. Exception: {$e->getMessage()}");
    // Trả về thông báo lỗi chung chung
    die("Rất tiếc, đã có lỗi xảy ra khi gửi yêu cầu đặt bàn của bạn. Vui lòng thử lại hoặc liên hệ trực tiếp với chúng tôi.");
}

?>
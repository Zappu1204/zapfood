<?php
// Bao gồm các tệp cần thiết của PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // Thông tin người gửi
    $mail->setFrom('win10pro.2004+zapfood@gmail.com', 'ZapFood');

    // Thông tin người nhận
    $mail->addAddress('win10pro.2004+zapfood@gmail.com', 'Zap');

    // Nội dung email
    $mail->isHTML(true);
    $mail->Subject = 'Thông báo đặt bàn mới từ website';
    $mail->Body    = "
        <h2>Thông tin đặt bàn:</h2>
        <p><strong>Tên:</strong> $name</p>
        <p><strong>Email:</strong> $email</p>
        <p><strong>Số điện thoại:</strong> $phone</p>
        <p><strong>Ngày:</strong> $date</p>
        <p><strong>Thời gian:</strong> $time</p>
        <p><strong>Số người:</strong> $people</p>
        <p><strong>Lời nhắn:</strong> $message</p>
    ";
    $mail->AltBody = "
        Thông tin đặt bàn:
        Tên: $name
        Email: $email
        Số điện thoại: $phone
        Ngày: $date
        Thời gian: $time
        Số người: $people
        Lời nhắn: $message
    ";

    $mail->send();
    echo 'OK';
    exit;
} catch (Exception $e) {
    // echo "Có lỗi xảy ra khi gửi email xác nhận đặt bàn: {$mail->ErrorInfo}";
    die("Có lỗi xảy ra khi gửi email xác nhận đặt bàn: {$mail->ErrorInfo}" . $e->getMessage());
}
?>

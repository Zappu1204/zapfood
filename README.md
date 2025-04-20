# ZapFood - Nhà Hàng Trực Tuyến

![ZapFood Logo](assets/img/iconz.png)

## Giới Thiệu

ZapFood là một nền tảng nhà hàng trực tuyến hiện đại, cung cấp trải nghiệm ẩm thực tuyệt vời cho khách hàng với đa dạng món ăn và dịch vụ đặt bàn trực tuyến. Dự án này được xây dựng bằng HTML, CSS, JavaScript và PHP, với thiết kế responsive đảm bảo trải nghiệm người dùng tốt trên mọi thiết bị.

ZapFood không chỉ là một trang web đặt món ăn thông thường mà còn là một giải pháp toàn diện cho việc quản lý nhà hàng trực tuyến, tích hợp các tính năng như đặt bàn, hiển thị thực đơn, giới thiệu đầu bếp và quản lý sự kiện.

## Tính Năng Chính

- **Đặt Bàn Trực Tuyến**: Hệ thống đặt bàn tiện lợi với xác thực reCAPTCHA và xác nhận qua email
- **Thực Đơn Đa Dạng**: Hiển thị thực đơn theo nhiều danh mục (khai vị, món chính, đồ ăn nhanh)
- **Chức Năng Liên Hệ**: Form liên hệ với bảo vệ chống spam
- **Thiết Kế Responsive**: Tương thích với mọi kích thước màn hình
- **Chế Độ Sáng/Tối**: Cho phép người dùng thay đổi giao diện
- **Tích Hợp Video**: Nền video tương tác tạo trải nghiệm thú vị
- **Tích Hợp Bản Đồ**: Bản đồ Google Maps để dễ dàng tìm đường
- **Tích Hợp Mạng Xã Hội**: Kết nối với nền tảng mạng xã hội

## Cấu Trúc Dự Án

```
ZapFood/
├── assets/                     # Tài nguyên tĩnh (CSS, JS, hình ảnh)
│   ├── css/                    # File CSS
│   ├── img/                    # Hình ảnh
│   ├── js/                     # JavaScript
│   ├── scss/                   # SCSS source files
│   ├── vendor/                 # Thư viện bên thứ ba
│   └── vid/                    # File video
├── forms/                      # Xử lý biểu mẫu PHP
├── PHPMailer/                  # Thư viện gửi email
├── Dockerfile                  # Cấu hình Docker
├── docker-compose.yml          # Cấu hình Docker Compose
├── index.html                  # Trang chủ
├── privacy.html                # Trang chính sách bảo mật
└── terms.html                  # Trang điều khoản sử dụng
```

## Yêu Cầu Hệ Thống

- PHP 8.0 trở lên
- Web server (Apache/Nginx)
- Hỗ trợ SMTP để gửi email
- Docker (tùy chọn, nếu sử dụng container)

## Cài Đặt và Triển Khai

### Cài Đặt Thông Thường

1. Clone repository:
   ```bash
   git clone https://your-repository-url.git
   cd zapfood
   ```

2. Cấu hình máy chủ web (Apache/Nginx) trỏ đến thư mục dự án.

3. Cấu hình PHP và cài đặt các extension cần thiết.

4. Cấu hình gửi email trong `forms/book-a-table.php` và `forms/contact.php`.

### Sử Dụng Docker

1. Đảm bảo Docker và Docker Compose đã được cài đặt.

2. Khởi chạy ứng dụng bằng Docker Compose:
   ```bash
   docker-compose up -d
   ```

3. Truy cập ứng dụng tại `http://localhost`.

## Cấu Hình

### Cấu Hình Email

Chỉnh sửa thông tin SMTP trong các file PHP để gửi email:

1. Mở file `forms/book-a-table.php`
2. Cập nhật thông tin SMTP:
   ```php
   $mail->Host = 'smtp.example.com';
   $mail->SMTPAuth = true;
   $mail->Username = 'your-email@example.com';
   $mail->Password = 'your-password';
   $mail->SMTPSecure = 'tls';
   $mail->Port = 587;
   ```

### Cấu Hình reCAPTCHA

1. Đăng ký và lấy khóa reCAPTCHA từ [Google reCAPTCHA](https://www.google.com/recaptcha)
2. Cập nhật khóa trong file `index.html` và các file PHP liên quan

## Tùy Chỉnh

### Thay Đổi Thực Đơn

1. Mở file `index.html`
2. Tìm đến phần có id="menu"
3. Chỉnh sửa nội dung HTML cho từng mục thực đơn

### Thay Đổi Thông Tin Liên Hệ

1. Mở file `index.html`
2. Tìm đến phần có id="contact" và footer
3. Cập nhật thông tin liên hệ, địa chỉ, email, số điện thoại

## Đóng Góp

Chúng tôi luôn chào đón mọi đóng góp! Nếu bạn muốn tham gia phát triển dự án:

1. Fork dự án
2. Tạo nhánh tính năng (`git checkout -b feature/amazing-feature`)
3. Commit thay đổi (`git commit -m 'Add some amazing feature'`)
4. Push lên nhánh (`git push origin feature/amazing-feature`)
5. Mở Pull Request

## Giấy Phép

Dự án này được phân phối dưới Giấy phép MIT. Xem file `LICENSE` để biết thêm chi tiết.

## Liên Hệ

- **Email:** tlthpt123@gmail.com
- **Điện thoại:** +84 0963287236
- **Website:** [zapfood.vn](https://zapfood.vn)
- **Facebook:** [ZapFood Facebook](https://www.facebook.com/profile.php?id=100016413974771)
- **Instagram:** [@g.zappu](https://www.instagram.com/g.zappu/)

---

&copy; 2025 ZapFood. Thiết kế bởi Zappu. Mọi quyền được bảo lưu.
<?php
// Thông số kết nối Database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'library_db');

// Thông tin chung về ứng dụng
define('APP_NAME', 'eBooks Library');
define('APP_VERSION', '1.0.0');

// URL gốc của ứng dụng (dùng cho các liên kết frontend như css, js, ảnh)
define('URL_ROOT', '/eBooks-library/public');

// Đường dẫn vật lý đến thư mục gốc (dùng để include/require các file PHP)
define('APP_ROOT', dirname(dirname(__FILE__)));

// Cấu hình bảo mật cho Session
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Đặt thành 1 nếu chạy trên HTTPS

// Khởi động Session nếu chưa bắt đầu
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Thiết lập múi giờ mặc định
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Cấu hình hiển thị lỗi (Dành cho môi trường Development, nên tắt khi Production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

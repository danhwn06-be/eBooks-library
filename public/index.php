<?php
// Nạp file cấu hình (chứa thông tin DB, hằng số...)
require_once '../config/config.php';

// Nạp các lớp Core (Database, Controller cơ sở, Router)
require_once '../app/core/Database.php';
require_once '../app/core/Controller.php';
require_once '../app/core/App.php';

// Khởi tạo Router để điều hướng request
$router = new App();

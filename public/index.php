<?php
// Nạp Autoloader của Composer (để dùng thư viện của .env)
require_once '../vendor/autoload.php';

// Cấu hình .env
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

// Nạp file cấu hình (chứa thông tin DB, hằng số...)
require_once '../config/config.php';

// Nạp các lớp Core (Database, Controller cơ sở, Router)
require_once '../app/core/Database.php';
require_once '../app/core/Controller.php';
require_once '../app/core/App.php';

$app = new App();
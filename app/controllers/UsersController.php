<?php

class UsersController extends Controller
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = $this->model('User');
    }

    // Trang hồ sơ cá nhân: /users/profile
    public function profile()
    {
        // 1. Kiểm tra xem đã đăng nhập chưa
        if (!isset($_SESSION['user_id'])) {
            // Sửa đường dẫn redirect khớp với header.php của bạn là /users/login
            header('Location: ' . URL_ROOT . '/users/login');
            return;
        }

        $userId = $_SESSION['user_id'];
        $user = $this->userModel->getUserById($userId);
        $borrowHistory = $this->userModel->getBorrowHistory($userId);

        $data = [
            'title' => 'Hồ sơ cá nhân',
            'user' => $user,
            'borrow_history' => $borrowHistory,
            'current_page' => 'profile'
        ];

        // ĐÚNG: Vào thư mục views/profile/index.php
        $this->view('profile/index', $data);
    }

    public function login()
    {
        if (isset($_SESSION['user_id'])) {
            header('Location: ' . URL_ROOT);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            $user = $this->userModel->login($email, $password);

            if ($user) {
                $_SESSION['user_id']   = $user->user_id;
                $_SESSION['user_name'] = $user->full_name;
                $_SESSION['user_role'] = $user->role;

                if ($user->role === 'Admin') {
                    header('Location: ' . URL_ROOT . '/admin/books');
                } else {
                    header('Location: ' . URL_ROOT);
                }
                exit;
            }

            $data['error'] = 'Email or password is incorrect! Please try again.';
            // ĐÚNG: Vào thư mục views/users/login.php
            $this->view('users/login', $data);
        }

        $this->view('users/login');
    }
    
    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
        }
        session_destroy();
        header('Location: ' . URL_ROOT);
        exit;
    }

    // Phương thức mặc định nếu truy cập /users hoặc /users/index
    public function index() {
        // Chuyển hướng thẳng sang hàm profile để tránh viết lặp code
        $this->profile();
    }

    // Hàm edit() hồ sơ
    public function edit()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . URL_ROOT . '/users/login');
            exit;
        }

        $userId = $_SESSION['user_id'];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            $data = [
                'user_id' => $userId,
                'full_name' => trim($_POST['full_name']),
                'email' => trim($_POST['email']),
                'phone_number' => trim($_POST['phone_number']),
                'address' => trim($_POST['address']),
                'full_name_err' => '',
                'email_err' => ''
            ];

            if (empty($data['full_name'])) $data['full_name_err'] = 'Vui lòng nhập tên.';
            if (empty($data['email'])) $data['email_err'] = 'Vui lòng nhập email.';

            if (empty($data['full_name_err']) && empty($data['email_err'])) {
                if ($this->userModel->updateUser($data)) {
                    $_SESSION['user_name'] = $data['full_name'];
                    // Chuyển hướng về lại trang profile (hàm profile bên trên)
                    header('Location: ' . URL_ROOT . '/users/profile?status=success');
                } else {
                    die('Đã xảy ra lỗi khi cập nhật.');
                }
            } else {
                // ĐÚNG: Vào thư mục views/profile/edit.php
                $this->view('profile/edit', $data);
            }

        } else {
            $user = $this->userModel->getUserById($userId);
            $data = [
                'user' => $user,
                'title' => 'Chỉnh sửa hồ sơ'
            ];
            // ĐÚNG: Vào thư mục views/profile/edit.php
            $this->view('profile/edit', $data);
        }
    }
}
<?php

class UsersController extends Controller
{
    private $userModel;

    public function __construct()
    {
        // Kiểm tra đăng nhập ngay tại đây để bảo mật
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . URL_ROOT . '/user/login');
            exit;
        }
        $this->userModel = $this->model('User');
    }

    // Trang hồ sơ cá nhân: /user/profile
    public function profile()
    {
        // 1. Kiểm tra xem đã đăng nhập chưa
        if (!isset($_SESSION['user_id'])) {
            // Chưa đăng nhập thì đá về trang login
            header('Location: ' . URL_ROOT . '/user/login');
            return;
        }

        // 2. Lấy ID từ session
        $userId = $_SESSION['user_id'];

        // 3. Gọi Model để lấy thông tin chi tiết user
        $user = $this->userModel->getUserById($userId);
        
        // Lấy lịch sử mượn sách (nếu cần hiển thị trong profile)
        $borrowHistory = $this->userModel->getBorrowHistory($userId);

        $data = [
            'title' => 'Hồ sơ cá nhân',
            'user' => $user,
            'borrow_history' => $borrowHistory,
            'current_page' => 'profile'
        ];

        // 4. Trả về View
        $this->view('user/profile', $data);
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

            $userModel = $this->model('User');
            $user = $userModel->login($email, $password);

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
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_destroy();
        header('Location: ' . URL_ROOT);
        exit;
    }

    // Đây là hàm sẽ chạy khi bạn gõ /profile/index hoặc /profile
    public function index() {
        $userId = $_SESSION['user_id'];
        $user = $this->userModel->getUserById($userId);

        $data = [
            'user' => $user,
            'title' => 'Trang cá nhân'
        ];

        // Gọi đúng file view bạn đã tạo
        $this->view('user/profile', $data);
    }
}
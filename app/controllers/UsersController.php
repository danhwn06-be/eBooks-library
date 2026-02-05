<?php

class UsersController extends Controller
{
        private $userModel;
        private $loanModel;

    public function __construct()
    {
        $this->userModel = $this->model('User');
        $this->loanModel = $this->model('Loan');
    }

        public function index() 
    {
        $this->register(); 
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'email'        => trim($_POST['email']),
                'phone_number' => trim($_POST['phone_number']),
                'full_name'    => trim($_POST['full_name']),
                'user_name'    => trim($_POST['user_name']),
                'address'      => trim($_POST['address']),
                'password'     => $_POST['password'],
                'confirm_password' => $_POST['confirm_password'],
                'error'        => ''
            ];

            // Kiểm tra không được để trống các trường bắt buộc
            if (empty($data['email']) || empty($data['phone_number']) || empty($data['user_name']) || empty($data['full_name'])) {
                $data['error'] = "Please fill in all required fields (Email, Phone, Username, Fullname).";
            } 
            // Kiểm tra định dạng Email
            elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $data['error'] = "Invalid email format.";
            }
            // Kiểm tra định dạng Phone
            elseif (!preg_match('/^(0|84)[3|5|7|8|9][0-9]{8}$/', $data['phone_number'])) {
                $data['error'] = "Invalid Vietnamese phone number format.";
            }
            // Kiểm tra Username tồn tại
            elseif ($this->userModel->findUserByField('user_name', $data['user_name'])) {
                $data['error'] = "Username is already taken.";
            }
            // Kiểm tra Email tồn tại
            elseif ($this->userModel->findUserByField('email', $data['email'])) {
                $data['error'] = "Email is already in use.";
            }
            // Kiểm tra Password 
            elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $data['password'])) {
                $data['error'] = "Password must be at least 8 characters with uppercase, number and symbol.";
            }

            if (empty($data['error'])) {
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
                if ($this->userModel->register($data)) {
                    header('Location: ' . URL_ROOT . '/users/login?status=success');
                    exit;
                } else {
                    $data['error'] = "Something went wrong during registration.";
                }
            }
            $this->view('users/register', $data);
        } else {
            $this->view('users/register', ['error' => '']);
        }
    }


public function profile()
    {
        // 1. Kiểm tra đăng nhập
        
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . URL_ROOT . '/users/login');
            return;
        }

        $userId = $_SESSION['user_id'];
        
        // 2. Lấy thông tin User
        $user = $this->userModel->getUserById($userId);
        
        // 3. Lấy dữ liệu thống kê & Lịch sử
        $borrowHistory = $this->loanModel->getBorrowHistory($userId);
        $countReading = $this->loanModel->countReading($userId);       // Số sách đang đọc
        $countBorrowed = $this->loanModel->countTotalBorrowed($userId); // Tổng số sách đã mượn

        $data = [
            'title' => 'Hồ sơ cá nhân',
            'user' => $user,
            'borrow_history' => $borrowHistory,
            'count_reading' => $countReading,   // Truyền sang view
            'count_borrowed' => $countBorrowed, // Truyền sang view
            'current_page' => 'profile'
        ];

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
            $this->view('users/login', $data);
        } else {
            $this->view('users/login');
        }
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

    // public function index() {
    //     // Chuyển hướng thẳng sang hàm profile để tránh viết lặp code
    //     $this->profile();
    // }

    // Hàm edit() hồ sơ
// File: app/controllers/UsersController.php

public function edit()
{
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . URL_ROOT . '/users/login');
        exit;
    }

    $userId = $_SESSION['user_id'];
    
    // Lấy thông tin user hiện tại từ DB (để lấy pass cũ và hiển thị form)
    $currentUser = $this->userModel->getUserById($userId);

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Sanitize POST data
        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

        $data = [
            'user_id' => $userId,
            'full_name' => trim($_POST['full_name']),
            'email' => trim($_POST['email']),
            'phone_number' => trim($_POST['phone_number']),
            'address' => trim($_POST['address']),
            
            // Password fields
            'current_password' => $_POST['current_password'],
            'new_password' => $_POST['new_password'],
            'confirm_password' => $_POST['confirm_password'],
            
            // Password mặc định là password cũ (nếu không đổi)
            'password' => $currentUser->password_hash, 
            
            // Errors
            'full_name_err' => '',
            'email_err' => '',
            'password_err' => '',
            'user' => $currentUser // Giữ lại object user để view dùng nếu có lỗi
        ];

        // 1. Validate thông tin cơ bản
        if (empty($data['full_name'])) $data['full_name_err'] = 'Vui lòng nhập tên.';
        if (empty($data['email'])) $data['email_err'] = 'Vui lòng nhập email.';

        // 2. Validate đổi mật khẩu (Chỉ chạy khi người dùng nhập mật khẩu mới)
        if (!empty($data['new_password'])) {
            // Kiểm tra mật khẩu hiện tại có đúng không
            if (password_verify($data['current_password'], $currentUser->password_hash)) {
                // Kiểm tra pass mới và confirm pass
                if ($data['new_password'] == $data['confirm_password']) {
                    // Nếu pass mới quá ngắn (tuỳ chọn)
                    if (strlen($data['new_password']) < 6) {
                        $data['password_err'] = 'Mật khẩu mới phải có ít nhất 6 ký tự';
                    } else {
                        // Mọi thứ ok -> Hash pass mới để chuẩn bị lưu
                        $data['password'] = password_hash($data['new_password'], PASSWORD_DEFAULT);
                    }
                } else {
                    $data['password_err'] = 'Mật khẩu nhập lại không khớp';
                }
            } else {
                $data['password_err'] = 'Mật khẩu hiện tại không đúng';
            }
        }

        // 3. Kiểm tra tổng thể lỗi
        if (empty($data['full_name_err']) && empty($data['email_err']) && empty($data['password_err'])) {
            // Update User (Gọi Model)
            if ($this->userModel->updateUser($data)) {
                // Cập nhật lại Session tên nếu người dùng đổi tên
                $_SESSION['user_name'] = $data['full_name'];
                
                header('Location: ' . URL_ROOT . '/users/profile?status=success');
                exit; // QUAN TRỌNG: Phải có exit sau header
            } else {
                die('Đã xảy ra lỗi hệ thống (Database Error).');
            }

        } else {
            // Có lỗi -> Load lại view edit với các lỗi
            $this->view('profile/edit', $data);
        }

    } else {
        // GET Request: Load form lần đầu
        $data = [
            'user' => $currentUser,
            'title' => 'Chỉnh sửa hồ sơ',
            'full_name_err' => '',
            'email_err' => '',
            'password_err' => ''
        ];
        $this->view('profile/edit', $data);
    }
}
}
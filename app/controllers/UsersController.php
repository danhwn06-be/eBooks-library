<?php

class UsersController extends Controller
{
    public function index() 
    {
        $this->register(); // Mặc định hiển thị trang đăng ký
    }

    public function register() 
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userModel = $this->model('User');
            $account = trim($_POST['email']); 
            
            $data = [
                'account'   => $account,
                'full_name' => trim($_POST['full_name']),
                'address'   => trim($_POST['address']),
                'password'  => $_POST['password'],
                'confirm_password' => $_POST['confirm_password'],
                'error'     => ''
            ];

            // Nhận diện loại field 
            $isEmail = filter_var($account, FILTER_VALIDATE_EMAIL);
            $isPhone = preg_match('/^(0|84)[3|5|7|8|9][0-9]{8}$/', $account);
            $field = $isEmail ? 'email' : ($isPhone ? 'phone_number' : null);

            if (!$field) {
                $data['error'] = "Invalid email or phone number format.";
            } 
            // Validate mật khẩu server-side
            elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $data['password'])) {
                $data['error'] = "Password must be at least 8 characters with uppercase, number and symbol.";
            }
            // Kiểm tra tồn tại
            elseif ($userModel->findUserByField($field, $account)) {
                $data['error'] = "This " . $field . " is already in use.";
            }

            if (empty($data['error'])) {
                // Mã hóa và lưu
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
                $data['field_type'] = $field; 

                if ($userModel->register($data)) {
                    // Chuyển hướng khi thành công
                    header('Location: ' . URL_ROOT . '/users/login?status=success');
                    exit;
                } else {
                    // Nếu câu lệnh SQL thất bại (thường do database chưa cho phép NULL)
                    $data['error'] = "Something went wrong. Please check your database settings.";
                }
            }
            $this->view('users/register', $data);
        } else {
            $this->view('users/register', ['error' => '']);
        }
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
}

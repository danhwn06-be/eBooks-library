<?php

class UsersController extends Controller
{
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

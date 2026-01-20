<?php

class UsersController extends Controller
{
        public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $email = $_POST['email'];
            $password = $_POST['password'];

            $userModel = $this->model('User');
            $user = $userModel->login($email, $password);

            if ($user) {
                $_SESSION['user_id'] = $user->id;
                $_SESSION['user_name'] = $user->name;
                $_SESSION['user_role'] = $user->role;

                if ($user->role === 'admin') {
                    header('Location: ' . URL_ROOT . '/admin/dashboard');
                } else {
                    header('Location: ' . URL_ROOT . '/home');
                }
                exit;
            }

            $data['error'] = 'Email or password is incorrect! Please try again.';
            $this->view('users/login', $data);
        } else {
            $this->view('users/login');
        }
    }
}

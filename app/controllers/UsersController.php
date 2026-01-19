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

                header('Location: ' . URL_ROOT . '/home');
                exit;
            } else {
                $data['error'] = 'Email or password is incorrect';
                $this->view('users/login', $data);
            }
        } else {
            $this->view('users/login');
        }
    }
}

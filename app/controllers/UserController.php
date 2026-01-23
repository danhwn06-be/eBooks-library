<?php


class UserController extends Controller
{
    public function index()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $userId = $_SESSION['user_id'];

        $userModel = $this->model('UserModel');
        $loanModel = $this->model('LoanModel');

        $user = $userModel->getById($userId);
        $history = $loanModel->getBorrowHistoryByUser($userId);

        $this->view('profile/index', [
            'user' => $user,
            'history' => $history
        ]);
    }
}

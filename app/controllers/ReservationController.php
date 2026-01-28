<?php

class ReservationController extends Controller
{
    private $reservationModel;
    private $userModel;

    public function __construct()
    {
        $this->reservationModel = $this->model("ReservationModel");
        $this->userModel = $this->model("User");
    }

    // HIỂN THỊ FORM
    public function create($bookId)
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . URL_ROOT . '/users/login');
            exit;
        }

        //LẤY USER ĐỂ ĐỔ DATA VÀO FORM
        $user = $this->userModel->getUserById($_SESSION['user_id']);

        // book đã có sẵn theo logic route
        $book = $this->model("BookModel")->getBookById($bookId);

        $data = [
            'user' => $user,
            'book' => $book
        ];

        $this->view('reservation/confirm', $data);
    }

    // LƯU RESERVATION
    public function store()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . URL_ROOT . '/users/login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $data = [
                'user_id'     => $_SESSION['user_id'],
                'book_id'     => $_POST['book_id'],
                'member_code' => $_POST['member_code'],
                'email'       => $_POST['email'],
                'phone'       => $_POST['phone'],
                'address'     => $_POST['address'],
                'borrow_date' => $_POST['borrow_date'],
                'loan_term'   => $_POST['loan_term'],
            ];

            if (empty($data['book_id']) || empty($data['member_code'])) {
                echo "<script>
                    alert('Missing reservation information');
                    window.history.back();
                </script>";
                exit;
            }

            if ($this->reservationModel->createReservation($data)) {
                echo "<script>
                    alert('Reservation created successfully!');
                    window.location.href = '" . URL_ROOT . "/users/profile';
                </script>";
            } else {
                echo "<script>
                    alert('Reservation failed. Please try again.');
                    window.history.back();
                </script>";
            }
            exit;
        }
    }
}

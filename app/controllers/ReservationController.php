<?php

class ReservationController extends Controller
{
    private $reservationModel;
    private $userModel;

    public function __construct()
    {
        $this->reservationModel = $this->model("reservation");
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
        $book = $this->model("Book")->getBookById($bookId);

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

            $userId = $_SESSION['user_id'];
            $bookId = $_POST['book_id'];

            if (empty($bookId)) {
                echo "<script>
                    alert('Missing book information');
                    window.history.back();
                </script>";
                exit;
            }

            if ($this->reservationModel->createReservation($userId, $bookId)) {
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

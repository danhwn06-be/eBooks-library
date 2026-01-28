<?php

class ReservationController extends Controller
{
    private $reservationModel;

    public function __construct()
    {
        $this->reservationModel = $this->model("ReservationModel");
    }

    public function store()
    {
        // Chưa đăng nhập
        if (!isset($_SESSION['user_id'])) {
            echo "<script>
                alert('Please login first');
                window.location.href = '" . URL_ROOT . "/users/login';
            </script>";
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $userId = $_SESSION['user_id'];
            $bookId = $_POST['book_id'] ?? null;

            if (!$bookId) {
                echo "<script>
                    alert('Missing book information');
                    window.location.href = '" . URL_ROOT . "/books';
                </script>";
                exit;
            }

            $result = $this->reservationModel->createReservation($userId, $bookId);

            if ($result) {
                echo "<script>
                    alert('Reservation created successfully!');
                    window.location.href = '" . URL_ROOT . "';
                </script>";
            } else {
                // Lỗi
                echo "<script>
                    alert('Reservation failed. Please try again.');
                    window.history.back();
                </script>";
            }
            exit;
        }
    }
}

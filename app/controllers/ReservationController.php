<?php

class ReservationController extends Controller
{
    private $reservationModel;

    public function __construct()
    {
        $this->reservationModel = $this->model("ReservationModel");
    }

    /**
     * Xử lý tạo reservation (trang 2 – Confirm)
     * URL: /reservation/store
     * Method: POST
     */
    public function store()
    {
        // Chưa đăng nhập thì đá về login
        if (!isset($_SESSION['user_id'])) {
            header("Location: /auth/login");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $userId = $_SESSION['user_id'];
            $bookId = $_POST['book_id'] ?? null;

            if (!$bookId) {
                // thiếu dữ liệu → quay lại
                header("Location: /books");
                exit;
            }

            $result = $this->reservationModel->createReservation($userId, $bookId);

            if ($result) {
                // thành công → có thể chuyển sang trang thông báo
                header("Location: /reservation/success");
            } else {
                // lỗi DB
                header("Location: /reservation/error");
            }
            exit;
        }
    }

    /**
     * Hiển thị danh sách reservation của user
     * URL: /reservation/my
     */
    public function my()
    {
        if (!isset($_SESSION['user_id'])) {
            header("Location: /auth/login");
            exit;
        }

        $userId = $_SESSION['user_id'];
        $reservations = $this->reservationModel->getReservationsByUser($userId);

        $this->view("reservation/myReservations", [
            "reservations" => $reservations
        ]);
    }

    /**
     * Trang thông báo thành công
     */
    public function success()
    {
        $this->view("reservation/success");
    }

    /**
     * Trang lỗi
     */
    public function error()
    {
        $this->view("reservation/error");
    }
}

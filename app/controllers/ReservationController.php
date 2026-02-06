<?php

class ReservationController extends Controller
{
    private $reservationModel;
    private $userModel;

    /**
     * Khởi tạo Controller và load các Model cần thiết
     */
    public function __construct()
    {
        $this->reservationModel = $this->model("Reservation");
        $this->userModel = $this->model("User");
    }

    // 1. USER ACTIONS

    /**
     * Hiển thị trang xác nhận đặt trước sách
     * @param int|null $bookId ID sách cần đặt
     */
    public function create($bookId = null)
    {
        // 1. Kiểm tra đăng nhập
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . URL_ROOT . '/user/login');
            exit;
        }

        // Nếu không có bookId, quay về trang chủ
        if ($bookId === null) {
            header('Location: ' . URL_ROOT . '/home');
            exit;
        }

        $userId = $_SESSION['user_id'];

        // 2. Lấy thông tin chi tiết
        $userDetails = $this->reservationModel->getReservationDetails($userId, $bookId);
        $book = $this->model("Book")->getBookById($bookId);

        // KIỂM TRA: Nếu không tìm thấy user hoặc book trong DB, không cho load view
        if (!$userDetails || !$book) {
            echo "<script>alert('Data not found!'); window.location.href='".URL_ROOT."/home';</script>";
            exit;
        }

        // 4. Truyền dữ liệu sang View
        $data = [
            'user' => $userDetails,
            'book' => $book,
            'book_id' => $bookId
        ];

        $this->view('reservation/confirm', $data);
    }

    /**
     * Xử lý lưu đơn đặt trước (POST)
     */
    public function store()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . URL_ROOT . '/user/login');
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

            // Thực hiện giao dịch mượn sách trong Model
            if ($this->reservationModel->createReservation($userId, $bookId)) {
                echo "<script>
                    alert('Reservation created successfully!');
                    window.location.href = '" . URL_ROOT . "/user/profile';
                </script>";
            } else {
                echo "<script>
                    alert('Reservation failed. No copies available or system error.');
                    window.history.back();
                </script>";
            }
            exit;
        }
    }
}
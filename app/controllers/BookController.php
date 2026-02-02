<?php
class BookController extends Controller
{
    private $bookModel;

    public function __construct()
    {
        $this->bookModel = $this->model('Book');
    }

    // Hàm mặc định (nếu gõ /books sau URL)
   public function index()
    {
        $filters = [
            'category' => $_GET['category'] ?? '',
            'year'     => $_GET['year'] ?? '',
            'author'   => $_GET['author'] ?? ''
        ];

        // Kiểm tra xem người dùng có đang thực hiện lọc không
        if (array_filter($filters)) {
            $books = $this->bookModel->getFilteredBooks($filters);
            $totalBooks = count($books);
        } else {
            $page = $_GET['page'] ?? 1;
            $limit = 6;
            $offset = ($page - 1) * $limit;
            $books = $this->bookModel->getBooksWithPagination($limit, $offset);
            $totalBooks = $this->bookModel->getTotalBookCount();
        }

        $data = [
            'books' => $books,
            'categories' => $this->bookModel->getAllCategories(),
            'current_page' => 'home',
            'pagination' => [
                'current_page' => $_GET['page'] ?? 1,
                'total_pages' => ceil($totalBooks / 6)
            ]
        ];

        $this->view('home/index', $data);
    }

    // Hàm xem chi tiết: /books/detail/{id}
    public function detail($id = null)
    {
        if ($id == null) {
            header('Location: ' . URL_ROOT);
            return;
        }

        // Lấy dữ liệu từ Model
        $book = $this->bookModel->getBookById($id);

        // Nếu id sai (không tìm thấy sách)
        if (!$book) {
            header('Location: ' . URL_ROOT);
            return;
        }

        $data = [
            'title' => $book['title'],
            'book' => $book,
            'current_page' => 'books',
            'categories' => $this->bookModel->getAllCategories()
        ];

        $this->view('book/detail', $data);
    }

    public function search()
    {
        //kiểm tra từ khóa mà người dùng nhập vào
        $keyword = isset($_GET['keyword']) ? trim ($_GET['keyword']) : "";
        //xử lý nếu từ khóa trống sẽ trả về tất cả sách
        if ($keyword === " ") {
            $books = $this->bookModel->getAllBooks();
            $pageTitle = 'Tất cả sách';
        } else {
            //tìm kiếm bằng tên của sách
            $books = $this->bookModel->searchBooks($keyword);
            $pageTitle = 'Kết quả tìm kiếm cho: ' . htmlspecialchars($keyword);
        }

        //xử lý khi người dùng tìm kiếm mà không có 
        $noResult = empty($books);

        $categories = $this->bookModel->getAllCategories();

        $data = [
            'title' => 'Search Result',
            'books' => $books,
            'categories' => $categories,
            'current_page' => 'home',
            'keyword' => htmlspecialchars($keyword),
            'noResult' => $noResult,
            'pagination' => null,
            'show-filter' => false
        ];

        $this->view('book/index', $data);
    }

    public function filter() {
        
    }
    public function reserve($bookId)
{
    // Chưa login → login trước
    if (!isset($_SESSION['user_id'])) {
        header("Location: " . URL_ROOT . "/users/login");
        exit;
    }

    // Sử dụng model đã load sẵn trong __construct
    $book = $this->bookModel->getBookById($bookId);

    if (!$book) {
        header("Location: " . URL_ROOT . "/book");
        exit;
    }

    // Fix lỗi Deprecated: htmlspecialchars(): Passing null
    // Chuyển đổi các giá trị null thành chuỗi rỗng để tránh lỗi khi hiển thị
    if (is_array($book)) {
        foreach ($book as $key => $value) {
            if ($value === null) {
                $book[$key] = '';
            }
        }
    }

    // Xử lý khi người dùng xác nhận đặt sách (POST)
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $reservationModel = $this->model('Reservation');
        if ($reservationModel->createReservation($_SESSION['user_id'], $bookId)) {
            header("Location: " . URL_ROOT . "/users/profile?status=reserved");
        } else {
            header("Location: " . URL_ROOT . "/book/detail/$bookId?error=failed");
        }
        exit;
    }

    $this->view("reservation/confirm", [
        "book" => $book
    ]);
}
}

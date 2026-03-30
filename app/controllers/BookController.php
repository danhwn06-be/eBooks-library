<?php
class BookController extends Controller
{
    private $bookModel;
    private $categoryModel;

    /**
     * Khởi tạo Controller và load Model
     */
    public function __construct()
    {
        $this->bookModel = $this->model('Book');
        $this->categoryModel = $this->model('Category');
    }

    // 1. PUBLIC VIEWS (CATALOG & SEARCH)

    /**
     * Trang chủ / Danh sách sách (Hỗ trợ Phân trang & Lọc)
     */
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
            'categories' => $this->categoryModel->getAllCategories(),
            'current_page' => 'home',
            'pagination' => [
                'current_page' => $_GET['page'] ?? 1,
                'total_pages' => ceil($totalBooks / 6)
            ]
        ];

        $this->view('home/index', $data);
    }

    /**
     * Tìm kiếm sách theo từ khóa
     */
    public function search()
    {
        // Kiểm tra từ khóa mà người dùng nhập vào
        $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : "";
        
        // Xử lý nếu từ khóa trống sẽ trả về tất cả sách
        if (empty($keyword)) {
            $books = $this->bookModel->getAllBooks();
            $pageTitle = 'Tất cả sách';
        } else {
            // Tìm kiếm bằng tên của sách
            $books = $this->bookModel->searchBooks($keyword);
            $pageTitle = 'Kết quả tìm kiếm cho: ' . htmlspecialchars($keyword);
        }

        // Xử lý khi người dùng tìm kiếm mà không có kết quả
        $noResult = empty($books);

        $categories = $this->categoryModel->getAllCategories();

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

    /**
     * Xem chi tiết một cuốn sách
     * @param int|null $id ID sách
     */
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
            'categories' => $this->categoryModel->getAllCategories()
        ];

        $this->view('book/detail', $data);
    }
}

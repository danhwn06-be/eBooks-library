<?php
class BookController extends Controller
{
    private $bookModel;

    public function __construct()
    {
        $this->bookModel = $this->model('BookModel');
    }

    // Hàm mặc định (nếu gõ /books sau URL)
    public function index()
    {
        header('Location: ' . URL_ROOT);
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
            'current_page' => 'books'
        ];

        $this->view('book/detail', $data);
    }

    // public function search() {

    // }

    public function search()
    {
        $bookModel = $this->model('BookModel');
        //kiểm tra từ khóa mà người dùng nhập vào
        $keyword = isset($_GET['keyword']) ? trim ($_GET['keyword']) : "";
        //xử lý nếu từ khóa trống sẽ trả về tất cả sách
        if ($keyword === " ") {
            $books = $bookModel->getAllBooks();
            $pageTitle = 'Tất cả sách';
        } else {
            //tìm kiếm bằng tên của sách
            $books = $bookModel->searchByTitle($keyword);
            $pageTitle = 'Kết quả tìm kiếm cho: ' . htmlspecialchars($keyword);
        }

        //xử lý khi người dùng tìm kiếm mà không có 
        $noResult = empty($books);

        $categories = $bookModel->getAllCategories();

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
}
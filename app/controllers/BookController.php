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
            'current_page' => 'books'
        ];

        $this->view('book/detail', $data);
    }
}
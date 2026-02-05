<?php
class HomeController extends Controller
{
    private $bookModel;
    private $categoryModel;

    /**
     * Khởi tạo Controller và load các Model cần thiết
     */
    public function __construct()
    {
        $this->bookModel = $this->model('Book');
        $this->categoryModel = $this->model('Category');
    }

    // 1. PUBLIC VIEWS

    /**
     * Trang chủ hiển thị danh sách sách (có phân trang)
     */
    public function index()
    {
        // Giới hạn số sách trong 1 trang
        $limit = 6;
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $offset = ($page - 1) * $limit;

        $books = $this->bookModel->getBooksWithPagination($limit, $offset);
        $totalBooks = $this->bookModel->getTotalBookCount();
        $totalPages = ceil($totalBooks / $limit);
        $categories = $this->categoryModel->getAllCategories();

        $data = [
            'title' => 'Home Page',
            'books' => $books,
            'categories' => $categories,
            'current_page' => 'home',
            'keyword' => '',
            'noResult' => false,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages
            ]
        ];

        $this->view('home/index', $data);
    }
}
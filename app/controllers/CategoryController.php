<?php
class CategoryController extends Controller {
    private $bookModel;

    public function __construct() {
        $this->bookModel = $this->model('Book');
    }

    public function index($cat_id = null) {
        $categories = $this->bookModel->getAllCategories();
        
        // Xử lý pagination
        $limit = 12; // Số sách mỗi trang
        $currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $offset = ($currentPage - 1) * $limit;

        // Nếu có ID thì lọc theo thể loại
        if ($cat_id) {
            $title = $this->bookModel->getCategoryNameById($cat_id);
            // Lấy tổng số sách trong category
            $totalBooks = $this->bookModel->getBookCountByCategoryId($cat_id);
            // Lấy sách phân trang
            $books = $this->bookModel->getBooksByCategoryIdPaginated($cat_id, $limit, $offset);
        } else {
            // Không có ID: lấy tất cả sách
            $title = "All Categories";
            $totalBooks = $this->bookModel->getTotalBookCount();
            $books = $this->bookModel->getBooksWithPagination($limit, $offset);
        }

        $totalPages = ceil($totalBooks / $limit);

        $data = [
            'title' => $title,
            'books' => $books,
            'categories' => $categories,
            'current_page' => 'category',
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'totalBooks' => $totalBooks
        ];

        $this->view('category/index', $data);
    }

}
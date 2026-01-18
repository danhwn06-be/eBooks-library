<?php
class CategoryController extends Controller {
    private $bookModel;

    public function __construct() {
        $this->bookModel = $this->model('BookModel');
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
            $totalBooks = $this->getBookCountByCategory($cat_id);
            // Lấy sách phân trang
            $books = $this->getBooksByCategoryPaginated($cat_id, $limit, $offset);
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

    // Helper method: đếm sách theo category
    private function getBookCountByCategory($cat_id) {
        $sql = "SELECT COUNT(*) as total FROM Books WHERE category_id = :cat_id";
        try {
            $stmt = $this->bookModel->getDb()->getConnection()->prepare($sql);
            $stmt->bindValue(':cat_id', $cat_id, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch();
            return $row['total'];
        } catch (PDOException $e) {
            return 0;
        }
    }

    // Helper method: lấy sách theo category có phân trang
    private function getBooksByCategoryPaginated($cat_id, $limit, $offset) {
        $sql = "SELECT b.book_id, b.title, b.author, b.isbn, b.image_url, 
                COUNT(bc.copy_id) AS total_copies, 
                COALESCE(SUM(CASE WHEN bc.status = 'Available' THEN 1 ELSE 0 END), 0) AS available_copies 
                FROM Books b 
                LEFT JOIN BookCopies bc ON b.book_id = bc.book_id 
                WHERE b.category_id = :cat_id
                GROUP BY b.book_id
                ORDER BY b.created_at DESC
                LIMIT :limit OFFSET :offset";
        
        try {
            $stmt = $this->bookModel->getDb()->getConnection()->prepare($sql);
            $stmt->bindValue(':cat_id', $cat_id, PDO::PARAM_INT);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    // Method để truy cập BookModel nếu cần
    public function getBookModel() {
        return $this->bookModel;
    }
}
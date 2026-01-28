<?php
class HomeController extends Controller
{
    public function index()
    {
        $bookModel = $this->model('Book');
        $categoryModel = $this->model('Categories');

        // Giới hạn số sách trong 1 trang
        $limit = 6;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($page < 1) $page = 1;
        $offset = ($page - 1) * $limit;

        $books = $bookModel->getBooksWithPagination($limit, $offset);
        $totalBooks = $bookModel->getTotalBookCount();
        $totalPages = ceil($totalBooks / $limit);
        $categories = $categoryModel->getAllCategories();

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
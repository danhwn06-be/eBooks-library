<?php
class HomeController extends Controller
{
    public function index()
    {
        $bookModel = $this->model('BookModel');

        $limit = 6;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($page < 1) $page = 1;
        $offset = ($page - 1) * $limit;

        $books = $bookModel->getBooksWithPagination($limit, $offset);
        $totalBooks = $bookModel->getTotalBookCount();
        $totalPages = ceil($totalBooks / $limit);

        $data = [
            'title' => 'Home Page',
            'books' => $books,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages
            ]
        ];

        $this->view('home/index', $data);
    }

    // chức năng tìm kiếm sách cho khách & thành viên
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
            'pagination' => null
        ];

        $this->view('home/index',$data);
    }
}
<?php
class AdminController extends Controller
{
    private $bookModel;

    public function __construct()
    {
        $this->bookModel = $this->model('BookModel');
    }

    // Trang Dashboard quản lý sách
    public function books()
    {
        // Lấy tất cả sách
        $books = $this->bookModel->getbooksForAdmin();

        $data = [
            'books' => $books,
            'page_title' => 'Book Inventory Management'
        ];

        // Gọi view
        $this->view('admin/books/index', $data);
    }

    // Trang BookCopies của từng quyển sách
    public function copies($id = null)
    {
        if ($id == null) {
            header('Location: ' . URL_ROOT . '/admin');
            return;
        }

        $book = $this->bookModel->getBookById($id);
        $copies = $this->bookModel->getCopiesByBookId($id);

        // Nếu sách không tồn tại
        if (!$book) {
            header('Location: ' . URL_ROOT . '/admin');
            return;
        }

        $data = [
            'book' => $book,
            'copies' => $copies
        ];

        // Gọi view
        $this->view('admin/books/copies', $data);
    }
}
<?php
class BookController extends Controller
{
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
}
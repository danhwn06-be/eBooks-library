<?php
class BookController extends Controller
{
    public function index()
    {
        // Khởi tạo model
        $bookModel = $this->model('BookModel');
        
        // Lấy danh sách sách từ CSDL
        $books = $bookModel->getAllBooks();

        // Gọi view và truyền dữ liệu
        $this->view('books/index', ['books' => $books]);
    }
}
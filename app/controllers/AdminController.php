<?php
class AdminController extends Controller {
    private $bookModel;
    
    public function __construct() {
        $this->bookModel = $this->model('BookModel');
    }

    // Trang Dashboard quản lý sách
    public function books() {
        // Lấy tất cả sách
        $books = $this->bookModel->getbooksForAdmin();

        $data = [
            'books' => $books,
            'page_title' => 'Book Inventory Management'
        ];

        // Gọi view
        $this->view('admin/books/index', $data);
    }
}
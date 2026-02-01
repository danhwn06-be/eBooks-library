<?php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
// use PhpOffice\PhpSpreadsheet\Style\Alignment;
// use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\IOFactory;

class AdminController extends Controller
{
    private $bookModel;
    private $userModel;
    private $categoryModel;
    private $copyModel;
    private $loanModel;

    public function __construct()
    {
        $this->bookModel = $this->model('Book');
        $this->userModel = $this->model('User');
        $this->categoryModel = $this->model('Category');
        $this->copyModel = $this->model('BookCopy');
        $this->loanModel = $this->model('Loan');
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

    public function add()
    {
        // Cần lấy danh mục để hiện trong thẻ <select>
        $categories = $this->categoryModel->getAllCategories();

        $data = [
            'categories' => $categories
        ];

        // Chuyển hướng sang file view/admin/books/add.php
        $this->view('admin/books/add', $data);
    }

    // Xử lý lưu sách mới (POST)
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // 1. Xử lý ảnh
            $imageName = $this->handleImageUpload();

            // 2. Gom dữ liệu
            $data = [
                'title' => trim($_POST['title']),
                'isbn' => trim($_POST['isbn']),
                'author' => trim($_POST['author']),
                'category_id' => $_POST['category_id'],
                'publisher' => trim($_POST['publisher']),
                'publication_year' => $_POST['publication_year'],
                'description' => trim($_POST['description']),
                'image_url' => $imageName // Có thể là tên file hoặc null
            ];

            if ($this->bookModel->addBook($data)) {
                header('Location: ' . URL_ROOT . '/admin/books');
            } else {
                die('Error adding book');
            }
        }
    }

    // --- 2. CHỨC NĂNG CHỈNH SỬA (EDIT) ---

    // Hiển thị form sửa (GET) -> URL: /admin/edit/5
    public function edit($id)
    {
        $book = $this->bookModel->getBookById($id);
        $categories = $this->categoryModel->getAllCategories();

        if (!$book) {
            header('Location: ' . URL_ROOT . '/admin/books');
            return;
        }

        $this->view('admin/books/edit', [
            'book' => $book,
            'categories' => $categories
        ]);
    }

    // Xử lý cập nhật (POST) -> Action form
    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // 1. Xử lý ảnh (nếu có upload mới thì lấy tên mới, không thì null)
            $imageName = $this->handleImageUpload();

            $data = [
                'book_id' => $id,
                'title' => trim($_POST['title']),
                'isbn' => trim($_POST['isbn']),
                'author' => trim($_POST['author']),
                'category_id' => $_POST['category_id'],
                'publisher' => trim($_POST['publisher']),
                'publication_year' => $_POST['publication_year'],
                'description' => trim($_POST['description']),
                'image_url' => $imageName // Truyền vào Model để xử lý
            ];

            if ($this->bookModel->updateBook($data)) {
                header('Location: ' . URL_ROOT . '/admin/books');
            } else {
                die('Error updating book');
            }
        }
    }

    // --- 3. CHỨC NĂNG XÓA (DELETE) ---
    // URL: /admin/delete/5
    public function delete($id)
    {
        // Kiểm tra xem sách có bản sao không (Sử dụng CopyModel)
        $copies = $this->copyModel->getCopiesByBookId($id);
        if (count($copies) > 0) {
            echo "<script>alert('Cannot delete book. It has copies in the system.'); window.location.href='" . URL_ROOT . "/admin/books';</script>";
            return;
        }

        if ($this->bookModel->deleteBook($id)) {
            header('Location: ' . URL_ROOT . '/admin/books');
        } else {
            // Thông báo lỗi đơn giản
            echo "<script>alert('Cannot delete book. It may have copies or loans.'); window.location.href='" . URL_ROOT . "/admin/books';</script>";
        }
    }

    private function handleImageUpload()
    {
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            $filename = $_FILES['image']['name'];
            $filetmp = $_FILES['image']['tmp_name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            if (in_array($ext, $allowed)) {
                // Đặt tên file mới để tránh trùng lặp: time_tenfile.jpg
                $newFilename = time() . '_' . $filename;
                // Đường dẫn thư mục public/images/books/
                $destination = 'images/books/' . $newFilename;

                if (move_uploaded_file($filetmp, $destination)) {
                    return $newFilename;
                }
            }
        }
        return null; // Trả về null nếu không có file hoặc lỗi
    }

    // COPIES
    // Trang BookCopies của từng quyển sách
    public function copies($id = null)
    {
        if ($id == null) {
            header('Location: ' . URL_ROOT . '/admin');
            return;
        }

        $book = $this->bookModel->getBookById($id);
        $copies = $this->copyModel->getCopiesByBookId($id);

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

    // 1. Giao diện Thêm Copy (GET)
    public function add_copy($book_id = null) {
        if (!$book_id) header('Location: ' . URL_ROOT . '/admin/books');

        $book = $this->bookModel->getBookById($book_id);
        
        $data = [
            'book' => $book,
            'book_id' => $book_id
        ];

        $this->view('admin/books/add_copy', $data);
    }

    // 2. Xử lý Lưu Copy mới (POST)
    public function store_copy() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $data = [
                'book_id' => $_POST['book_id'],
                'copy_code' => trim($_POST['copy_code']),
                'status' => $_POST['status'],
                'quality' => trim($_POST['quality'])
            ];

            if ($this->copyModel->addCopy($data)) {
                // Quay lại trang danh sách copy của cuốn sách đó
                header('Location: ' . URL_ROOT . '/admin/copies/' . $data['book_id']);
            } else {
                die('Error adding copy');
            }
        }
    }

    // 3. Giao diện Sửa Copy (GET)
    public function edit_copy($copy_id) {
        $copy = $this->copyModel->getCopyById($copy_id);
        
        if (!$copy) {
            header('Location: ' . URL_ROOT . '/admin/books');
            return;
        }

        // Lấy thông tin sách để hiển thị tên sách cho đẹp (tùy chọn)
        $book = $this->bookModel->getBookById($copy['book_id']);

        $data = [
            'copy' => $copy,
            'book' => $book
        ];

        $this->view('admin/books/edit_copy', $data);
    }

    // 4. Xử lý Cập nhật Copy (POST)
    public function update_copy($copy_id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $book_id = $_POST['book_id_redirect']; // Lấy ID sách để redirect về đúng chỗ

            $data = [
                'copy_id' => $copy_id,
                'status' => $_POST['status'],
                'quality' => trim($_POST['quality'])
            ];

            if ($this->copyModel->updateCopy($data)) {
                header('Location: ' . URL_ROOT . '/admin/copies/' . $book_id);
            } else {
                die('Error updating copy');
            }
        }
    }

    // 5. Xóa Copy
    public function delete_copy($copy_id) {
        // Lấy thông tin copy trước để biết book_id mà quay về
        $copy = $this->copyModel->getCopyById($copy_id);
        $book_id = $copy['book_id'];

        if ($this->copyModel->deleteCopy($copy_id)) {
            header('Location: ' . URL_ROOT . '/admin/copies/' . $book_id);
        } else {
            die('Cannot delete this copy (Maybe borrowed?)');
        }
    }

    // --- HIỂN THỊ DANH SÁCH ---
    public function users() {
        $users = $this->userModel->getAllUsers();
        $data = [
            'users' => $users
        ];
        $this->view('admin/users/index', $data);
    }

    // --- THÊM NGƯỜI DÙNG MỚI ---
public function addUser() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_DEFAULT);

            $data = [
                // XÓA dòng nhận member_code ở đây
                'full_name' => trim($_POST['full_name'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'address' => trim($_POST['address'] ?? ''),
                'phone_number' => trim($_POST['phone_number'] ?? ''),
                'password' => trim($_POST['password'] ?? ''),
                'confirm_password' => trim($_POST['confirm_password'] ?? ''),
                'email_err' => '',
                'password_err' => '',
                'confirm_password_err' => ''
            ];

            // Validation (Giữ nguyên)
            if (empty($data['email'])) { $data['email_err'] = 'Please enter email'; }
            else {
                if ($this->userModel->findUserByField('email', $data['email'])) {
                    $data['email_err'] = 'Email is already taken';
                }
            }
            if (empty($data['password'])) { $data['password_err'] = 'Please enter password'; }
            if ($data['password'] != $data['confirm_password']) { $data['confirm_password_err'] = 'Passwords do not match'; }

            if (empty($data['email_err']) && empty($data['password_err']) && empty($data['confirm_password_err'])) {
                
                // Hash mật khẩu
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

                // Gọi hàm thêm user (Hàm này sẽ tự động sinh Member Code và insert vào DB)
                if ($this->userModel->addUser($data)) {
                    header('Location: ' . URL_ROOT . '/admin/users');
                } else {
                    die('Something went wrong');
                }
            } else {
                $this->view('admin/users/add', $data);
            }
        } else {
            // Init data (Không cần khởi tạo member_code nữa)
            $data = [
                'full_name' => '', 'email' => '', 
                'address' => '', 'phone_number' => '', 'password' => '', 'confirm_password' => ''
            ];
            $this->view('admin/users/add', $data);
        }
    }

    // --- SỬA NGƯỜI DÙNG ---
    public function editUser($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_DEFAULT);

            $data = [
                'user_id' => $id ?? null,
                'full_name' => trim($_POST['full_name'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'address' => trim($_POST['address'] ?? ''),
                'phone_number' => trim($_POST['phone_number'] ?? ''),
                'password' => trim($_POST['password'] ?? ''), // Mật khẩu mới (nếu có)
                'confirm_password' => trim($_POST['confirm_password'] ?? ''),
                'user' => $this->userModel->getUserById($id) // Để giữ lại data cũ nếu lỗi
            ];

            // Xử lý password nếu người dùng nhập mới
            if (!empty($data['password'])) {
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }

            if ($this->userModel->updateUser($data)) {
                header('Location: ' . URL_ROOT . '/admin/users');
            } else {
                die('Something went wrong');
            }

        } else {
            // Lấy thông tin user hiện tại
            $user = $this->userModel->getUserById($id);
            // Kiểm tra user có tồn tại không
            if(!$user) { header('Location: ' . URL_ROOT . '/admin/users'); }

            $data = [
                'user_id' => $id,
                'member_code' => $user->member_code ?? 'MB'.str_pad($user->user_id, 3, '0', STR_PAD_LEFT),
                'full_name' => $user->full_name,
                'email' => $user->email,
                'address' => $user->address,
                'phone_number' => $user->phone_number,
                'user' => $user
            ];
            $this->view('admin/users/edit', $data);
        }
    }

    // --- XÓA NGƯỜI DÙNG ---
    public function deleteUser($id) {
        if ($this->userModel->deleteUser($id)) {
            header('Location: ' . URL_ROOT . '/admin/users');
        } else {
            die('Something went wrong');
        }
    }

    public function index() {
        $this->books(); // Mặc định vào trang books nếu không có tham số
    }

    public function loans() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $data = [
                'member_code' => trim($_POST['member_code']),
                'copy_id'     => trim($_POST['copy_id']),
                'borrow_date' => $_POST['borrow_date'],
                'due_date'    => $_POST['due_date'],
                'note'        => trim($_POST['note']),
                'error'       => ''
            ];

            // Luôn lấy lại danh sách sách để hiển thị lại form nếu có lỗi
            $data['books'] = $this->loanModel->getAvailableCopies();

            // 1. Validate Member Code (Chỉnh lại kiểm tra object)
            $user = $this->loanModel->checkMemberExist($data['member_code']);
            if (!$user) {
                $data['error'] = 'Member code does not exist!';
                $this->view('admin/loans/borrow', $data);
                return;
            }

            // 2. Validate Date (Max 30 days)
            $diff = (strtotime($data['due_date']) - strtotime($data['borrow_date'])) / (60 * 60 * 24);
            if ($diff > 30 || $diff < 1) {
                $data['error'] = 'Invalid loan period (1-30 days only)!';
                $this->view('admin/loans/borrow', $data);
                return;
            }

            // 3. Thực hiện lưu
            if ($this->loanModel->createLoan($data)) {
                // Thành công -> Chuyển hướng
                header('Location: ' . URL_ROOT . '/admin/loans?success=true');
                exit();
            } else {
                // Thay vì die, hãy hiện lỗi lên giao diện để admin biết
                $data['error'] = 'System error: Could not process the loan. Please try again.';
                $this->view('admin/loans/borrow', $data);
            }

        } else {
            // Logic cho phương thức GET (giữ nguyên của bạn)
            $availableBooks = $this->loanModel->getAvailableCopies();
            $reservations = $this->loanModel->getReservations();
            $data = [
                'books' => $availableBooks,
                'reservations' => $reservations,
                'current_date' => date('Y-m-d'),
                'default_due_date' => date('Y-m-d', strtotime('+14 days')),
                'max_due_date' => date('Y-m-d', strtotime('+30 days')),
                'member_code' => '',
                'note' => '',
                'error' => ''
            ];
            $this->view('admin/loans/borrow', $data);
        }
    }

    public function returns() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Xử lý lưu dữ liệu trả sách
            $loan_id = $_POST['loan_id'];
            $copy_id = $_POST['copy_id'];
            $return_date = $_POST['return_date'];

            if ($this->loanModel->updateReturn($loan_id, $copy_id, $return_date)) {
                header('Location: ' . URL_ROOT . '/admin/loans?return_success=true');
                exit();
            } else {
                die("Something went wrong during the return process.");
            }
        } else {
            // Load giao diện trả sách (GET)
            $data = [
                'current_date' => date('Y-m-d'),
                'error' => ''
            ];
            $this->view('admin/loans/return', $data);
        }
    }

    public function getMemberLoans($code) {
        $loans = $this->loanModel->getActiveLoansByMember($code);
        header('Content-Type: application/json');
        echo json_encode($loans);
    }
}
    


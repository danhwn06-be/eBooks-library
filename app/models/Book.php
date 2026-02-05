<?php

class Book
{
    private $db;

    /**
     * Khởi tạo kết nối Database
     */
    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // 1. CÁC HÀM ĐỌC DỮ LIỆU (READ) - DÀNH CHO NGƯỜI DÙNG (USER FRONTEND)

    /**
     * Lấy danh sách sách có phân trang (Hiển thị Trang chủ)
     * @param int $limit Số lượng sách mỗi trang
     * @param int $offset Vị trí bắt đầu
     * @return array Danh sách sách
     */
    public function getBooksWithPagination($limit, $offset)
    {
        $sql = "SELECT 
                b.book_id, b.title, b.author, b.isbn, b.image_url, COUNT(bc.copy_id) AS total_copies, COALESCE(SUM(CASE WHEN bc.status = 'Available' THEN 1 ELSE 0 END), 0) AS available_copies 
            FROM Books b 
            LEFT JOIN BookCopies bc ON b.book_id = bc.book_id GROUP BY b.book_id 
            ORDER BY b.created_at DESC
            LIMIT :limit OFFSET :offset";

        try {
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Tìm kiếm sách theo Tiêu đề, Tác giả hoặc ISBN
     * @param string $keyword Từ khóa tìm kiếm
     * @return array Danh sách sách tìm thấy
     */
    public function searchBooks($keyword)
    {
        $sql = "SELECT b.*, 
                COUNT(bc.copy_id) AS total_copies, 
                COALESCE(SUM(CASE WHEN bc.status = 'Available' THEN 1 ELSE 0 END), 0) AS available_copies
                FROM Books b
                LEFT JOIN BookCopies bc ON b.book_id = bc.book_id
                WHERE b.title LIKE :kw OR b.author LIKE :kw OR b.isbn LIKE :kw
                GROUP BY b.book_id";

        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute(['kw' => "%$keyword%"]);
        return $stmt->fetchAll();
    }

    /**
     * Lấy chi tiết 1 cuốn sách theo ID
     * @param int $id ID của sách
     * @return mixed Mảng thông tin sách hoặc false nếu không tìm thấy
     */
    public function getBookById($id)
    {
        $sql = "SELECT 
                b.*,
                c.category_name,
                COUNT(bc.copy_id) AS total_copies,
                COALESCE(SUM(CASE WHEN bc.status = 'Available' THEN 1 ELSE 0 END), 0) AS available_copies
            FROM Books b
            LEFT JOIN Categories c ON b.category_id = c.category_id
            LEFT JOIN BookCopies bc ON b.book_id = bc.book_id
            WHERE b.book_id = :id
            GROUP BY b.book_id";

        try {
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException) {
            return false;
        };
    }

    /**
     * Kiểm tra ISBN đã tồn tại chưa
     * @param string $isbn
     * @return bool True nếu đã tồn tại
     */
    public function checkIsbnExists($isbn)
    {
        $sql = "SELECT COUNT(*) as count FROM Books WHERE isbn = :isbn";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':isbn', $isbn);
        $stmt->execute();
        $row = $stmt->fetch();
        return $row['count'] > 0;
    }

    /**
     * Lọc sách theo nhiều tiêu chí (Category, Year, Author)
     * @param array $f Mảng chứa các bộ lọc
     * @return array Danh sách sách đã lọc
     */
    public function getFilteredBooks($f)
    {
        $sql = "SELECT b.book_id, b.title, b.author, b.isbn, b.image_url, 
                COUNT(bc.copy_id) AS total_copies, 
                COALESCE(SUM(CASE WHEN bc.status = 'Available' THEN 1 ELSE 0 END), 0) AS available_copies 
                FROM Books b 
                LEFT JOIN BookCopies bc ON b.book_id = bc.book_id 
                WHERE 1=1";

        if (!empty($f['category'])) $sql .= " AND b.category_id = :cat";
        if (!empty($f['year']))     $sql .= " AND b.publication_year = :year";
        if (!empty($f['author']))   $sql .= " AND b.author LIKE :auth";

        $sql .= " GROUP BY b.book_id ORDER BY b.created_at DESC";

        $stmt = $this->db->getConnection()->prepare($sql);

        if (!empty($f['category'])) $stmt->bindValue(':cat', $f['category']);
        if (!empty($f['year']))     $stmt->bindValue(':year', $f['year']);
        if (!empty($f['author']))   $stmt->bindValue(':auth', "%" . $f['author'] . "%");

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Đếm tổng số sách trong hệ thống (Hỗ trợ phân trang)
     * @return int Tổng số sách
     */
    public function getTotalBookCount()
    {
        $sql = "SELECT COUNT(*) AS total
            FROM Books";
        try {
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute();
            $row = $stmt->fetch();
            $total = $row['total'];
            return $total;
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Hiển thị tất cả sách (Danh sách cơ bản, không phân trang)
     * @return array
     */
    public function getAllBooks()
    {
        $sql = "SELECT * FROM Books";
        $stmt = $this->db->getConnection()->query($sql);
        return $stmt->fetchAll();
    }

    // 2. CÁC HÀM LIÊN QUAN ĐẾN DANH MỤC (CATEGORY RELATED)

    /**
     * Lấy danh sách sách theo ID danh mục (Không phân trang)
     * @param int $cat_id ID danh mục
     * @return array
     */
    public function getBooksByCategoryId($cat_id)
    {
        $sql = "SELECT b.book_id, b.title, b.author, b.image_url, 
                COUNT(bc.copy_id) AS total_copies, 
                COALESCE(SUM(CASE WHEN bc.status = 'Available' THEN 1 ELSE 0 END), 0) AS available_copies
                FROM Books b 
                LEFT JOIN BookCopies bc ON b.book_id = bc.book_id 
                WHERE b.category_id = :cat_id
                GROUP BY b.book_id";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':cat_id', $cat_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Lấy danh sách sách theo danh mục có phân trang
     * @param int $cat_id ID danh mục
     * @param int $limit Số lượng mỗi trang
     * @param int $offset Vị trí bắt đầu
     * @return array
     */
    public function getBooksByCategoryIdPaginated($cat_id, $limit, $offset)
    {
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
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->bindValue(':cat_id', $cat_id, PDO::PARAM_INT);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Đếm số lượng sách trong một danh mục (Hỗ trợ phân trang)
     * @param int $cat_id ID danh mục
     * @return int Số lượng sách
     */
    public function getBookCountByCategoryId($cat_id)
    {
        $sql = "SELECT COUNT(*) as total FROM Books WHERE category_id = :cat_id";
        try {
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->bindValue(':cat_id', $cat_id, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch();
            return $row['total'];
        } catch (PDOException $e) {
            return 0;
        }
    }

    /**
     * Lấy tất cả danh mục sách
     * @return array Danh sách danh mục
     */
    public function getAllCategories()
    {
        $sql = "SELECT * FROM Categories ORDER BY category_name ASC";
        $stmt = $this->db->getConnection()->query($sql);
        $categories = $stmt->fetchAll();

        return $categories;
    }

    /**
     * Lấy tên danh mục dựa trên ID
     * @param int $id ID danh mục
     * @return string Tên danh mục
     */
    public function getCategoryNameById($id)
    {
        $sql = "SELECT category_name FROM Categories WHERE category_id = :id";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result ? $result['category_name'] : 'Unknown Category';
    }


    // 3. CÁC HÀM QUẢN TRỊ (ADMIN READ)

    /**
     * Lấy danh sách sách cho Admin có phân trang (Không Cache - Realtime)
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getBooksForAdminPaginated($limit, $offset)
    {
        $sql = "SELECT b.*, c.category_name, COUNT(bc.copy_id) as total_copies
                FROM Books b
                LEFT JOIN Categories c ON b.category_id = c.category_id
                LEFT JOIN BookCopies bc ON b.book_id = bc.book_id
                GROUP BY b.book_id
                ORDER BY b.book_id DESC
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Lấy toàn bộ sách cho Dashboard hoặc Export
     * @return array
     */
    public function getBooksForAdmin()
    {
        $sql = "SELECT b.*, c.category_name, COUNT(bc.copy_id) as total_copies
                FROM Books b
                LEFT JOIN Categories c ON b.category_id = c.category_id
                LEFT JOIN BookCopies bc ON b.book_id = bc.book_id
                GROUP BY b.book_id
                ORDER BY b.book_id DESC";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }


    // 4. CÁC HÀM GHI DỮ LIỆU (WRITE - CRUD)


    /**
     * Thêm sách mới vào hệ thống
     * @param array $data Mảng dữ liệu sách
     * @return mixed ID sách vừa tạo hoặc false
     */
    public function addBook($data)
    {
        $sql = "INSERT INTO Books (title, author, isbn, category_id, publisher, publication_year, description, image_url) 
                VALUES (:title, :author, :isbn, :category_id, :publisher, :year, :desc, :image)";

        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':title', $data['title']);
        $stmt->bindValue(':author', $data['author']);
        $stmt->bindValue(':isbn', $data['isbn']);
        $stmt->bindValue(':category_id', $data['category_id']);
        $stmt->bindValue(':publisher', $data['publisher']);
        $stmt->bindValue(':year', $data['publication_year']);
        $stmt->bindValue(':desc', $data['description']);
        $stmt->bindValue(':image', $data['image_url']); // Lưu tên file ảnh

        if ($stmt->execute()) {
            return $this->db->getConnection()->lastInsertId();
        }
        return false;
    }

    /**
     * Cập nhật thông tin sách
     * @param array $data Mảng dữ liệu cập nhật
     * @return bool True nếu thành công
     */
    public function updateBook($data)
    {
        // Nếu người dùng không upload ảnh mới ($data['image_url'] rỗng), ta không update cột image_url
        if (empty($data['image_url'])) {
            $sql = "UPDATE Books SET 
                    title = :title, author = :author, isbn = :isbn, 
                    category_id = :category_id, publisher = :publisher, 
                    publication_year = :year, description = :desc
                    WHERE book_id = :id";
        } else {
            // Nếu có ảnh mới, update cả cột image_url
            $sql = "UPDATE Books SET 
                    title = :title, author = :author, isbn = :isbn, 
                    category_id = :category_id, publisher = :publisher, 
                    publication_year = :year, description = :desc,
                    image_url = :image
                    WHERE book_id = :id";
        }

        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':title', $data['title']);
        $stmt->bindValue(':author', $data['author']);
        $stmt->bindValue(':isbn', $data['isbn']);
        $stmt->bindValue(':category_id', $data['category_id']);
        $stmt->bindValue(':publisher', $data['publisher']);
        $stmt->bindValue(':year', $data['publication_year']);
        $stmt->bindValue(':desc', $data['description']);
        $stmt->bindValue(':id', $data['book_id']);

        if (!empty($data['image_url'])) {
            $stmt->bindValue(':image', $data['image_url']);
        }

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    /**
     * Xóa sách khỏi hệ thống
     * @param int $id ID sách cần xóa
     * @return bool True nếu xóa thành công
     */
    public function deleteBook($id)
    {
        $sql = "DELETE FROM Books WHERE book_id = :id";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':id', $id);
        
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
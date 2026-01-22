<?php

use PgSql\Lob;

class BookModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // Lấy danh sách có phân trang
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

    // Đếm tổng số sách (để tính số trang)
    public function getTotalBookCount()
    {
        $sql = "SELECT COUNT(*) AS total
            FROM Books";
        try {
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute();
            $row = $stmt->fetch();
            return $row['total'];
        } catch (PDOException $e) {
            return [];
        }
    }

    // Hiển thị tất cả sách
    public function getAllBooks()
    {
        $sql = "SELECT * FROM Books";
        $stmt = $this->db->getConnection()->query($sql);
        return $stmt->fetchAll();
    }

    //Tìm kiếm sách bằng tên sách
    public function searchByTitle($keyword)
    {
        $sql = "SELECT b.*, 
                COUNT(bc.copy_id) AS total_copies, 
                COALESCE(SUM(CASE WHEN bc.status = 'Available' THEN 1 ELSE 0 END), 0) AS available_copies
                FROM Books b
                LEFT JOIN BookCopies bc ON b.book_id = bc.book_id
                WHERE b.title LIKE :kw
                GROUP BY b.book_id";

        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute(['kw' => "%$keyword%"]);
        return $stmt->fetchAll();
    }

    // Lấy danh sách tất cả danh mục
    public function getAllCategories()
    {
        $sql = "SELECT * FROM Categories ORDER BY category_name ASC";
        try {
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    // Lấy chi tiết 1 cuốn sách theo ID
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

    // Lấy sách theo Category ID
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

    // Lấy tên Category theo ID
    public function getCategoryNameById($cat_id)
    {
        $sql = "SELECT category_name FROM Categories WHERE category_id = :cat_id";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':cat_id', $cat_id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch();
        return $row ? $row['category_name'] : '';
    }

    // Method để lấy database instance (cần cho CategoryController)
    public function getDb()
    {
        return $this->db;
    }

    /* Get BOOK cho admin */
    // Lấy sách để hiển thị cho dashboard
    public function getBooksForAdmin()
    {
        $sql = "SELECT
            b.book_id,
            b.isbn,
            b.title,
            b.author,
            b.image_url,
            b.publisher,
            b.publication_year,
            b.description,
            b.created_at,
            c.category_name,
            COUNT(bc.copy_id) as total_copies
        FROM Books b
        LEFT JOIN Categories c ON b.category_id = c.category_id
        LEFT JOIN BookCopies bc ON b.book_id = bc.book_id
        GROUP BY b.book_id
        ORDER BY b.book_id DESC";

        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Lấy thông tin cơ bản của cuốn sách: public function getBookById($id) {};

    // Lấy danh sách các bản sao của sách đó
    public function getCopiesByBookId($bookId)
    {
        $sql = "SELECT *
            FROM BookCopies
            WHERE book_id = :book_id";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':book_id', $bookId);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}

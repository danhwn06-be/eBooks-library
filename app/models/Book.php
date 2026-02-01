<?php

class Book
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
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
            $total = $row['total'];
            return $total;
        } catch (PDOException $e) {
            return [];
        }
    }

    // Hiển thị tất cả sách (Basic list)
    public function getAllBooks()
    {
        $sql = "SELECT * FROM Books";
        $stmt = $this->db->getConnection()->query($sql);
        return $stmt->fetchAll();
    }

    // Lấy danh sách có phân trang (Trang chủ)
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
            $result = $stmt->fetch();
            return $result;
        } catch (PDOException) {
            return false;
        };
    }

    // Lấy tất cả danh mục sách (Bổ sung để hỗ trợ Controller và Cache) -- test
    public function getAllCategories()
    {
        $sql = "SELECT * FROM Categories ORDER BY category_name ASC";
        $stmt = $this->db->getConnection()->query($sql);
        $categories = $stmt->fetchAll();

        return $categories;
    }

    // Lấy tên danh mục theo ID (Bổ sung)
    public function getCategoryNameById($id)
    {
        $sql = "SELECT category_name FROM Categories WHERE category_id = :id";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result ? $result['category_name'] : 'Unknown Category';
    }

    //Tìm kiếm sách (Title, Author, ISBN)
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

    // Lọc sách theo nhiều tiêu chí
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


    // Đếm số sách trong 1 danh mục
    public function getBookCountByCategoryId($cat_id)
    {
        $sql = "SELECT COUNT(*) as total FROM Books WHERE category_id = :cat_id";
        try {
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->bindValue(':cat_id', $cat_id, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch();
            $total = $row['total'];
            return $total;
        } catch (PDOException $e) {
            return 0;
        }
    }

    // Lấy sách theo danh mục có phân trang
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

    // Lấy sách để hiển thị cho dashboard
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
        $result = $stmt->fetchAll();
        return $result;
    }

    // Thêm sách mới
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

    // Cập nhật sách
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

    // Xóa sách
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




   
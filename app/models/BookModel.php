<?php
class BookModel {
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // Lấy danh sách có phân trang
    public function getBooksWithPagination($limit, $offset) {
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
    public function getTotalBookCount() {
        $sql = "SELECT COUNT(*) AS total
            FROM Books";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute();
        $row = $stmt->fetch();
        return $row['total'];
    }
}
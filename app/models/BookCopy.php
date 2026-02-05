<?php
class BookCopy {
    private $db;

    /**
     * Khởi tạo kết nối Database
     */
    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // 1. READ METHODS

    /**
     * Lấy danh sách các bản sao của một cuốn sách cụ thể
     * @param int $bookId ID của sách
     * @return array Danh sách các bản sao
     */
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

    /**
     * Lấy thông tin chi tiết của một bản sao dựa trên ID
     * @param int $id ID của bản sao
     * @return mixed Thông tin bản sao hoặc false nếu không tìm thấy
     */
    public function getCopyById($id)
    {
        $sql = "SELECT * FROM BookCopies WHERE copy_id = :id";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    // 2. WRITE METHODS (ADMIN)

    /**
     * Thêm một bản sao sách mới vào hệ thống
     * @param array $data Mảng chứa thông tin bản sao
     * @return bool True nếu thêm thành công
     */
    public function addCopy($data)
    {
        $sql = "INSERT INTO BookCopies (book_id, copy_code, status, condition_note) 
                VALUES (:book_id, :copy_code, :status, :quality)";

        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':book_id', $data['book_id']);
        $stmt->bindValue(':copy_code', $data['copy_code']);
        $stmt->bindValue(':status', $data['status']);
        $stmt->bindValue(':quality', $data['condition_note']); // Lưu ý: Database dùng cột 'quality' hay 'condition_note' hãy kiểm tra lại, ở đây tôi dùng 'quality' theo code cũ của bạn

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    /**
     * Cập nhật thông tin của một bản sao
     * @param array $data Mảng chứa thông tin cập nhật
     * @return bool True nếu cập nhật thành công
     */
    public function updateCopy($data)
    {
        $sql = "UPDATE BookCopies SET status = :status, condition_note = :quality WHERE copy_id = :id";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':status', $data['status']);
        $stmt->bindValue(':quality', $data['quality']);
        $stmt->bindValue(':id', $data['copy_id']);
        
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    /**
     * Xóa một bản sao khỏi hệ thống
     * @param int $id ID của bản sao cần xóa
     * @return bool True nếu xóa thành công
     */
    public function deleteCopy($id)
    {
        $sql = "DELETE FROM BookCopies WHERE copy_id = :id";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':id', $id);
        
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
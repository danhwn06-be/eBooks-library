<?php
class BookCopy extends Model
{

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
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':book_id' => $bookId]);
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
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
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

        $stmt = $this->db->prepare($sql);
        $params = [
            ':book_id' => $data['book_id'],
            ':copy_code' => $data['copy_code'],
            ':status' => $data['status'],
            ':quality' => $data['condition_note']
        ];

        if ($stmt->execute($params)) {
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
        $stmt = $this->db->prepare($sql);
        $params = [
            ':status' => $data['status'],
            ':quality' => $data['quality'],
            ':id' => $data['copy_id']
        ];
        
        if ($stmt->execute($params)) {
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
        $stmt = $this->db->prepare($sql);
        
        if ($stmt->execute([':id' => $id])) {
            return true;
        }
        return false;
    }
}
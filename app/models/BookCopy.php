<?php
class BookCopy {
    private $db;
    private $cache;

    public function __construct()
    {
        $this->db = Database::getInstance();
        require_once APP_ROOT . '/app/core/Cache.php';
        $this->cache = new Cache();
    }

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

    // Thêm bản sao mới
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
            $this->cache->delete('book_detail_' . $data['book_id']);
            $this->cache->delete('admin_books_list');
            return true;
        }
        return false;
    }

    // Lấy thông tin 1 bản sao cụ thể (để sửa)
    public function getCopyById($id)
    {
        $sql = "SELECT * FROM BookCopies WHERE copy_id = :id";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    // Cập nhật bản sao
    public function updateCopy($data)
    {
        $oldCopy = $this->getCopyById($data['copy_id']);

        $sql = "UPDATE BookCopies SET status = :status, condition_note = :quality WHERE copy_id = :id";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':status', $data['status']);
        $stmt->bindValue(':quality', $data['quality']);
        $stmt->bindValue(':id', $data['copy_id']);
        
        if ($stmt->execute()) {
            if ($oldCopy) $this->cache->delete('book_detail_' . $oldCopy['book_id']);
            $this->cache->delete('admin_books_list');
            return true;
        }
        return false;
    }

    // Xóa bản sao
    public function deleteCopy($id)
    {
        $oldCopy = $this->getCopyById($id);

        $sql = "DELETE FROM BookCopies WHERE copy_id = :id";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':id', $id);
        
        if ($stmt->execute()) {
            if ($oldCopy) $this->cache->delete('book_detail_' . $oldCopy['book_id']);
            $this->cache->delete('admin_books_list');
            return true;
        }
        return false;
    }
}

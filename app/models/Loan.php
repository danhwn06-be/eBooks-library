<?php

class Loan
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // 1. Đếm số sách ĐANG mượn (Reading) - Chưa trả
    public function countReading($userId) {
        $sql = "SELECT COUNT(*) as count FROM Loans WHERE user_id = :user_id AND status = 'Active'";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':user_id', $userId);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        return $result ? $result->count : 0;
    }

    // 2. Đếm tổng số sách ĐÃ mượn trong quá khứ
    public function countTotalBorrowed($userId)
    {
        $sql = "SELECT COUNT(*) as count FROM Loans WHERE user_id = :user_id";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':user_id', $userId);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        return $result ? $result->count : 0;
    }

    // 3. Lấy lịch sử mượn trả (Chi tiết hơn hàm cũ)
    public function getBorrowHistory($userId)
    {
        $sql = "SELECT DISTINCT b.book_id, b.title, l.loan_id, l.borrow_date, l.due_date, l.return_date, l.status
                FROM Loans l
                JOIN BookCopies bc ON l.copy_id = bc.copy_id
                JOIN Books b ON bc.book_id = b.book_id
                WHERE l.user_id = :user_id
                ORDER BY l.borrow_date DESC";
        try {
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->bindValue(':user_id', $userId);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            return [];
        }
    }

    // 4. Tạo phiếu mượn
    public function createLoan($data)
    {
        $dueDate = empty($data['due_date']) ? date('Y-m-d', strtotime('+14 days')) : $data['due_date'];
        $borrowDate = date('Y-m-d');

        $sql = "INSERT INTO Loans (user_id, copy_id, borrow_date, due_date, status)
                VALUES (:user_id, :copy_id, :borrow_date, :due_date, 'Active')";

        try {
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->bindValue(':user_id', $data['user_id']);
            $stmt->bindValue(':copy_id', $data['copy_id']);
            $stmt->bindValue(':borrow_date', $borrowDate);
            $stmt->bindValue(':due_date', $dueDate);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
}

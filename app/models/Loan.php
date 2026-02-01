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

    private function conn() {
        return $this->db->getConnection();
    }

    public function getCurrentBorrowByUser($userId)
    {
        $sql = "SELECT l.loan_id, bk.title, l.borrow_date, l.due_date
                FROM Loans l
                JOIN Books bk ON l.book_id = bk.book_id
                WHERE l.user_id = :userId
                  AND l.return_date IS NULL";
        try {
            $stmt = $this->conn()->prepare($sql);
            $stmt->bindValue(':userId', $userId);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ); // Tương đương resultSet()
        } catch (PDOException $e) {
            return [];
        }   
    }

    public function getBorrowHistoryByUser($userId)
    {
        $sql = "SELECT bk.title, l.borrow_date, l.return_date
                FROM Loans l
                JOIN Books bk ON l.book_id = bk.book_id
                WHERE l.user_id = :userId
                  AND l.return_date IS NOT NULL";
        try {
            $stmt = $this->conn()->prepare($sql);
            $stmt->bindValue(':userId', $userId);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getAvailableCopies() {
        $sql = "SELECT bc.copy_id, b.title, b.book_id 
                FROM BookCopies bc 
                JOIN Books b ON bc.book_id = b.book_id 
                WHERE bc.status = 'Available'";
        $stmt = $this->conn()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ); 
    }

    public function checkMemberExist($member_code) {
        $sql = "SELECT user_id FROM Users WHERE member_code = :code";
        $stmt = $this->conn()->prepare($sql);
        $stmt->bindValue(':code', $member_code);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_OBJ); // Trả về object hoặc false nếu không thấy
    }

    public function createLoan($data) {
        try {
            // Bắt đầu Transaction để đảm bảo dữ liệu đồng nhất
            $this->conn()->beginTransaction();

            // 1. Tìm user_id dựa trên member_code
            $sqlUser = "SELECT user_id FROM Users WHERE member_code = :member_code";
            $stmtUser = $this->conn()->prepare($sqlUser);
            $stmtUser->bindValue(':member_code', $data['member_code']);
            $stmtUser->execute();
            $user = $stmtUser->fetch(PDO::FETCH_OBJ);

            if (!$user) {
                return "User not found"; // Trả về lỗi nếu member code sai
            }

            // 2. Chèn dữ liệu vào bảng Loans
            $sqlLoan = "INSERT INTO Loans (user_id, copy_id, borrow_date, due_date, status, note) 
                        VALUES (:user_id, :copy_id, :borrow_date, :due_date, 'Active', :note)";
            $stmtLoan = $this->conn()->prepare($sqlLoan);
            $stmtLoan->bindValue(':user_id', $user->user_id);
            $stmtLoan->bindValue(':copy_id', $data['copy_id']);
            $stmtLoan->bindValue(':borrow_date', $data['borrow_date']);
            $stmtLoan->bindValue(':due_date', $data['due_date']);
            $stmtLoan->bindValue(':note', $data['note']);
            $stmtLoan->execute();

            // 3. Cập nhật trạng thái bản sao sách (BookCopies) thành 'Borrowed'
            $sqlUpdateCopy = "UPDATE BookCopies SET status = 'Borrowed' WHERE copy_id = :copy_id";
            $stmtUpdate = $this->conn()->prepare($sqlUpdateCopy);
            $stmtUpdate->bindValue(':copy_id', $data['copy_id']);
            $stmtUpdate->execute();

            // Hoàn tất lưu dữ liệu
            $this->conn()->commit();
            return true;
        } catch (PDOException $e) {
            $this->conn()->rollBack();
            return false;
        }
    }

    public function getActiveLoansByMember($member_code) {
        // Lấy kết nối PDO trực tiếp từ Singleton Database
        $conn = $this->db->getConnection();
    
        $sql = "SELECT l.loan_id, bc.copy_id, b.title, l.borrow_date, l.due_date
                FROM Loans l
                JOIN BookCopies bc ON l.copy_id = bc.copy_id
                JOIN Books b ON bc.book_id = b.book_id
               JOIN Users u ON l.user_id = u.user_id
               WHERE u.member_code = :member_code
                  AND l.return_date IS NULL";
              
        try {
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':member_code', $member_code);
            $stmt->execute();
            // Trả về Fetch Object để khớp với code View hiện tại
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getReservations() {
        $conn = $this->db->getConnection();
        $sql = "SELECT r.*, u.member_code, b.title 
                FROM Reservations r
                JOIN Users u ON r.user_id = u.user_id
                JOIN Books b ON r.book_id = b.book_id
                ORDER BY r.reservation_date DESC";
        
        try {
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            return [];
        }
    }


public function updateReturn($loan_id, $copy_id, $return_date, $note) {
    try {
        $this->conn()->beginTransaction();

            // Bước 1: Cập nhật phiếu mượn
            $sql1 = "UPDATE Loans SET return_date = :return_date, status = 'Returned' WHERE loan_id = :loan_id";
            $stmt1 = $conn->prepare($sql1);
            $stmt1->bindValue(':return_date', $return_date);
            $stmt1->bindValue(':loan_id', $loan_id);
            $stmt1->execute();
        // 1. Cập nhật ngày trả, trạng thái và ghi chú (Good/Bad...)
        $sql1 = "UPDATE Loans 
                 SET return_date = :return_date, 
                     status = 'Returned',
                     note = :note 
                 WHERE loan_id = :loan_id";
        $stmt1 = $this->conn()->prepare($sql1);
        $stmt1->bindValue(':return_date', $return_date);
        $stmt1->bindValue(':note', $note);
        $stmt1->bindValue(':loan_id', $loan_id);
        $stmt1->execute();

            // Bước 2: Cập nhật trạng thái sách trong kho
            $sql2 = "UPDATE BookCopies SET status = 'Available' WHERE copy_id = :copy_id";
            $stmt2 = $conn->prepare($sql2);
            $stmt2->bindValue(':copy_id', $copy_id);
            $stmt2->execute();
        // 2. Cập nhật sách về trạng thái có sẵn
        $sql2 = "UPDATE BookCopies SET status = 'Available' WHERE copy_id = :copy_id";
        $stmt2 = $this->conn()->prepare($sql2);
        $stmt2->bindValue(':copy_id', $copy_id);
        $stmt2->execute();

        return $this->conn()->commit();
    } catch (PDOException $e) {
        $this->conn()->rollBack();
        return false;
    }
}

    public function getAllLoans() {
        $sql = "SELECT l.*, u.member_code, b.title, l.copy_id 
                FROM Loans l
                JOIN Users u ON l.user_id = u.user_id
                JOIN BookCopies bc ON l.copy_id = bc.copy_id
                JOIN Books b ON bc.book_id = b.book_id
                ORDER BY l.borrow_date DESC";
        $stmt = $this->conn()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getLoanStats() {
        $stats = ['total' => 0, 'overdue' => 0, 'reservations' => 0];
        
        // Tổng số lượt mượn đang Active
        $sqlTotal = "SELECT COUNT(*) as count FROM Loans WHERE status = 'Active'";
        $stmt1 = $this->conn()->prepare($sqlTotal);
        $stmt1->execute();
        $stats['total'] = $stmt1->fetch(PDO::FETCH_OBJ)->count;

        // Số lượng quá hạn (Due date < Today và chưa trả)
        $sqlOverdue = "SELECT COUNT(*) as count FROM Loans WHERE status = 'Active' AND due_date < CURDATE()";
        $stmt2 = $this->conn()->prepare($sqlOverdue);
        $stmt2->execute();
        $stats['overdue'] = $stmt2->fetch(PDO::FETCH_OBJ)->count;

        // Số lượng đặt trước
        $sqlRes = "SELECT COUNT(*) as count FROM Reservations";
        $stmt3 = $this->conn()->prepare($sqlRes);
        $stmt3->execute();
        $stats['reservations'] = $stmt3->fetch(PDO::FETCH_OBJ)->count;

        return $stats;
    }
}

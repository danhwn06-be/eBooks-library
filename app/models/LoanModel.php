<?php

class LoanModel
{
    private $db;

    public function __construct()
    {
        // Lấy đối tượng Database singleton
        $this->db = Database::getInstance();
    }

    // Lấy kết nối PDO thực tế
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

    // SỬA LẠI: Trả về Object User để lấy được user_id
    public function checkMemberExist($member_code) {
        $sql = "SELECT user_id FROM Users WHERE member_code = :code";
        $stmt = $this->conn()->prepare($sql);
        $stmt->bindValue(':code', $member_code);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_OBJ); // Trả về object hoặc false nếu không thấy
    }

    public function createLoan($data) {
        try {
            // Bước 1: Lấy thông tin user từ member_code
            $user = $this->checkMemberExist($data['member_code']);
            if (!$user) return false; // Không thấy user thì thoát luôn

            $this->conn()->beginTransaction();

            // Bước 2: Insert vào bảng Loans (Sử dụng user_id vừa lấy được)
            $sql1 = "INSERT INTO Loans (user_id, copy_id, borrow_date, due_date, note, status) 
                     VALUES (:user_id, :copy_id, :borrow_date, :due_date, :note, 'Active')";
            
            $stmt1 = $this->conn()->prepare($sql1);
            $stmt1->bindValue(':user_id', $user->user_id); // Bây giờ $user đã là object, lấy được ID
            $stmt1->bindValue(':copy_id', $data['copy_id']);
            $stmt1->bindValue(':borrow_date', $data['borrow_date']);
            $stmt1->bindValue(':due_date', $data['due_date']);
            $stmt1->bindValue(':note', $data['note']);
            $stmt1->execute();

            // Bước 3: Cập nhật trạng thái bản sao sách thành 'Borrowed'
            $sql2 = "UPDATE BookCopies SET status = 'Borrowed' WHERE copy_id = :copy_id";
            $stmt2 = $this->conn()->prepare($sql2);
            $stmt2->bindValue(':copy_id', $data['copy_id']);
            $stmt2->execute();

            $this->conn()->commit();
            return true;
        } catch (PDOException $e) {
            if ($this->conn()->inTransaction()) {
                $this->conn()->rollBack();
            }
            return false;
        }
    }

    public function getActiveLoansByMember($member_code) {
        $sql = "SELECT l.loan_id, bc.copy_id, b.title, l.borrow_date, l.due_date
                FROM Loans l
                JOIN BookCopies bc ON l.copy_id = bc.copy_id
                JOIN Books b ON bc.book_id = b.book_id
                JOIN Users u ON l.user_id = u.user_id
                WHERE u.member_code = :member_code
                  AND l.return_date IS NULL";
        try {
            $stmt = $this->conn()->prepare($sql);
            $stmt->bindValue(':member_code', $member_code);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function updateReturnDate($loan_id, $return_date, $copy_id) {
        try {
            $this->conn()->beginTransaction();

            // Cập nhật ngày trả sách trong bảng Loans
            $sql1 = "UPDATE Loans SET return_date = :return_date, status = 'Returned' WHERE loan_id = :loan_id";
            $stmt1 = $this->conn()->prepare($sql1);
            $stmt1->bindValue(':return_date', $return_date);
            $stmt1->bindValue(':loan_id', $loan_id);
            $stmt1->execute();

            // Cập nhật trạng thái bản sao sách thành 'Available'
            $sql2 = "UPDATE BookCopies SET status = 'Available' WHERE copy_id = :copy_id";
            $stmt2 = $this->conn()->prepare($sql2);
            $stmt2->bindValue(':copy_id', $copy_id);
            $stmt2->execute();

            $this->conn()->commit();
            return true;
        } catch (PDOException $e) {
            if ($this->conn()->inTransaction()) {
                $this->conn()->rollBack();
            }
            return false;
        }
    }
}
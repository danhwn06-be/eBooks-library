<?php

class Loan
{
    private $db;

    /**
     * Khởi tạo kết nối Database
     */
    public function __construct()
    {
        $this->db = Database::getInstance();
    }


    // 1. USER PROFILE & HISTORY

    /**
     * Đếm số sách ĐANG mượn (Reading) - Chưa trả
     * @param int $userId ID người dùng
     * @return int Số lượng sách đang mượn
     */
    public function countReading($userId) {
        $sql = "SELECT COUNT(*) as count FROM Loans WHERE user_id = :user_id AND status = 'Active'";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':user_id', $userId);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        return $result ? $result->count : 0;
    }

    /**
     * Đếm tổng số sách ĐÃ mượn trong quá khứ
     * @param int $userId ID người dùng
     * @return int Tổng số sách đã mượn
     */
    public function countTotalBorrowed($userId)
    {
        $sql = "SELECT COUNT(*) as count FROM Loans WHERE user_id = :user_id";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':user_id', $userId);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        return $result ? $result->count : 0;
    }

    /**
     * Lấy lịch sử mượn trả chi tiết
     * @param int $userId ID người dùng
     * @return array Danh sách lịch sử mượn
     */
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

    /**
     * Lấy danh sách sách đang mượn của user (chưa trả)
     * @param int $userId
     * @return array
     */
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

    /**
     * Lấy lịch sử sách đã trả của user
     * @param int $userId
     * @return array
     */
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

    // 2. LOAN PROCESS (ADMIN)

    /**
     * Lấy danh sách các bản sao sách có sẵn để mượn
     * @return array
     */
    public function getAvailableCopies() {
        $sql = "SELECT bc.copy_id, b.title, b.book_id 
                FROM BookCopies bc 
                JOIN Books b ON bc.book_id = b.book_id 
                WHERE bc.status IN ('Available', 'Reserved')"; // Cho phép mượn sách đã đặt trước
        $stmt = $this->conn()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ); 
    }

    /**
     * Kiểm tra thành viên có tồn tại qua mã thành viên
     * @param string $member_code
     * @return mixed Object user hoặc false
     */
    public function checkMemberExist($member_code) {
        $sql = "SELECT user_id FROM Users WHERE member_code = :code";
        $stmt = $this->conn()->prepare($sql);
        $stmt->bindValue(':code', $member_code);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_OBJ); // Trả về object hoặc false nếu không thấy
    }

    /**
     * Tạo phiếu mượn sách mới (Transaction)
     * @param array $data Thông tin mượn
     * @return mixed True nếu thành công, string lỗi nếu thất bại
     */
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
                return "User not found";
            }

            // 2. Chèn dữ liệu vào bảng Loans
            $sqlLoan = "INSERT INTO Loans (user_id, copy_id, borrow_date, due_date, status, note) 
                        VALUES (:user_id, :copy_id, :borrow_date, :due_date, 'Active', :note)";
            $stmtLoan = $this->conn()->prepare($sqlLoan);
            $stmtLoan->bindValue(':user_id', $user->user_id);
            $stmtLoan->bindValue(':copy_id', $data['copy_id']);
            // Nối thêm giờ hiện tại để không bị 00:00:00
            $stmtLoan->bindValue(':borrow_date', $data['borrow_date'] . ' ' . date('H:i:s'));
            $stmtLoan->bindValue(':due_date', $data['due_date']);
            $stmtLoan->bindValue(':note', $data['note']);
            $stmtLoan->execute();

            // 3. Cập nhật trạng thái bản sao sách (BookCopies) thành 'Borrowed'
            $sqlUpdateCopy = "UPDATE BookCopies SET status = 'Borrowed' WHERE copy_id = :copy_id";
            $stmtUpdate = $this->conn()->prepare($sqlUpdateCopy);
            $stmtUpdate->bindValue(':copy_id', $data['copy_id']);
            $stmtUpdate->execute();

            // 4. Cập nhật trạng thái Reservation thành 'Fulfilled' (nếu có)
            // Lấy book_id từ copy_id để tìm đơn đặt hàng tương ứng
            $sqlGetBook = "SELECT book_id FROM BookCopies WHERE copy_id = :copy_id";
            $stmtGetBook = $this->conn()->prepare($sqlGetBook);
            $stmtGetBook->bindValue(':copy_id', $data['copy_id']);
            $stmtGetBook->execute();
            $book = $stmtGetBook->fetch(PDO::FETCH_OBJ);

            if ($book) {
                $sqlRes = "UPDATE Reservations SET status = 'Fulfilled' WHERE user_id = :user_id AND book_id = :book_id AND status NOT IN ('Cancelled', 'Fulfilled')";
                $stmtRes = $this->conn()->prepare($sqlRes);
                $stmtRes->bindValue(':user_id', $user->user_id);
                $stmtRes->bindValue(':book_id', $book->book_id);
                $stmtRes->execute();
            }

            // Hoàn tất lưu dữ liệu
            $this->conn()->commit();
            return true;
        } catch (PDOException $e) {
            $this->conn()->rollBack();
            return false;
        }
    }

    // 3. RETURN PROCESS (ADMIN)

    /**
     * Lấy danh sách sách đang mượn theo mã thành viên
     * @param string $member_code
     * @return array
     */
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


    /**
     * Cập nhật trả sách (Transaction)
     * @param int $loan_id
     * @param int $copy_id
     * @param string $return_date
     * @param string|null $note
     * @return bool
     */
    public function updateReturn($loan_id, $copy_id, $return_date, $note = null) {
        try {
            $this->conn()->beginTransaction();

            // 1. Cập nhật ngày trả, trạng thái và ghi chú (Good/Bad...)
            $sql1 = "UPDATE Loans 
                     SET return_date = :return_date, 
                         status = 'Returned',
                         note = :note 
                     WHERE loan_id = :loan_id";
            $stmt1 = $this->conn()->prepare($sql1);
            // Nối thêm giờ hiện tại khi trả sách
            $stmt1->bindValue(':return_date', $return_date . ' ' . date('H:i:s'));
            $stmt1->bindValue(':note', $note);
            $stmt1->bindValue(':loan_id', $loan_id);
            $stmt1->execute();

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

    // 4. ADMIN DASHBOARD & TRACKING

    /**
     * Lấy tất cả phiếu mượn (Lịch sử toàn hệ thống)
     * @return array
     */
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

    /**
     * Lấy danh sách các đơn đặt trước (Reservations)
     * @return array
     */
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

    /**
     * Lấy thống kê mượn trả cho Dashboard
     * @return array
     */
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

    // 5. HELPERS

    /**
     * Helper lấy kết nối PDO
     */
    private function conn() {
        return $this->db->getConnection();
    }
}
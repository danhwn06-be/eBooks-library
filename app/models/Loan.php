<?php

class Loan extends Model
{


    // 1. USER PROFILE & HISTORY

    /**
     * Đếm số sách ĐANG mượn (Reading) - Chưa trả
     * @param int $userId ID người dùng
     * @return int Số lượng sách đang mượn
     */
    public function countReading($userId) {
        $sql = "SELECT COUNT(*) as count FROM Loans WHERE user_id = :user_id AND status = 'Active'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
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
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
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
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':user_id' => $userId]);
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
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':userId' => $userId]);
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
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':userId' => $userId]);
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
        $stmt = $this->db->prepare($sql);
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
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':code' => $member_code]);
        
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
            $this->db->beginTransaction();

            // 1. Tìm user_id dựa trên member_code
            $user = $this->checkMemberExist($data['member_code']);

            if (!$user) {
                return "User not found";
            }

            // 2. Chèn dữ liệu vào bảng Loans
            $sqlLoan = "INSERT INTO Loans (user_id, copy_id, borrow_date, due_date, status, note) 
                        VALUES (:user_id, :copy_id, :borrow_date, :due_date, 'Active', :note)";
            $stmtLoan = $this->db->prepare($sqlLoan);
            
            // Đơn giản hóa: Nếu chuỗi chỉ có ngày (độ dài <= 10), nối thêm giờ hiện tại
            $borrowDate = $data['borrow_date'];
            if (strlen($borrowDate) <= 10) {
                $borrowDate .= ' ' . date('H:i:s');
            }
            
            $stmtLoan->execute([
                ':user_id' => $user->user_id,
                ':copy_id' => $data['copy_id'],
                ':borrow_date' => $borrowDate,
                ':due_date' => $data['due_date'],
                ':note' => $data['note']
            ]);

            // 3. Cập nhật trạng thái bản sao sách (BookCopies) thành 'Borrowed'
            $sqlUpdateCopy = "UPDATE BookCopies SET status = 'Borrowed' WHERE copy_id = :copy_id";
            $stmtUpdate = $this->db->prepare($sqlUpdateCopy);
            $stmtUpdate->execute([':copy_id' => $data['copy_id']]);

            // 4. Cập nhật trạng thái Reservation thành 'Fulfilled' (nếu có)
            // Lấy book_id từ copy_id để tìm đơn đặt hàng tương ứng
            $sqlGetBook = "SELECT book_id FROM BookCopies WHERE copy_id = :copy_id";
            $stmtGetBook = $this->db->prepare($sqlGetBook);
            $stmtGetBook->execute([':copy_id' => $data['copy_id']]);
            $book = $stmtGetBook->fetch(PDO::FETCH_OBJ);

            if ($book) {
                $sqlRes = "UPDATE Reservations SET status = 'Fulfilled' WHERE user_id = :user_id AND book_id = :book_id AND status NOT IN ('Cancelled', 'Fulfilled')";
                $stmtRes = $this->db->prepare($sqlRes);
                $stmtRes->execute([
                    ':user_id' => $user->user_id,
                    ':book_id' => $book->book_id
                ]);
            }

            // Hoàn tất lưu dữ liệu
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
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
        $sql = "SELECT l.loan_id, bc.copy_id, b.title, l.borrow_date, l.due_date
                FROM Loans l
                JOIN BookCopies bc ON l.copy_id = bc.copy_id
                JOIN Books b ON bc.book_id = b.book_id
               JOIN Users u ON l.user_id = u.user_id
               WHERE u.member_code = :member_code
                  AND l.return_date IS NULL";
              
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':member_code' => $member_code]);
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
            $this->db->beginTransaction();

            // 1. Cập nhật ngày trả, trạng thái và ghi chú (Good/Bad...)
            $sql1 = "UPDATE Loans 
                     SET return_date = :return_date, 
                         status = 'Returned',
                         note = :note 
                     WHERE loan_id = :loan_id";
            $stmt1 = $this->db->prepare($sql1);
            
            // Xử lý ngày trả tương tự
            $returnDate = $return_date;
            if (strlen($returnDate) <= 10) {
                $returnDate .= ' ' . date('H:i:s');
            }
            
            $stmt1->execute([
                ':return_date' => $returnDate,
                ':note' => $note,
                ':loan_id' => $loan_id
            ]);

            // 2. Cập nhật sách về trạng thái có sẵn
            $sql2 = "UPDATE BookCopies SET status = 'Available' WHERE copy_id = :copy_id";
            $stmt2 = $this->db->prepare($sql2);
            $stmt2->execute([':copy_id' => $copy_id]);

            return $this->db->commit();
        } catch (PDOException $e) {
            $this->db->rollBack();
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
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Lấy thống kê mượn trả cho Dashboard
     * @return array
     */
    public function getLoanStats() {
        $stats = ['total' => 0, 'overdue' => 0, 'reservations' => 0];
        
        // Tổng số lượt mượn đang Active
        $sqlTotal = "SELECT COUNT(*) as count FROM Loans WHERE status = 'Active'";
        $stmt1 = $this->db->prepare($sqlTotal);
        $stmt1->execute();
        $stats['total'] = $stmt1->fetch(PDO::FETCH_OBJ)->count;

        // Số lượng quá hạn (Due date < Today và chưa trả)
        $sqlOverdue = "SELECT COUNT(*) as count FROM Loans WHERE status = 'Active' AND due_date < CURDATE()";
        $stmt2 = $this->db->prepare($sqlOverdue);
        $stmt2->execute();
        $stats['overdue'] = $stmt2->fetch(PDO::FETCH_OBJ)->count;

        // Số lượng đặt trước
        $sqlRes = "SELECT COUNT(*) as count FROM Reservations";
        $stmt3 = $this->db->prepare($sqlRes);
        $stmt3->execute();
        $stats['reservations'] = $stmt3->fetch(PDO::FETCH_OBJ)->count;

        return $stats;
    }
}
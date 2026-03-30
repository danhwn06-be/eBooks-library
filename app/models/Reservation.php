<?php

class Reservation extends Model
{

    // 1. USER METHODS

    /**
     * Lấy thông tin chi tiết để hiển thị trên trang xác nhận
     * @param int $userId ID người dùng
     * @param int $bookId ID sách
     * @return mixed Object thông tin hoặc null
     */
    public function getReservationDetails($userId, $bookId) {
        // Đã đổi u.phone thành u.phone_number để khớp với bảng Users của bạn
        $sql = "SELECT u.email, u.address, u.member_code, u.phone_number, b.title 
                FROM Users u, Books b 
                WHERE u.user_id = :user_id AND b.book_id = :book_id";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':user_id' => $userId, ':book_id' => $bookId]);
            return $stmt->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Tạo đơn đặt trước sách (Transaction)
     * @param int $userId ID người dùng
     * @param int $bookId ID sách
     * @return bool True nếu thành công
     */
    public function createReservation($userId, $bookId)
    {
        try {
            $this->db->beginTransaction();

            // 1. Tìm bản sao sách đang có sẵn (Available) để giữ chỗ
            $sqlCheck = "SELECT copy_id FROM BookCopies WHERE book_id = :book_id AND status = 'Available' LIMIT 1 FOR UPDATE";
            $stmtCheck = $this->db->prepare($sqlCheck);
            $stmtCheck->execute([':book_id' => $bookId]);
            $copy = $stmtCheck->fetch(PDO::FETCH_OBJ);

            if (!$copy) {
                $this->db->rollBack();
                return false; // Không còn sách để đặt
            }

            // 2. Cập nhật trạng thái bản sao thành 'Reserved'
            $sqlUpdate = "UPDATE BookCopies SET status = 'Reserved' WHERE copy_id = :copy_id";
            $stmtUpdate = $this->db->prepare($sqlUpdate);
            $stmtUpdate->execute([':copy_id' => $copy->copy_id]);

            // 3. Tạo đơn đặt trước (Mặc định status là 'Waiting' theo DB của bạn)
            $sql = "INSERT INTO Reservations (user_id, book_id, reservation_date, status) 
                    VALUES (:user_id, :book_id, NOW(), 'Waiting')";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':user_id' => $userId,
                ':book_id' => $bookId
            ]);
            
            return $this->db->commit();
        } catch (PDOException $e) {
            $this->db->rollBack();
            return false;
        }
    }

    // 2. ADMIN METHODS

    /**
     * Lấy danh sách tất cả các đơn đặt trước (Admin)
     * @return array Danh sách reservations
     */
    public function getAllReservations()
    {
        $sql = "
            SELECT 
                r.reservation_id,
                r.reservation_date,
                r.status,
                u.member_code,
                b.title
            FROM Reservations r
            JOIN Users u ON r.user_id = u.user_id
            JOIN Books b ON r.book_id = b.book_id
            ORDER BY r.reservation_date DESC
        ";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            return [];
        }
    }
}
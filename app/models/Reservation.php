<?php

class Reservation
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function createReservation($userId, $bookId)
    {
        try {
            $this->db->getConnection()->beginTransaction();

            // 1. Tìm bản sao sách đang có sẵn (Available) để giữ chỗ
            $sqlCheck = "SELECT copy_id FROM BookCopies WHERE book_id = :book_id AND status = 'Available' LIMIT 1 FOR UPDATE";
            $stmtCheck = $this->db->getConnection()->prepare($sqlCheck);
            $stmtCheck->bindValue(':book_id', $bookId);
            $stmtCheck->execute();
            $copy = $stmtCheck->fetch(PDO::FETCH_OBJ);

            if (!$copy) {
                $this->db->getConnection()->rollBack();
                return false; // Không còn sách để đặt
            }

            // 2. Cập nhật trạng thái bản sao thành 'Reserved'
            $sqlUpdate = "UPDATE BookCopies SET status = 'Reserved' WHERE copy_id = :copy_id";
            $stmtUpdate = $this->db->getConnection()->prepare($sqlUpdate);
            $stmtUpdate->bindValue(':copy_id', $copy->copy_id);
            $stmtUpdate->execute();

            // 3. Tạo đơn đặt trước
            $sql = "INSERT INTO Reservations (user_id, book_id) VALUES (:user_id, :book_id)";
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([
                ':user_id' => $userId,
                ':book_id' => $bookId
            ]);
            
            return $this->db->getConnection()->commit();
        } catch (PDOException $e) {
            $this->db->getConnection()->rollBack();
            return false;
        }
    }

    // ================================
    // ADMIN: LẤY DANH SÁCH RESERVATIONS
    // ================================
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
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            return [];
        }
    }
}

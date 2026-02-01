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
        $sql = "INSERT INTO Reservations (user_id, book_id)
                VALUES (:user_id, :book_id)";

        try {
            $stmt = $this->db->getConnection()->prepare($sql);
            return $stmt->execute([
                ':user_id' => $userId,
                ':book_id' => $bookId
            ]);
        } catch (PDOException $e) {
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

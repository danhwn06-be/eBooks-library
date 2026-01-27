<?php

class ReservationModel
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

    public function getReservationsByUser($userId)
    {
        $sql = "SELECT r.reservation_date, r.status, b.title
                FROM Reservations r
                JOIN Books b ON r.book_id = b.book_id
                WHERE r.user_id = :user_id
                ORDER BY r.reservation_date DESC";

        try {
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([':user_id' => $userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}
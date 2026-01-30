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
}

<?php

class ReservationModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function createReservation($data)
    {
        $sql = "INSERT INTO Reservations 
                (user_id, book_id, member_code, email, phone, address, borrow_date, loan_term, status)
                VALUES 
                (:user_id, :book_id, :member_code, :email, :phone, :address, :borrow_date, :loan_term, 'Pending')";

        try {
            $stmt = $this->db->getConnection()->prepare($sql);
            return $stmt->execute([
                ':user_id'     => $data['user_id'],
                ':book_id'     => $data['book_id'],
                ':member_code' => $data['member_code'],
                ':email'       => $data['email'],
                ':phone'       => $data['phone'],
                ':address'     => $data['address'],
                ':borrow_date' => $data['borrow_date'],
                ':loan_term'   => $data['loan_term'],
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getReservationsByUser($userId)
    {
        $sql = "SELECT r.*, b.title 
                FROM Reservations r
                JOIN Books b ON r.book_id = b.book_id
                WHERE r.user_id = :user_id
                ORDER BY r.borrow_date DESC";

        try {
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([':user_id' => $userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}

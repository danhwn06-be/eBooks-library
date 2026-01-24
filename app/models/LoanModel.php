<?php

class LoanModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getCurrentBorrowByUser($userId)
    {
        $sql = "SELECT l.id, bk.title, l.borrow_date, l.due_date
                FROM Loans l
                JOIN books bk ON l.book_id = bk.id
                WHERE l.user_id = :userId
                  AND l.return_date IS NULL";
        try {
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->bindValue(':userId', $userId);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }   

        return $stmt->fetchAll();
    }

    public function getBorrowHistoryByUser($userId)
    {
        $sql = "SELECT bk.title, l.borrow_date, l.return_date
                FROM Loans l
                JOIN books bk ON l.book_id = bk.id
                WHERE l.user_id = :userId
                  AND l.return_date IS NOT NULL";
        try {
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->bindValue(':userId', $userId);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }

        return $stmt->fetchAll();
    }
}

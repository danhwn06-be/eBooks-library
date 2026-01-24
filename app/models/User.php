<?php

class User
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function login($email, $password)
    {
        $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            return (object) $user;
        }

        return false;
    }

    public function getUserById($id)
    {
        $sql = "SELECT user_id, member_code, full_name, email, password_hash, phone_number, address, created_at
                FROM users 
                WHERE user_id = :user_id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':user_id', $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            return false;
    }
        return $stmt->fetch();
    }

    public function getBorrowHistory($userId)
    {
        $sql = "SELECT b.title, l.borrow_date, l.due_date, l.return_date, l.status 
                FROM Loans l
                JOIN BookCopies bc ON l.copy_id = bc.copy_id
                JOIN Books b ON bc.book_id = b.book_id
                WHERE l.user_id = :user_id
                ORDER BY l.borrow_date DESC";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':user_id', $userId);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function updateUser($data)
    {
        $this->db->query("UPDATE Users SET full_name = :name, email = :email, phone_number = :phone, address = :address WHERE user_id = :id");
    
        // Bind các giá trị
        $this->db->bind(':name', $data['full_name']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':phone', $data['phone_number']);
        $this->db->bind(':address', $data['address']);
        $this->db->bind(':id', $data['user_id']);
        // Thực thi câu lệnh
        if ($this->db->execute()) {
            return true;
        }
        return false;
    }
}

<?php

class UserModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getById($id)
    {
        $sql = "SELECT user_id, member_code, full_name, email, password_hash, phone_number, address
                FROM users 
                WHERE id = :id";
    try {
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException) {
            return false;
    }
        return $stmt->fetch();
    }
}

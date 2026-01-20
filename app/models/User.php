<?php

class User
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function login($login, $password)
    {
        $sql = "SELECT * FROM users 
                WHERE email = :login OR phone = :login 
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':login', $login);
        $stmt->execute();

        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            return (object) $user;
        }

        return false;
    }
}

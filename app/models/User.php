<?php

class User
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    // LMS-139: Kiểm tra trùng lặp (Email hoặc Phone)
    public function findUserByField($field, $value) {
        $sql = "SELECT * FROM users WHERE $field = :value";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':value', $value);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    // LMS-140: Lưu thông tin người dùng
    public function register($data) {
        // Xác định cột nào lấy giá trị, cột nào để NULL
        $email = ($data['field_type'] === 'email') ? $data['account'] : null;
        $phone = ($data['field_type'] === 'phone_number') ? $data['account'] : null;

        $sql = "INSERT INTO users (email, phone_number, full_name, address, password_hash, role) 
                VALUES (:email, :phone, :full_name, :address, :password, 'Member')";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':full_name', $data['full_name']);
        $stmt->bindParam(':address', $data['address']);
        $stmt->bindParam(':password', $data['password']);

        return $stmt->execute();
    }

    // Cập nhật hàm Login để nhận diện cả 2 loại tài khoản
    public function login($account, $password) {
        $sql = "SELECT * FROM users WHERE email = :account OR phone_number = :account LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':account', $account);
        $stmt->execute();

        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password_hash'])) {
            return (object) $user;
        }
        return false;
    }
}
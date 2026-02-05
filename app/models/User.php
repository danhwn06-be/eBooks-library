<?php

class User
{
    private $db;

    /**
     * Khởi tạo kết nối Database
     */
    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // 1. AUTHENTICATION & REGISTRATION

    /**
     * Đăng nhập bằng email, phone, hoặc username
     * @param string $account
     * @param string $password
     * @return mixed Object user hoặc false
     */
    public function login($account, $password)
    {
        $sql = "SELECT * FROM users WHERE email = :acc OR phone_number = :acc OR user_name = :acc LIMIT 1";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindParam(':acc', $account);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_OBJ);
        if ($user && password_verify($password, $user->password_hash)) {
            return $user;
        }
        return false;
    }

    /**
     * Đăng ký người dùng mới (Member)
     * @param array $data
     * @return bool
     */
    public function register($data)
    {
        $member_code = $this->generateMemberCode();

        $sql = "INSERT INTO users (member_code, email, phone_number, full_name, user_name, address, password_hash, role)
                VALUES (:member_code, :email, :phone, :full_name, :user_name, :address, :password, 'Member')";

        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindParam(':member_code', $member_code);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':phone', $data['phone_number']);
        $stmt->bindParam(':full_name', $data['full_name']);
        $stmt->bindParam(':user_name', $data['user_name']);
        $stmt->bindParam(':address', $data['address']);
        $stmt->bindParam(':password', $data['password']);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    /**
     * Kiểm tra trùng lặp (Email hoặc Phone)
     * @param string $field Tên cột
     * @param string $value Giá trị
     * @return bool
     */
    public function findUserByField($field, $value)
    {
        $sql = "SELECT * FROM users WHERE $field = :value";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindParam(':value', $value);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    // 2. USER PROFILE (READ & UPDATE)

    /**
     * Lấy thông tin chi tiết User theo ID
     * @param int $id
     * @return mixed Object user
     */
    public function getUserById($id)
    {
        $sql = "SELECT user_id, member_code, full_name, user_name, email, password_hash, phone_number, address, created_at
                FROM users
                WHERE user_id = :user_id";
        try {
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->bindValue(':user_id', $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Cập nhật thông tin User (Admin & Profile Edit)
     * @param array $data
     * @return bool
     */
    public function updateUser($data)
    {
        if (!empty($data['password'])) {
            $sql = "UPDATE users
                    SET full_name = :full_name,
                        email = :email,
                        phone_number = :phone_number,
                        address = :address,
                        password_hash = :password
                    WHERE user_id = :id";
        } else {
            $sql = "UPDATE users
                    SET full_name = :full_name,
                        email = :email,
                        phone_number = :phone_number,
                        address = :address
                    WHERE user_id = :id";
        }

        $stmt = $this->db->getConnection()->prepare($sql);

        $stmt->bindValue(':id', $data['user_id']);
        $stmt->bindValue(':full_name', $data['full_name']);
        $stmt->bindValue(':email', $data['email']);
        $stmt->bindValue(':phone_number', $data['phone_number']);
        $stmt->bindValue(':address', $data['address']);

        if (!empty($data['password'])) {
            $stmt->bindValue(':password', $data['password']);
        }

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // 3. ADMIN MANAGEMENT

    /**
     * Lấy danh sách tất cả User (Admin)
     * @return array
     */
    public function getAllUsers()
    {
        $sql = "SELECT * FROM users ORDER BY created_at DESC";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Thêm user mới (Chức năng Admin)
     * @param array $data
     * @return bool
     */
    public function addUser($data)
    {
        $newMemberCode = $this->generateMemberCode();

        $sql = "INSERT INTO users (member_code, full_name, email, address, phone_number, password_hash, created_at)
                VALUES (:member_code, :full_name, :email, :address, :phone_number, :password, NOW())";

        $stmt = $this->db->getConnection()->prepare($sql);

        $stmt->bindValue(':member_code', $newMemberCode);
        $stmt->bindValue(':full_name', $data['full_name']);
        $stmt->bindValue(':email', $data['email']);
        $stmt->bindValue(':address', $data['address']);
        $stmt->bindValue(':phone_number', $data['phone_number']);
        $stmt->bindValue(':password', $data['password']);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    /**
     * Xóa User
     * @param int $id
     * @return bool
     */
    public function deleteUser($id)
    {
        $sql = "DELETE FROM users WHERE user_id = :id";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // 4. HELPER FUNCTIONS

    private function generateMemberCode()
    {
        $sql = "SELECT member_code FROM users ORDER BY member_code DESC LIMIT 1";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute();
        $lastUser = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($lastUser && !empty($lastUser['member_code'])) {
            $lastNumber = (int)substr($lastUser['member_code'], 3);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return 'MEM' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
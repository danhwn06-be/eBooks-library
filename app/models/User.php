<?php

class User
{
    private $db;
    private $cache;

    public function __construct()
    {
        $this->db = Database::getInstance();
        require_once APP_ROOT . '/app/core/Cache.php';
        $this->cache = new Cache();
    }

    // Kiểm tra trùng lặp (Email hoặc Phone)
    public function findUserByField($field, $value)
    {
        $sql = "SELECT * FROM users WHERE $field = :value";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindParam(':value', $value);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    // Tự động sinh mã Member Code
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

    // Đăng nhập bằng email, phone, hoặc username
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

    // Đăng ký người dùng mới (Member)
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
            // Xóa cache danh sách user để Admin thấy user mới ngay
            $this->cache->delete('all_users_list');
            return true;
        }
        return false;
    }

    public function getUserById($id)
    {
        $cacheKey = 'user_profile_' . $id;
        $cachedData = $this->cache->get($cacheKey);
        if ($cachedData !== false) return $cachedData;

        $sql = "SELECT user_id, member_code, full_name, user_name, email, password_hash, phone_number, address, created_at
                FROM users
                WHERE user_id = :user_id";
        try {
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->bindValue(':user_id', $id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_OBJ);
            
            if ($result) $this->cache->set($cacheKey, $result, 3600);
            return $result;
        } catch (PDOException $e) {
            return false;
        }
    }

    // Lấy thông tin user trả về mảng (dùng cho một số trường hợp legacy)
    public function getUsersById($id)
    {
        $sql = "SELECT * FROM users WHERE user_id = :id";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Cập nhật user (Admin & Profile Edit)
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
            // Xóa cache profile của user này
            $this->cache->delete('user_profile_' . $data['user_id']);
            $this->cache->delete('all_users_list');
            return true;
        }
        return false;
    }

    public function getAllUsers()
    {
        $cacheKey = 'all_users_list';
        $cachedData = $this->cache->get($cacheKey);
        if ($cachedData !== false) return $cachedData;

        $sql = "SELECT * FROM users ORDER BY created_at DESC";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->cache->set($cacheKey, $result, 3600);
        return $result;
    }

    // Thêm user mới (Admin)
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
            $this->cache->delete('all_users_list');
            return true;
        }
        return false;
    }

    public function deleteUser($id)
    {
        $sql = "DELETE FROM users WHERE user_id = :id";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            $this->cache->delete('user_profile_' . $id);
            $this->cache->delete('all_users_list');
            return true;
        }
        return false;
    }
}
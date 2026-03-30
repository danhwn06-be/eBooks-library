<?php

class User extends Model
{

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
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':acc' => $account]);

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

        $stmt = $this->db->prepare($sql);

        $params = [
            ':member_code' => $member_code,
            ':email' => $data['email'],
            ':phone' => $data['phone_number'],
            ':full_name' => $data['full_name'],
            ':user_name' => $data['user_name'],
            ':address' => $data['address'],
            ':password' => $data['password']
        ];

        if ($stmt->execute($params)) {
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
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':value' => $value]);
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
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':user_id' => $id]);
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
        $setParts = [
            'full_name = :full_name',
            'email = :email',
            'phone_number = :phone_number',
            'address = :address'
        ];

        $params = [
            ':full_name' => $data['full_name'],
            ':email' => $data['email'],
            ':phone_number' => $data['phone_number'],
            ':address' => $data['address'],
            ':id' => $data['user_id']
        ];

        // Nếu có mật khẩu mới được cung cấp, thêm nó vào truy vấn và tham số
        if (!empty($data['password'])) {
            $setParts[] = 'password_hash = :password';
            $params[':password'] = $data['password'];
        }

        $sql = "UPDATE users SET " . implode(', ', $setParts) . " WHERE user_id = :id";

        $stmt = $this->db->prepare($sql);

        if ($stmt->execute($params)) {
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
        $sql = "SELECT user_id, member_code, full_name, user_name, email, password_hash, phone_number, address, role, created_at, updated_at FROM users ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
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

        $stmt = $this->db->prepare($sql);

        $params = [
            ':member_code' => $newMemberCode,
            ':full_name' => $data['full_name'],
            ':email' => $data['email'],
            ':address' => $data['address'],
            ':phone_number' => $data['phone_number'],
            ':password' => $data['password']
        ];

        if ($stmt->execute($params)) {
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
        $stmt = $this->db->prepare($sql);
        
        if ($stmt->execute([':id' => $id])) {
            return true;
        }
        return false;
    }

    // 4. HELPER FUNCTIONS

    private function generateMemberCode()
    {
        $sql = "SELECT member_code FROM users ORDER BY member_code DESC LIMIT 1";
        $stmt = $this->db->prepare($sql);
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
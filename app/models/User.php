<?php

class User
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    // Kiểm tra trùng lặp (Email hoặc Phone)
    public function findUserByField($field, $value) 
    {
        $sql = "SELECT * FROM users WHERE $field = :value";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':value', $value);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    // Lưu thông tin người dùng
    public function register($data) 
    {
        // Tự sinh mã Member Code từ hàm đã có của bạn
        $member_code = $this->generateMemberCode();

        $sql = "INSERT INTO users (member_code, email, phone_number, full_name, user_name, address, password_hash, role) 
                VALUES (:member_code, :email, :phone, :full_name, :user_name, :address, :password, 'Member')";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':member_code', $member_code);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':phone', $data['phone_number']);
        $stmt->bindParam(':full_name', $data['full_name']);
        $stmt->bindParam(':user_name', $data['user_name']);
        $stmt->bindParam(':address', $data['address']);
        $stmt->bindParam(':password', $data['password']);

        return $stmt->execute();
    }

    // Cập nhật Login: Cho phép đăng nhập bằng 1 trong 3 trường
    public function login($account, $password) {
        $sql = "SELECT * FROM users WHERE email = :acc OR phone_number = :acc OR user_name = :acc LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':acc', $account);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_OBJ);
        if ($user && password_verify($password, $user->password_hash)) {
            return $user;
        }
        return false;
    }

    public function getUserById($id)
    {
        $sql = "SELECT user_id, member_code, full_name, user_name, email, password_hash, phone_number, address, created_at
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

    // --- HÀM MỚI: Tự động sinh mã Member Code ---
    // Logic: Lấy mã cuối cùng (ví dụ MEM0005) -> tách số 5 -> cộng 1 thành 6 -> tạo mã MEM0006
    private function generateMemberCode() {
        $sql = "SELECT member_code FROM users ORDER BY user_id DESC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $lastUser = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($lastUser && !empty($lastUser['member_code'])) {
            // Lấy phần số: substr('MEM0005', 3) sẽ lấy từ ký tự thứ 3 trở đi -> được chuỗi "0005"
            $lastNumber = (int)substr($lastUser['member_code'], 3);
            $newNumber = $lastNumber + 1;
        } else {
            // Nếu chưa có user nào, bắt đầu từ 1 (MEM0001) hoặc 0 tùy bạn chọn
            $newNumber = 1;
        }

        // Tạo chuỗi mới: 'MEM' + số đã được đệm số 0 cho đủ 4 chữ số
        return 'MEM' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    // --- 3. Thêm user mới (ĐÃ SỬA: Dùng cột password_hash và tự sinh code) ---
    public function addUser($data) {
        // 1. Tự sinh mã code
        $newMemberCode = $this->generateMemberCode();

        // 2. Câu lệnh SQL đã sửa: đổi 'password' thành 'password_hash'
        $sql = "INSERT INTO users (member_code, full_name, email, address, phone_number, password_hash, created_at) 
                VALUES (:member_code, :full_name, :email, :address, :phone_number, :password, NOW())";
        
        $stmt = $this->db->prepare($sql);
        
        // Bind các giá trị
        $stmt->bindValue(':member_code', $newMemberCode); // Dùng mã vừa sinh
        $stmt->bindValue(':full_name', $data['full_name']);
        $stmt->bindValue(':email', $data['email']);
        $stmt->bindValue(':address', $data['address']);
        $stmt->bindValue(':phone_number', $data['phone_number']);
        $stmt->bindValue(':password', $data['password']); // Giá trị mật khẩu đã hash

        return $stmt->execute();
    }

    // --- 4. Admin cập nhật user (ĐÃ SỬA: Dùng cột password_hash) ---
    public function updateUser($data) {
        if (!empty($data['password'])) {
            // SQL cập nhật có mật khẩu -> dùng cột password_hash
            $sql = "UPDATE users 
                    SET full_name = :full_name, 
                        email = :email, 
                        phone_number = :phone_number, 
                        address = :address, 
                        password_hash = :password 
                    WHERE user_id = :id";
        } else {
            // SQL cập nhật không đổi mật khẩu
            $sql = "UPDATE users 
                    SET full_name = :full_name, 
                        email = :email, 
                        phone_number = :phone_number, 
                        address = :address 
                    WHERE user_id = :id";
        }

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(':id', $data['user_id']);
        $stmt->bindValue(':full_name', $data['full_name']);
        $stmt->bindValue(':email', $data['email']);
        $stmt->bindValue(':phone_number', $data['phone_number']);
        $stmt->bindValue(':address', $data['address']);

        if (!empty($data['password'])) {
            $stmt->bindValue(':password', $data['password']);
        }

        return $stmt->execute();
    }

    // --- CÁC HÀM KHÁC GIỮ NGUYÊN ---
    public function getAllUsers() {
        $sql = "SELECT * FROM users ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUsersById($id) {
        $sql = "SELECT * FROM users WHERE user_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function deleteUser($id) {
        $sql = "DELETE FROM users WHERE user_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function findUserByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
}
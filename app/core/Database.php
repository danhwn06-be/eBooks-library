<?php
// Lớp xử lý kết nối CSDL sử dụng Singleton Pattern
class Database
{
    // Biến tĩnh giữ thể hiện duy nhất của lớp
    private static $instance = null;
    // Biến giữ kết nối PDO
    private $connection;

    // Constructor private để ngăn chặn khởi tạo trực tiếp từ bên ngoài (new Database)
    private function __construct()
    {
        try {
            // Khởi tạo kết nối PDO với thông tin từ config.php
            $this->connection = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS
            );

            // Cấu hình ném ra Exception khi có lỗi SQL để dễ debug
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Cấu hình mặc định trả về dữ liệu dạng mảng kết hợp (Associative Array)
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Ném ra Exception thay vì die() để bên ngoài có thể xử lý (try-catch) hoặc log lỗi
            throw new Exception("Database Connection Failed: " . $e->getMessage());
        }
    }

    /**
     * Lấy thể hiện duy nhất của lớp Database (Singleton Instance)
     * @return Database
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Lấy đối tượng kết nối PDO để thực hiện truy vấn
     * @return PDO
     */
    public function getConnection()
    {
        return $this->connection;
    }

    // Ngăn chặn việc clone đối tượng (bảo đảm tính duy nhất)
    private function __clone() {}

    // Ngăn chặn việc unserialize đối tượng
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }
}

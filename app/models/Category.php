<?php
class Category
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // Lấy danh sách tất cả danh mục
    public function getAllCategories()
    {
        $sql = "SELECT * FROM Categories ORDER BY category_name ASC";
        try {
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    // Lấy tên Category theo ID
    public function getCategoryNameById($cat_id)
    {
        $sql = "SELECT category_name FROM Categories WHERE category_id = :cat_id";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':cat_id', $cat_id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch();
        return $row ? $row['category_name'] : '';
    }
}

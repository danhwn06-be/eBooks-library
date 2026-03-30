<?php
class Category extends Model
{

    /**
     * Lấy danh sách tất cả danh mục
     * @return array Danh sách các danh mục
     */
    public function getAllCategories()
    {
        $sql = "SELECT * FROM Categories ORDER BY category_name ASC";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Lấy tên Category theo ID
     * @param int $cat_id ID của danh mục
     * @return string Tên danh mục hoặc chuỗi rỗng nếu không tìm thấy
     */
    public function getCategoryNameById($cat_id)
    {
        $sql = "SELECT category_name FROM Categories WHERE category_id = :cat_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':cat_id', $cat_id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch();
        return $row ? $row['category_name'] : '';
    }
}

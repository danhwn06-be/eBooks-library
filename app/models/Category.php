<?php
class Category
{
    private $db;
    private $cache;

    public function __construct()
    {
        $this->db = Database::getInstance();
        require_once APP_ROOT . '/app/core/Cache.php';
        $this->cache = new Cache();
    }

    // Lấy danh sách tất cả danh mục
    public function getAllCategories()
    {
        $cacheKey = 'all_categories_list';
        $cachedData = $this->cache->get($cacheKey);
        if ($cachedData !== false) return $cachedData;

        $sql = "SELECT * FROM Categories ORDER BY category_name ASC";
        try {
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll();
            $this->cache->set($cacheKey, $result, 86400); // Cache 24h
            return $result;
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

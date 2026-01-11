<?php
class BookModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAllBooks()
    {
        $stmt = $this->db->prepare("SELECT * FROM Books ORDER BY created_at DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
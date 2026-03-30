<?php

abstract class Model
{
    /**
     * @var PDO|false false PDO connection object
     */
    protected $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
}
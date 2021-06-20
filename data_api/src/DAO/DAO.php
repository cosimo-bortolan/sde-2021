<?php namespace Sagra\Data\DAO;

/**
 * Classe astratta per DAO
 */
abstract class DAO {

    protected $db;
    protected $conn;

    public function __construct($db) {
        $this->db = $db;
        $this->conn = $db->getConnection();
    }

}

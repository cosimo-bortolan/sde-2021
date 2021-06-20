<?php namespace Sagra\Data;

use \PDO;

class Database {

  private $conn;

  public function __construct() {
    $database = json_decode(file_get_contents(__DIR__ . "/../config/database.json"));
    $this->conn = new PDO("mysql:host=" . $database->host . ";dbname=" . $database->db_name, $database->username, $database->password);
    $this->conn->exec("set names utf8mb4");
    $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }

  public function getConnection() {
    return $this->conn;
  }

  function lock_tables() {
    $query = "LOCK TABLES
    pietanze WRITE,
    categorie WRITE,
    aggiunte_pietanze WRITE,
    prenotazioni WRITE,
    ordini WRITE,
    pietanze_ordini WRITE,
    aggiunte_pietanze_ordini WRITE,
    aggiunte_prenotazioni WRITE,
    aggiunte WRITE,
    serate WRITE,
    messaggi WRITE,
    casse WRITE,
    stampanti WRITE,
    scontrini WRITE";

    $stmt = $this->conn->prepare($query);

    $stmt->execute();
  }

  function unlock_tables() {
    $query = "UNLOCK TABLES";

    $stmt = $this->conn->prepare($query);

    $stmt->execute();
  }

  function begin_transaction() {
    $query = "SET autocommit = 0";

    $stmt = $this->conn->prepare($query);

    $stmt->execute();
  }

  function commit() {
    $query = "COMMIT";

    $stmt = $this->conn->prepare($query);

    $stmt->execute();
  }

  function rollback() {
    $query = "ROLLBACK";

    $stmt = $this->conn->prepare($query);

    $stmt->execute();
  }

}

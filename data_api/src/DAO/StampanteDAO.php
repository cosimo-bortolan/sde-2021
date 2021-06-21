<?php namespace Sagra\Data\DAO;

use Sagra\Data\DAO\DAO;
use Sagra\Data\DAO\ScontrinoDAO;
use Sagra\Entities\Stampante;
use Sagra\Exceptions\InputException;

class StampanteDAO extends DAO {

  function get($id, $getScontrini = true) {
    $scontrinoDAO = new ScontrinoDAO($this->db);

    $query = "SELECT
    id, nome, ip
    FROM
    stampanti
    WHERE
    id=:id";
    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(":id", $id);

    $stmt->execute();
    if ($stmt->rowCount() == 0) {
      throw new InputException(NOT_FOUND, ["resource" => "stampante", "id" => $id]);
    }
    $stampante = $stmt->fetchObject("Sagra\Entities\Stampante");

    if($getScontrini){
      $stampante->scontrini = $scontrinoDAO->getListByStampante($stampante->id);
    }

    return $stampante;
  }

  function getAll() {
    $scontrinoDAO = new ScontrinoDAO($this->db);

    $query = "SELECT
    id, nome, ip
    FROM
    stampanti";
    $stmt = $this->conn->prepare($query);

    $stmt->execute();
    $stampanti = [];
    while ($stampante = $stmt->fetchObject("Sagra\Entities\Stampante")) {
      $stampante->scontrini = $scontrinoDAO->getListByStampante($stampante->id);
      $stampanti[] = $stampante;
    }

    return $stampanti;
  }

  function insert($data) {
    $stampante = new Stampante($data);

    $query = "INSERT INTO
    stampanti
    SET
    nome=:nome,
    ip=:ip";

    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(":nome", $stampante->nome);
    $stmt->bindParam(":ip", $stampante->ip);

    $this->db->begin_transaction();
    $stmt->execute();
    $stampante->id = $this->conn->lastInsertId();
    $this->updateScontrini($stampante);
    $this->db->commit();

    return $stampante;
  }

  function update($stampante, $data) {
    $stampante->patch($data);

    $query = "UPDATE
    stampanti
    SET
    nome=:nome,
    ip=:ip
    WHERE
    id=:id";

    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(":nome", $stampante->nome);
    $stmt->bindParam(":ip", $stampante->ip);
    $stmt->bindParam(":id", $stampante->id);

    $this->db->begin_transaction();
    $stmt->execute();
    $this->updateScontrini($stampante);
    $this->db->commit();

    return $stampante;
  }

  function delete($stampante) {
    $query = "DELETE
    FROM
    stampanti
    WHERE
    id=:id";

    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(":id", $stampante->id);

    $stmt->execute();
  }

  private function updateScontrini($stampante) {
    $scontrinoDAO = new ScontrinoDAO($this->db);

    $query = "DELETE FROM
    scontrini_stampanti
    WHERE
    stampante=:stampante";
    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(":stampante", $stampante->id);
    $stmt->execute();

    foreach ($stampante->scontrini as $scontrino) {
      $query = "INSERT INTO
      scontrini_stampanti
      SET
      scontrino=:scontrino, stampante=:stampante";

      $stmt = $this->conn->prepare($query);

      $stmt->bindParam(":scontrino", $scontrino->id);
      $stmt->bindParam(":stampante", $stampante->id);
      $stmt->execute();
    }
    $stampante->scontrini = $scontrinoDAO->getListByStampante($stampante->id);
  }
}

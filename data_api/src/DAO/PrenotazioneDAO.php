<?php namespace Sagra\Data\DAO;

use Sagra\Data\DAO\DAO;
use Sagra\Data\DAO\AggiuntaDAO;
use Sagra\Entities\Prenotazione;
use Sagra\Exceptions\InputException;

class PrenotazioneDAO extends DAO {

  function get($id) {
    $aggiuntaDAO = new AggiuntaDAO($this->db);

    $query = "SELECT
    id, cassa, pietanza
    FROM
    prenotazioni
    WHERE
    id=:id";
    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(":id", $id);

    $stmt->execute();
    if ($stmt->rowCount() == 0) {
      throw new InputException(NOT_FOUND, ["resource" => "prenotazione", "id" => $id]);
    }
    $prenotazione = $stmt->fetchObject("Sagra\Entities\Prenotazione");
    $prenotazione->aggiunte = $aggiuntaDAO->getListByPrenotazione($prenotazione->id);

    return $prenotazione;
  }

  function getByIdAndPietanzaAndCassa($id, $pietanza, $cassa) {
    $aggiuntaDAO = new AggiuntaDAO($this->db);

    $query = "SELECT
    id, cassa, pietanza
    FROM
    prenotazioni
    WHERE
    id=:id AND
    pietanza=:pietanza AND
    cassa=:cassa";
    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(":id", $id);
    $stmt->bindParam(":pietanza", $pietanza);
    $stmt->bindParam(":cassa", $cassa);

    $stmt->execute();
    if ($stmt->rowCount() == 0) {
      throw new InputException(NOT_FOUND, ["resource" => "prenotazione", "id" => $id]);
    }
    $prenotazione = $stmt->fetchObject("Sagra\Entities\Prenotazione");
    $prenotazione->aggiunte = $aggiuntaDAO->getListByPrenotazione($prenotazione->id);

    return $prenotazione;
  }

  function getByPietanza($pietanza) {
    $aggiuntaDAO = new AggiuntaDAO($this->db);

    $query = "SELECT
    id, cassa, pietanza
    FROM
    prenotazioni
    WHERE
    pietanza=:pietanza";
    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(":pietanza", $pietanza);

    $stmt->execute();

    $prenotazioni = [];
    while ($prenotazione = $stmt->fetchObject("Sagra\Entities\Prenotazione")) {
      $prenotazione->aggiunte = $aggiuntaDAO->getListByPrenotazione($prenotazione->id);
      $prenotazioni[] = $prenotazione;
    }

    return $prenotazioni;
  }

  function getByCassa($cassa) {
    $aggiuntaDAO = new AggiuntaDAO($this->db);

    $query = "SELECT
    id, cassa, pietanza
    FROM
    prenotazioni
    WHERE
    cassa=:cassa";
    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(":cassa", $cassa);

    $stmt->execute();

    $prenotazioni = [];
    while ($prenotazione = $stmt->fetchObject("Sagra\Entities\Prenotazione")) {
      $prenotazione->aggiunte = $aggiuntaDAO->getListByPrenotazione($prenotazione->id);
      $prenotazioni[] = $prenotazione;
    }

    return $prenotazioni;
  }

  function getByPietanzaAndCassa($pietanza, $cassa) {
    $aggiuntaDAO = new AggiuntaDAO($this->db);

    $query = "SELECT
    id, cassa, pietanza
    FROM
    prenotazioni
    WHERE
    pietanza=:pietanza AND
    cassa=:cassa";
    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(":cassa", $cassa);
    $stmt->bindParam(":pietanza", $pietanza);

    $stmt->execute();

    $prenotazioni = [];
    while ($prenotazione = $stmt->fetchObject("Sagra\Entities\Prenotazione")) {
      $prenotazione->aggiunte = $aggiuntaDAO->getListByPrenotazione($prenotazione->id);
      $prenotazioni[] = $prenotazione;
    }

    return $prenotazioni;
  }

  function getAll() {
    $aggiuntaDAO = new AggiuntaDAO($this->db);

    $query = "SELECT
    id, cassa, pietanza
    FROM
    prenotazioni";
    $stmt = $this->conn->prepare($query);

    $stmt->execute();
    $prenotazioni = [];

    while ($prenotazione = $stmt->fetchObject("Sagra\Entities\Prenotazione")) {
      $prenotazione->aggiunte = $aggiuntaDAO->getListByPrenotazione($prenotazione->id);
      $prenotazioni[] = $prenotazione;
    }

    return $prenotazioni;
  }

  function insert($data) {
    $prenotazione = new Prenotazione($data);

    $query = "INSERT INTO
    prenotazioni
    SET
    pietanza=:pietanza,
    cassa=:cassa";
    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(":pietanza", $prenotazione->pietanza);
    $stmt->bindParam(":cassa", $prenotazione->cassa);

    $this->db->begin_transaction();
    $this->db->lock_tables();
    $stmt->execute();
    $prenotazione->id = $this->conn->lastInsertId();
    $this->updateAggiunte($prenotazione);
    $this->db->commit();
    $this->db->unlock_tables();

    return $prenotazione;
  }

  function update($prenotazione, $data) {
    $prenotazione->patch($data);

    $this->db->begin_transaction();
    $this->db->lock_tables();
    $this->updateAggiunte($prenotazione);
    $this->db->commit();
    $this->db->unlock_tables();

    return $prenotazione;
  }

  function delete($id) {
    $query = "DELETE
    FROM
    prenotazioni
    WHERE
    id=:id";

    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(":id", $id);

    $stmt->execute();
  }

  function deleteByCassa($cassa) {
    $query = "DELETE
    FROM
    prenotazioni
    WHERE
    cassa=:cassa";

    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(":cassa", $cassa);

    $stmt->execute();
  }

  function deleteByPietanza($pietanza) {
    $query = "DELETE
    FROM
    prenotazioni
    WHERE
    pietanza=:pietanza";

    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(":pietanza", $pietanza);

    $stmt->execute();
  }

  function deleteAll() {
    $query = "DELETE
    FROM
    prenotazioni";

    $stmt = $this->conn->prepare($query);

    $stmt->execute();
  }

  private function updateAggiunte($prenotazione) {
    $aggiuntaDAO = new AggiuntaDAO($this->db);

    $query = "DELETE FROM
    aggiunte_prenotazioni
    WHERE
    prenotazione=:prenotazione";
    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(":prenotazione", $prenotazione->id);
    $stmt->execute();

    foreach ($prenotazione->aggiunte as $aggiunta) {
      $query = "INSERT INTO
      aggiunte_prenotazioni
      SET
      prenotazione=:prenotazione, aggiunta=:aggiunta";

      $stmt = $this->conn->prepare($query);

      $stmt->bindParam(":prenotazione", $prenotazione->id);
      $stmt->bindParam(":aggiunta", $aggiunta->id);
      $stmt->execute();
    }
    $prenotazione->aggiunte = $aggiuntaDAO->getListByPrenotazione($prenotazione->id);
  }
}

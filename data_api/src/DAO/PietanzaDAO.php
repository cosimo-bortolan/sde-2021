<?php namespace Sagra\Data\DAO;

use Sagra\Data\DAO\DAO;
use Sagra\Data\DAO\AggiuntaDAO;
use Sagra\Data\DAO\PrenotazioneDAO;
use Sagra\Entities\Pietanza;
use Sagra\Exceptions\InputException;

class PietanzaDAO extends DAO {

  function get($id) {
    $aggiuntaDAO = new AggiuntaDAO($this->db);

    $query = "SELECT
    id, nome, quantita, prezzo, categoria, ordering
    FROM
    pietanze
    WHERE
    id=:id";
    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(":id", $id);

    $stmt->execute();
    if ($stmt->rowCount() == 0) {
      throw new InputException(NOT_FOUND, ["resource" => "pietanza", "id" => $id]);
    }
    $pietanza = $stmt->fetchObject("Sagra\Entities\Pietanza");
    $pietanza->aggiunte = $aggiuntaDAO->getListByPietanza($pietanza->id);

    return $pietanza;
  }

  function getAll() {
    $aggiuntaDAO = new AggiuntaDAO($this->db);

    $query = "SELECT
    p.id AS id, p.nome AS nome, quantita, prezzo, categoria, p.ordering AS ordering
    FROM
    pietanze AS p, categorie AS c
    WHERE
    p.categoria = c.id
    ORDER BY
    c.ordering ASC, ordering ASC";
    $stmt = $this->conn->prepare($query);

    $stmt->execute();
    $pietanze = [];
    while ($pietanza = $stmt->fetchObject("Sagra\Entities\Pietanza")) {
      $pietanza->aggiunte = $aggiuntaDAO->getListByPietanza($pietanza->id);
      $pietanze[] = $pietanza;
    }

    return $pietanze;
  }

  function getByCassa($cassa) {
    $prenotazioneDAO = new PrenotazioneDAO($this->db);

    $pietanze = $this->getAll();

    foreach ($pietanze as $pietanza) {
      $pietanza->prenotazioni = $prenotazioneDAO->getByPietanzaAndCassa($pietanza->id, $cassa);
      $pietanza->quantita_prenotazione = count($pietanza->prenotazioni);

    }

    return $pietanze;
  }

  function insert($data) {
    $pietanza = new Pietanza($data);

    $query = "INSERT INTO
    pietanze
    SET
    nome=:nome, quantita=:quantita,
    prezzo=:prezzo, categoria=:categoria, ordering=:ordering";
    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(":nome", $pietanza->nome);
    $stmt->bindParam(":quantita", $pietanza->quantita);
    $stmt->bindParam(":prezzo", $pietanza->prezzo);
    $stmt->bindParam(":categoria", $pietanza->categoria);
    $stmt->bindParam(":ordering", $pietanza->ordering);

    $this->db->begin_transaction();
    $this->db->lock_tables();
    $this->prepareOrder($pietanza);
    $stmt->execute();
    $pietanza->id = $this->conn->lastInsertId();
    $this->updateAggiunte($pietanza);
    $this->db->commit();
    $this->db->unlock_tables();

    return $pietanza;
  }

  function update($pietanza, $data) {
    $oldOrdering = $pietanza->ordering;
    $oldCategoria = $pietanza->categoria;
    $pietanza->patch($data);
    $newOrdering = $pietanza->ordering;
    $newCategoria = $pietanza->categoria;

    $query = "UPDATE
    pietanze
    SET
    nome=:nome, quantita=:quantita,
    prezzo=:prezzo, categoria=:categoria, ordering=:ordering
    WHERE
    id=:id";

    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(":nome", $pietanza->nome);
    $stmt->bindParam(":quantita", $pietanza->quantita);
    $stmt->bindParam(":prezzo", $pietanza->prezzo);
    $stmt->bindParam(":categoria", $pietanza->categoria);
    $stmt->bindParam(":ordering", $pietanza->ordering);
    $stmt->bindParam(":id", $pietanza->id);

    $this->db->begin_transaction();
    $this->db->lock_tables();
    if ($oldOrdering != $newOrdering || $oldCategoria != $newCategoria) {
      $this->prepareOrder($pietanza);
    }
    $stmt->execute();
    $this->updateAggiunte($pietanza);
    $this->db->commit();
    $this->db->unlock_tables();

    return $pietanza;
  }

  function increaseQuantity($pietanza, $quantita = 1) {
    $query = "UPDATE
    pietanze
    SET
    quantita=quantita+:quantita
    WHERE
    id=:id";

    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(":id", $pietanza);
    $stmt->bindParam(":quantita", $quantita);

    $stmt->execute();
  }

  function decreaseQuantity($pietanza, $quantita = 1) {
    $query = "UPDATE
    pietanze
    SET
    quantita=quantita-:quantita
    WHERE
    id=:id";

    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(":id", $pietanza);
    $stmt->bindParam(":quantita", $quantita);

    $stmt->execute();
  }

  function delete($pietanza) {
    $query = "DELETE FROM
    pietanze
    WHERE
    id=:id;";

    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(":id", $pietanza->id);

    $stmt->execute();
  }

  private function updateAggiunte($pietanza) {
    $aggiuntaDAO = new AggiuntaDAO($this->db);

    $query = "DELETE FROM
    aggiunte_pietanze
    WHERE
    pietanza=:pietanza";
    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(":pietanza", $pietanza->id);
    $stmt->execute();

    foreach ($pietanza->aggiunte as $aggiunta) {
      $query = "INSERT INTO
      aggiunte_pietanze
      SET
      pietanza=:pietanza, aggiunta=:aggiunta";

      $stmt = $this->conn->prepare($query);

      $stmt->bindParam(":pietanza", $pietanza->id);
      $stmt->bindParam(":aggiunta", $aggiunta->id);
      $stmt->execute();
    }
    $pietanza->aggiunte = $aggiuntaDAO->getListByPietanza($pietanza->id);
  }

  private function prepareOrder($pietanza) {
    $query = "UPDATE
    pietanze
    SET
    ordering=ordering+1
    WHERE
    ordering>=:ordering AND categoria=:categoria";
    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(":ordering", $pietanza->ordering);
    $stmt->bindParam(":categoria", $pietanza->categoria);

    $stmt->execute();
  }

}

<?php namespace Sagra\Data\DAO;

use Sagra\Data\DAO\DAO;
use Sagra\Data\DAO\CategoriaDAO;
use Sagra\Entities\Scontrino;
use Sagra\Exceptions\InputException;

class ScontrinoDAO extends DAO {

  function get($id) {
    $categoriaDAO = new CategoriaDAO($this->db);

    $query = "SELECT
    id, nome, ricevuta, immagine, dimensione, intestazione, prezzi,
    totale, tavolo, barcode, dataora, ordering
    FROM
    scontrini
    WHERE
    id=:id";
    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(":id", $id);

    $stmt->execute();
    if ($stmt->rowCount() == 0) {
      throw new InputException(NOT_FOUND, ["resource" => "scontrino", "id" => $id]);
    }
    $scontrino = $stmt->fetchObject("Sagra\Entities\Scontrino");
    $scontrino->categorie = $categoriaDAO->getListByScontrino($scontrino->id);

    return $scontrino;
  }

  function getListByStampante($stampante) {
    $query = "SELECT
    id
    FROM
    scontrini, scontrini_stampanti
    WHERE
    stampante=:stampante AND scontrini.id = scontrini_stampanti.scontrino";
    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(":stampante", $stampante);

    $stmt->execute();
    $scontrini = $stmt->fetchAll(\PDO::FETCH_OBJ);

    return $scontrini;
  }

  function getAll() {
    $categoriaDAO = new CategoriaDAO($this->db);

    $query = "SELECT
    id, nome, ricevuta, immagine, dimensione, intestazione, prezzi,
    totale, tavolo, barcode, dataora, ordering
    FROM
    scontrini
    ORDER BY
    ordering ASC";
    $stmt = $this->conn->prepare($query);

    $stmt->execute();
    $scontrini = [];
    while ($scontrino = $stmt->fetchObject("Sagra\Entities\Scontrino")) {
      $scontrino->categorie = $categoriaDAO->getListByScontrino($scontrino->id);
      $scontrini[] = $scontrino;
    }

    return $scontrini;
  }

  function insert($data) {
    $scontrino = new Scontrino($data);

    $query = "INSERT INTO
    scontrini
    SET
    nome=:nome, ricevuta=:ricevuta, immagine=:immagine,
    dimensione=:dimensione, intestazione=:intestazione, prezzi=:prezzi,
    totale=:totale, tavolo=:tavolo, barcode=:barcode, dataora=:dataora,
    ordering=:ordering";
    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(":nome", $scontrino->nome);
    $stmt->bindParam(":ricevuta", $scontrino->ricevuta);
    $stmt->bindParam(":immagine", $scontrino->immagine);
    $stmt->bindParam(":dimensione", $scontrino->dimensione);
    $stmt->bindParam(":intestazione", $scontrino->intestazione);
    $stmt->bindParam(":prezzi", $scontrino->prezzi);
    $stmt->bindParam(":totale", $scontrino->totale);
    $stmt->bindParam(":tavolo", $scontrino->tavolo);
    $stmt->bindParam(":barcode", $scontrino->barcode);
    $stmt->bindParam(":dataora", $scontrino->dataora);
    $stmt->bindParam(":ordering", $scontrino->ordering);

    $this->db->begin_transaction();
    $this->prepareOrder($scontrino);
    $stmt->execute();
    $scontrino->id = $this->conn->lastInsertId();
    $this->updateCategorie($scontrino);
    $this->db->commit();

    return $scontrino;
  }

  function update($scontrino, $data) {
    $oldOrdering = $scontrino->ordering;
    $scontrino->patch($data);
    $newOrdering = $scontrino->ordering;

    $query = "UPDATE
    scontrini
    SET
    nome=:nome, ricevuta=:ricevuta, immagine=:immagine,
    dimensione=:dimensione, intestazione=:intestazione, prezzi=:prezzi,
    totale=:totale, tavolo=:tavolo, barcode=:barcode, dataora=:dataora,
    ordering=:ordering
    WHERE
    id=:id";

    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(":nome", $scontrino->nome);
    $stmt->bindParam(":ricevuta", $scontrino->ricevuta);
    $stmt->bindParam(":immagine", $scontrino->immagine);
    $stmt->bindParam(":dimensione", $scontrino->dimensione);
    $stmt->bindParam(":intestazione", $scontrino->intestazione);
    $stmt->bindParam(":prezzi", $scontrino->prezzi);
    $stmt->bindParam(":totale", $scontrino->totale);
    $stmt->bindParam(":tavolo", $scontrino->tavolo);
    $stmt->bindParam(":barcode", $scontrino->barcode);
    $stmt->bindParam(":dataora", $scontrino->dataora);
    $stmt->bindParam(":ordering", $scontrino->ordering);
    $stmt->bindParam(":id", $scontrino->id);

    $this->db->begin_transaction();
    if ($oldOrdering != $newOrdering) {
      $this->prepareOrder($scontrino);
    }
    $stmt->execute();
    $this->updateCategorie($scontrino);
    $this->db->commit();

    return $scontrino;
  }

  function delete($scontrino) {
    $query = "DELETE FROM
    scontrini
    WHERE
    id=:id;";

    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(":id", $scontrino->id);

    $stmt->execute();
  }

  private function updateCategorie($scontrino) {
    $categoriaDAO = new CategoriaDAO($this->db);

    $query = "DELETE FROM
    categorie_scontrini
    WHERE
    scontrino=:scontrino";
    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(":scontrino", $scontrino->id);
    $stmt->execute();

    foreach ($scontrino->categorie as $categoria) {
      $query = "INSERT INTO
      categorie_scontrini
      SET
      scontrino=:scontrino, categoria=:categoria";

      $stmt = $this->conn->prepare($query);

      $stmt->bindParam(":scontrino", $scontrino->id);
      $stmt->bindParam(":categoria", $categoria->id);
      $stmt->execute();
    }
    $scontrino->categorie = $categoriaDAO->getListByScontrino($scontrino->id);
  }

  private function prepareOrder($scontrino) {
    $query = "UPDATE
    scontrini
    SET
    ordering=ordering+1
    WHERE
    ordering>=:ordering";
    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(":ordering", $scontrino->ordering);

    $stmt->execute();
  }

}

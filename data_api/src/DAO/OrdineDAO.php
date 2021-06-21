<?php namespace Sagra\Data\DAO;

use Sagra\Data\DAO\DAO;
use Sagra\Data\DAO\AggiuntaDAO;
use Sagra\Data\DAO\PietanzaDAO;
use Sagra\Data\DAO\PrenotazioneDAO;
use Sagra\Data\DAO\CassaDAO;
use Sagra\Data\DAO\StampanteDAO;
use Sagra\Data\DAO\ScontrinoDAO;
use Sagra\Entities\Ordine;
use Sagra\Exceptions\InputException;

class OrdineDAO extends DAO {

  function get($id) {
    $query = "SELECT
    id, serata, numero_ordine, data_inserimento, cassa,
    omaggio, asporto, nome, tavolo, coperti
    FROM
    ordini
    WHERE
    id=:id";
    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(":id", $id);

    $stmt->execute();
    if ($stmt->rowCount() == 0) {
      throw new InputException(NOT_FOUND, ["resource" => "ordine", "id" => $id]);
    }
    $ordine = $stmt->fetchObject("Sagra\Entities\Ordine");
    $ordine->pietanze = $this->getPietanze($ordine->id);

    return $ordine;
  }

  function getByCassa($cassa) {
    $query = "SELECT
    id, serata, numero_ordine, data_inserimento, cassa,
    omaggio, asporto, nome, tavolo, coperti
    FROM
    ordini
    WHERE
    cassa=:cassa";
    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(":cassa", $cassa);

    $stmt->execute();
    $ordini = [];
    while ($ordine = $stmt->fetchObject("Sagra\Entities\Ordine")) {
      $ordine->pietanze = $this->getPietanze($ordine->id);
      $ordini[] = $ordine;
    }

    return $ordini;
  }

  function getBySerata($serata) {
    $query = "SELECT
    id, serata, numero_ordine, data_inserimento, cassa,
    omaggio, asporto, nome, tavolo, coperti
    FROM
    ordini
    WHERE
    serata=:serata";
    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(":serata", $serata);

    $stmt->execute();
    $ordini = [];
    while ($ordine = $stmt->fetchObject("Sagra\Entities\Ordine")) {
      $ordine->pietanze = $this->getPietanze($ordine->id);
      $ordini[] = $ordine;
    }

    return $ordini;
  }

  function getAll() {
    $query = "SELECT
    id, serata, numero_ordine, data_inserimento, cassa,
    omaggio, asporto, nome, tavolo, coperti
    FROM
    ordini";
    $stmt = $this->conn->prepare($query);

    $stmt->execute();
    $ordini = [];
    while ($ordine = $stmt->fetchObject("Sagra\Entities\Ordine")) {
      $ordine->pietanze = $this->getPietanze($ordine->id);
      $ordini[] = $ordine;
    }

    return $ordini;
  }

  function insert($data) {
    $prenotazioneDAO = new PrenotazioneDAO($this->db);

    $ordine = new Ordine($data);
    $ordine->data_inserimento = date("Y-m-d H:i:s");

    $query = "INSERT INTO
    ordini
    SET
    serata=:serata,
    numero_ordine=:numero_ordine,
    data_inserimento=:data_inserimento,
    cassa=:cassa,
    omaggio=:omaggio,
    asporto=:asporto,
    nome=:nome,
    tavolo=:tavolo,
    coperti=:coperti";

    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(":serata", $ordine->serata);
    $stmt->bindParam(":data_inserimento", $ordine->data_inserimento);
    $stmt->bindParam(":cassa", $ordine->cassa);
    $stmt->bindParam(":omaggio", $ordine->omaggio);
    $stmt->bindParam(":asporto", $ordine->asporto);
    $stmt->bindParam(":nome", $ordine->nome);
    $stmt->bindParam(":tavolo", $ordine->tavolo);
    $stmt->bindParam(":coperti", $ordine->coperti);

    $this->db->begin_transaction();
    $this->db->lock_tables();
    $ordine->numero_ordine = $this->getMaxId($ordine->serata);
    $stmt->bindParam(":numero_ordine", $ordine->numero_ordine);
    $stmt->execute();
    $ordine->id = $this->conn->lastInsertId();
    $this->addPietanze($ordine);
    $prenotazioneDAO->deleteByCassa($ordine->cassa);
    $this->db->commit();
    $this->db->unlock_tables();

    return $ordine;
  }

  function delete($ordine) {
    $pietanzaDAO = new PietanzaDAO($this->db);

    $query = "DELETE
    FROM
    ordini
    WHERE
    id=:id";

    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(":id", $ordine->id);

    $this->db->begin_transaction();
    $this->db->lock_tables();
    $pietanze = $this->getPietanzeCount($ordine->id);
    foreach ($pietanze as $pietanza) {
      $pietanzaDAO->increaseQuantity($pietanza->id, $pietanza->quantita);
    }
    $stmt->execute();
    $this->db->commit();
    $this->db->unlock_tables();
  }

  function deleteBySerata($serata) {
    $ordini = $this->getBySerata($serata);

    foreach ($ordini as $ordine) {
      $this->delete($ordine);
    }
  }

  function deleteAll() {
    $ordini = $this->getAll();

    foreach ($ordini as $ordine) {
      $this->delete($ordine);
    }
  }

  private function getMaxId($serata) {
    $query = "SELECT
    MAX(numero_ordine)+1
    FROM
    ordini
    WHERE
    serata=:serata";
    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(":serata", $serata);

    $stmt->execute();

    $maxId = $stmt->fetchColumn();

    return ($maxId === null ? 1 : $maxId);
  }

  private function addPietanze($ordine) {
    $pietanzaDAO = new PietanzaDAO($this->db);
    $prenotazioneDAO = new PrenotazioneDAO($this->db);

    $ordine->totale = 0;
    foreach ($ordine->pietanze as $key => $pietanza_ordine) {
      $pietanza = $pietanzaDAO->get($pietanza_ordine->id);

      $query = "INSERT INTO
      pietanze_ordini
      SET
      ordine=:ordine, pietanza=:pietanza, prezzo=:prezzo";
      $stmt = $this->conn->prepare($query);

      $stmt->bindParam(":ordine", $ordine->id);
      $stmt->bindParam(":pietanza", $pietanza->id);
      $stmt->bindParam(":prezzo", $pietanza->prezzo);

      if ($pietanza->quantita < 1) {
        throw new InputException(QUANTITY_EXCEEDED, ["pietanza" => $pietanza->id]);
      }

      try {
        $prenotazione = $prenotazioneDAO->getByIdAndPietanzaAndCassa($pietanza_ordine->prenotazione, $pietanza->id, $ordine->cassa);
      } catch (InputException $e) {
        throw new InputException(RESERVATION_EXCEEDED, ["pietanza" => $pietanza->id]);
      }

      $aggiunte_errate = array_values(array_diff($prenotazione->aggiunte, $pietanza->aggiunte));
      if ($aggiunte_errate) {
        throw new InputException(BAD_SUPPLEMENT, ["pietanza" => $pietanza->id, "aggiunte_errate" => $aggiunte_errate]);
      }

      $stmt->execute();
      $id_pietanze_ordini = $this->conn->lastInsertId();

      $pietanzaDAO->decreaseQuantity($pietanza->id);
      $prenotazioneDAO->delete($prenotazione->id);
      $pietanza->aggiunte = $this->addPietanzeAggiunte($id_pietanze_ordini, $prenotazione->aggiunte);
      $ordine->totale += $pietanza->prezzo;
      unset($pietanza->quantita);
      unset($pietanza->ordering);
      $ordine->pietanze[$key] = $pietanza;
    }
    if ($ordine->omaggio > $ordine->totale) {
      throw new InputException(OMAGGIO_EXCEEDED, ["totale" => $ordine->totale]);
    }
  }

  private function addPietanzeAggiunte($id_pietanze_ordini, $aggiunte_richieste) {
    $aggiuntaDAO = new AggiuntaDAO($this->db);

    $aggiunte = [];
    foreach ($aggiunte_richieste as $aggiunta) {

      $query = "INSERT INTO
      aggiunte_pietanze_ordini
      SET
      pietanze_ordini=:pietanze_ordini, aggiunta=:aggiunta";

      $stmt = $this->conn->prepare($query);
      $stmt->bindParam(":pietanze_ordini", $id_pietanze_ordini);
      $stmt->bindParam(":aggiunta", $aggiunta->id);
      $stmt->execute();

      $aggiunte[] = $aggiuntaDAO->get($aggiunta->id);
    }
    return $aggiunte;
  }

  private function getPietanze($id_ordine) {
    $pietanzaDAO = new PietanzaDAO($this->db);

    $pietanze = [];

    $query = "SELECT
    id, pietanza, prezzo
    FROM
    pietanze_ordini
    WHERE
    ordine=:ordine";
    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(":ordine", $id_ordine);

    $stmt->execute();
    while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
      $pietanza = $pietanzaDAO->get($row['pietanza']);
      $pietanza->prezzo = $row['prezzo'];
      $pietanza->aggiunte = $this->getPietanzeAggiunte($row['id']);
      unset($pietanza->ordering);
      unset($pietanza->quantita);
      $pietanze[] = $pietanza;
    }
    return $pietanze;
  }

  private function getPietanzeCount($id_ordine) {
    $pietanze = [];

    $query = "SELECT
    pietanza, COUNT(pietanza) AS quantita
    FROM
    pietanze_ordini
    WHERE
    ordine=:ordine
    GROUP BY
    pietanza";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":ordine", $id_ordine);

    $stmt->execute();
    while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
      $pietanze[] = (object) ["id" => $row['pietanza'], "quantita" => $row['quantita']];
    }
    return $pietanze;
  }

  private function getPietanzeAggiunte($id_pietanze_ordini) {
    $aggiuntaDAO = new AggiuntaDAO($this->db);
    $aggiunte = [];

    $query = "SELECT
    aggiunta
    FROM
    aggiunte_pietanze_ordini
    WHERE
    pietanze_ordini=:pietanze_ordini";
    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(":pietanze_ordini", $id_pietanze_ordini);

    $stmt->execute();
    while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
      $aggiunte[] = $aggiuntaDAO->get($row['aggiunta']);
    }
    return $aggiunte;
  }

}

<?php namespace Sagra\Data\DAO;

use Sagra\Data\DAO\DAO;
use Sagra\Entities\Aggiunta;
use Sagra\Exceptions\InputException;

class AggiuntaDAO extends DAO {

    function get($id) {
        $query = "SELECT
                id, nome, ordering
            FROM
                aggiunte
            WHERE
                id=:id";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":id", $id);

        $stmt->execute();
        if ($stmt->rowCount() == 0) {
            throw new InputException(NOT_FOUND, ["resource" => "aggiunta", "id" => $id]);
        }
        $aggiunta = $stmt->fetchObject("Sagra\Entities\Aggiunta");

        return $aggiunta;
    }

    function getListByPietanza($pietanza) {
        $query = "SELECT
                id, nome, ordering
            FROM
                aggiunte, aggiunte_pietanze
            WHERE
                pietanza=:pietanza AND aggiunte.id = aggiunte_pietanze.aggiunta
            ORDER BY
                ordering";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":pietanza", $pietanza);

        $stmt->execute();
        $aggiunte = [];
        while ($aggiunta = $stmt->fetchObject("Sagra\Entities\Aggiunta")) {
            $aggiunte[] = $aggiunta;
        }

        return $aggiunte;
    }
    function getListByPrenotazione($prenotazione) {
        $query = "SELECT
                id
            FROM
                aggiunte, aggiunte_prenotazioni
            WHERE
                prenotazione=:prenotazione AND aggiunte.id = aggiunte_prenotazioni.aggiunta";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":prenotazione", $prenotazione);

        $stmt->execute();
        $aggiunte = [];
        while ($aggiunta = $stmt->fetchObject("Sagra\Entities\Aggiunta")) {
            $aggiunte[] = $aggiunta;
        }

        return $aggiunte;
    }

    function getAll() {
        $query = "SELECT
                id, nome, ordering
            FROM
                aggiunte
            ORDER BY
                ordering ASC";
        $stmt = $this->conn->prepare($query);

        $stmt->execute();
        $aggiunte = [];
        while ($aggiunta = $stmt->fetchObject("Sagra\Entities\Aggiunta")) {
            $aggiunte[] = $aggiunta;
        }

        return $aggiunte;
    }

    function insert($data) {
        $aggiunta = new Aggiunta($data);

        $query = "INSERT INTO
                    aggiunte
                SET
                    nome=:nome,
                    ordering=:ordering";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":nome", $aggiunta->nome);
        $stmt->bindParam(":ordering", $aggiunta->ordering);

        $this->db->begin_transaction();
        $this->prepareOrder($aggiunta);
        $stmt->execute();
        $aggiunta->id = $this->conn->lastInsertId();
        $this->db->commit();

        return $aggiunta;
    }

    function update($aggiunta, $data) {
        $oldOrdering = $aggiunta->ordering;
        $aggiunta->patch($data);
        $newOrdering = $aggiunta->ordering;

        $query = "UPDATE
                    aggiunte
                SET
                    nome=:nome,
                    ordering=:ordering
                WHERE
                    id=:id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":nome", $aggiunta->nome);
        $stmt->bindParam(":ordering", $aggiunta->ordering);
        $stmt->bindParam(":id", $aggiunta->id);

        $this->db->begin_transaction();
        if ($oldOrdering != $newOrdering) {
            $this->prepareOrder($aggiunta);
        }
        $stmt->execute();
        $this->db->commit();

        return $aggiunta;
    }

    function delete($aggiunta) {
        $query = "DELETE
            FROM
                aggiunte
            WHERE
                id=:id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":id", $aggiunta->id);

        $stmt->execute();
    }

    private function prepareOrder($aggiunta) {
        $query = "UPDATE
                aggiunte
            SET
                ordering=ordering+1
            WHERE
                ordering>=:ordering";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":ordering", $aggiunta->ordering);

        $stmt->execute();
    }

}

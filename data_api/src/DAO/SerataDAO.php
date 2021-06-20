<?php namespace Sagra\Data\DAO;

use Sagra\Data\DAO\DAO;
use Sagra\Data\DAO\CassaDAO;
use Sagra\Data\DAO\PrenotazioneDAO;
use Sagra\Entities\Serata;
use Sagra\Exceptions\InputException;
use Sagra\Exceptions\ObjectException;

class SerataDAO extends DAO {

    function get($id) {
        $query = "SELECT
                id, nome, inizio, fine, attiva
            FROM
                serate
            WHERE
                id=:id";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":id", $id);

        $stmt->execute();
        if ($stmt->rowCount() == 0) {
            throw new InputException(NOT_FOUND, ["resource" => "serata", "id" => $id]);
        }
        $serata = $stmt->fetchObject("Sagra\Entities\Serata");
        $serata->inizioTimestamp = strtotime($serata->inizio);

        return $serata;
    }

    function getAttiva() {
        $query = "SELECT
                id, nome, inizio, fine, attiva
            FROM
                serate
            WHERE
                attiva=1 AND NOW() BETWEEN inizio AND fine";
        $stmt = $this->conn->prepare($query);

        $stmt->execute();
        $serate = [];
        while ($serata = $stmt->fetchObject("Sagra\Entities\Serata")) {
            $serata->inizioTimestamp = strtotime($serata->inizio);
            $serate[] = $serata;
        }
        if (count($serate) > 1) {
            throw new InternalException();
        } else if (count($serate) == 0) {
            throw new InputException(NOT_FOUND);
        }

        return $serate[0];
    }

    function getAll() {
        $query = "SELECT
                id, nome, inizio, fine, attiva
            FROM
                serate
            ORDER BY
                inizio";
        $stmt = $this->conn->prepare($query);

        $stmt->execute();
        $serate = [];
        while ($serata = $stmt->fetchObject("Sagra\Entities\Serata")) {
            $serata->inizioTimestamp = strtotime($serata->inizio);
            $serate[] = $serata;
        }

        return $serate;
    }

    function insert($data) {
        $serata = new Serata($data);

        $query = "INSERT INTO
                    serate
                SET
                    nome=:nome,
                    inizio=:inizio,
                    fine=:fine,
                    attiva=:attiva";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":nome", $serata->nome);
        $stmt->bindParam(":inizio", $serata->inizio);
        $stmt->bindParam(":fine", $serata->fine);
        $stmt->bindParam(":attiva", $serata->attiva);

        $this->db->begin_transaction();
        $this->db->lock_tables();
        $serate = $this->getAll();
        foreach ($serate as $s) {
            if ($serata->inizio <= $s->fine && $serata->fine >= $s->inizio) {
                throw new ObjectException(OBJ_CONSTRAINT, ["constraint" => CNS_SERATA_1, "message" => "Serate cannot overlap"]);
            }
        }
        $stmt->execute();
        $serata->id = $this->conn->lastInsertId();
        if ($serata->attiva == 1) {
            $this->disableAllExcept($serata);
            $this->resetCasse();
        }
        $this->db->commit();
        $this->db->unlock_tables();

        return $serata;
    }

    function update($serata, $data) {
        $attivaOld = $serata->attiva;

        $serata->patch($data);

        $query = "UPDATE
                    serate
                SET
                    nome=:nome,
                    inizio=:inizio,
                    fine=:fine,
                    attiva=:attiva
                WHERE
                    id=:id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":nome", $serata->nome);
        $stmt->bindParam(":inizio", $serata->inizio);
        $stmt->bindParam(":fine", $serata->fine);
        $stmt->bindParam(":attiva", $serata->attiva);
        $stmt->bindParam(":id", $serata->id);

        $this->db->begin_transaction();
        $this->db->lock_tables();
        $serate = $this->getAll();
        foreach ($serate as $s) {
            if ($serata->id != $s->id && $serata->inizio <= $s->fine && $serata->fine >= $s->inizio) {
                throw new ObjectException(OBJ_CONSTRAINT, ["constraint" => CNS_SERATA_1, "message" => "Serate cannot overlap"]);
            }
        }
        $stmt->execute();
        if ($serata->attiva == 1) {
            $this->disableAllExcept($serata);
            $this->resetCasse();
        } else if ($attivaOld == 1) {
            $this->resetCasse();
        }
        $this->db->commit();
        $this->db->unlock_tables();

        return $serata;
    }

    function delete($serata) {
        $query = "DELETE
            FROM
                serate
            WHERE
                id=:id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":id", $serata->id);

        $stmt->execute();
    }

    private function disableAllExcept($serata) {

        $query = "UPDATE
                    serate
                SET
                    attiva=0
                WHERE
                    id!=:id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":id", $serata->id);

        $stmt->execute();
        $this->resetCasse();
    }

    private function resetCasse() {
        $cassaDAO = new CassaDAO($this->db);
        $prenotazioneDAO = new PrenotazioneDAO($this->db);

        $cassaDAO->deleteCookies();
        $cassaDAO->resetNotifications();
        $prenotazioneDAO->deleteAll();
    }

}

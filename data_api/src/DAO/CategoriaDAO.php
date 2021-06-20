<?php namespace Sagra\Data\DAO;

use Sagra\Data\DAO\DAO;
use Sagra\Entities\Categoria;
use Sagra\Exceptions\InputException;

class CategoriaDAO extends DAO {

    function get($id) {
        $query = "SELECT
                id, nome, gruppo, ordering
            FROM
                categorie
            WHERE
                id=:id";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":id", $id);

        $stmt->execute();
        if ($stmt->rowCount() == 0) {
            throw new InputException(NOT_FOUND, ["resource" => "categoria", "id" => $id]);
        }
        $categoria = $stmt->fetchObject("Sagra\Entities\Categoria");

        return $categoria;
    }

    function getListByScontrino($scontrino) {
        $query = "SELECT
                id
            FROM
                categorie, categorie_scontrini
            WHERE
                scontrino=:scontrino AND categorie.id = categorie_scontrini.categoria";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":scontrino", $scontrino);

        $stmt->execute();
        $categorie = [];
        while ($categoria = $stmt->fetchObject("Sagra\Entities\Categoria")) {
            $categorie[] = $categoria;
        }

        return $categorie;
    }

    function getAll() {
        $query = "SELECT
                id, nome, gruppo, ordering
            FROM
                categorie
            ORDER BY
                ordering ASC";
        $stmt = $this->conn->prepare($query);

        $stmt->execute();
        $categorie = [];
        while ($categoria = $stmt->fetchObject("Sagra\Entities\Categoria")) {
            $categorie[] = $categoria;
        }

        return $categorie;
    }

    function insert($data) {
        $categoria = new Categoria($data);

        $query = "INSERT INTO
                    categorie
                SET
                    nome=:nome,
                    gruppo=:gruppo,
                    ordering=:ordering";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":nome", $categoria->nome);
        $stmt->bindParam(":gruppo", $categoria->gruppo);
        $stmt->bindParam(":ordering", $categoria->ordering);

        $this->db->begin_transaction();
        $this->prepareOrder($categoria);
        $stmt->execute();
        $categoria->id = $this->conn->lastInsertId();
        $this->db->commit();

        return $categoria;
    }

    function update($categoria, $data) {
        $oldOrdering = $categoria->ordering;
        $categoria->patch($data);
        $newOrdering = $categoria->ordering;

        $query = "UPDATE
                    categorie
                SET
                    nome=:nome,
                    gruppo=:gruppo,
                    ordering=:ordering
                WHERE
                    id=:id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":nome", $categoria->nome);
        $stmt->bindParam(":gruppo", $categoria->gruppo);
        $stmt->bindParam(":ordering", $categoria->ordering);
        $stmt->bindParam(":id", $categoria->id);

        $this->db->begin_transaction();
        if ($oldOrdering != $newOrdering) {
            $this->prepareOrder($categoria);
        }
        $stmt->execute();
        $this->db->commit();

        return $categoria;
    }

    function delete($categoria) {
        $query = "DELETE
            FROM
                categorie
            WHERE
                id=:id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":id", $categoria->id);

        $stmt->execute();
    }

    private function prepareOrder($categoria) {
        $query = "UPDATE
                categorie
            SET
                ordering=ordering+1
            WHERE
                ordering>=:ordering";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":ordering", $categoria->ordering);

        $stmt->execute();
    }

}

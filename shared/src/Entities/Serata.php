<?php namespace Sagra\Entities;

use Sagra\Entities\ApiObjectId;
use Sagra\Exceptions\ObjectException;

class Serata extends ApiObjectId {

    public $id;
    public $nome;
    public $inizio;
    public $fine;
    public $attiva;

    protected function checkValid() {
        check_var($this->nome, "nome", true);
        check_var($this->inizio, "inizio", true);
        check_var($this->fine, "fine", true);
        check_var($this->attiva, "attiva", false);
        if ($this->attiva === null) {
            $this->attiva = 0;
        }
        if ($this->fine <= $this->inizio) {
            throw new ObjectException(OBJ_CONSTRAINT, ["constraint" => CNS_SERATA_0, "message" => "Fine cannot be smaller or equal to inizio"]);
        }
    }

    function fill($data) {
        if ($data->id !== null) {
            $this->id = filter($data->id, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0]]);
        }
        if ($data->nome !== null) {
            $this->nome = filter($data->nome, FILTER_VALIDATE_STRING, ["options" => ["strip_tags" => true, "ucfirst" => true, "empty" => false]]);
        }
        if ($data->inizio !== null) {
            $this->inizio = filter($data->inizio, FILTER_VALIDATE_DATE, ["options" => ["format" => "Y-m-d H:i:s"]]);
        }
        if ($data->fine !== null) {
            $this->fine = filter($data->fine, FILTER_VALIDATE_DATE, ["options" => ["format" => "Y-m-d H:i:s"]]);
        }
        if ($data->attiva !== null) {
            $this->attiva = filter($data->attiva, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0, "max_range" => 1]]);
        }
    }

}

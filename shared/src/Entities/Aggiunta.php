<?php namespace Sagra\Entities;

use Sagra\Entities\ApiObjectId;

class Aggiunta extends ApiObjectId {

    public $id;
    public $nome;
    public $ordering;

    protected function checkValid() {
        check_var($this->nome, "nome", true);
        check_var($this->ordering, "ordering", true);
    }

    function fill($data) {
        if ($data->id !== null) {
            $this->id = filter($data->id, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0]]);
        }
        if ($data->nome !== null) {
            $this->nome = filter($data->nome, FILTER_VALIDATE_STRING, ["options" => ["strip_tags" => true, "ucfirst" => true, "empty" => false]]);
        }
        if ($data->ordering !== null) {
            $this->ordering = filter($data->ordering, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0]]);
        }
    }

}

<?php namespace Sagra\Entities;

use Sagra\Entities\ApiObjectId;

class Categoria extends ApiObjectId {

    public $id;
    public $nome;
    public $gruppo;
    public $ordering;

    protected function checkValid() {
        check_var($this->nome, "nome", true);
        check_var($this->gruppo, "gruppo", true);
        check_var($this->ordering, "ordering", true);
    }

    function fill($data) {
        if ($data->id !== null) {
            $this->id = filter($data->id, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0]]);
        }
        if ($data->nome !== null) {
            $this->nome = filter($data->nome, FILTER_VALIDATE_STRING, ["options" => ["strip_tags" => true, "ucfirst" => true, "empty" => false]]);
        }
        if ($data->gruppo !== null) {
            $this->gruppo = filter($data->gruppo, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0, "max_range" => 1]]);
        }
        if ($data->ordering !== null) {
            $this->ordering = filter($data->ordering, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0]]);
        }
    }

}

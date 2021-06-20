<?php namespace Sagra\Entities;

use Sagra\Entities\ApiObjectId;

class Stampante extends ApiObjectId {

    public $id;
    public $nome;
    public $ip;
    public $scontrini;

    protected function checkValid() {
        check_var($this->nome, "nome", true);
        check_var($this->ip, "ip", true);
        check_var($this->scontrini, "scontrini", true);
    }

    function fill($data) {
        if ($data->id !== null) {
            $this->id = filter($data->id, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0]]);
        }
        if ($data->nome !== null) {
            $this->nome = filter($data->nome, FILTER_VALIDATE_STRING, ["options" => ["strip_tags" => true, "ucfirst" => true, "empty" => false]]);
        }
        if ($data->ip !== null) {
            $this->ip = filter($data->ip, FILTER_VALIDATE_IP, null);
        }
        if ($data->scontrini !== null) {
            $this->scontrini = filter_array($data->scontrini, FILTER_VALIDATE_ID, __NAMESPACE__ . "\Scontrino");
        }
    }

}

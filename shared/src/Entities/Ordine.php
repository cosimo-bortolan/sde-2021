<?php namespace Sagra\Entities;

use Sagra\Entities\ApiObjectId;
use Sagra\Exceptions\ObjectException;

class Ordine extends ApiObjectId {

  public $id;
  public $serata;
  public $numero_ordine;
  public $data_inserimento;
  public $cassa;
  public $pietanze;
  public $omaggio;
  public $asporto;
  public $nome;
  public $tavolo;
  public $coperti;

  protected function checkValid() {
    check_var($this->serata, "serata", true);
    check_var($this->cassa, "cassa", true);
    check_var($this->pietanze, "pietanze", true);
    check_var($this->omaggio, "omaggio", true);
    check_var($this->asporto, "asporto", true);
    check_var($this->nome, "nome", true);
    check_var($this->tavolo, "tavolo", true);
    check_var($this->coperti, "coperti", true);

    if ($this->asporto == 0 && $this->tavolo == 0) {
      throw new ObjectException(OBJ_CONSTRAINT, ["constraint" => CNS_ORDINE_0, "message" => "Asporto and tavolo cannot be both unset"]);
    }
    if ($this->asporto == 1 && $this->tavolo != 0) {
      throw new ObjectException(OBJ_CONSTRAINT, ["constraint" => CNS_ORDINE_1, "message" => "Asporto and tavolo cannot be both set"]);
    }
    if ($this->asporto == 1 && empty($this->nome)) {
      throw new ObjectException(OBJ_CONSTRAINT, ["constraint" => CNS_ORDINE_2, "message" => "Name cannot be unset if asporto is set"]);
    }
  }

  protected function fill($data) {
    if ($data->id !== null) {
      $this->id = filter($data->id, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0]]);
    }
    if ($data->serata !== null) {
      $this->serata = filter($data->serata, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0]]);
    }
    if ($data->cassa !== null) {
      $this->cassa = filter($data->cassa, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0]]);
    }
    if ($data->pietanze !== null) {
      $this->pietanze = filter_array($data->pietanze, FILTER_VALIDATE_ID, __NAMESPACE__ . "\Pietanza");
      if ($this->pietanze !== false) {
        foreach ($this->pietanze as $pietanza) {
          $pietanza->aggiunte = filter_array($pietanza->aggiunte, FILTER_VALIDATE_ID, __NAMESPACE__ . "\Aggiunta");
          if ($pietanza->aggiunte === false) {
            $pietanza->aggiunte = [];
          }
        }
      }
    }
    if ($data->omaggio !== null) {
      $this->omaggio = filter($data->omaggio, FILTER_VALIDATE_FLOAT, ["options" => ["min_range" => 0]]);
    }
    if ($data->asporto !== null) {
      $this->asporto = filter($data->asporto, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0, "max_range" => 1]]);
    }
    if ($data->nome !== null) {
      $this->nome = filter($data->nome, FILTER_VALIDATE_STRING, ["options" => ["strip_tags" => true, "ucfirst" => true]]);
    }
    if ($data->tavolo !== null) {
      $this->tavolo = filter($data->tavolo, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0]]);
    }
    if ($data->coperti !== null) {
      $this->coperti = filter($data->coperti, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0]]);
    }
  }

}

<?php namespace Sagra\Entities;

use Sagra\Entities\ApiObjectId;

class Prenotazione extends ApiObjectId {

  public $id;
  public $cassa;
  public $pietanza;
  public $aggiunte;

  protected function checkValid() {
    check_var($this->cassa, "cassa", true);
    check_var($this->pietanza, "pietanza", true);
    check_var($this->aggiunte, "aggiunte", true);
  }

  function fill($data) {
    if ($data->id !== null) {
      $this->id = filter($data->id, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0]]);
    }
    if ($data->cassa !== null) {
      $this->cassa = filter($data->cassa, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0]]);
    }
    if ($data->pietanza !== null) {
      $this->pietanza = filter($data->pietanza, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0]]);
    }
    if ($data->aggiunte !== null) {
      $this->aggiunte = filter_array($data->aggiunte, FILTER_VALIDATE_ID, __NAMESPACE__ . "\Aggiunta");
    }
  }

}

<?php namespace Sagra\Entities;

use Sagra\Entities\ApiObjectId;

class Pietanza extends ApiObjectId {

  public $id;
  public $nome;
  public $aggiunte;
  public $quantita;
  public $prezzo;
  public $categoria;
  public $prenotazione;
  public $ordering;

  protected function checkValid() {
    check_var($this->nome, "nome", true);
    check_var($this->aggiunte, "aggiunte", true);
    check_var($this->quantita, "quantita", true);
    check_var($this->prezzo, "prezzo", true);
    check_var($this->categoria, "categoria", true);
    check_var($this->prenotazione, "prenotazione", false);
    check_var($this->ordering, "ordering", true);
  }

  protected function fill($data) {
    if ($data->id !== null) {
      $this->id = filter($data->id, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0]]);
    }
    if ($data->nome !== null) {
      $this->nome = filter($data->nome, FILTER_VALIDATE_STRING, ["options" => ["strip_tags" => true, "ucfirst" => true, "empty" => false]]);
    }
    if ($data->aggiunte !== null) {
      $this->aggiunte = filter_array($data->aggiunte, FILTER_VALIDATE_ID, __NAMESPACE__ . "\Aggiunta");
    }
    if ($data->quantita !== null) {
      $this->quantita = filter($data->quantita, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0]]);
    }
    if ($data->prezzo !== null) {
      $this->prezzo = filter($data->prezzo, FILTER_VALIDATE_FLOAT, ["options" => ["min_range" => 0]]);
    }
    if ($data->categoria !== null) {
      $this->categoria = filter($data->categoria, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0]]);
    }
    if ($data->prenotazione !== null) {
      $this->prenotazione = filter($data->prenotazione, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0]]);
    }
    if ($data->ordering !== null) {
      $this->ordering = filter($data->ordering, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0]]);
    }
  }

}

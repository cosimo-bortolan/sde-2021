<?php namespace Sagra\Entities;

use Sagra\Entities\ApiObjectId;

class Cassa extends ApiObjectId {

  public $id;
  public $nome;
  public $cookie;
  public $cookie_date;
  public $password;
  public $permessi;
  public $notifiche;
  public $stampante;
  public $asporto;

  protected function checkValid() {
    check_var($this->nome, "nome", true);
    check_var($this->cookie, "cookie", false);
    check_var($this->cookie_date, "cookie_date", false);
    check_var($this->password, "password", true);
    check_var($this->permessi, "permessi", true);
    check_var($this->notifiche, "notifiche", false);
    check_var($this->stampante, "stampante", false);
    check_var($this->asporto, "asporto", true);
  }

  protected function fill($data) {
    if ($data->id !== null) {
      $this->id = filter($data->id, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0]]);
    }
    if ($data->nome !== null) {
      $this->nome = filter($data->nome, FILTER_VALIDATE_STRING);
    }
    if (array_key_exists("cookie", $data)) {
      if ($data->cookie !== null) {
        $this->cookie = filter($data->cookie, FILTER_VALIDATE_STRING);
      } else {
        $this->cookie = null;
      }
    }
    if (array_key_exists("cookie_date", $data)) {
      if ($data->cookie_date !== null) {
        $this->cookie_date = filter($data->cookie_date, FILTER_VALIDATE_DATE, ["options" => ["format" => "Y-m-d H:i:s"]]);
      } else {
        $this->cookie_date = null;
      }
    }
    if ($data->password !== null) {
      $this->password = filter($data->password, FILTER_VALIDATE_STRING, ["options" => ["empty" => false]]);
    }
    if ($data->permessi !== null) {
      $this->permessi = filter($data->permessi, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0]]);
    }
    if ($data->notifiche !== null) {
      $this->notifiche = filter($data->notifiche, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0]]);
    }
    if (array_key_exists("stampante", $data)) {
      if ($data->stampante !== null) {
        $this->stampante = filter($data->stampante, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0]]);
      } else {
        $this->stampante = null;
      }
    }
    if ($data->asporto !== null) {
      $this->asporto = filter($data->asporto, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0, "max_range" => 1]]);
    }
  }

}

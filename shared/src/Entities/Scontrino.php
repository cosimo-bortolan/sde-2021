<?php namespace Sagra\Entities;

use Sagra\Entities\ApiObjectId;
use Sagra\Exceptions\ObjectException;

class Scontrino extends ApiObjectId {

    public $id;
    public $nome;
    public $ricevuta;
    public $categorie;
    public $immagine;
    public $dimensione;
    public $intestazione;
    public $prezzi;
    public $totale;
    public $tavolo;
    public $barcode;
    public $dataora;
    public $ordering;

    protected function checkValid() {
        check_var($this->nome, "nome", true);
        check_var($this->ricevuta, "ricevuta", true);
        check_var($this->categorie, "categorie", true);
        check_var($this->immagine, "immagine", true);
        check_var($this->dimensione, "dimensione", true);
        check_var($this->intestazione, "intestazione", true);
        check_var($this->prezzi, "prezzi", true);
        check_var($this->totale, "totale", true);
        check_var($this->tavolo, "tavolo", true);
        check_var($this->barcode, "barcode", true);
        check_var($this->dataora, "dataora", true);
        check_var($this->ordering, "ordering", true);

        if ($this->ricevuta == 0 && $this->categorie === []) {
            throw new ObjectException(OBJ_CONSTRAINT, ["constraint" => CNS_SCONTRINO_0, "message" => "Ricevuta and categorie cannot be both unset"]);
        }
    }

    protected function fill($data) {
        if ($data->id !== null) {
            $this->id = filter($data->id, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0]]);
        }
        if ($data->nome !== null) {
            $this->nome = filter($data->nome, FILTER_VALIDATE_STRING, ["options" => ["strip_tags" => true, "ucfirst" => true, "empty" => false]]);
        }
        if ($data->ricevuta !== null) {
            $this->ricevuta = filter($data->ricevuta, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0, "max_range" => 1]]);
        }
        if ($data->categorie !== null) {
            $this->categorie = filter_array($data->categorie, FILTER_VALIDATE_ID, __NAMESPACE__ . "\Categoria");
        }
        if ($data->immagine !== null) {
            $this->immagine = filter($data->immagine, FILTER_VALIDATE_STRING);
        }
        if ($data->dimensione !== null) {
            $this->dimensione = filter($data->dimensione, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0, "max_range" => 1]]);
        }
        if ($data->intestazione !== null) {
            $this->intestazione = filter($data->intestazione, FILTER_VALIDATE_STRING, ["options" => ["strip_tags" => true, "ucfirst" => true]]);
        }
        if ($data->prezzi !== null) {
            $this->prezzi = filter($data->prezzi, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0, "max_range" => 1]]);
        }
        if ($data->totale !== null) {
            $this->totale = filter($data->totale, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0, "max_range" => 1]]);
        }
        if ($data->tavolo !== null) {
            $this->tavolo = filter($data->tavolo, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0, "max_range" => 1]]);
        }
        if ($data->barcode !== null) {
            $this->barcode = filter($data->barcode, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0, "max_range" => 1]]);
        }
        if ($data->dataora !== null) {
            $this->dataora = filter($data->dataora, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0, "max_range" => 1]]);
        }
        if ($data->ordering !== null) {
            $this->ordering = filter($data->ordering, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0]]);
        }
    }

}

<?php namespace Sagra\Entities;

use Sagra\EntitiesApiObject;
use Sagra\Exception\ObjectException;

/**
 * Classe astratta per entità di una API che necessitano di un id
 */
abstract class ApiObjectId extends ApiObject {

    /**
     * Id dell'oggetto
     * @var int
     */
    public $id;

    /**
     * Popola l'oggetto con i dati passati come argomento ed esegue un
     * controllo sulla validità dell'oggetto.
     *
     * Il controllo sulla validità dell'ogggeto viene effettuato attraverso la
     * funzione {@link \ApiObject::checkValid() checkValid()}.
     *
     * @param object $data contiene i valori per i campi dell'oggetto che si
     * vogliono valorizzare
     * @throws ObjectException
     */
    function __construct1($data) {
        $this->__construct2($data, false);
    }

    /**
     * Popola l'oggetto con i dati passati come argomento ed esegue un
     * controllo sulla validità dell'oggetto (o esclusivamente del suo id se
     * il paramentro checkOnlyId è settato a true).
     *
     * Il controllo sulla validità dell'ogggeto viene effettuato attraverso la
     * funzione {@link \ApiObject::checkValid() checkValid()} nel caso del
     * controllo su tutto l'oggetto, attraverso la funzione {@link check_var()}
     * nel caso del solo controllo dell'id.
     *
     * @param object $data contiene i valori per i campi dell'oggetto che si
     * vogliono valorizzare
     * @param boolean $checkOnlyId true se è necessario effettuare il controllo
     * esclusivamente sul campo id dell'oggetto.
     * @throws ObjectException
     */
    function __construct2($data, $checkOnlyId) {
        $this->fill($data);
        if ($checkOnlyId === true) {
            check_var($this->id, get_class($this) . "->id", true);
        } else {
            $this->checkValid();
        }
    }

    /**
     * Ritorna l'id dell'oggetto in formato stringa.
     *
     * @return string id dell'oggetto
     */
    public function __toString() {
        return (string) $this->id;
    }

}

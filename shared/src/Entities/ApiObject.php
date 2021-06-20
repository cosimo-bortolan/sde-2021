<?php namespace Sagra\Entities;

use Sagra\Exception\InternalException;
use Sagra\Exception\ObjectException;

/**
 * Classe astratta per entità di una API
 */
abstract class ApiObject {

    /**
     * Costruttore generico per la gestione di più costruttori con numero di
     * argomenti variabile
     *
     * In base al numero di argomenti (**N**) passati al momento della
     * creazione dell'oggeto invoca il costruttore corrispondente, che deve
     * essere chiamato __construct**N** e accettare **N** argomenti.
     * Questo costruttore corrisponde al costruttore con 0 argomenti.
     *
     * @throws InternalException se non è presente un costruttore per il numero
     * di argomenti passato alla funzione
     */
    public function __construct() {
        $get_arguments = func_get_args();
        $number_of_arguments = func_num_args();

        if ($number_of_arguments > 0) {
            if (method_exists($this, $method_name = '__construct' . $number_of_arguments)) {
                call_user_func_array(array($this, $method_name), $get_arguments);
            } else {
                throw new InternalException();
            }
        }
    }

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
    protected function __construct1($data) {
        $this->fill($data);
        $this->checkValid();
    }

    /**
     * Modifica l'oggetto con i dati passati come argomento ed esegue un
     * controllo sulla validità dell'oggetto.
     *
     * Il controllo sulla validità dell'ogggeto viene effettuato attraverso la
     * funzione {@link \ApiObject::checkValid() checkValid()}.
     *
     * @param object $data contiene i nuovi valori per i campi dell'oggetto
     * che si vogliono aggiornare
     * @throws ObjectException
     */
    public function patch($data) {
        $this->fill($data);
        $this->checkValid();
    }

    /**
     * Controlla che i valori dei campi e i vincoli su di essi siano rispettati
     *
     * Le richieste possono riguardare campi obbligatori, controlli sui
     * valori dei campi e vincoli su insiemi di campi.
     *
     * @throws ObjectException se i vincoli non sono rispettati
     */
    abstract protected function checkValid();

    /**
     * Modifica l'oggetto con i dati passati come argomento.
     *
     * @param object $data contiene i nuovi valori per i campi che si vogliono
     * aggiornare
     */
    abstract protected function fill($data);
}

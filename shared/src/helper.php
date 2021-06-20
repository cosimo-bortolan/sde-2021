<?php

use Sagra\Exceptions\InputException;
use Sagra\Exceptions\ObjectException;

//filter
define("FILTER_VALIDATE_STRING", 0);
define("FILTER_VALIDATE_DATE", 1);
//filter_validate_array
define("FILTER_VALIDATE_ID", 0);
//user permissions
define("AUTH", 0);
define("ADMIN", 1);
define("CASSA", 2);
define("USER", 4);
define("ALL", 511);
define("BLOCK", 512);
//error codes
define("ERR_NOT_AUTHENTICATED", 0);
define("ERR_NOT_ALLOWED", 1);
define("ERR_NOT_JSON", 2);
define("ERR_BAD_SUPPLEMENT", 3);
define("ERR_QUANTITY_EXCEEDED", 4);
define("ERR_RESERVATION_EXCEEDED", 5);
define("ERR_GIFT_EXCEEDED", 6);
define("ERR_REQUIRED_VAR", 7);
define("ERR_BAD_VALUE", 8);
define("ERR_OBJECT_CONSTRAINT", 9);
define("ERR_UNIQUE_CONSTRAINT", 10);
define("ERR_FOREIGN_KEY_CONSTRAINT", 11);
define("ERR_INTERNAL", 12);
define("ERR_BAD_PASSWORD", 13);
define("ERR_NOT_FOUND", 14);
define("ERR_BAD_REQUEST", 15);
define("ERR_BAD_ID", 16);
define("ERR_BAD_METHOD", 17);
define("ERR_NOT_EXPECTED", 18);
define("ERR_PRINTER_KO", 19);
define("ERR_PAYMENT_KO", 20);
//constraints codes
define("CNS_SERATA_0", 0);
define("CNS_SERATA_1", 1);
define("CNS_SCONTRINO_0", 2);
define("CNS_ORDINE_0", 3);
define("CNS_ORDINE_1", 4);
define("CNS_ORDINE_2", 5);
//login duration, in hours
define("EXPIRATION_TIME", 12);

function jsonDecode($string) {
    $data = json_decode($string);
    if ($data === null) {
        throw new InputException(NOT_JSON);
    }
    return $data;
}

function print_log($e) {
    $data = date("Y-m-d H:i:s") . " " . $e . "\n";
    file_put_contents(__DIR__ . "/log.txt", $data, FILE_APPEND | LOCK_EX);
}

/**
 * Lancia un'eccezione se la variabile contiene un valore non atteso.
 *
 * Se $required è settato a true, si aspetta che $var abbia un valore
 * divreso da null e da false. Se $required è settato a false si aspetta
 * che $var abbia un valore diverso da false.
 *
 * @param $var la variabile da controllare
 * @param string $name il nome della variabile, utile per la stampa del
 * messaggio di errore all'interno dell'eccezione
 * @param bool $required indica se è richiesto che la variabile assuma un valore diverso da null
 * @throws ObjectException
 */
function check_var($var, $name, $required = false) {
    if ($required && $var === null) {
        throw new ObjectException(REQUIRED_VAR, ["field" => $name]);
    }
    if ($var === false) {
        throw new ObjectException(BAD_VALUE, ["field" => $name]);
    }
}

/**
 * Filtra una variabile con uno specifico filtro.
 *
 * Per il filtro FILTER_VALIDATE_STRING restituisce la stringa passata come
 * input dopo che è stata depurata da caratteri speciali, spazi all'inizio
 * e alla fine e con la prima lettera maiuscola.
 * Per il filtro FILTER_VALIDATE_DATE restituisce la stringa passata in input
 * se rappresenta una data valida nel formato passato come argomento.
 * Per tutti gli altri filtri restituisce il valore ritornato dalla funzione
 * filter_var.
 *
 * @param $val la variabile da filtrare
 * @param int $filter l'ID del filtro da applicare
 * @param array $options opzioni accettate dalla funzione filter_var
 */
function filter($val, $filter, $options = null) {
    $new_val = false;
    switch ($filter) {
        case FILTER_VALIDATE_STRING:
            $new_val = trim($val);
            if (isset($options["options"]["strip_tags"]) &&
                    $options["options"]["strip_tags"] === true) {
                $new_val = strip_tags($new_val);
            }
            if (isset($options["options"]["strip_tags"]) &&
                    $options["options"]["ucfirst"] === true) {
                $new_val = ucfirst($new_val);
            }
            if (isset($options["options"]["empty"]) &&
                    $options["options"]["empty"] === false &&
                    empty($new_val)) {
                $new_val = false;
            }
            break;
        case FILTER_VALIDATE_DATE:
            if (isset($options["options"]["format"])) {
                $new_val = validateDate($val, $options["options"]["format"]);
            } else {
                $new_val = validateDate($val);
            }
            break;
        default:
            $new_val = filter_var($val, $filter, $options);
    }
    return $new_val;
}

/**
 * Filtra un array con uno specifico filtro.
 *
 * Per il filtro FILTER_VALIDATE_ID restituisce l'array in cui è stato creato
 * un oggetto per ogni elemento dell'array passato come input, dopo aver
 * controllato che l'id dell'oggetto sia valorizzato ed abbia un valore valido
 * (altrimenti lancia un'eccezione).
 *
 * Se la variabile $array non contiene un array ritorna false.
 * Se la variabile $array contiene un arrray vuoto ritorna un array vuoto.
 *
 * @param $array la variabile da filtrare
 * @param $name il nome della variabile da filtrare, utile per la stampa del
 * messaggio di errore all'interno dell'eccezione
 * @param int $filter l'ID del filtro da applicare
 * @param string $type il tipo dell'oggetto da creare per ogni elemento
 * dell'array
 * @throws InputException
 */
function filter_array($array, $filter, $type) {
    $new_array = false;
    if (is_array($array)) {
        $new_array = [];
        switch ($filter) {
            case FILTER_VALIDATE_ID:
                foreach ($array as $value) {
                    $new_array[] = new $type($value, true);
                }
                break;
            default: throw new InternalException();
        }
    }
    return $new_array;
}

function validateDate($date, $format = 'Y-m-d') {
    $newDate = false;
    $d = DateTime::createFromFormat($format, $date);
    if ($d && $d->format($format) == $date) {
        $newDate = $date;
    }
    return $newDate;
}

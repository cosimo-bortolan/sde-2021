<?php namespace Sagra\Exceptions;

use Sagra\Exceptions\MyException;
use Sagra\Exceptions\InternalException;

define("NOT_JSON", 0);
define("BAD_SUPPLEMENT", 1);
define("QUANTITY_EXCEEDED", 2);
define("RESERVATION_EXCEEDED", 3);
define("OMAGGIO_EXCEEDED", 4);
define("BAD_PASSWORD", 5);
define("NOT_FOUND", 6);
define("BAD_REQUEST", 7);
define("BAD_ID", 8);
define("BAD_METHOD", 9);
define("PRINTER_KO", 10);
define("PAYMENT_KO", 11);

class InputException extends MyException {

  protected function setValues() {
    $this->httpErrorCode = 400;
    switch ($this->getCode()) {
      case NOT_JSON:
      $this->setBody(ERR_NOT_JSON, "Data are not is json format.");
      break;
      case BAD_SUPPLEMENT:
      $this->setBody(ERR_BAD_SUPPLEMENT, "Bad supplement for the pietanza.", $this->details);
      break;
      case QUANTITY_EXCEEDED:
      $this->setBody(ERR_QUANTITY_EXCEEDED, "Quantity exceeded for the pietanza.", $this->details);
      break;
      case RESERVATION_EXCEEDED:
      $this->setBody(ERR_RESERVATION_EXCEEDED, "Reservations exceeded for the pietanza.", $this->details);
      break;
      case OMAGGIO_EXCEEDED:
      $this->setBody(ERR_GIFT_EXCEEDED, "The value of the gift cannot exceed the total of the order.");
      break;
      case BAD_PASSWORD:
      $this->setBody(ERR_BAD_PASSWORD, "The password is not correct.");
      break;
      case NOT_FOUND:
      $this->setBody(ERR_NOT_FOUND, "Unable to retrive the resource. No resource found.", $this->details);
      $this->httpErrorCode = 404;
      break;
      case BAD_REQUEST:
      $this->setBody(ERR_BAD_REQUEST, "The requested url doesn't represent any resource. Please select a resource from those below.", $this->details);
      break;
      case BAD_ID:
      $this->setBody(ERR_BAD_ID, "This request must include a positive integer id in the format /" . $this->details . "/:id");
      break;
      case BAD_METHOD:
      $this->setBody(ERR_BAD_METHOD, "The method is not supported for this resource. Please select a method from those below.", $this->details);
      break;
      case PRINTER_KO:
      $this->setBody(ERR_PRINTER_KO, "The order has been inserted correctly but not all scontrini has been printed. See details for more informations.", $this->details);
      $this->httpErrorCode = 500;
      break;
      case PAYMENT_KO:
      $this->setBody(ERR_PAYMENT_KO, "The order has been inserted correctly but the payment has not been accepted. See details for more informations.", $this->details);
      $this->httpErrorCode = 500;
      break;
      default: throw new InternalException($this);
    }
  }
}

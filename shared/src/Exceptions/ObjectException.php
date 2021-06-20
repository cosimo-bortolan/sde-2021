<?php namespace Sagra\Exceptions;

use Sagra\Exceptions\MyException;
use Sagra\Exceptions\InternalException;

define("REQUIRED_VAR", 0);
define("BAD_VALUE", 1);
define("OBJ_CONSTRAINT", 2);

class ObjectException extends MyException {

  protected function setValues() {
    $this->httpErrorCode = 400;
    switch ($this->getCode()) {
      case REQUIRED_VAR:
      $this->setBody(ERR_REQUIRED_VAR, "A field is required.", $this->details);
      break;
      case BAD_VALUE:
      $this->setBody(ERR_BAD_VALUE, "Bad value for a field", $this->details);
      break;
      case OBJ_CONSTRAINT:
      $this->setBody(ERR_OBJECT_CONSTRAINT, "A constraint is not respected for this resource", $this->details);
      break;
      default: throw new InternalException($this);
    }
  }
}

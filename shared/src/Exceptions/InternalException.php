<?php namespace Sagra\Exceptions;

class InternalException extends \Exception {

  public function __construct(Exception $previous = null) {
    parent::__construct(null, null, $previous);
  }

}

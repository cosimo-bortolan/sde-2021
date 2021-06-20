<?php namespace Sagra\Exceptions;

use Sagra\Exceptions\MyException;
use Sagra\Exceptions\InternalException;

define("NOT_AUTHENTICATED", 0);
define("NOT_ALLOWED", 1);
define("NOT_EXPECTED", 2);

class LoginException extends MyException {

  protected function setValues() {
    $endpoints = json_decode(file_get_contents(__DIR__ . "/../../config/api_endpoints.json"));
    switch ($this->getCode()) {
      case NOT_AUTHENTICATED:
      $this->setBody(ERR_NOT_AUTHENTICATED, "You are not allowed to acces this resource without authentication. Submit a PUT request to ".$endpoints->auth_api."login/{id} to authenticate.");
      $this->httpErrorCode = 401;
      break;
      case NOT_ALLOWED:
      $this->setBody(ERR_NOT_ALLOWED, "You are not allowed to use this method on this resource.");
      $this->httpErrorCode = 403;
      break;
      case NOT_EXPECTED:
      $this->setBody(ERR_NOT_EXPECTED, "You are not expected to login. No active serata at the moment.");
      $this->httpErrorCode = 403;
      break;
      default: throw new InternalException($this);
    }
  }
}

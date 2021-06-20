<?php namespace Sagra\Exceptions;

class MyException extends \Exception{

  protected $details;
  protected $body;
  protected $httpErrorCode;

  public function __construct($code, $details = null, $message = "", Exception $previous = null) {
    parent::__construct($message, $code, $previous);
    $this->details = $details;
    $this->setValues();
  }

  public function getBody() {
      return $this->body;
  }

  public function getHttpErrorCode(){
    return $this->httpErrorCode;
  }

  public function getDetails(){
    return $this->details;
  }

  protected function setValues(){}

  protected function setBody($code, $message, $resource = null){
    if ($resource == null) {
      $this->body = ["error" => $code, "message" => $message];
    } else {
      $this->body = ["error" => $code, "message" => $message, "details" => $resource];
    }
  }

}

<?php namespace Sagra\Data\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Sagra\Exceptions\InputException;
use Sagra\Exceptions\ObjectException;

class SerateController {

  public function getAll(Request $request, Response $response, $args){
    $attiva = $request->getQueryParams()['attiva'];

    if(is_null($attiva)){
      $result = $request->getAttribute('DAO')->getAll();
    } else if ($attiva == 1) {
      $result = $request->getAttribute('DAO')->getAttiva();
      $request = $request->withAttribute('plural_name', 'serata');
    } else {
      throw new InputException(BAD_REQUEST, ["/serate", "/serate?attiva=1"]);
    }
    $response->getBody()->write(json_encode([$request->getAttribute('plural_name') => $result]));
    return $response;
  }

}

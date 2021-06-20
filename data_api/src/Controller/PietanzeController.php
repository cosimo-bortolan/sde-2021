<?php namespace Sagra\Data\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Sagra\Exceptions\InputException;

class PietanzeController {

  public function getByCassa(Request $request, Response $response, $args){
    $id = $request->getAttribute('id');
    $result = $request->getAttribute('DAO')->getByCassa($id);
    $response->getBody()->write(json_encode([$request->getAttribute('plural_name') => $result]));
    return $response;
  }

}

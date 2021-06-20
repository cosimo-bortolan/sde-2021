<?php namespace Sagra\Data\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class PrenotazioniController {

  public function getByPietanza(Request $request, Response $response, $args){
    $id = $request->getAttribute('id');
    $result = $request->getAttribute('DAO')->getByPietanza($id);
    $response->getBody()->write(json_encode([$request->getAttribute('plural_name') => $result]));
    return $response;
  }

  public function insert(Request $request, Response $response, $args){
    $data = jsonDecode($request->getBody());
    $data->pietanza = $request->getAttribute('id');
    $result = $request->getAttribute('DAO')->insert($data);
    $response->getBody()->write(json_encode([$request->getAttribute('name') => $result]));
    return $response;
  }

  public function deleteByPietanza(Request $request, Response $response, $args){
    $id = $request->getAttribute('id');
    $result = $request->getAttribute('DAO')->deleteByPietanza($id);
    $response = $response->withStatus(204);
    return $response;
  }

  public function deleteByCassa(Request $request, Response $response, $args){
    $id = $request->getAttribute('id');
    $result = $request->getAttribute('DAO')->deleteByCassa($id);
    $response = $response->withStatus(204);
    return $response;
  }

}

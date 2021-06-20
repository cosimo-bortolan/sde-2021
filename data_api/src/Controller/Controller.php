<?php namespace Sagra\Data\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class Controller {

  public function getAll(Request $request, Response $response, $args){
    $result = $request->getAttribute('DAO')->getAll();
    $response->getBody()->write(json_encode([$request->getAttribute('plural_name') => $result]));
    return $response;
  }

  public function insert(Request $request, Response $response, $args){
    $data = jsonDecode($request->getBody());
    $result = $request->getAttribute('DAO')->insert($data);
    $response->getBody()->write(json_encode([$request->getAttribute('name') => $result]));
    return $response;
  }

  public function get(Request $request, Response $response, $args){
    $id = $request->getAttribute('id');
    $result = $request->getAttribute('DAO')->get($id);
    $response->getBody()->write(json_encode([$request->getAttribute('name') => $result]));
    return $response;
  }

  public function update(Request $request, Response $response, $args){
    $resource = $request->getAttribute('resource');
    $data = jsonDecode($request->getBody());
    $result = $request->getAttribute('DAO')->update($resource, $data);
    $response->getBody()->write(json_encode([$request->getAttribute('name') => $result]));
    return $response;
  }

  public function delete(Request $request, Response $response, $args){
    $id = $request->getAttribute('id');
    $result = $request->getAttribute('DAO')->delete($id);
    $response->getBody()->write(json_encode([$request->getAttribute('name') => $result]));
    return $response;
  }

}

<?php namespace Sagra\Data\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Sagra\Exceptions\InputException;
use Sagra\Exceptions\ObjectException;

class CasseController {

  public function getAll(Request $request, Response $response, $args){
    $permessi = $request->getQueryParams()['permessi'];
    $cookie = $request->getQueryParams()['cookie'];

    if(is_null($permessi) && is_null($cookie)){
      $result = $request->getAttribute('DAO')->getAll();
    } else if (is_null($permessi) && !is_null($cookie)) {
      $result = $request->getAttribute('DAO')->getByCookie($cookie);
      $request = $request->withAttribute('plural_name', 'cassa');
    } else if (!is_null($permessi) && is_null($cookie)) {
      $result = $request->getAttribute('DAO')->getByPermessi($permessi);
    } else {
      throw new InputException(BAD_REQUEST, ["/casse", "/casse?cookie={cookie}", "/casse?permessi={permessi}"]);
    }
    $response->getBody()->write(json_encode([$request->getAttribute('plural_name') => $result]));
    return $response;
  }

  public function get(Request $request, Response $response, $args){
    $password = $request->getQueryParams()['password'];
    if(is_null($password) || $password != 1){
      $result = $request->getAttribute('resource');
    }else{
      $result = $request->getAttribute('DAO')->getPassword($request->getAttribute('id'));
    }
    $response->getBody()->write(json_encode([$request->getAttribute('name') => $result]));
    return $response;
  }

  public function update(Request $request, Response $response, $args){
    $resource = $request->getAttribute('resource');
    $data = jsonDecode($request->getBody());
    if ($data->password !== null) {
      $result = $request->getAttribute('DAO')->update($resource, $data);
    } else {
      $result = $request->getAttribute('DAO')->updateNoPassword($resource, $data);
    }
    $response->getBody()->write(json_encode([$request->getAttribute('name') => $result]));
    return $response;
  }

}

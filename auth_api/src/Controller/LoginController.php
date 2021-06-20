<?php namespace Sagra\Auth\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Sagra\Exceptions\InputException;
use Sagra\Exceptions\LoginException;
use GuzzleHttp\Exception\ClientException;

class LoginController {

  private $data_client;

  public function __construct(ContainerInterface $container)  {
    $this->data_client = $container->get('data_client');
  }

  public function getAll(Request $request, Response $response, $args){
    if(isset($_COOKIE['user'])){
      $cookie = $_COOKIE['user'];
    }else{
      $cookie = $request->getQueryParams()['cookie'];
    }
    $permessi = $request->getQueryParams()['permessi'];
    try{
      $response = $this->data_client->get('/casse?cookie='.$cookie);
      $cassa = json_decode($response->getBody())->cassa;
      if (!is_null($permessi) && $permessi != $cassa->permessi) {
        unset($_COOKIE['user']);
        setcookie('user', '', time() - 3600, '/');
        throw new InputException(NOT_FOUND);
      }
    }catch(ClientException $e) {
      $response = $e->getResponse();
    }
    return $response;
  }

  public function put(Request $request, Response $response, $args){
    $id = $args['id'];
    if (!ctype_digit($id)) {
      throw new InputException(BAD_ID, 'login');
    }
    $data = jsonDecode($request->getBody());
    try{
      $response = $this->data_client->get('/casse/'.$id.'?password=1');
      $cassa = json_decode($response->getBody())->cassa;
      try{
        $serata_response = $this->data_client->get('/serate?attiva=1');
      }catch(ClientException $e) {
        if($cassa->permessi != ADMIN){
          throw new LoginException(NOT_EXPECTED);
        }
      }
      $shaPassword = hash("sha256", $data->password);
      if ($shaPassword != $cassa->password) {
        throw new InputException(BAD_PASSWORD);
      }
      $cookie_value = sha1(rand());
      $cookie_date = date('Y-m-d H:i:s');
      $response = $this->data_client->patch('/casse/'.$cassa->id, ['body' => json_encode(['cookie' => $cookie_value, 'cookie_date' => $cookie_date])]);
      setcookie("user", $cookie_value, time() + (EXPIRATION_TIME * 3600), "/");
    }catch(ClientException $e) {
      $response = $e->getResponse();
    }
    return $response;
  }

  public function delete(Request $request, Response $response, $args){
    $id = $args['id'];
    if (!ctype_digit($id)) {
      throw new InputException(BAD_ID, 'login');
    }
    $cassa = $request->getAttribute('cassa');
    if($cassa->id != $id){
      throw new LoginException(NOT_ALLOWED);
    }
    try{
      $response = $this->data_client->patch('/casse/'.$id, ['body' => json_encode(['cookie' => NULL, 'cookie_date' => NULL])]);
      setcookie('user', '', time() - 3600, '/');
      $response = new \Slim\Psr7\Response();
      $response = $response->withStatus(204);
    }catch(ClientException $e) {
      $response = $e->getResponse();
    }
    return $response;
  }

}

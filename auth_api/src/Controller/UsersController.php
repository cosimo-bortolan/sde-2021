<?php namespace Sagra\Auth\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use GuzzleHttp\Exception\ClientException;

class UsersController {

  private $data_client;

  public function __construct(ContainerInterface $container)  {
    $this->data_client = $container->get('data_client');
  }

  public function getAll(Request $request, Response $response, $args){
    $permessi = $request->getQueryParams()['permessi'];
    try{
      if(is_null($permessi)){
        $result = $this->data_client->get('/casse');
      } else {
        $result = $this->data_client->get('/casse?permessi='.$permessi);
      }
      $users = json_decode($result->getBody())->casse;
      foreach ($users as $user) {
        unset($user->cookie);
        unset($user->cookie_date);
        unset($user->notifiche);
        unset($user->stampante);
      }
      $response = new \Slim\Psr7\Response();
      $response->getBody()->write(json_encode(['users' => $users]));
    }catch(ClientException $e) {
      $response = $e->getResponse();
    }
    return $response;
  }

}

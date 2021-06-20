<?php namespace Sagra\Middleware;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Sagra\Exceptions\LoginException;
use GuzzleHttp\Exception\ClientException;

class UserMiddleware {

  private $auth_client;

  public function __construct(ContainerInterface $container){
    $this->auth_client = $container->get('auth_client');
  }

  public function __invoke(Request $request, RequestHandler $handler): Response {
    if(isset($_COOKIE['user'])){
      $cookie = $_COOKIE['user'];
    }else{
      throw new LoginException(NOT_AUTHENTICATED);
    }
    try{
      $cassa_response = $this->auth_client->get('login?cookie='.$cookie);
      $cassa = json_decode($cassa_response->getBody())->cassa;
      $request = $request->withAttribute('cassa', $cassa);
    }catch(ClientException $e) {
      throw new LoginException(NOT_AUTHENTICATED);
    }
    $response = $handler->handle($request);
    return $response;
  }
}

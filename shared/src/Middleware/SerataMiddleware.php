<?php namespace Sagra\Middleware;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Sagra\Exceptions\LoginException;
use GuzzleHttp\Exception\ClientException;

class SerataMiddleware {

  private $data_client;

  public function __construct(ContainerInterface $container){
    $this->data_client = $container->get('data_client');
  }

  public function __invoke(Request $request, RequestHandler $handler): Response {
    $cassa = $request->getAttribute('cassa');
    try{
      $serata_response = $this->data_client->get('/serate?attiva=1');
      $serata = json_decode($serata_response->getBody())->serata;
      $request = $request->withAttribute('serata', $serata);
    }catch(ClientException $e) {
      if($cassa->permessi != ADMIN){
        throw new LoginException(NOT_EXPECTED);
      }
    }
    $response = $handler->handle($request);
    return $response;
  }
}

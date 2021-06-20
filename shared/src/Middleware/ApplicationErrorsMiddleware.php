<?php namespace Sagra\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Sagra\Exceptions\InputException;
use Sagra\Exceptions\ObjectException;
use Sagra\Exceptions\LoginException;

class ApplicationErrorsMiddleware {

  public function __invoke(Request $request, RequestHandler $handler): Response {
    try{
      $response = $handler->handle($request);
    }catch(InputException | ObjectException | LoginException $e){
      $response = new \Slim\Psr7\Response();
      $response->getBody()->write(json_encode($e->getBody()));
      $response = $response->withStatus($e->getHttpErrorCode());
    }
    return $response;
  }
}

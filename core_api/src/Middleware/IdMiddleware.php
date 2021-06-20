<?php namespace Sagra\Core\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Sagra\Exceptions\InputException;

class IdMiddleware {

  public function __invoke(Request $request, RequestHandler $handler): Response {
    $args = \Slim\Routing\RouteContext::fromRequest($request)->getRoute()->getArguments();
    $id = $args['id'];
    if (!ctype_digit($id)) {
      $path = $request->getUri()->getPath();
      $name = explode("/", $path, 3)[1];
      throw new InputException(BAD_ID, $name);
    }
    return $handler->handle($request);
  }
}

<?php namespace Sagra\Data\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Sagra\Exceptions\InputException;

class IdMiddleware {

  public function __invoke(Request $request, RequestHandler $handler): Response {
    $args = \Slim\Routing\RouteContext::fromRequest($request)->getRoute()->getArguments();
    $id = $args['id'];
    if (!ctype_digit($id)) {
      throw new InputException(BAD_ID, $request->getAttribute('name'));
    }
    $resource = $request->getAttribute('DAO')->get($id);
    $request = $request->withAttribute('resource', $resource);
    return $handler->handle($request);
  }
}

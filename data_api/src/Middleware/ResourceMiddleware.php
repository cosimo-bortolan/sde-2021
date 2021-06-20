<?php namespace Sagra\Data\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class ResourceMiddleware {

  private $db;
  private $level;

  public function __construct($db, $level){
    $this->db = $db;
    $this->level = $level;
  }

  public function __invoke(Request $request, RequestHandler $handler): Response {
    global $resourceMapping;

    $path = $request->getUri()->getPath();
    $resources = explode("/", $path, 6);

    switch($this->level){
      case 1: $resource = $resources[1];
      break;
      case 2: $resource = $resources[3];
      break;
    }

    $className = "Sagra\\Data\\DAO\\".$resourceMapping[$resource]['DAO'];
    $request = $request->withAttribute('name', $resourceMapping[$resource]['name']);
    $request = $request->withAttribute('plural_name', $resource);
    $request = $request->withAttribute('DAO', new $className($this->db));
    $response = $handler->handle($request);
    return $response;
  }
}

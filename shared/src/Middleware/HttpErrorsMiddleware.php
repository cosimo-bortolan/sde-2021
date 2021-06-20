<?php namespace Sagra\Middleware;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Sagra\Exceptions\InputException;

class HttpErrorsMiddleware {

  private $routes;

  public function __construct(ContainerInterface $container){
    $this->routes = $container->get('app')->getRouteCollector()->getRoutes();
  }

  public function __invoke(Request $request, RequestHandler $handler): Response {
    global $resourceMapping;
    $routes = $this->routes;
    $path = $request->getUri()->getPath();
    $resources = explode("/", $path, 6);
    try{
      $response = $handler->handle($request);
    }catch(\Slim\Exception\HttpNotFoundException $e){
      $allowedResources = [];
      //check if some of the resources in the url matches with some route
      foreach ($routes as $routeKey => $routeValue) {
        foreach ($resources as $resourceKey => $resourceValue){
          if(array_key_exists($resourceValue, $resourceMapping) &&
          str_contains($routeValue->getPattern(), $resourceValue) &&
          !in_array($routeValue->getPattern(),$allowedResources)){
            array_push($allowedResources, $routeValue->getPattern());
          }
        }
      }
      //otherwise, return the base resources
      if(empty($allowedResources)){
        foreach ($routes as $key => $value) {
          if(substr_count($value->getPattern(), "/") == 1 &&
          !in_array($value->getPattern(),$allowedResources)){
            array_push($allowedResources, $value->getPattern());
          }
        }
      }
      throw new InputException(BAD_REQUEST, $allowedResources);
    }catch(\Slim\Exception\HttpMethodNotAllowedException $e){
      throw new InputException(BAD_METHOD, $e->getAllowedMethods());
    }
    return $response;
  }
}

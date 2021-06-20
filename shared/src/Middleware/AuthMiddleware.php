<?php namespace Sagra\Middleware;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Sagra\Exceptions\InputException;
use Sagra\Exceptions\LoginException;
use GuzzleHttp\Exception\ClientException;

class AuthMiddleware {

  private $data_client;
  private $base_path;
  private $reqired;
  private $requiredForId;
  private $id;

  public function __construct(ContainerInterface $container, $required, $requiredForId = null, $id = null){
    $this->data_client = $container->get('data_client');
    $this->base_path = $container->get('app')->getBasePath();
    $this->required = $required;
    $this->requiredForId = $requiredForId;
    $this->id = $id;
  }

  public function __invoke(Request $request, RequestHandler $handler): Response {
    $user = $request->getAttribute('cassa');
    if (($this->required & $user->permessi) != $this->required) {
      if(!is_null($this->requiredForId)){
        $path = substr($request->getUri()->getPath(), strlen($this->base_path));
        $path = explode("/", $path, 4);
        $method = $request->getMethod();
        try{
          $response = $this->data_client->get('/'.$path[1].'/'.$path[2]);
          $resource_name = $this->id[0];
          $property_name = $this->id[1];
          $id = json_decode($response->getBody())->$resource_name->$property_name;
          if ($id != $user->id) {
            throw new LoginException(NOT_ALLOWED);
          }
          if (($requiredForId & $user->permessi) != $requiredForId) {
            throw new LoginException(NOT_ALLOWED);
          }
        }catch(ClientException $e) {
          $response = $e->getResponse();
          return $response;
        }
      }else{
        throw new LoginException(NOT_ALLOWED);
      }
    }
    $response = $handler->handle($request);
    return $response;
  }
}

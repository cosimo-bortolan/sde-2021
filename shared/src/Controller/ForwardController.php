<?php namespace Sagra\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Sagra\Exceptions\InputException;
use GuzzleHttp\Exception\ClientException;

class ForwardController {

  private $data_client;
  private $base_path;

  public function __construct(ContainerInterface $container)  {
    $this->data_client = $container->get('data_client');
    $this->base_path = $container->get('app')->getBasePath();
  }

  public function forward(Request $request, Response $response, $args){
    $path = substr($request->getUri()->getPath(), strlen($this->base_path));
    $method = $request->getMethod();
    try{
      $response = $this->data_client->request($method, $path, ['body' => $request->getBody()]);
    }catch(ClientException $e) {
      $response = $e->getResponse();
    }
    return $response;
  }
}

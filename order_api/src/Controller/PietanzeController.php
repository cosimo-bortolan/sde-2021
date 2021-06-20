<?php namespace Sagra\Order\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Sagra\Exceptions\InputException;
use Sagra\Exceptions\LoginException;
use Sagra\Entities\Categoria;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;

class PietanzeController {

  private $data_client;

  public function __construct(ContainerInterface $container)  {
    $this->data_client = $container->get('data_client');
  }

  public function getByCassa(Request $request, Response $response, $args){
    $cassa = $request->getAttribute('cassa');
    try{
      $response = $this->data_client->request('get', '/casse/'.$cassa->id.'/pietanze', ['body' => $request->getBody()]);
    }catch(ClientException $e) {
      $response = $e->getResponse();
    }
    return $response;
  }

}

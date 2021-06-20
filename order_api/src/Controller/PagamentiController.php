<?php namespace Sagra\Order\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Sagra\Exceptions\InputException;
use Sagra\Exceptions\LoginException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;

class PagamentiController {

  private $payment_client;

  public function __construct(ContainerInterface $container)  {
    $this->payment_client = $container->get('payment_client');
  }

  public function getPending(Request $request, Response $response, $args){
    try{
      $pagamenti_response = $this->payment_client->get('/satispay/payments?status=PENDING');
      $pagamenti = json_decode($pagamenti_response->getBody())->payments;
      $response = new \Slim\Psr7\Response();
      $response->getBody()->write(json_encode(['pagamenti' => $pagamenti]));
    }catch(ClientException $e) {
      $response = $e->getResponse();
    }
    return $response;
  }

}

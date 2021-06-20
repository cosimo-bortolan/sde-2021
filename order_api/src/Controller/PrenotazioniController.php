<?php namespace Sagra\Order\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Sagra\Exceptions\InputException;
use Sagra\Exceptions\LoginException;
use Sagra\Entities\Categoria;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;

class PrenotazioniController {

  private $data_client;

  public function __construct(ContainerInterface $container)  {
    $this->data_client = $container->get('data_client');
  }

  public function insert(Request $request, Response $response, $args){
    $id = $args['id'];
    if (!ctype_digit($id)) {
      throw new InputException(BAD_ID, 'login');
    }
    $cassa = $request->getAttribute('cassa');
    $data = jsonDecode($request->getBody());
    $data->cassa = $cassa->id;
    try{
      $response = $this->data_client->post('/pietanze/'.$id.'/prenotazioni', ['body' => json_encode($data)]);
    }catch(ClientException $e) {
      $response = $e->getResponse();
    }
    return $response;
  }

  public function deleteByCassa(Request $request, Response $response, $args){
    $cassa = $request->getAttribute('cassa');
    try{
      $response = $this->data_client->delete('/casse/'.$cassa->id.'/prenotazioni');
    }catch(ClientException $e) {
      $response = $e->getResponse();
    }
    return $response;
  }

}

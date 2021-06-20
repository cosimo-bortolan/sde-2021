<?php namespace Sagra\Order\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Sagra\Exceptions\InputException;
use Sagra\Exceptions\LoginException;
use Sagra\Entities\Categoria;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;

class OrdiniController {

  private $data_client;
  private $print_client;
  private $payment_client;

  public function __construct(ContainerInterface $container)  {
    $this->data_client = $container->get('data_client');
    $this->print_client = $container->get('print_client');
    $this->payment_client = $container->get('payment_client');
  }

  public function insert(Request $request, Response $response, $args){
    $cassa = $request->getAttribute('cassa');
    $serata = $request->getAttribute('serata');
    $data = jsonDecode($request->getBody());
    $data->cassa = $cassa->id;
    $data->serata = $serata->id;
    try{
      $ordine_response = $this->data_client->post('/ordini', ['body' => json_encode($data)]);
      $ordine = json_decode($ordine_response->getBody())->ordine;
      if(!is_null($data->pagamento)){
        try{
          $pagamento_response = $this->payment_client->patch('/satispay/payments/'.$data->pagamento->id, ['body' => json_encode(['action' => 'ACCEPT'])]);
        }catch(ClientException | ServerException $e){
          throw new InputException(PAYMENT_KO, [
            "ordine" => $ordine
          ]);
        }
      }
      $this->printOrder($ordine, $cassa);
      $response = $ordine_response;
    }catch(ClientException $e) {
      $response = $e->getResponse();
    }catch(ServerException $e) {
      $response = $e->getResponse();
    }
    return $response;
  }

  private function printOrder($ordine, $cassa){
    $categorie = $this->getCategorie($ordine);
    if ($cassa->stampante != null) {
      print_log("Stampante " . $cassa->stampante . " stampa ricevuta");
    }
    $stampanti_response = $this->data_client->get('/stampanti');
    $stampanti = json_decode($stampanti_response->getBody())->stampanti;
    $ordine->pietanze = $this->groupPietanze($ordine->pietanze);
    //preload all scontrini
    foreach ($stampanti as $stampante) {
      foreach ($stampante->scontrini as $key => $scontrino) {
        $scontrino_response = $this->data_client->get('/scontrini/'.$scontrino->id);
        $stampante->scontrini[$key] = json_decode($scontrino_response->getBody())->scontrino;
        $scontrini[] = $stampante->scontrini[$key]->nome;
      }
    }
    //print all scontrini
    $scontrini_ok = [];
    foreach ($stampanti as $stampante) {
      foreach ($stampante->scontrini as $scontrino) {
        foreach($scontrino->categorie as $categoria){
          $scontrino_categorie[] = $categoria->id;
        }
        if (!empty(array_intersect($scontrino_categorie, $categorie))) {
          try{
            $print_response = $this->print_client->post('/printers/'.$stampante->ip, ['body' => json_encode(['scontrino' => $scontrino, 'ordine' => $ordine])]);
          }catch(ServerException $e){
            throw new InputException(PRINTER_KO, [
              "ordine" => $ordine,
              "printed" => $scontrini_ok,
              "not_printed" => array_diff($scontrini, $scontrini_ok)
            ]);
          }
          $scontrini_ok[] = $scontino->nome;
        }
      }
    }
  }

  private function getCategorie($ordine) {
    $categorie = [];
    foreach ($ordine->pietanze as $pietanza) {
      if (!in_array($pietanza->categoria, $categorie)) {
        $categorie[] = $pietanza->categoria;
      }
    }
    return $categorie;
  }

  private function groupPietanze($pietanze) {
    $group = [];
    foreach ($pietanze as $pietanza) {
      sort($pietanza->aggiunte);
      $found = false;
      foreach ($group as $element) {
        if ($element->id == $pietanza->id &&
        $element->aggiunte == $pietanza->aggiunte) {
          $found = true;
          $element->quantita_ordinata++;
        }
      }
      if (!$found) {
        $pietanza->quantita_ordinata = 1;
        $group[] = $pietanza;
      }
    }
    return $group;
  }

}

<?php namespace Sagra\Printer\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use GuzzleHttp\Exception\ClientException;
use Sagra\Entities\Scontrino;
use Sagra\Entities\Ordine;
use Mike42\Escpos\Printer;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Sagra\Exceptions\ObjectException;

class PrintersController {

  public function print(Request $request, Response $response, $args){
    $ip = $args['ip'];
    $data = jsonDecode($request->getBody());
    $status = 200;

    if(is_null($data->scontrino)){
      throw new ObjectException(REQUIRED_VAR, ["field" => "scontrino"]);
    }else{
      try{
        $scontrino = new Scontrino($data->scontrino);
      }catch(ObjectException $e){
        throw new ObjectException(REQUIRED_VAR, ["field" => "scontrino->".$e->getDetails()["field"]]);
      }
    }
    if(is_null($data->ordine)){
      throw new ObjectException(REQUIRED_VAR, ["field" => "ordine"]);
    }else{
      try{
        $ordine = new Ordine($data->ordine);
      }catch(ObjectException $e){
        throw new ObjectException(REQUIRED_VAR, ["field" => "ordine->".$e->getDetails()["field"]]);
      }
    }

    try{
      $this->stampa($ip, $scontrino, $ordine);
    }catch(\Exception $e){
      $status = 500;
    }
    return $response
    ->withHeader('Content-Type', 'application/json')
    ->withStatus($status);
  }

  private function stampa($stampante, $scontrino, $ordine) {
    print_log("ciao".$stampante);
    $connector = new NetworkPrintConnector($stampante, 9100, 5);
    $printer = new Printer($connector);

    $printer->setJustification(Printer::JUSTIFY_CENTER);
    if ($scontrino->immagine != "") {
      $logo = EscposImage::load(__DIR__ . "\\" . $scontrino->immagine);
      $printer->bitImage($logo);
    }
    if ($scontrino->intestazione != "") {
      $printer->setTextSize(1, 1);
      $printer->text(substr($scontrino->intestazione, 0, 24));
      $printer->text("\n\n");
    }
    $printer->setJustification(Printer::JUSTIFY_LEFT);
    if ($scontrino->dimensione == 1) {
      $d1 = 2;
      $d2 = 3;
    } else {
      $d1 = 1;
      $d2 = 2;
    }
    $printer->setTextSize($d1, $d1);
    $printer->text("Ordine n.");
    $printer->setTextSize($d2, $d1);
    $printer->text($ordine->numero_ordine . "\n");
    $printer->setTextSize($d1, $d1);
    if ($scontrino->tavolo == 1) {
      if ($ordine->tavolo != 0) {
        $printer->text("Tavolo n.");
        $printer->setTextSize($d2, $d1);
        $printer->text($ordine->tavolo . "\n");
        $printer->setTextSize($d1, $d1);
        $printer->text("Coperti n.");
        $printer->text($ordine->coperti . "\n\n");
      } else {
        $printer->setTextSize($d2, $d1);
        $printer->text("ASPORTO: ");
        $printer->setTextSize($d1, $d1);
        $printer->text($ordine->nome . "\n\n");
      }
      $printer->setTextSize(2, 2);
    }

    /* Information for the receipt */
    $items = [];
    foreach ($ordine->pietanze as $pietanza) {
      if (in_array($pietanza->categoria, $scontrino->categorie)) {
        $items[] = new Item($pietanza->nome, $scontrino->dimensione, $pietanza->quantita_ordinata, $pietanza->quantita_ordinata * $pietanza->prezzo);
        foreach ($pietanza->aggiunte as $aggiunta) {
          $items[] = new Item($aggiunta->nome, $scontrino->dimensione);
        }
      } else if ($scontrino->ricevuta == 1) {
        $items[] = new Item($pietanza->nome, 0, $pietanza->quantita_ordinata, $pietanza->quantita_ordinata * $pietanza->prezzo);
        foreach ($pietanza->aggiunte as $aggiunta) {
          $items[] = new Item($aggiunta->nome, 0);
        }
      }
    }
    if ($scontrino->totale == 1) {
      $total = new item('TOTALE', $scontrino->dimensione, ' ', $ordine->totale);
    }

    foreach ($items as $item) {
      if ($item->isSmall()) {
        $printer->setTextSize(1, 1);
      }
      $printer->text($item);
      $printer->setTextSize($d1, $d1);
    }
    $printer->setTextSize(1, 1);
    $printer->text("\n");
    $printer->setTextSize($d1, $d1);
    $printer->text($total);

    /* Footer */
    $printer->text("\n");
    $printer->setJustification(Printer::JUSTIFY_CENTER);
    if ($scontrino->barcode == 1) {
      $printer->setBarcodeHeight(80);
      $printer->setBarcodeWidth(4);
      $printer->barcode(str_pad($ordine->id, 7, 0, STR_PAD_LEFT), Printer::BARCODE_JAN8);
    }
    if ($scontrino->dataora == 1) {
      $printer->setTextSize(1, 1);
      $printer->text("\n" . $ordine->cassa . "  " . $ordine->data_inserimento . "\n\n");
    }
    $printer->cut();
    $printer->close();
  }

}

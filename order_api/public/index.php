<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;
use Slim\Factory\AppFactory;
use GuzzleHttp\Client;
use Sagra\Exceptions\InputException;
use Sagra\Order\Controller\OrdiniController;
use Sagra\Order\Controller\PrenotazioniController;
use Sagra\Order\Controller\PietanzeController;
use Sagra\Order\Controller\PagamentiController;
use Sagra\Order\Middleware\IdMiddleware;
use Sagra\Controller\ForwardController;
use Sagra\Middleware\AuthMiddleware;
use Sagra\Middleware\SerataMiddleware;
use Sagra\Middleware\UserMiddleware;
use Sagra\Middleware\ApplicationErrorsMiddleware;
use Sagra\Middleware\HttpErrorsMiddleware;
use Sagra\Middleware\JsonMiddleware;

require __DIR__ . '/../vendor/autoload.php';

$resourceMapping = [
  "ordini" => ["name" => 'ordine'],
  "pietanze" => ["name" => 'pietanze'],
  "prenotazioni" => ["name" => 'prenotazione'],
  "pagamenti" => ["name" => 'pagamento']
];

$endpoints = json_decode(file_get_contents("../../shared/config/api_endpoints.json"));

$container = new DI\Container();
AppFactory::setContainer($container);
$app = AppFactory::create();
$app->setBasePath('/order_api');

$container->set('data_client', new Client([
  'base_uri' => $endpoints->data_api,
  'timeout'  => 2.0,
]));
$container->set('auth_client', new Client([
  'base_uri' => $endpoints->auth_api,
  'timeout'  => 2.0,
]));
$container->set('print_client', new Client([
  'base_uri' => $endpoints->printer_api,
  'timeout'  => 8.0,
]));
$container->set('payment_client', new Client([
  'base_uri' => $endpoints->payment_api,
  'timeout'  => 8.0,
]));
$container->set('app', $app);

$app->group('/ordini', function (RouteCollectorProxy $app){
  $app->post('', OrdiniController::class . ':insert');
})->add(new AuthMiddleware($container, CASSA));

$app->group('/pietanze', function (RouteCollectorProxy $app) use ($container){
  $app->get('', PietanzeController::class . ':getByCassa');
  $app->group('/{id}', function (RouteCollectorProxy $app){
    $app->group('/prenotazioni', function (RouteCollectorProxy $app){
      $app->post('', PrenotazioniController::class . ':insert');
    });
  })->add(new IdMiddleware());
})->add(new AuthMiddleware($container, CASSA));

$app->group('/prenotazioni', function (RouteCollectorProxy $app) use ($container){
  $app->delete('', PrenotazioniController::class . ':deleteByCassa');
  $app->group('/{id}', function (RouteCollectorProxy $app){
    $app->patch('', ForwardController::class . ':forward');
    $app->delete('', ForwardController::class . ':forward');
  })->add(new AuthMiddleware($container, BLOCK, CASSA, ['prenotazione','cassa']))
  ->add(new IdMiddleware());
})->add(new AuthMiddleware($container, CASSA));

$app->group('/pagamenti', function (RouteCollectorProxy $app) use ($container){
  $app->get('', PagamentiController::class . ':getPending');
})->add(new AuthMiddleware($container, CASSA));

$app->add(SerataMiddleware::class);
$app->add(UserMiddleware::class);
$app->add(HttpErrorsMiddleware::class);
$app->add(ApplicationErrorsMiddleware::class);
$app->add(JsonMiddleware::class);

$app->run();

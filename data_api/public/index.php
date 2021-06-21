<?php

use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;
use Slim\Factory\AppFactory;
use Sagra\Data\Database;
use Sagra\Data\Controller\Controller;
use Sagra\Data\Controller\CasseController;
use Sagra\Data\Controller\PietanzeController;
use Sagra\Data\Controller\PrenotazioniController;
use Sagra\Data\Controller\OrdiniController;
use Sagra\Data\Controller\SerateController;
use Sagra\Data\Middleware\ResourceMiddleware;
use Sagra\Data\Middleware\IdMiddleware;
use Sagra\Data\Middleware\DatabaseErrorsMiddleware;
use Sagra\Middleware\HttpErrorsMiddleware;
use Sagra\Middleware\ApplicationErrorsMiddleware;
use Sagra\Middleware\JsonMiddleware;
use Sagra\Exceptions\InputException;
use Sagra\Exceptions\ObjectException;

require __DIR__ . '/../vendor/autoload.php';

$resourceMapping = [
  "aggiunte" => ["name" => 'aggiunta', "DAO" => 'AggiuntaDAO'],
  "casse" => ["name" => 'cassa', "DAO" => 'CassaDAO'],
  "categorie" => ["name" => 'categoria', "DAO" => 'CategoriaDAO'],
  "ordini" => ["name" => 'ordine', "DAO" => 'OrdineDAO'],
  "pietanze" => ["name" => 'pietanza', "DAO" => 'PietanzaDAO'],
  "prenotazioni" => ["name" => 'prenotazione', "DAO" => 'PrenotazioneDAO'],
  "scontrini" => ["name" => 'scontrino', "DAO" => 'ScontrinoDAO'],
  "serate" => ["name" => 'serata', "DAO" => 'SerataDAO'],
  "stampanti" => ["name" => 'stampante', "DAO" => 'StampanteDAO']
];

$container = new DI\Container();
AppFactory::setContainer($container);
$app = AppFactory::create();
$db = new Database();

$container->set('app', $app);
$container->set('db', $db);

$app->group('/aggiunte', function (RouteCollectorProxy $app){
  $app->get('', Controller::class . ':getAll');
  $app->post('', Controller::class . ':insert');
  $app->group('/{id}', function (RouteCollectorProxy $app) {
    $app->get('', Controller::class . ':get');
    $app->patch('', Controller::class . ':update');
    $app->delete('', Controller::class . ':delete');
  })->add(new IdMiddleware());
})->add(new ResourceMiddleware($db, 1));

$app->group('/casse', function (RouteCollectorProxy $app) use ($db){
  $app->get('', CasseController::class . ':getAll');
  $app->post('', Controller::class . ':insert');
  $app->group('/{id}', function (RouteCollectorProxy $app) use ($db){
    $app->get('', CasseController::class . ':get');
    $app->patch('', CasseController::class . ':update');
    $app->delete('', Controller::class . ':delete');
    $app->group('/pietanze', function (RouteCollectorProxy $app) {
      $app->get('', PietanzeController::class . ':getByCassa');
    })->add(new ResourceMiddleware($db, 2));
    $app->group('/prenotazioni', function (RouteCollectorProxy $app) {
      $app->delete('', PrenotazioniController::class . ':deleteByCassa');
    })->add(new ResourceMiddleware($db, 2));
  })->add(new IdMiddleware());
})->add(new ResourceMiddleware($db, 1));

$app->group('/categorie', function (RouteCollectorProxy $app){
  $app->get('', Controller::class . ':getAll');
  $app->post('', Controller::class . ':insert');
  $app->group('/{id}', function (RouteCollectorProxy $app) {
    $app->get('', Controller::class . ':get');
    $app->patch('', Controller::class . ':update');
    $app->delete('', Controller::class . ':delete');
  })->add(new IdMiddleware());
})->add(new ResourceMiddleware($db, 1));

$app->group('/ordini', function (RouteCollectorProxy $app) use ($db){
  $app->get('', Controller::class . ':getAll');
  $app->post('', Controller::class . ':insert');
  $app->group('/{id}', function (RouteCollectorProxy $app) use ($db){
    $app->get('', Controller::class . ':get');
    $app->delete('', Controller::class . ':delete');
  })->add(new IdMiddleware());
})->add(new ResourceMiddleware($db, 1));

$app->group('/pietanze', function (RouteCollectorProxy $app) use ($db){
  $app->get('', Controller::class . ':getAll');
  $app->post('', Controller::class . ':insert');
  $app->group('/{id}', function (RouteCollectorProxy $app) use ($db){
    $app->get('', Controller::class . ':get');
    $app->patch('', Controller::class . ':update');
    $app->delete('', Controller::class . ':delete');
    $app->group('/prenotazioni', function (RouteCollectorProxy $app) {
      $app->post('', PrenotazioniController::class . ':insert');
    })->add(new ResourceMiddleware($db, 2));
  })->add(new IdMiddleware());
})->add(new ResourceMiddleware($db, 1));

$app->group('/prenotazioni', function (RouteCollectorProxy $app) use ($db){
  $app->get('', Controller::class . ':getAll');
  $app->group('/{id}', function (RouteCollectorProxy $app) use ($db){
    $app->get('', Controller::class . ':get');
    $app->patch('', Controller::class . ':update');
    $app->delete('', Controller::class . ':delete');
  })->add(new IdMiddleware());
})->add(new ResourceMiddleware($db, 1));

$app->group('/scontrini', function (RouteCollectorProxy $app) use ($db){
  $app->get('', Controller::class . ':getAll');
  $app->post('', Controller::class . ':insert');
  $app->group('/{id}', function (RouteCollectorProxy $app) use ($db){
    $app->get('', Controller::class . ':get');
    $app->patch('', Controller::class . ':update');
    $app->delete('', Controller::class . ':delete');
  })->add(new IdMiddleware());
})->add(new ResourceMiddleware($db, 1));

$app->group('/serate', function (RouteCollectorProxy $app) use ($db){
  $app->get('', SerateController::class . ':getAll');
  $app->post('', Controller::class . ':insert');
  $app->group('/{id}', function (RouteCollectorProxy $app) use ($db){
    $app->get('', Controller::class . ':get');
    $app->patch('', Controller::class . ':update');
    $app->delete('', Controller::class . ':delete');
  })->add(new IdMiddleware());
})->add(new ResourceMiddleware($db, 1));

$app->group('/stampanti', function (RouteCollectorProxy $app) use ($db){
  $app->get('', Controller::class . ':getAll');
  $app->post('', Controller::class . ':insert');
  $app->group('/{id}', function (RouteCollectorProxy $app) use ($db){
    $app->get('', Controller::class . ':get');
    $app->patch('', Controller::class . ':update');
    $app->delete('', Controller::class . ':delete');
  })->add(new IdMiddleware());
})->add(new ResourceMiddleware($db, 1));

$app->add(HttpErrorsMiddleware::class);
$app->add(ApplicationErrorsMiddleware::class);
$app->add(DatabaseErrorsMiddleware::class);
$app->add(JsonMiddleware::class);

$app->run();

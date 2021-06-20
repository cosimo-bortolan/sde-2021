<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;
use Slim\Factory\AppFactory;
use GuzzleHttp\Client;
use Sagra\Exceptions\InputException;
use Sagra\Core\Middleware\IdMiddleware;
use Sagra\Controller\ForwardController;
use Sagra\Middleware\AuthMiddleware;
use Sagra\Middleware\SerataMiddleware;
use Sagra\Middleware\UserMiddleware;
use Sagra\Middleware\ApplicationErrorsMiddleware;
use Sagra\Middleware\HttpErrorsMiddleware;
use Sagra\Middleware\JsonMiddleware;

require __DIR__ . '/../vendor/autoload.php';

$resourceMapping = [
  "aggiunte" => ["name" => 'aggiunta'],
  "casse" => ["name" => 'cassa'],
  "categorie" => ["name" => 'categoria'],
  "pietanze" => ["name" => 'pietanza'],
  "scontrini" => ["name" => 'scontrino'],
  "serate" => ["name" => 'serata'],
  "stampanti" => ["name" => 'stampante']
];

$endpoints = json_decode(file_get_contents("../../shared/config/api_endpoints.json"));

$container = new DI\Container();
AppFactory::setContainer($container);
$app = AppFactory::create();
$app->setBasePath('/core_api');

$container->set('data_client', new Client([
  'base_uri' => $endpoints->data_api,
  'timeout'  => 2.0,
]));
$container->set('auth_client', new Client([
  'base_uri' => $endpoints->auth_api,
  'timeout'  => 2.0,
]));
$container->set('app', $app);

$app->group('/aggiunte', function (RouteCollectorProxy $app) use ($container) {
  $app->get('', ForwardController::class . ':forward');
  $app->post('', ForwardController::class . ':forward')->add(new AuthMiddleware($container, ADMIN));
  $app->group('/{id}', function (RouteCollectorProxy $app) use ($container) {
    $app->get('', ForwardController::class . ':forward');
    $app->patch('', ForwardController::class . ':forward')->add(new AuthMiddleware($container, ADMIN));
    $app->delete('', ForwardController::class . ':forward')->add(new AuthMiddleware($container, ADMIN));
  })->add(new IdMiddleware());
})->add(new AuthMiddleware($container, AUTH));

$app->group('/casse', function (RouteCollectorProxy $app) use ($container) {
  $app->get('', ForwardController::class . ':forward');
  $app->post('', ForwardController::class . ':forward')->add(new AuthMiddleware($container, ADMIN));
  $app->group('/{id}', function (RouteCollectorProxy $app) use ($container) {
    $app->get('', ForwardController::class . ':forward');
    $app->patch('', ForwardController::class . ':forward')->add(new AuthMiddleware($container, ADMIN));
    $app->delete('', ForwardController::class . ':forward')->add(new AuthMiddleware($container, ADMIN));
  })->add(new IdMiddleware());
})->add(new AuthMiddleware($container, AUTH));

$app->group('/categorie', function (RouteCollectorProxy $app) use ($container) {
  $app->get('', ForwardController::class . ':forward');
  $app->post('', ForwardController::class . ':forward')->add(new AuthMiddleware($container, ADMIN));
  $app->group('/{id}', function (RouteCollectorProxy $app) use ($container) {
    $app->get('', ForwardController::class . ':forward');
    $app->patch('', ForwardController::class . ':forward')->add(new AuthMiddleware($container, ADMIN));
    $app->delete('', ForwardController::class . ':forward')->add(new AuthMiddleware($container, ADMIN));
  })->add(new IdMiddleware());
})->add(new AuthMiddleware($container, AUTH));

$app->group('/pietanze', function (RouteCollectorProxy $app) use ($container) {
  $app->get('', ForwardController::class . ':forward');
  $app->post('', ForwardController::class . ':forward')->add(new AuthMiddleware($container, ADMIN));
  $app->group('/{id}', function (RouteCollectorProxy $app) use ($container) {
    $app->get('', ForwardController::class . ':forward');
    $app->patch('', ForwardController::class . ':forward')->add(new AuthMiddleware($container, ADMIN));
    $app->delete('', ForwardController::class . ':forward')->add(new AuthMiddleware($container, ADMIN));
  })->add(new IdMiddleware());
})->add(new AuthMiddleware($container, AUTH));

$app->group('/scontrini', function (RouteCollectorProxy $app) use ($container) {
  $app->get('', ForwardController::class . ':forward');
  $app->post('', ForwardController::class . ':forward');
  $app->group('/{id}', function (RouteCollectorProxy $app) use ($container) {
    $app->get('', ForwardController::class . ':forward');
    $app->patch('', ForwardController::class . ':forward');
    $app->delete('', ForwardController::class . ':forward');
  })->add(new IdMiddleware());
})->add(new AuthMiddleware($container, ADMIN));

$app->group('/serate', function (RouteCollectorProxy $app) use ($container) {
  $app->get('', ForwardController::class . ':forward');
  $app->post('', ForwardController::class . ':forward')->add(new AuthMiddleware($container, ADMIN));
  $app->group('/{id}', function (RouteCollectorProxy $app) use ($container) {
    $app->get('', ForwardController::class . ':forward');
    $app->patch('', ForwardController::class . ':forward')->add(new AuthMiddleware($container, ADMIN));
    $app->delete('', ForwardController::class . ':forward')->add(new AuthMiddleware($container, ADMIN));
  })->add(new IdMiddleware());
})->add(new AuthMiddleware($container, AUTH));

$app->group('/stampanti', function (RouteCollectorProxy $app) use ($container) {
  $app->get('', ForwardController::class . ':forward');
  $app->post('', ForwardController::class . ':forward');
  $app->group('/{id}', function (RouteCollectorProxy $app) use ($container) {
    $app->get('', ForwardController::class . ':forward');
    $app->patch('', ForwardController::class . ':forward');
    $app->delete('', ForwardController::class . ':forward');
  })->add(new IdMiddleware());
})->add(new AuthMiddleware($container, ADMIN));

$app->add(SerataMiddleware::class);
$app->add(UserMiddleware::class);
$app->add(HttpErrorsMiddleware::class);
$app->add(ApplicationErrorsMiddleware::class);
$app->add(JsonMiddleware::class);

$app->run();

<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;
use Slim\Factory\AppFactory;
use Sagra\Printer\Controller\PrintersController;
use Sagra\Middleware\ApplicationErrorsMiddleware;
use Sagra\Middleware\HttpErrorsMiddleware;
use Sagra\Middleware\JsonMiddleware;

require __DIR__ . '/../vendor/autoload.php';

$resourceMapping = [
  "printers" => ["name" => 'printer']
];

$container = new DI\Container();
AppFactory::setContainer($container);
$app = AppFactory::create();

$container->set('app', $app);

$app->group('/printers', function (RouteCollectorProxy $app){
  $app->group('/{ip}', function (RouteCollectorProxy $app){
    $app->post('', PrintersController::class . ':print');
  });
});

$app->add(HttpErrorsMiddleware::class);
$app->add(ApplicationErrorsMiddleware::class);
$app->add(JsonMiddleware::class);

$app->run();

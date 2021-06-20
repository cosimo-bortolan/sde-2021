<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;
use Slim\Factory\AppFactory;
use GuzzleHttp\Client;
use Sagra\Exceptions\InputException;
use Sagra\Auth\Controller\LoginController;
use Sagra\Auth\Controller\UsersController;
use Sagra\Middleware\UserMiddleware;
use Sagra\Middleware\ApplicationErrorsMiddleware;
use Sagra\Middleware\HttpErrorsMiddleware;
use Sagra\Middleware\JsonMiddleware;

require __DIR__ . '/../vendor/autoload.php';

$resourceMapping = [
  "login" => ["name" => 'login'],
  "users" => ["name" => 'user']
];

$endpoints = json_decode(file_get_contents("../../shared/config/api_endpoints.json"));

$container = new DI\Container();
AppFactory::setContainer($container);
$app = AppFactory::create();
$app->setBasePath('/auth_api');

$container->set('data_client', new Client([
  'base_uri' => $endpoints->data_api,
  'timeout'  => 2.0,
]));
$container->set('auth_client', new Client([
  'base_uri' => $endpoints->auth_api,
  'timeout'  => 2.0,
]));
$container->set('app', $app);

$app->group('/login', function (RouteCollectorProxy $app){
  $app->get('', LoginController::class . ':getAll');
  $app->group('/{id}', function (RouteCollectorProxy $app) {
    $app->put('', LoginController::class . ':put');
    $app->delete('', LoginController::class . ':delete')->add(UserMiddleware::class);
  });
});

$app->group('/users', function (RouteCollectorProxy $app){
  $app->get('', UsersController::class . ':getAll');
});

$app->add(HttpErrorsMiddleware::class);
$app->add(ApplicationErrorsMiddleware::class);
$app->add(JsonMiddleware::class);

$app->run();

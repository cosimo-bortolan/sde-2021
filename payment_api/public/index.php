<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;
use Slim\Factory\AppFactory;
use Sagra\Payment\Middleware\SatispayMiddleware;
use Sagra\Middleware\ApplicationErrorsMiddleware;
use Sagra\Middleware\HttpErrorsMiddleware;
use Sagra\Middleware\JsonMiddleware;

require __DIR__ . '/../vendor/autoload.php';

$resourceMapping = [
  "satispay" => ["name" => 'satispay']
];

$container = new DI\Container();
AppFactory::setContainer($container);
$app = AppFactory::create();

$container->set('app', $app);

$app->group('/satispay', function (RouteCollectorProxy $app) {
  $app->group('/payments', function (RouteCollectorProxy $app) {
    $app->get('', function (Request $request, Response $response, $args) {
      $status = $request->getQueryParams()['status'];
      if(!is_null($status)){
        $body = ['status' => $status];
      }else{
        $body = [];
      }
      $payments = \SatispayGBusiness\Payment::all($body);
      $payment_list = [];
      foreach($payments->data as $payment){
        $payment_list[] = [
          'id' => $payment->id,
          'type' => $payment->type,
          'status' => $payment->status,
          'amount' => number_format($payment->amount_unit/100, 2, ',', '.'),
          'sender' => $payment->sender->name
        ];
      }
      $response->getBody()->write(json_encode(['payments' => $payment_list]));
      return $response;
    });
    $app->group('/{id}', function (RouteCollectorProxy $app) {
      $app->patch('', function (Request $request, Response $response, $args) {
        $id = $args['id'];
        $data = jsonDecode($request->getBody());
        try{
          $payment_response = \SatispayGBusiness\Payment::update($id, [
            "action" => $data->action
          ]);
          $payment = [
            'id' => $payment_response->id,
            'type' => $payment_response->type,
            'status' => $payment_response->status,
            'amount' => number_format($payment_response->amount_unit/100, 2, ',', '.'),
            'sender' => $payment_response->sender->name
          ];
          $response->getBody()->write(json_encode(['payment' => $payment]));
        }catch(Exception $e){
          $response = $response->withStatus(503);
        }
        return $response;
      });
    });
  });
})->add(SatispayMiddleware::class);

$app->add(HttpErrorsMiddleware::class);
$app->add(ApplicationErrorsMiddleware::class);
$app->add(JsonMiddleware::class);

$app->run();

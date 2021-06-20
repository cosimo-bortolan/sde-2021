<?php namespace Sagra\Payment\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use SatispayGBusiness\Api;

class SatispayMiddleware {

  public function __invoke(Request $request, RequestHandler $handler): Response {
    $authData = json_decode(file_get_contents( __DIR__ . "/../../config/satispay_authentication.json"));

    Api::setSandbox(true);
    Api::setPublicKey($authData->public_key);
    Api::setPrivateKey($authData->private_key);
    Api::setKeyId($authData->key_id);

    $response = $handler->handle($request);
    return $response;
  }
}

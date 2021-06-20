<?php namespace Sagra\Data\Middleware;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Sagra\Exceptions\InputException;
use Sagra\Exceptions\ObjectException;
use Sagra\Exceptions\LoginException;

class DatabaseErrorsMiddleware {

  private $db;

  public function __construct(ContainerInterface $container){
    $this->db = $container->get('db');
  }

  public function __invoke(Request $request, RequestHandler $handler): Response {
    try{
      $response = $handler->handle($request);
    } catch (\PDOException $e) {
        print_log($e);
        switch ($e->errorInfo[1]) {
            case 1062:
                $msg_substr = substr($e->errorInfo[2], strpos($e->errorInfo[2], "for key '") + 9);
                $resource = substr($msg_substr, 0, strpos($msg_substr, "'"));
                $code = ERR_UNIQUE_CONSTRAINT;
                $message = "Impossible to insert or update the resource. Duplicate value for a field with unique constraint.";
                $httpErrrrorCode = 400;
                $details = ["duplicate_field" => $resource];
                break;
            case 1452:
                $msg_substr = substr($e->errorInfo[2], strpos($e->errorInfo[2], "FOREIGN KEY (") + 14);
                $resource = substr($msg_substr, 0, strpos($msg_substr, ")") - 1);
                $code = ERR_FOREIGN_KEY_CONSTRAINT;
                $message = "Impossible to insert or update the resource. A reference for an external resource is incorrect.";
                $httpErrrrorCode = 400;
                $details = ["external_resource" => $resource];
                break;
            default: throw new InternalException($e);
        }
        $response = new \Slim\Psr7\Response();
        $response->getBody()->write(json_encode(["error" => $code, "message" => $message, "details" => $resource]));
        $response = $response->withStatus($httpErrrrorCode);
    } finally {
        $this->db->rollback();
        $this->db->unlock_tables();
    }
    return $response;
  }
}

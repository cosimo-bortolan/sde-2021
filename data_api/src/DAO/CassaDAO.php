<?php namespace Sagra\Data\DAO;

use Sagra\Data\DAO\DAO;
use Sagra\Data\DAO\StampanteDAO;
use Sagra\Entities\Cassa;
use Sagra\Exceptions\InputException;

const EXPIRATION_TIME = 12;

class CassaDAO extends DAO {

  function get($id) {
    $query = "SELECT
    id, nome, cookie, cookie_date, permessi, notifiche, stampante, asporto
    FROM
    casse
    WHERE
    id=:id";
    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(":id", $id);

    $stmt->execute();
    if ($stmt->rowCount() == 0) {
      throw new InputException(NOT_FOUND, ["resource" => "cassa", "id" => $id]);
    }
    $cassa = $stmt->fetchObject("Sagra\Entities\Cassa");
    $cassa->permessi = intval($cassa->permessi);
    unset($cassa->password);

    return $cassa;
  }

  function getPassword($id) {
    $query = "SELECT
    id, nome, cookie, cookie_date, password, permessi, notifiche, stampante, asporto
    FROM
    casse
    WHERE
    id=:id";
    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(":id", $id);

    $stmt->execute();
    if ($stmt->rowCount() == 0) {
      throw new InputException(NOT_FOUND);
    }
    $cassa = $stmt->fetchObject("Sagra\Entities\Cassa");
    $cassa->permessi = intval($cassa->permessi);

    return $cassa;
  }

  public function getByCookie($cookie) {
    $query = "SELECT
    id, nome, cookie, cookie_date, permessi, notifiche, stampante, asporto
    FROM
    casse
    WHERE
    cookie=:cookie AND
    TIMESTAMPDIFF(HOUR, cookie_date, NOW())<:exp";
    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(":cookie", $cookie);
    $exp = EXPIRATION_TIME;
    $stmt->bindParam(":exp", $exp);

    $stmt->execute();
    $casse = [];
    while ($cassa = $stmt->fetchObject("Sagra\Entities\Cassa")) {
      $cassa->permessi = intval($cassa->permessi);
      unset($cassa->password);
      $casse[] = $cassa;
    }
    if (count($casse) > 1) {
      throw new InternalException();
    } else if (count($casse) == 0) {
      throw new InputException(NOT_FOUND);
    }

    return $casse[0];
  }

  public function getByPermessi($permessi) {
    $query = "SELECT
    id, nome, cookie, cookie_date, permessi, notifiche, stampante, asporto
    FROM
    casse
    WHERE
    permessi=:permessi";
    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(":permessi", $permessi);

    $stmt->execute();
    $casse = [];
    while ($cassa = $stmt->fetchObject("Sagra\Entities\Cassa")) {
      $cassa->permessi = intval($cassa->permessi);
      unset($cassa->password);
      $casse[] = $cassa;
    }

    return $casse;
  }

  function getAll() {
    $stampanteDAO = new StampanteDAO($this->db);

    $query = "SELECT
    id, nome, cookie, cookie_date, permessi, notifiche, stampante, asporto
    FROM
    casse";
    $stmt = $this->conn->prepare($query);

    $stmt->execute();
    $casse = [];
    while ($cassa = $stmt->fetchObject("Sagra\Entities\Cassa")) {
      $cassa->permessi = intval($cassa->permessi);
      if ($cassa->stampante != null) {
        $cassa->stampante = $stampanteDAO->get($cassa->stampante, false);
      }
      unset($cassa->password);
      $casse[] = $cassa;
    }

    return $casse;
  }

  function insert($data) {
    $cassa = new Cassa($data);

    $query = "INSERT INTO
    casse
    SET
    nome=:nome,
    password=SHA2(:password,256),
    permessi=:permessi,
    stampante=:stampante,
    asporto=:asporto";

    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(":nome", $cassa->nome);
    $stmt->bindParam(":password", $cassa->password);
    $stmt->bindParam(":permessi", $cassa->permessi);
    $stmt->bindParam(":stampante", $cassa->stampante);
    $stmt->bindParam(":asporto", $cassa->asporto);

    $stmt->execute();
    $cassa->id = $this->conn->lastInsertId();
    unset($cassa->password);

    return $cassa;
  }

  function update($cassa, $data) {
    $cassa->patch($data);

    $query = "UPDATE
    casse
    SET
    nome=:nome,
    password=SHA2(:password,256),
    cookie=:cookie,
    cookie_date=:cookie_date,
    permessi=:permessi,
    notifiche=:notifiche,
    stampante=:stampante,
    asporto=:asporto
    WHERE
    id=:id";

    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(":nome", $cassa->nome);
    $stmt->bindParam(":password", $cassa->password);
    $stmt->bindParam(":cookie", $cassa->cookie);
    $stmt->bindParam(":cookie_date", $cassa->cookie_date);
    $stmt->bindParam(":permessi", $cassa->permessi);
    $stmt->bindParam(":notifiche", $cassa->notifiche);
    $stmt->bindParam(":stampante", $cassa->stampante);
    $stmt->bindParam(":asporto", $cassa->asporto);
    $stmt->bindParam(":id", $cassa->id);

    $stmt->execute();
    unset($cassa->password);

    return $cassa;
  }

  function updateNoPassword($cassa, $data) {
    $data->password = "password";
    $cassa->patch($data);
    unset($cassa->password);

    $query = "UPDATE
    casse
    SET
    nome=:nome,
    cookie=:cookie,
    cookie_date=:cookie_date,
    permessi=:permessi,
    notifiche=:notifiche,
    stampante=:stampante,
    asporto=:asporto
    WHERE
    id=:id";

    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(":nome", $cassa->nome);
    $stmt->bindParam(":cookie", $cassa->cookie);
    $stmt->bindParam(":cookie_date", $cassa->cookie_date);
    $stmt->bindParam(":permessi", $cassa->permessi);
    $stmt->bindParam(":notifiche", $cassa->notifiche);
    $stmt->bindParam(":stampante", $cassa->stampante);
    $stmt->bindParam(":asporto", $cassa->asporto);
    $stmt->bindParam(":id", $cassa->id);

    $stmt->execute();

    return $cassa;
  }

  function addNotifications() {
    $query = "UPDATE
    casse
    SET
    notifiche=notifiche+1";

    $stmt = $this->conn->prepare($query);

    $stmt->execute();
  }

  function resetNotificationsByCassa($cassa) {
    $query = "UPDATE
    casse
    SET
    notifiche=0
    WHERE
    id=:id";

    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(":id", $cassa->id);

    $stmt->execute();
  }

  function resetNotifications() {
    $query = "UPDATE
    casse
    SET
    notifiche=0";

    $stmt = $this->conn->prepare($query);

    $stmt->execute();
  }

  function deleteCookies() {
    $query = "UPDATE
    casse
    SET
    cookie=NULL,
    cookie_date=NULL
    WHERE
    permessi!=:permessi";

    $stmt = $this->conn->prepare($query);

    $stmt->bindValue(":permessi", ADMIN);

    $stmt->execute();
  }

  function delete($cassa) {
    $query = "DELETE
    FROM
    casse
    WHERE
    id=:id";

    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(":id", $cassa->id);

    $stmt->execute();
  }

}

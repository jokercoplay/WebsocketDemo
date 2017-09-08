<?php
namespace backend;

use Ratchet\Server\IoServer;
use backend\RatWebsocket;

class RatWebsocketServer {

  function __construct() {
    $server = IoServer::factory(
        new RatWebsocket(),
        8078,
        '127.0.0.1'
    );

    $server->run();
  }
}

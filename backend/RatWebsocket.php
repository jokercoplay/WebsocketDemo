<?php
namespace backend;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class RatWebsocket implements MessageComponentInterface {
    protected $clients;

    public function __construct() {
        echo "server start\n";
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        $conn->hasShakeHand = false;
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        echo json_encode($from) . "\n";
        if (!$from->hasShakeHand) {
            $from->send($this->upgrade($from, $msg));
            $from->hasShakeHand = true;
        } else {
            foreach ($this->clients as $client) {
                $client->send($msg);
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }

    public function upgrade($socket, $data)
    {
        if (preg_match("/Sec-WebSocket-Key: (.*)\r\n/", $data, $match)) {
            $response = base64_encode(sha1($match[1] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));
            $upgrade  = "HTTP/1.1 101 Switching Protocol\r\n" .
                    "Upgrade: websocket\r\n" .
                    "Connection: Upgrade\r\n" .
                    "Sec-WebSocket-Accept: " . $response . "\r\n\r\n";
            return $upgrade;
        }
    }
}

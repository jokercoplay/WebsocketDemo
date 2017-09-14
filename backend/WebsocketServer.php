<?php
    $master = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    socket_set_option($master, SOL_SOCKET, SO_REUSEADDR, 1);
    socket_bind($master, '192.168.225.143', '8078');
    socket_listen($master);
    socket_set_nonblock($master);
    $sockets = [];
    array_push($sockets, $master);

    while (true) {
        foreach ($sockets as $socket) {
            if ($socket === $master) {
                if ($client = socket_accept($master)) {
                    $header = socket_read($client, 1024);
                    upgrade($client, $header);
                    array_push($sockets, $client);
                    echo $client . " connected \n";
                }
            } else {
                $msgLength = socket_recv($socket, $message, 2048, MSG_DONTWAIT);
                if ($msgLength < 7 && $msgLength > 0) {
                    unset($sockets[array_keys($sockets, $socket)[0]]);
                    echo $socket . "disconnect \n" . sizeof($sockets);
                } elseif (!$msgLength == 0) {
                   send($message);
                }
            }
        }
    }

    function upgrade($socket, $data)
    {
        if (preg_match("/Sec-WebSocket-Key: (.*)\r\n/", $data, $match)) {
            $response = base64_encode(sha1($match[1] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));
            $upgrade  = "HTTP/1.1 101 Switching Protocol\r\n" .
                    "Upgrade: websocket\r\n" .
                    "Connection: Upgrade\r\n" .
                    "Sec-WebSocket-Accept: " . $response . "\r\n\r\n";
            socket_write($socket, $upgrade, strlen($upgrade));
        }
    }

    function send($message) {
        global $sockets;
        $clients = $sockets;
        unset($clients[0]);
        $msg = frame(decode($message));
        foreach ($clients as $client) {
            socket_write($client, $msg, strlen($msg));
        }
    }

    function decode($buffer)  {
        $len = $masks = $data = $decoded = null;
        $len = ord($buffer[1]) & 127;

        if ($len === 126)  {
            $masks = substr($buffer, 4, 4);
            $data = substr($buffer, 8);
        } else if ($len === 127)  {
            $masks = substr($buffer, 10, 4);
            $data = substr($buffer, 14);
        } else {
            $masks = substr($buffer, 2, 4);
            $data = substr($buffer, 6);
        }
        for ($index = 0; $index < strlen($data); $index++) {
            $decoded .= $data[$index] ^ $masks[$index % 4];
        }
        return $decoded;
    }

    function frame($message) {
        $a = str_split($message, 125);
        if (count($a) == 1) {
            return "\x81" . chr(strlen($a[0])) . $a[0];
        }
        $ns = "";
        foreach ($a as $o) {
            $ns .= "\x81" . chr(strlen($o)) . $o;
        }
        return $ns;
    }

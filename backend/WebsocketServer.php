<?php
    $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);
    socket_bind($socket, '127.0.0.1', '8078');
    socket_listen($socket);
    $clients = [];

    while (true) {
        $newSocket = socket_accept($socket);
        array_push($clients, $newSocket);
        $header = socket_read($newSocket, 1024);
        upgrade($newSocket, $header);
        echo $newSocket . ' connected--- ';
        // foreach ($clients as $client) {
        //     if ($msgLength = socket_recv($client, $buffer, 2048, 0)) {
        //         echo $msgLength;
        //     }
        // }
        // foreach ($clients as $client) {
        //     if ($msgLength == 8) {
        //         unset($clients[1]);
        //     } else {
        //         foreach ($clients as $client) {
        //             send(decode($buffer));
        //         }
        //     }
        // }
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
        global $clients;
        $msg = frame($message);
        foreach ($clients as $client) {
            socket_write($client, $msg, strlen($msg));
            echo $client . ' send' . $msg;
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

<?php
    $master = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    socket_set_option($master, SOL_SOCKET, SO_REUSEADDR, 1);
    socket_bind($master, '127.0.0.1', '8078');
    socket_listen($master);
    $sockets = [];
    array_push($sockets, $master);

    while (true) {
        $write = null;
        $except = null;
        echo sizeof($sockets) . "\n";
        socket_select($sockets, $write, $except, null);
        echo sizeof($sockets) . "\n";
        foreach ($sockets as $socket) {
            if ($socket == $master) {
                $client = socket_accept($master);
                $header = socket_read($client, 1024);
                upgrade($client, $header);
                array_push($sockets, $client);
                echo $client . " connected \n";
            } else {
                $bufferLength = socket_recv($socket, $message, 2048, 0);
                send($message);
                break;
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

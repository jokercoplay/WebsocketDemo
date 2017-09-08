[slide]
# websocket初体验
----
### dony
[slide]
# 什么是websocket？
----
- 一种持久化的网络协议。
- 客户端与服务端的全双工通信。
<image src="/images/request.png"></image>
<image src="/images/response.png"></image>
[slide]
# websocket的作用
----
- ajax轮询：浏览器每隔一定时间就发送一次请求，询问服务器是否有新信息。
- 长轮询long poll：客户端发起request后，服务端如果没消息，就一直不返回Response给客户端。直到有消息才返回，返回完之后，客户端再次建立连接，周而复始。
- websocket：整个通讯过程是建立在一次连接状态中，通过回调的方式服务端可以主动向客户端发送消息，实现即时通信。
[slide]
# 客户端使用websocket
----
```
  var websocket = new WebSocket("ws://localhost:8080/websocket/{channel_id}")
  websocket.onerror()
  websocket.onopen()
  websocket.onmessage(event) {
    send(event.data);
  }
  websocket.onclose()
  send(message) {
    websocket.send(message);
  }
```
[slide]
# 服务端（J2EE）websocket
----
```
  import javax.websocket
  @ServerEndpoint("/websocket/{param}")
  @onError
  @onOpen
  @onMessage
  @onClose
  sendMessage()
```
[slide]
# 服务端（php）websocket
```
  socket_create();  socket_set_option();  socket_bind();  socket_listen();
  while (true) {
    $newSocket = socket_accept($socket);
    $header = socket_read($newSocket, 1024);
    upgrade($newSocket, $header);
  }
  function upgrade($socket, $data) {
    $upgrade  = "HTTP/1.1 101 Switching Protocol\r\n" .
    "Upgrade: websocket\r\n" .
    "Connection: Upgrade\r\n" .
    "Sec-WebSocket-Accept: " . $response . "\r\n\r\n";
    socket_write($socket, $upgrade, strlen($upgrade));
  }
```
[slide]
# 服务端（php）Ratchet
----
```
  class RatWebsocket implements MessageComponentInterface {
    onopen(ConnectionInterface $conn) {}
    onMessage(ConnectionInterface $from, $msg) {
      upgrade() or sendMessage()
    }
    onClose()
    onError()
  }

  $server = IoServer::factory(
    new RatWebsocket(), 8078, '127.0.0.1'
  );
  $server->run();
```
[slide]
<iframe src="http://127.0.0.1:8079/websocketDemo/frontend/Chat.html"></iframe>

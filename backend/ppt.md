[slide]
# websocket初体验
----
### dony
[slide]
# 什么是websocket？
----
- 一种持久化的网络协议。
- 客户端与服务端的全双工通信。
<image src="/images/connPic.png"></image>
[slide]
# Request and Response
---
<image src="/images/request.png"></image>
---
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
websocket = new WebSocket("ws://192.168.225.143:8078/websocketDemo/backend/WebSocketServer.php")
websocket.onerror()
websocket.onopen()
websocket.onmessage(event)
websocket.onclose()
websocket.send(message)
websocket.close()
```
[slide]
# 服务端(JAVA)websocket
----
```
import javax.websocket
@ServerEndpoint("/websocket/{param}")
class websocketServer{
  private static Map<String, ArrayList<WebsocketServer>> webSocketMap = new HashMap<String, ArrayList<WebsocketServer>>();
  @onError(Session session, Throwable error){}
  @onOpen(@PathParam(value="param") String param, Session session){
    if (!webSocketMap.containsKey(channel)) {
        webSocketMap.put(channel, new ArrayList());
    }
    webSocketMap.get(channel).add(this);
  }
  @onMessage(String message, Session session) {
      for(WebsocketServer item: webSocketMap.get(channel)){
          try {
              item.getBasicRemote().sendText(message);
          } catch (IOException e) {}
      }
  }
  @onClose() {
    webSocketMap.get(channel).remove(this);
  }
}
```
[slide]
# 服务端（php）websocket
## php Socket函数库
```
resource socket_create ( int $domain , int $type , int $protocol )
bool socket_set_option ( resource $socket , int $level , int $optname , mixed $optval )
bool socket_bind ( resource $socket , string $address [, int $port = 0 ] )
bool socket_listen ( resource $socket [, int $backlog = 0 ] )
bool socket_set_nonblock ( resource $socket )
resource socket_accept ( resource $socket )
string socket_read ( resource $socket , int $length [, int $type = PHP_BINARY_READ ] )
int socket_recv ( resource $socket , string &$buf , int $len , int $flags )
int socket_write ( resource $socket , string $buffer [, int $length ] )
```
[slide]
## 创建主机socket
```
$master = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_set_option($master, SOL_SOCKET, SO_REUSEADDR, 1);
socket_bind($master, '192.168.225.143', '8078');
socket_listen($master);
socket_set_nonblock($master);
$sockets = [];
array_push($sockets, $master);
```
[slide]
## 死循环监听socket状态
```
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
                echo $socket . "disconnect \n";
            } elseif (!$msgLength == 0) {
               send($message);
            }
        }
    }
}
```
[slide]
## 升级协议
```
function upgrade() {
    if (preg_match("/Sec-WebSocket-Key: (.*)\r\n/", $data, $match)) {
        $response = base64_encode(sha1($match[1] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));
        $upgrade  = "HTTP/1.1 101 Switching Protocol\r\n" .
                "Upgrade: websocket\r\n" .
                "Connection: Upgrade\r\n" .
                "Sec-WebSocket-Accept: " . $response . "\r\n\r\n";
        socket_write($socket, $upgrade, strlen($upgrade));
    }
}
```
---
## 处理数据帧
```
function decode()
function frame()
```
[slide]
# 服务端（php）Ratchet
----
```
class RatWebsocket implements MessageComponentInterface {
  onopen(ConnectionInterface $conn) {}
  onMessage(ConnectionInterface $from, $msg) {
    upgrade() or sendMessage() or close()
  }
  onClose()
  onError()
}

$server = IoServer::factory(
  new RatWebsocket(), 8078, '192.168.225.143'
);
$server->run();
```
[slide]
<iframe src="http://192.168.225.143:8079/WebsocketDemo/frontend/Chat.html"></iframe>
<iframe src="http://192.168.225.143:8079/WebsocketDemo/frontend/Chat.html"></iframe>
<iframe src="http://192.168.225.143:8079/WebsocketDemo/frontend/Chat.html"></iframe>
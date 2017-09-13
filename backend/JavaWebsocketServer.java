import java.io.IOException;
import java.util.*;
import javax.websocket.*;
import javax.websocket.server.ServerEndpoint;

@ServerEndpoint("/websocket")
public class JavaWebsocketServer {
    private static List<JavaWebsocketServer> webSocketList = new ArrayList<JavaWebsocketServer>();

    private Session session;

    @OnOpen
    public void onOpen(Session session){
        webSocketList.add(this);
        System.out.println("有新连接加入！");
    }

    @OnClose
    public void onClose(){
        webSocketList.remove(this);
        System.out.println("有一连接关闭！");
    }

    @OnMessage
    public void onMessage(String message, Session session) {
        System.out.println("来自客户端的消息:" + message);
        for(JavaWebsocketServer item: webSocketList){
            try {
                item.sendMessage(message);
            } catch (IOException e) {
                e.printStackTrace();
                continue;
            }
        }
    }

    @OnError
    public void onError(Session session, Throwable error){
        System.out.println("发生错误");
        error.printStackTrace();
    }

    public void sendMessage(String message) throws IOException{
        this.session.getBasicRemote().sendText(message);
    }
}

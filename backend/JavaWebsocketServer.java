import java.io.IOException;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.Map;

import javax.websocket.*;
import javax.websocket.server.PathParam;
import javax.websocket.server.ServerEndpoint;

@ServerEndpoint("/websocket/{param}")
public class WebsocketServer { 
    private static Map<String, ArrayList<WebsocketServer>> webSocketMap = new HashMap<String, ArrayList<WebsocketServer>>();

    private Session session;
    private String channel;

    @OnOpen
    public void onOpen(@PathParam(value="param") String param, Session session){
        this.session = session;
        this.channel = param;
        if (!webSocketMap.containsKey(channel)) {
        	webSocketMap.put(channel, new ArrayList());
        }
        webSocketMap.get(channel).add(this);
        System.out.println("所在频道为" + channel);
    }

    @OnClose
    public void onClose(){
    	webSocketMap.get(channel).remove(this);
        System.out.println(channel + "频道有一人退出,当前在线" + webSocketMap.get(channel).size() + "人");
    }

    @OnMessage
    public void onMessage(String message, Session session) {
        for(WebsocketServer item: webSocketMap.get(channel)){
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
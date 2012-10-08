package chatserver;

import java.util.Map;

/**
 *
 */
public interface ActionHandler {
    public Map<String, Object> handle(HttpChatCallInformation info) throws ActionHandlerException;
}

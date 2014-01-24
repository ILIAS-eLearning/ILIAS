package chatserver.handler;

import chatserver.HttpChatCallInformation;
import chatserver.Message;
import chatserver.MissingArgumentException;
import chatserver.ActionHandlerException;
import chatserver.ActionHandler;
import chatserver.MapMessageNotifier;
import chatserver.Subscriber;
import java.util.HashMap;
import java.util.Map;

/**
 * handler for returning just a success=true to check if the controll stack is
 * working
 */
public class StatusActionHandler implements ActionHandler {

	public Map<String, Object> handle(HttpChatCallInformation info) throws ActionHandlerException {
		Map<String, Object> result = new HashMap<String, Object>();
		result.put("success", true);
		return result;
	}
}

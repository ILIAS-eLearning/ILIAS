package chatserver.handler;

import chatserver.HttpChatCallInformation;
import chatserver.MissingArgumentException;
import chatserver.ActionHandlerException;
import chatserver.ActionHandler;
import chatserver.ChatScope;
import java.util.HashMap;
import java.util.Map;

/**
 * handler for creating a new private room (sub scope)
 */
public class CreatePrivateRoomActionHandler implements ActionHandler {

	public Map<String, Object> handle(HttpChatCallInformation info) throws ActionHandlerException {
		// sub scope id given? this id is managed by the remote instance
		if (info.getParams().containsKey("id") == false) {
			throw new ActionHandlerException(new MissingArgumentException("no id given"));
		}

		// push new sub scope
		ChatScope subScope = info.getScope().createSubScope(info.getParams().getInt("id"));

		Map<String, Object> result = new HashMap<String, Object>();
		result.put("success", true);
		result.put("id", subScope.getId());

		return result;
	}
}

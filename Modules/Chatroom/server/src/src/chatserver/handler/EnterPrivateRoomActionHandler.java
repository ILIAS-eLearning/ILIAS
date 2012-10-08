package chatserver.handler;

import chatserver.HttpChatCallInformation;
import chatserver.MissingArgumentException;
import chatserver.ActionHandlerException;
import chatserver.ActionHandler;
import java.util.HashMap;
import java.util.Map;

/**
 * handler for subscribing a user to an sub scope
 */
public class EnterPrivateRoomActionHandler implements ActionHandler {

	public Map<String, Object> handle(HttpChatCallInformation info) throws ActionHandlerException {
		// sub scope id given?
		if (info.getParams().containsKey("sub") == false) {
			throw new ActionHandlerException(new MissingArgumentException("no sub scope given"));
		}
		// sub scope id valid?
		else if (!info.getScope().subScopeExists(info.getParams().getInt("sub"))) {
			throw new ActionHandlerException(new MissingArgumentException("invalid sub scope id given"));
		}

		// attatch user
		info.getScope().attachSubscriberToSubScope(info.getParams().getInt("sub"), info.getScope().getSubscibers().getSubscriberById(info.getParams().getInt("user")));

		Map<String, Object> result = new HashMap<String, Object>();
		result.put("success", true);

		return result;
	}
}

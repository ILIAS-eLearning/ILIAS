package chatserver.handler;

import chatserver.HttpChatCallInformation;
import chatserver.MissingArgumentException;
import chatserver.ActionHandlerException;
import chatserver.ActionHandler;
import java.util.HashMap;
import java.util.Map;

/**
 * handler for leaving a private room
 */
public class LeavePrivateRoomActionHandler implements ActionHandler {

	public Map<String, Object> handle(HttpChatCallInformation info) throws ActionHandlerException {
		if (info.getParams().containsKey("sub") == false || !info.getScope().subScopeExists(info.getParams().getInt("sub"))) {
			throw new ActionHandlerException(new MissingArgumentException("no sub scope or invalid id given"));
		}

		// just detatch the user from the sub scope
		info.getScope().detachSubscriberToSubScope(info.getParams().getInt("sub"), info.getScope().getSubscibers().getSubscriberById(info.getParams().getInt("user")));

		Map<String, Object> result = new HashMap<String, Object>();
		result.put("success", true);

		return result;
	}
}

package chatserver.handler;

import chatserver.HttpChatCallInformation;
import chatserver.Message;
import chatserver.MissingArgumentException;
import chatserver.ActionHandlerException;
import chatserver.ActionHandler;
import chatserver.Subscriber;
import java.util.HashMap;
import java.util.Map;

/**
 * handler for receiving new posts
 */
public class PostActionHandler implements ActionHandler {

	public Map<String, Object> handle(HttpChatCallInformation info) throws ActionHandlerException {
		// need a message to post
		if (info.getParams().containsKey("message") == false) {
			throw new ActionHandlerException(new MissingArgumentException("no message given"));
		}

		int sub = 0;
		// check if the message is for a main scope or a sub scope
		if (info.getParams().containsKey("sub")) {
			sub = info.getParams().getInt("sub");
		}

		// get optional recipients
		// missing recipients will cause a public message to all users in the room (default)
		String recipients = info.getParams().get("recipients");
		int publicMessage = info.getParams().getInt("public");
		Subscriber subscriber;

		// handle private message
		if (publicMessage == 0 && recipients != null) {

			String[] recipientsArray = recipients.split(",");

			// send the message to every recipient that is addressed in the list
			for (String recipient : recipientsArray) {
				try {
					subscriber = info.getScope().getSubscibers().getSubscriberById(Integer.parseInt(recipient));
					if (subscriber != null && (sub == 0 || info.getScope().isSubscriberSubscribedToSubScope(subscriber, sub))) {
						subscriber.addMessage(new Message(info.getParams().get("message")));
					}
				} catch (NumberFormatException e) {
					// ignore
					e.printStackTrace();
				}
			}
		}
		// handle public message
		else {
			Message message = new Message(info.getParams().get("message"));
			// post to scope
			if (sub == 0) {
				info.getScope().getSubscibers().notify(message);
			}
			// post to sub scope
			else {
				info.getScope().getSubScopeSubscribers(sub).notify(message);
			}

		}
		Map<String, Object> result = new HashMap<String, Object>();
		result.put("success", true);

		return result;
	}
}

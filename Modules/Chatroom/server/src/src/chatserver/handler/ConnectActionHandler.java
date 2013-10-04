package chatserver.handler;

import chatserver.*;
import java.text.SimpleDateFormat;
import java.util.*;
import java.util.logging.Level;
import java.util.logging.Logger;

/**
 * Handler for connecting a new user to the chat
 */
public class ConnectActionHandler implements ActionHandler {
	private final SimpleDateFormat sdf = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss");
	public static HashSet<Integer> knownSessionIdSet = new HashSet<Integer>();

	private static int generateSessionId() {
		// create a highily secured session id ;)
		Random generator = new Random(System.currentTimeMillis());

		int tmp;

		// find an unused id
		while (knownSessionIdSet.contains(tmp = generator.nextInt()));

		knownSessionIdSet.add(tmp);

		return tmp;
	}

	public Map<String, Object> handle(HttpChatCallInformation info) throws ActionHandlerException {
		if (!info.getParams().containsKey("id")) {
			throw new ActionHandlerException(new MissingArgumentException("id"));
		}

		// user id of the user to connect
		int id = info.getParams().getInt("id");

		Map<String, Object> result = new HashMap<String, Object>();

		int sessionId = generateSessionId();
		
		// check if user is alread active in the addressed scope
		// user is active
		if (info.getScope().getSubscibers().getSubscriberById(id) != null) {
			Subscriber subscriber = info.getScope().getSubscibers().getSubscriberById(id);
			// register another user session (maybe the user opend a second window)
			info.getScope().getSubscibers().registerSession(sessionId, subscriber);
			Logger.getLogger("default").log(
				Level.INFO,
				"[{0}] Registered session {1,number} for substriber {2,number} in scope {3,number}",
				new Object[]{
					info.getInstance().getIliasClient(),
					sessionId,
					subscriber.getId(),
					info.getScope().getId()
				}
			);
		}
		// user is not active
		else {
			Subscriber subscriber = new Subscriber();
			subscriber.setId(id);
			subscriber.addSessionId(sessionId);

			info.getScope().attatchSubscriber(subscriber);
			Logger.getLogger("default").log(
				Level.INFO,
				"[{0}] Registered session {1,number} for new substriber {2,number} in scope {3,number}",
				new Object[]{
					info.getInstance().getIliasClient(),
					sessionId,
					subscriber.getId(),
					info.getScope().getId()
				}
			);

			// post the message given by the remote instance (e.g. user xxx has entered the room)
			if (info.getParams().containsKey("message") && info.getParams().get("message").length() > 0) {
				info.getScope().getSubscibers().notify(new Message(info.getParams().get("message")));
			}
		}

		// return the new session id
		result.put("session-id", sessionId);

		return result;
	}
}

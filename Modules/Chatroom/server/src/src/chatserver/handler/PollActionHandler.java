package chatserver.handler;

import chatserver.*;
import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.HashMap;
import java.util.LinkedList;
import java.util.Map;
import java.util.logging.Level;
import java.util.logging.Logger;

/**
 * handler for fetching all new messages since the last polling call
 * 
 * this handler performs a long poll. if there are no changes in the subscribers message
 * queue, the handler waits some time and checks again to reduce http connections.
 * 
 * if a change is detected, the handler will return immediately
 * 
 * @todo currently the long waits a maximum of one second... that could be better
 */
public class PollActionHandler implements ActionHandler {
	private final SimpleDateFormat sdf = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss");

	public Map<String, Object> handle(HttpChatCallInformation info) throws ActionHandlerException {
		if (!info.getParams().containsKey("id")) {
			throw new ActionHandlerException(new MissingArgumentException("id"));
		} else if (!info.getParams().containsKey("pos")) {
			throw new ActionHandlerException(new MissingArgumentException("pos"));
		}

		int id = info.getParams().getInt("id");
		int buffer_position = info.getParams().getInt("pos");

		// the user is not subscribed to the scope if == null
		if (info.getScope().getSubscibers().getSubscriberBySessionId(id) == null) {
			Map<String, Object> result = new HashMap<String, Object>();
			result.put("subscribed", false);
			result.put("messages", new LinkedList<String>());
			result.put("next_position", 0);
			return result;
		}

		Subscriber subscriber = info.getScope().getSubscibers().getSubscriberBySessionId(id);
		subscriber.refreshSubscription();
		Logger.getLogger("default").log(
			Level.FINEST,
			"[{0}] Session refreshed for subscriber {1,number} in scope {2,number}, Current datetime: {3}",
			new Object[]{
				info.getInstance().getIliasClient(),
				subscriber.getId(),
				info.getScope().getId(),
				sdf.format(new Date(subscriber.getLastConnect()))
			}
		);
								

		// using a map notifier to let the messages easily be serialized to json
		MapMessageNotifier notifier = new MapMessageNotifier();

		double secondsWaited = 0;

		int nextPosition = buffer_position;
		int startingPosition = nextPosition;
		try {
			do {
				nextPosition = subscriber.notify(notifier, buffer_position);
				if (nextPosition != startingPosition) {
					// there was a change, so stop the long poll and return the response
					break;
				}
				// there was no change, so wait some time and check again
				Thread.sleep(500);
				secondsWaited += 0.5;
			}
			while (notifier.getMessages().size() == 0 && secondsWaited < 1);
		} catch (Exception e) {
			throw new ActionHandlerException(e);
		}

		// build the response
		Map<String, Object> result = new HashMap<String, Object>();
		result.put("messages", notifier.getMessages());
		result.put("next_position", nextPosition);
		result.put("subscribed", true);
		return result;
	}
}

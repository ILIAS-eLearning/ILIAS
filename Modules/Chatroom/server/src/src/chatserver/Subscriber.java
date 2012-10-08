package chatserver;

import java.util.LinkedList;
import java.util.List;

/**
 * A chat user
 */
public class Subscriber {

	/**
	 * the messages for the user to receive
	 */
	private MessageBuffer buffer = new MessageBuffer();
	/**
	 * the user id
	 */
	private int id;
	/**
	 * last connection timestamp (is refreshed for each poll)
	 */
	private long lastConnect = System.currentTimeMillis();
	/**
	 * list of http sessions of the user
	 * 
	 * a user might connect multiple times, so we need a list
	 */
	private List<Integer> sessions = new LinkedList<Integer>();

	public int getId() {
		return id;
	}

	public void setId(int id) {
		this.id = id;
	}

	public void addMessage(Message message) {
		buffer.enqueue(message);
	}

	/**
	 * notifies the given notifier about all new messages, starting at the given
	 * buffer position.
	 * 
	 * if messages has been added to the notifier, the position of the next position
	 * that will hold the next new message will be returned. otherwise the current position
	 * will be returend so that the next poll can ask again for changes.
	 * 
	 * @param notifier
	 * @param position
	 * @return 
	 */
	public int notify(MessageNotifier notifier, int position) {
		BufferMessage message = null;
		while (buffer.hasEntries(position)) {
			message = buffer.dequeue(position);
			if (message == null) {
				break;
			}
			notifier.notify(message.getMessage());
			position = message.getPosition();
		}

		if (position == -1) {
			position = buffer.getListEndPosition();
		}

		return message == null ? position : message.getPosition();
	}

	public void refreshSubscription() {
		lastConnect = System.currentTimeMillis();
	}

	public long getLastConnect() {
		return lastConnect;
	}

	public void addSessionId(int id) {
		sessions.add(id);
	}

	public List<Integer> getSessions() {
		return sessions;
	}
}

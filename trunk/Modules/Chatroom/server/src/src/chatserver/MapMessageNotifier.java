package chatserver;

import java.util.LinkedList;

/**
 * A message handler that stores all messages in a simple list
 * 
 * @todo this class should be called ListMessageNotifier
 */
public class MapMessageNotifier implements MessageNotifier {

	private LinkedList<String> messages;

	public MapMessageNotifier() {
		messages = new LinkedList<String>();
	}

	public void notify(Message m) {
		messages.add(m.getData());
	}

	public LinkedList<String> getMessages() {
		return messages;
	}

	public void begin() {
	}

	public void end() {
	}
}

package chatserver;

/**
 * A message handler that outputs all messages to stdout
 * 
 * only for testing
 */
public class SystemOutMessageNotifier implements MessageNotifier {

	public void notify(Message m) {
		System.out.println(m.getData());
	}

	public void begin() {
	}

	public void end() {
	}
}

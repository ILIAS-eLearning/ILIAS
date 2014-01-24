package chatserver;

/**
 * Basic interface for message handlers.
 */
public interface MessageNotifier {

	public void begin();

	public void notify(Message m);

	public void end();
}

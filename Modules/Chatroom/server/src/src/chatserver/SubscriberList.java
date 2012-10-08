package chatserver;

import java.util.HashMap;
import java.util.Iterator;
import java.util.LinkedList;
import java.util.List;
import java.util.Map;

/**
 * Wrapper for managing a list of subscribers
 */
public class SubscriberList implements Iterable<Subscriber> {
	/**
	 * flat list of subscribers
	 */
	private List<Subscriber> subscribers = new LinkedList<Subscriber>();
	/**
	 * lookup map by user id
	 */
	private Map<Integer, Subscriber> subscribersById = new HashMap<Integer, Subscriber>();
	/**
	 * lookup my by session id
	 */
	private Map<Integer, Subscriber> subscribersBySessionId = new HashMap<Integer, Subscriber>();

	public void add(Subscriber subscriber) {
		subscribers.add(subscriber);
		subscribersById.put(subscriber.getId(), subscriber);
		for (Integer id : subscriber.getSessions()) {
			subscribersBySessionId.put(id, subscriber);
		}
	}

	public Subscriber getSubscriberById(int id) {
		return subscribersById.get(id);
	}

	public Subscriber getSubscriberBySessionId(int id) {
		return subscribersBySessionId.get(id);
	}

	public void remove(Subscriber subscriber) {
		subscribers.remove(subscriber);
		subscribersById.remove(subscriber.getId());
		for (Integer id : subscriber.getSessions()) {
			subscribersBySessionId.remove(id);
		}
	}

	public Iterator<Subscriber> iterator() {
		return new Iterator<Subscriber>() {

			private Iterator<Subscriber> iterator = subscribers.iterator();
			private Subscriber current;

			public boolean hasNext() {
				return iterator.hasNext();
			}

			public Subscriber next() {
				return current = iterator.next();
			}

			public void remove() {
				iterator.remove();
				subscribersById.remove(current.getId());
			}
		};
	}

	/**
	 * adds a new message to each of the subscribers that are registered to this list
	 * 
	 * this is the typical case if someone post a public message to a room
	 * 
	 * @param message 
	 */
	public void notify(Message message) {
		for (Subscriber subscriber : subscribers) {
			subscriber.addMessage(message);
		}
	}

	public int size() {
		return subscribers.size();
	}

	public void registerSession(int sessionId, Subscriber subscriber) {
		subscriber.addSessionId(sessionId);
		subscribersBySessionId.put(sessionId, subscriber);
	}
}

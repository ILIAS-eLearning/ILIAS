package chatserver;

import java.util.HashMap;
import java.util.HashSet;
import java.util.Map;
import java.util.Set;

/**
 * This class represents a single main chat room. Each instance may have unlimited
 * scopes. Each scope has a list of subscribers (users) and sub scopes (private rooms)
 */
public class ChatScope {

	/**
	 * id of the current scope
	 */
	private int id = 0;
	/**
	 * list of all subscribed users
	 */
	private SubscriberList subscribers = new SubscriberList();
	/**
	 * list of registered private rooms
	 */
	private Map<Integer, ChatScope> subScopes = new HashMap<Integer, ChatScope>();
	/**
	 * lookup map for retreiving all sub scopes of a user
	 */
	private Map<Subscriber, Set<ChatScope>> subScopesBySubscriber = new HashMap<Subscriber, Set<ChatScope>>();
	/**
	 * lookup map for retreiving all subscribers of a sub scope
	 */
	private Map<ChatScope, SubscriberList> subscribersBySubScopes = new HashMap<ChatScope, SubscriberList>();

	public void setId(int id) {
		this.id = id;
	}

	public int getId() {
		return this.id;
	}

	/**
	 * adds a new subscriber to this scope
	 * 
	 * @param subscriber 
	 */
	public void attatchSubscriber(Subscriber subscriber) {
		subscribers.add(subscriber);
	}

	/**
	 * removes a subscriber from this scope
	 * 
	 * the subscriber is not removed from the child scopes... seems to be no problem
	 * 
	 * @param subscriber 
	 */
	public void detatchSubscriber(Subscriber subscriber) {
		subscribers.remove(subscriber);
	}

	public SubscriberList getSubscibers() {
		return subscribers;
	}

	/**
	 * creates and registers a new sub scope for this scope
	 * the new sub scope is returnes
	 * 
	 * @param scopeId
	 * @return 
	 */
	public ChatScope createSubScope(int scopeId) {
		if (subScopes.containsKey(scopeId)) {
			return subScopes.get(scopeId);
		}

		ChatScope sub = new ChatScope();
		sub.setId(scopeId);

		subScopes.put(scopeId, sub);

		subscribersBySubScopes.put(sub, new SubscriberList());

		return sub;
	}

	public void attachSubscriberToSubScope(Integer scopeId, Subscriber subscriber) {
		if (isSubscriberSubscribedToSubScope(subscriber, scopeId)) {
			return;
		}

		subScopes.get(scopeId).attatchSubscriber(subscriber);

		if (!subScopesBySubscriber.containsKey(subscriber)) {
			subScopesBySubscriber.put(subscriber, new HashSet<ChatScope>());
		}

		subScopesBySubscriber.get(subscriber).add(subScopes.get(scopeId));

		subscribersBySubScopes.get(subScopes.get(scopeId)).add(subscriber);
	}

	public void detachSubscriberToSubScope(Integer scopeId, Subscriber subscriber) {
		subScopes.get(scopeId).detatchSubscriber(subscriber);
		if (subScopesBySubscriber.containsKey(subscriber)) {
			subScopesBySubscriber.remove(subscriber);
		}

		subscribersBySubScopes.get(subScopes.get(scopeId)).remove(subscriber);
	}

	public boolean isSubscriberSubscribedToSubScope(Subscriber subscriber, int scopeId) {
		return subScopes.containsKey(scopeId) && subScopesBySubscriber.containsKey(subscriber) && subScopesBySubscriber.get(subscriber).contains(subScopes.get(scopeId));
	}

	public SubscriberList getSubScopeSubscribers(int scopeId) {
		return subscribersBySubScopes.get(subScopes.get(scopeId));
	}

	public boolean subScopeExists(int scopeId) {
		return subScopes.containsKey(scopeId);
	}
}

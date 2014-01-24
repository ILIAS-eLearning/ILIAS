package chatserver;

import java.util.HashMap;
import java.util.Iterator;
import java.util.LinkedList;
import java.util.List;
import java.util.Map;

/**
 * wrapper class to manage a list of scopes (or sub scopes)
 */
public class ChatScopeList implements Iterable<ChatScope> {

	private List<ChatScope> scopes = new LinkedList<ChatScope>();
	private Map<Integer, ChatScope> scopesById = new HashMap<Integer, ChatScope>();

	public void add(ChatScope scope) {
		scopes.add(scope);
		scopesById.put(scope.getId(), scope);
	}

	public ChatScope getScopeById(int id) {
		return scopesById.get(id);
	}

	public void remove(ChatScope scope) {
		scopes.remove(scope);
		scopesById.remove(scope.getId());
	}

	public Iterator<ChatScope> iterator() {
		return scopes.iterator();
	}

	public void notify(Message message) {
		for (ChatScope scope : scopes) {
			scope.getSubscibers().notify(message);
		}
	}
}

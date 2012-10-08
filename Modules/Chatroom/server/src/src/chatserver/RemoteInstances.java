package chatserver;

import java.util.HashMap;
import java.util.Iterator;
import java.util.Map;

/**
 * Wrapper for managing a list of remote instances
 */
public class RemoteInstances implements Iterable<RemoteInstance> {

	/**
	 * lookup map for finding an instance by name
	 */
	private Map<String, RemoteInstance> instancesByName = new HashMap<String, RemoteInstance>();
	/**
	 * lookup map for finding an instance by hash
	 */
	private Map<String, RemoteInstance> instancesByHash = new HashMap<String, RemoteInstance>();

	public void registerRemoteInstance(RemoteInstance instance) {
		instancesByHash.put(instance.getHash(), instance);
		instancesByName.put(instance.getName(), instance);
	}

	public RemoteInstance getRemoteInstanceByName(String name) {
		return instancesByName.get(name);
	}

	public RemoteInstance getRemoteInstanceByHash(String hash) {
		return instancesByHash.get(hash);
	}

	public void removeRemoteInstance(RemoteInstance instance) {
		instancesByHash.remove(instance.getHash());
		instancesByName.remove(instance.getName());
	}

	public Iterator<RemoteInstance> iterator() {
		return instancesByHash.values().iterator();
	}
}

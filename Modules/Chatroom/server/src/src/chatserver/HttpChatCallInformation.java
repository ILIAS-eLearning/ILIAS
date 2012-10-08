package chatserver;

/**
 * Class to store basic information of a request to the chatserver
 */
public class HttpChatCallInformation {

	/**
	 * the instance that was called
	 */
	private RemoteInstance instance;
	/**
	 * the scope that was called
	 */
	private ChatScope scope;
	/**
	 * the action to execute
	 */
	private String action;
	/**
	 * the additional parameters (e.g. a chat message or a user id to kick)
	 */
	private Parameters params;

	public HttpChatCallInformation(RemoteInstance instance, ChatScope scope, String action, Parameters params) {
		this.instance = instance;
		this.scope = scope;
		this.action = action;
		this.params = params;
	}

	/**
	 * THIS IS NOT A SINGLETON!
	 * 
	 * @todo bad name for getting the remote instance
	 * 
	 * @return 
	 */
	public RemoteInstance getInstance() {
		return instance;
	}

	public ChatScope getScope() {
		return scope;
	}

	public String getAction() {
		return action;
	}

	public Parameters getParams() {
		return params;
	}
}

package chatserver.handler;

import chatserver.HttpChatCallInformation;
import chatserver.Message;
import chatserver.MissingArgumentException;
import chatserver.ActionHandlerException;
import chatserver.ActionHandler;
import chatserver.ChatScope;
import chatserver.RemoteInstance;
import chatserver.Subscriber;
import java.io.IOException;
import java.io.InputStream;
import java.io.OutputStream;
import java.net.URLConnection;
import java.util.HashMap;
import java.util.Map;
import java.util.logging.Level;
import java.util.logging.Logger;

/**
 * handler for removing a user from a scope
 */
public class KickActionHandler implements ActionHandler {

	public Map<String, Object> handle(HttpChatCallInformation info) throws ActionHandlerException {
		// there must be a message given
		// the message will be sent to the user who is kicked
		if (info.getParams().containsKey("message") == false) {
			throw new ActionHandlerException(new MissingArgumentException("no message given"));
		}

		// user id of the user to kcik
		int userToKick = info.getParams().getInt("userToKick");

		// subscriber object of the user to kick
		Subscriber user = info.getScope().getSubscibers().getSubscriberById(userToKick);

		// send kick message BEFORE the user is detatched from the scope
		info.getScope().getSubscibers().notify(new Message(info.getParams().get("message")));
		// detach user
		info.getScope().detatchSubscriber(info.getScope().getSubscibers().getSubscriberById(userToKick));

		try {
			// notify the remote instance about detatching the user
			sendFeedback(info.getInstance(), info.getScope(), user);
		} catch (Exception e) {
			//e.printStackTrace();
			throw new ActionHandlerException(e);
		}

		Map<String, Object> result = new HashMap<String, Object>();
		result.put("success", true);

		return result;
	}

	/**
	 * POSTs the kick information to the remote instance
	 * 
	 * @param instance
	 * @param scope
	 * @param user
	 * @throws IOException 
	 */
	private void sendFeedback(RemoteInstance instance, ChatScope scope, Subscriber user) throws IOException {

		String query = "task=disconnectedUsers&handledAction=kick&scope[" + scope.getId() + "]=" + user.getId();

		URLConnection connection = instance.getFeedbackConnection("");
		Logger.getLogger("default").log(Level.INFO, "[{0}] Calling {1} for disconnected users", new Object[]{instance.getIliasClient(), connection.getURL()});
		Logger.getLogger("default").log(Level.INFO, "[{0}] Body {1}", new Object[]{instance.getIliasClient(), query});
		connection.setDoOutput(true); // Triggers POST.
		connection.setRequestProperty("Accept-Charset", "utf-8");
		connection.setRequestProperty("Content-Type", "application/x-www-form-urlencoded;charset=utf-8");
		OutputStream output = null;
		InputStream in = null;
		try {
			output = connection.getOutputStream();
			output.write(query.getBytes("utf-8"));
			in = connection.getInputStream();
		} catch (Exception e) {
			Logger.getLogger("default").log(Level.SEVERE, null, e);
		} finally {
			if (output != null) {
				try {
					output.close();
				} catch (IOException e) {
					Logger.getLogger("default").log(Level.SEVERE, null, e);
				}
			}
			if (in != null) {
				try {
					in.close();
				} catch (IOException e) {
					Logger.getLogger("default").log(Level.SEVERE, null, e);
				}
			}
		}
	}
}

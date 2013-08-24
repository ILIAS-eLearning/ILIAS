package chatserver;

import java.io.IOException;
import java.io.OutputStream;
import java.net.URLConnection;
import java.text.SimpleDateFormat;
import java.util.*;
import java.util.concurrent.Executors;
import java.util.concurrent.ScheduledExecutorService;
import java.util.concurrent.ScheduledFuture;
import java.util.concurrent.TimeUnit;
import java.util.logging.Level;
import java.util.logging.Logger;
import javax.sound.midi.MidiDevice;

/**
 * Quick and dirty session handler
 */
public class HttpSessionGC {
	private final SimpleDateFormat sdf = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss");
	private final ScheduledExecutorService scheduler = Executors.newScheduledThreadPool(1);
	private final RemoteInstances instances;
	private ScheduledFuture<?> gcHandle;
	/**
	 * sessions times out after 20 seconds
	 */
	private long sessionTimeout = 20000;

	public HttpSessionGC(RemoteInstances instances) {
		this.instances = instances;
	}

	/**
	 * starts the background thread
	 */
	public void startGC() {

		final Runnable gc = new Runnable() {
			
			public void run() {
				// storage for users to disconnect
				/**
				 *  @todo currently it seems that the list is never emptied?
				 */
				Map<RemoteInstance, List<DisconnectTupple>> usersToDisconnect = new HashMap<RemoteInstance, List<DisconnectTupple>>();
				for (RemoteInstance instance : instances) {
					// storage for users to disconnect by instance
					List<DisconnectTupple> disconnectsByInstance = new LinkedList<DisconnectTupple>();

					// search all scopes of the current instance
					for (ChatScope scope : instance.getScopeList()) {

						Iterator<Subscriber> subscribers = scope.getSubscibers().iterator();

						// disconnects by scope
						DisconnectTupple disconnectInfo = new DisconnectTupple(scope, new SubscriberList());

						while (subscribers.hasNext()) {
							Subscriber subscriber = subscribers.next();
							// find all subscribers with a timed out session
							if (System.currentTimeMillis() - subscriber.getLastConnect() > sessionTimeout) {
								Logger.getLogger("default").log(
									Level.INFO,
									"[{0}] Will remove subscriber {1} fro scope {2}, Last connect: {3}, Current datetime: {4}",
									new Object[]{
										instance.getIliasClient(),
										scope.getId(),
										subscriber.getId(),
										sdf.format(new Date(subscriber.getLastConnect())),
										sdf.format(new Date(System.currentTimeMillis()))
									}
								);
								// remove the subscriber from the scope
								subscribers.remove();
								Logger.getLogger("default").log(
									Level.INFO,
									"[{0}] Removed subscriber {1} from subscriber list in scope {2}",
									new Object[] {
										instance.getIliasClient(),
										subscriber.getId(),
										scope.getId()
									}
								);
								// add the subscriber to the "this-users-must-be-disconnected"-list
								disconnectInfo.getSubscriberList().add(subscriber);
							}
						}

						// merge scope disconnects with instance disconnects
						if (disconnectInfo.getSubscriberList().size() > 0) {
							disconnectsByInstance.add(disconnectInfo);
						}
					}

					// merge instance disconnects with global disconnects
					if (disconnectsByInstance.size() > 0) {
						usersToDisconnect.put(instance, disconnectsByInstance);
					}
				}
				
				try {
					// push disconnect information to the remote instance (e.g. ILIAS)
					sendFeedback(usersToDisconnect);
				} catch (IOException ex) {
					Logger.getLogger("default").log(Level.SEVERE, null, ex);
				}
			}

			/**
			 * notifies the remote instances of users that have been disconnected
			 * 
			 * that is needed to display the current "who is online" status or
			 * how many users are in a room (for list guis)
			 */
			private void sendFeedback(Map<RemoteInstance, List<DisconnectTupple>> data) throws IOException {
				for (RemoteInstance instance : data.keySet()) {
					// create query string with all user ids to disconnect
					String query = "task=disconnectedUsers&";
					for (DisconnectTupple tupple : data.get(instance)) {
						query += "scope[" + tupple.getScope().getId() + "]=";
						for (Subscriber subscriber : tupple.getSubscriberList()) {
							query += subscriber.getId() + ",";
						}
						query += "&";
					}

					URLConnection connection = instance.getFeedbackConnection("");
					Logger.getLogger("default").finer("Calling " + connection.getURL() + " for disconnected users");
					connection.setDoOutput(true); // Triggers POST.
					connection.setRequestProperty("Accept-Charset", "utf-8");
					connection.setRequestProperty("Content-Type", "application/x-www-form-urlencoded;charset=utf-8");
					OutputStream output = null;
					try {
						// send as post
						output = connection.getOutputStream();
						output.write(query.getBytes("utf-8"));
					} finally {
						if (output != null) {
							try {
								output.close();
							} catch (IOException logOrIgnore) {
							}
						}
					}

					/*
					// for debugging
					InputStream in = connection.getInputStream();
					int letter;
					while (-1 != (letter = in.read())) {
						//System.out.print((char) letter);
					}
					 */

				}
			}
		};

		gcHandle = scheduler.scheduleAtFixedRate(gc, 10, 10, TimeUnit.SECONDS);
	}

	public void stopGC() {
		gcHandle.cancel(true);
	}
}

/**
 * Wrapper around a list of subscribers to disconnect after a session timeout
 */
class DisconnectTupple {

	private ChatScope scope;
	private SubscriberList subscriberList;

	public DisconnectTupple(ChatScope scope, SubscriberList subscriberList) {
		this.scope = scope;
		this.subscriberList = subscriberList;
	}

	public ChatScope getScope() {
		return scope;
	}

	public SubscriberList getSubscriberList() {
		return subscriberList;
	}
}

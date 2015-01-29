package chatserver;

import com.sun.net.httpserver.HttpExchange;
import com.sun.net.httpserver.HttpServer;
import com.sun.net.httpserver.HttpsConfigurator;
import com.sun.net.httpserver.HttpsParameters;
import com.sun.net.httpserver.HttpsServer;
import java.io.FileInputStream;
import java.io.IOException;
import java.net.InetSocketAddress;
import java.security.KeyManagementException;
import java.security.KeyStore;
import java.security.KeyStoreException;
import java.security.NoSuchAlgorithmException;
import java.security.UnrecoverableKeyException;
import java.security.cert.CertificateException;
import java.util.Map;
import java.util.Properties;
import java.util.concurrent.Executors;
import java.util.logging.Level;
import java.util.logging.Logger;
import javax.net.ssl.KeyManagerFactory;
import javax.net.ssl.SSLContext;
import javax.net.ssl.SSLParameters;
import javax.net.ssl.TrustManagerFactory;

/**
 * Base handler for receiving http calls
 * 
 * @todo bad class name
 */
public class HttpUserHandler implements ChatHandler {

	protected HttpServer server;
	protected RemoteInstances instances;

	public HttpUserHandler(RemoteInstances instances) {
		this.instances = instances;
	}

	/**
	 * Tries to execute the action described by the info object.
	 * 
	 * The target handler is loaded by its name with the action name as class
	 * name prefix (e.g. the action Poll will require a handler at
	 * chatserver.handler.PollActionHandler)
	 * 
	 * @param info
	 * @return
	 * @throws ClassNotFoundException
	 * @throws InstantiationException
	 * @throws IllegalAccessException
	 * @throws ActionHandlerException 
	 */
	private Map<String, Object> handleCall(HttpChatCallInformation info) throws ClassNotFoundException, InstantiationException, IllegalAccessException, ActionHandlerException {
		ActionHandler handler = (ActionHandler) Class.forName("chatserver.handler." + info.getAction() + "ActionHandler").newInstance();
		return handler.handle(info);
	}

	/**
	 * launches a new server instance
	 * 
	 * @param address
	 * @param props
	 * @throws IOException 
	 */
	public void start(InetSocketAddress address, Properties props) throws IOException {
		boolean useSSL = props.get("https").equals("1");
		final String[] allowedBackendHosts = ((String) props.get("privileged_hosts")).split(",");

		// start http instance
		if (!useSSL) {
			this.server = HttpServer.create(address, 0);
		}
		// or start https instance
		else {
			try {
				final SSLContext sslContext = SSLContext.getInstance("TLSv1");

				// initialise the keystore
				char[] keypass = ((String) props.get("keypass")).toCharArray();
				char[] storepass = ((String) props.get("storepass")).toCharArray();
				KeyStore ks = KeyStore.getInstance("PKCS12");
				ks.load(new FileInputStream((String) props.get("keystore")), storepass);

				// setup the key manager factory
				KeyManagerFactory kmf = KeyManagerFactory.getInstance("SunX509");
				kmf.init(ks, keypass);

				// setup the trust manager factory
				TrustManagerFactory tmf = TrustManagerFactory.getInstance("SunX509");
				tmf.init(ks);

				HttpsServer sslserver = HttpsServer.create(address, 0);
				// setup the HTTPS context and parameters
				sslContext.init(kmf.getKeyManagers(), tmf.getTrustManagers(), null);
				sslserver.setHttpsConfigurator(new HttpsConfigurator(sslContext) {
					@Override
					public void configure(HttpsParameters params) {
						try {
							SSLParameters sslparams = sslContext.getDefaultSSLParameters();
							sslparams.setProtocols(
								new String[]{"TLSv1", "TLSv1.1", "TLSv1.2"}
							);
							params.setSSLParameters(sslparams);
						} catch (Exception ex) {
							Logger.getLogger("default").severe("Failed to create HTTPS port... exit");
							System.exit(1);
						}
					}
				});
				this.server = sslserver;
			} catch (KeyManagementException ex) {
				Logger.getLogger(HttpUserHandler.class.getName()).log(Level.SEVERE, null, ex);
			} catch (UnrecoverableKeyException ex) {
				Logger.getLogger(HttpUserHandler.class.getName()).log(Level.SEVERE, null, ex);
			} catch (NoSuchAlgorithmException ex) {
				Logger.getLogger(HttpUserHandler.class.getName()).log(Level.SEVERE, null, ex);
			} catch (CertificateException ex) {
				Logger.getLogger(HttpUserHandler.class.getName()).log(Level.SEVERE, null, ex);
			} catch (KeyStoreException ex) {
				Logger.getLogger(HttpUserHandler.class.getName()).log(Level.SEVERE, null, ex);
			} catch (RuntimeException e) {
			}
		}
		// create the /backend context (e.g. http://mychat/backend/...)
		// this context is used for actions like connecting a user to the
		// chat server or kick a user from a scope. also posting a message
		// must be invoked using the backend handler
		// this commands must be executed from a client (e.g. ILIAS but not the user itself)
		// that is in the allowedBackendHosts lists (privileged hosts)
		this.server.createContext("/backend", new HttpJsonHandler(this.instances) {

			public Map<String, Object> handleRequest(HttpExchange he, HttpChatCallInformation info) throws Exception {
				String remote = he.getRemoteAddress().getAddress().getHostAddress();
				Logger.getLogger("default").finer("Backend connection from " + remote + ": " + he.getRequestURI().toString());

				// check if current host is allowed to execute backend commands
				for (String allowedHost : allowedBackendHosts) {
					if (allowedHost.equals(remote)) {
						////////////////////////
						// execute the action //
						////////////////////////
						Logger.getLogger("default").finer("Accepted connection from " + remote + ": " + he.getRequestURI().toString());
						return handleCall(info);
					}
				}

				Logger.getLogger("default").warning("Refused connection from " + remote + " (not in the list of allowed hosts): " + he.getRequestURI().toString());
				throw new Exception(remote + " is not in the list of allowed hosts");
			}
		});
		// create the /backend context (e.g. http://mychat/backend/...)
		// this context is used for default actions
		// CURRENTLY ONLY POLLING FOR NEW MESSAGES IS SUPPORTED
		// all other tasks must be executed by the owning remote instance
		this.server.createContext("/frontend", new HttpJsonHandler(this.instances) {

			public Map<String, Object> handleRequest(HttpExchange he, HttpChatCallInformation info) throws Exception {
				Logger.getLogger("default").finest("Frontend connection from " + he.getRemoteAddress().getAddress().getHostAddress().toString() + ": " + he.getRequestURI().toString());
				if (!info.getAction().equals("Poll") && !info.getAction().equals("Status")) {
					Logger.getLogger("default").warning("Access from " + he.getRemoteAddress().getAddress().getHostAddress().toString() + " denied to handler: " + info.getAction());
					throw new Exception(info.getAction() + " is not accessible by frontend call. Use /backend instead.");
				}
				////////////////////////
				// execute the action //
				////////////////////////
				return handleCall(info);
			}
		});

		this.server.setExecutor(Executors.newFixedThreadPool(10));
		this.server.start();

		HttpSessionGC gcHandler = new HttpSessionGC(instances);
		gcHandler.startGC();
	}
}

package chatserver;

import java.io.BufferedInputStream;
import java.io.File;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.IOException;
import java.io.InputStream;
import java.io.OutputStream;
import java.net.InetSocketAddress;
import java.net.MalformedURLException;
import java.net.URLConnection;
import java.util.Properties;
import java.util.Vector;
import java.util.logging.ConsoleHandler;
import java.util.logging.FileHandler;
import java.util.logging.Level;
import java.util.logging.Logger;

/**
 * Starting class for the chat server
 */
public class Main {

	private RemoteInstances instances = new RemoteInstances();

	public Main(Properties serverProperties, Vector<Properties> instanceProperties) throws FileNotFoundException, IOException {
		/**
		 * register all instances
		 */
		for (Properties instanceProps : instanceProperties.toArray(new Properties[]{})) {
			RemoteInstance instance = RemoteInstance.fromProperties(instanceProps);
			instances.registerRemoteInstance(instance);
			Logger.getLogger("default").info("loaded instance file " + instanceProps.getProperty("origin"));
		}

		initializeServer();

		HttpUserHandler handler = new HttpUserHandler(instances);

		InetSocketAddress address = new InetSocketAddress(serverProperties.getProperty("host"), Integer.parseInt(serverProperties.getProperty("port")));

		handler.start(address, serverProperties);
	}

	final public void initializeServer() throws MalformedURLException, IOException {
		for (RemoteInstance instance : instances) {
			String query = "task=serverStarted";

			instance.login();
			//URL url = instance.getFeedbackURL("");
			URLConnection connection = instance.getFeedbackConnection(""); //= url.openConnection();
			//System.out.println(connection.getURL().toString());
			connection.setDoOutput(true);
			connection.setRequestProperty("Accept-Charset", "utf-8");
			connection.setRequestProperty("Content-Type", "application/x-www-form-urlencoded;charset=utf-8");
			OutputStream output = null;
			try {
				output = connection.getOutputStream();
				output.write(query.getBytes("utf-8"));
			} catch (Exception e) {
				e.printStackTrace();
			} finally {
				if (output != null) {
					try {
						output.close();
					} catch (IOException logOrIgnore) {
						logOrIgnore.printStackTrace();
					}
				}
			}

			InputStream in = connection.getInputStream();
			int letter;
			while (-1 != (letter = in.read())) {
				//System.out.print((char) letter);
			}

		}
	}

	/**
	 * Start the chatserver.
	 * 
	 * The first argument must point the the server.properties file which contains
	 * the server settings. An example file is included in the main directory
	 * (settings.properties)
	 * 
	 * After the server configuration there must be at least one parameter that
	 * points to an instance (ILIAS) configuration file. Additional instance configuration files
	 * may be added. Each instance has its own chatrooms. Intercommunication is
	 * currently not supported.
	 * 
	 * @param args the command line arguments
	 */
	public static void main(String[] args) throws IOException {
		Properties props = new Properties();
		
		// load the server configuration
		BufferedInputStream bis = new BufferedInputStream(new FileInputStream(args[0]));
		props.load(bis);
		bis.close();

		Logger logger = Logger.getLogger("default");
		logger.setUseParentHandlers(false);

		// @todo this setting is currently not documented and not configurable in ILIAS
		// appends an optional file logger
		if (props.getProperty("log_path") != null) {
			FileHandler fh = new FileHandler(props.getProperty("log_path"));
			fh.setLevel(Level.ALL);
			logger.addHandler(fh);
		}

		ConsoleHandler ch = new ConsoleHandler();
		ch.setLevel(Level.ALL);

		logger.addHandler(ch);

		logger.info("Server starting");

		/**
		 * list of instance properties
		 */
		Vector<Properties> oProperties = new Vector<Properties>();

		try {
			// load the instance files
			for (int i = 1; i < args.length; ++i) {
				Properties oProp = new Properties();
				oProp.load(new BufferedInputStream(new FileInputStream(new File(args[i]))));
				oProp.put("origin", args[i]);
				oProperties.add(oProp);
			}
		} catch (Exception e) {
			logger.log(Level.SEVERE, "Failed to load properties file", e);
			System.exit(1);
		}

		if (oProperties.size() <= 0) {
			logger.log(Level.WARNING, "No client configurations given... exit");
			System.exit(1);
		}

		// launch the server
		new Main(props, oProperties);
	}
}

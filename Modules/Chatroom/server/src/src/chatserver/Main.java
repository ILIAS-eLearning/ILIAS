package chatserver;

import java.io.*;
import java.net.InetSocketAddress;
import java.net.MalformedURLException;
import java.net.URLConnection;
import java.util.Properties;
import java.util.Vector;
import java.util.logging.*;

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
			Logger.getLogger("default").log(Level.INFO, "Loaded instance file {0}", instanceProps.getProperty("origin"));
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

			URLConnection connection = instance.getFeedbackConnection(""); //= url.openConnection();

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
		Logger logger = Logger.getLogger("default");
		logger.setUseParentHandlers(false);

		ConsoleHandler ch = new ConsoleHandler();
		ch.setLevel(Level.ALL);
		logger.addHandler(ch);
		
		if (args.length < 2) {
			logger.log(Level.WARNING, getUsage());
			System.exit(1);
		}
            
		Properties props = new Properties();
		
		// load the server configuration
		try {
			BufferedInputStream bis = new BufferedInputStream(new FileInputStream(args[0]));
			props.load(bis);
			bis.close();
		} catch(IOException e) {
			logger.log(Level.SEVERE, "Failed to load server properties file", e);
			System.exit(1);
		}
		
		// @todo this setting is currently not documented and not configurable in ILIAS
		// appends an optional file logger
		if (props.getProperty("log_path") != null) {
			FileHandler fh;
			try {
				fh = new FileHandler(props.getProperty("log_path"));
				fh.setFormatter(new SimpleFormatter());
				fh.setLevel(Level.ALL);
				logger.addHandler(fh);
			} catch (IOException e) {
				Logger.getLogger("default").info("Could not attach file logger: " + e.getMessage());
			} catch (SecurityException e) {
				Logger.getLogger("default").info("Could not attach file logger: " + e.getMessage());
			}
		} else {
			logger.log(Level.INFO, "Hint: You can enable file logging by adding the property \"log_path\" to {0}", args[0]);
		}
		
		if (props.getProperty("log_level") != null) {
			try {
				logger.setLevel(Level.parse(props.getProperty("log_level")));
			} catch (IllegalArgumentException e) {
				Logger.getLogger("default").info("Passed log level not supported, fallback to default");
			}
		} else {
			logger.log(Level.INFO, "Hint: You can set the log level by adding the property \"log_level\" to {0}", args[0]);
		}

		logger.info("Server starting");

		/**
		 * list of instance properties
		 */
		Vector oProperties = new Vector<Properties>();

		try {
			// load the instance files
			for (int i = 1; i < args.length; ++i) {
				Properties oProp = new Properties();
				oProp.load(new BufferedInputStream(new FileInputStream(new File(args[i]))));
				oProp.put("origin", args[i]);
				oProperties.add(oProp);
			}
		} catch (Exception e) {
			logger.log(Level.SEVERE, "Failed to load client properties file", e);
			System.exit(1);
		}

		if (oProperties.size() <= 0) {
			logger.log(Level.WARNING, "No client configurations given... exit");
			System.exit(1);
		}
		// launch the server
		Main main = new Main(props, oProperties);
	}
	
	/**
	 * 
	 * @return String usage
	 */
	private static String getUsage() {
		return
			"== Usage ==\n\nSingle ILIAS client:\n\t" +
			"java -jar Chatserver.jar \"path/to/server.properties\" \"path/to/client.properties\"\n\n" +
			"Multiple ILIAS clients:\n\t" +
			"java -jar Chatserver.jar \"path/to/server.properties\" \"path/to/first/client.properties\" \"path/to/second/client.properties\" ....";
	}
}

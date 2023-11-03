/*
        +-----------------------------------------------------------------------------+
        | ILIAS open source                                                           |
        +-----------------------------------------------------------------------------+
        | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
        |                                                                             |
        | This program is free software; you can redistribute it and/or               |
        | modify it under the terms of the GNU General Public License                 |
        | as published by the Free Software Foundation; either version 2              |
        | of the License, or (at your option) any later version.                      |
        |                                                                             |
        | This program is distributed in the hope that it will be useful,             |
        | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
        | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
        | GNU General Public License for more details.                                |
        |                                                                             |
        | You should have received a copy of the GNU General Public License           |
        | along with this program; if not, write to the Free Software                 |
        | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
        +-----------------------------------------------------------------------------+
*/

package de.ilias;

import de.ilias.services.rpc.RPCServer;
import de.ilias.services.settings.*;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;
import org.apache.xmlrpc.XmlRpcException;
import org.apache.xmlrpc.client.XmlRpcClient;
import org.apache.xmlrpc.client.XmlRpcClientConfigImpl;
import org.apache.xmlrpc.client.XmlRpcCommonsTransportFactory;

import java.io.IOException;
import java.net.MalformedURLException;
import java.net.URL;
import java.util.Vector;


/**
 * Main class
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
public class ilServer {

	private static final Logger logger = LogManager.getLogger(ilServer.class );
	private final String version = "4.4.0.1";
	
	private final String[] arguments;
	private String command;
	

	public ilServer(String[] args) {

		arguments = args;
	}

	public static void main(String[] args)
	{
		ilServer server = null;
		server = new ilServer(args);
		boolean success = server.handleRequest();
		System.exit(success ? 0 : 1);
	}

	private boolean handleRequest()
	{
		Boolean status;

		if(arguments.length < 1) {
			logger.error(this.getUsage());
			return false;
		}
		if (arguments.length >= 1) {
			LogConfigManager logConfigManager = new LogConfigManager();
			try {
				logConfigManager.parse(arguments[0]);
				logConfigManager.initLogManager();
			} catch (ConfigurationException e) {
				logger.error("Logging to ?");
			}
		}

		if(arguments.length == 1) {
			command = arguments[0];
			if(command.compareTo("version") == 0) {
				System.out.println("ILIAS java server version \"" + version + "\"");
				return true;
			}
		}
		command = arguments[1];
		if(command.compareTo("start") == 0) {
			if(arguments.length != 2) {
				System.err.println("Usage: java -jar ilServer.jar PATH_TO_SERVER_INI start");
				return false;
			}
			return startServer();
		}
		else if(command.compareTo("stop") == 0) {
			if(arguments.length != 2) {
				System.err.println("Usage: java -jar ilServer.jar PATH_TO_SERVER_INI stop");
				return false;
			}
			return stopServer();
		}
		else if(command.compareTo("createIndex") == 0) {
			if(arguments.length != 3) {
				System.err.println("Usage java -jar ilServer.jar PATH_TO_SERVER_INI createIndex CLIENT_KEY");
				return false;
			}
			status = createIndexer();
			logger.info("Finished creation of new index for client {}", arguments[2]);
			return status;
		}
		else if(command.compareTo("updateIndex") == 0) {
			if(arguments.length < 3) {
				System.err.println("Usage java -jar ilServer.jar PATH_TO_SERVER_INI updateIndex CLIENT_KEY");
				return false;
			}
			return updateIndexer();
		}
		else if(command.compareTo("search") == 0) {
			if(arguments.length != 4) {
				System.err.println("Usage java -jar ilServer.jar PATH_TO_SERVER_INI CLIENT_KEY search QUERY_STRING");
				return false;
			}
			return startSearch();
			
		}
		else if(command.compareTo("status") == 0) {
			if(arguments.length != 2) {
				System.err.println("Usage java -jar ilServer.jar PATH_TO_SERVER_INI status");
				return false;
			}
			return getStatus();
		}
		else {
			System.err.println(getUsage());
			return false;
		}
	}

	private boolean createIndexer() {

		XmlRpcClient client;
		CommonsIniFileParser parser;
		
		try {
			parser = new CommonsIniFileParser();
			parser.parseSettings(arguments[0],true);
			
			if(!ClientSettings.exists(arguments[2])) {
				throw new ConfigurationException("Unknown client given: " + arguments[2]);
			}

			client = initRpcClient();
			Vector params = new Vector();
			params.add(arguments[2]);
			params.add(false);
			client.execute("RPCIndexHandler.index",params);
			logger.info("Finished indexing");
		}
		catch (Exception e) {
			logger.error(e.getMessage());
			System.exit(1);
		}
		return false;
	}

	private boolean updateIndexer() {

		XmlRpcClient client;
		CommonsIniFileParser parser;

		try {
			parser = new CommonsIniFileParser();
			parser.parseSettings(arguments[0],true);
			
			if(!ClientSettings.exists(arguments[2])) {
				throw new ConfigurationException("Unknown client given: " + arguments[2]);
			}

			client = initRpcClient();
			Vector params = new Vector();
			params.add(arguments[2]);
			params.add(true);
			client.execute("RPCIndexHandler.index",params);
			logger.info("Finished indexing");
			return true;
		} 
		catch (Exception e) {
			logger.error(e.getMessage());
			System.exit(1);
		}
		return false;
	}

	/**
	 * @return
	 */
	@SuppressWarnings("unchecked")
	private boolean startSearch() {

		XmlRpcClient client;
		CommonsIniFileParser parser;

		try {
			parser = new CommonsIniFileParser();
			parser.parseSettings(arguments[0],true);
			
			if(!ClientSettings.exists(arguments[2])) {
				throw new ConfigurationException("Unknown client given: " + arguments[2]);
			}
			
			client = initRpcClient();
			Vector params = new Vector();
			params.add(arguments[2]);
			params.add(arguments[3]);
			params.add(1);
			String response  = (String) client.execute("RPCSearchHandler.search",params);
			System.out.println(response);
			return true;
		} 
		catch (Exception e) {
			logger.error(e.getMessage());
			System.exit(1);
		}
		return false;
	}

	
	/**
	 * Start RPC services
	 */
	private boolean startServer() {
		
		ServerSettings settings;
		RPCServer rpc;
		XmlRpcClient client;
		CommonsIniFileParser parser;
		String status;

		try {
			logger.debug("Start parsing");
			parser = new CommonsIniFileParser();
			logger.debug("Parser created");
			parser.parseSettings(arguments[0], true);
			logger.debug("Parser parsed");
			settings = ServerSettings.getInstance();
			client = initRpcClient();

			// Check if server is already running
			try {
				status = (String) client.execute("RPCAdministration.status",new Vector());
				System.err.println("Server already started. Aborting");
				System.exit(1);
			}
			catch(XmlRpcException e) {
				logger.info("No server running. Starting new instance...");
			}
			
			logger.info("Listening on {}:{}", settings.getHost(), settings.getPort());
			rpc = RPCServer.getInstance(settings.getHost(),settings.getPort());
			logger.info("Server started");
			rpc.start();
			
			client = initRpcClient();
			client.execute("RPCAdministration.start",new Vector());

			//Vector params = new Vector();
			//params.add(arguments[0]);
			//client.execute("RPCAdministration.initLogManager", params);

			// Check if webserver is alive
			// otherwise stop execution
			while(true) {
				Thread.sleep(3000);
				if(!rpc.isAlive()) {
					rpc.shutdown();
					break;
				}
			}
			logger.info("WebServer shutdown. Aborting...");
			return true;
			
		}
		catch (ConfigurationException e) {
			System.exit(1);
			return false;
		} 
		catch (InterruptedException e) {
			logger.error("VM did not allow to sleep. Aborting!");
		} 
		catch (XmlRpcException e) {
			System.out.println("Error starting server: " + e);
			System.exit(1);
		} 
		catch (IOException e) {
			logger.error("IOException " + e.getMessage());
		}
		catch (Exception e) {
			logger.error("IOException " + e.getMessage());			
		}
		catch(Throwable e) {
			logger.error("IOException " + e.getMessage());			
		}
		return false;
	}

	/**
	 * Call RPC stop method, which will stop the WebServer 
	 * and after that stop the execution of the main thread
	 * 
	 */
	@SuppressWarnings("unchecked")
	private boolean stopServer() {
		
		XmlRpcClient client;
		CommonsIniFileParser parser;

		try {
			parser = new CommonsIniFileParser();
			parser.parseSettings(arguments[0],false);
			
			client = initRpcClient();
			logger.debug("Client execute");
			client.execute("RPCAdministration.stop",new Vector());
			logger.info("Server stopped");
			return true;
		} 
		catch (ConfigurationException e) {
			logger.error("Configuration " + e.getMessage());
		} 
		catch (XmlRpcException e) {
			logger.error("XMLRPC " + e.getMessage());
		} 
		catch (IOException e) {
			logger.error("IOException " + e.getMessage());
		}
		return false;
	}
	
	private boolean getStatus() {
		
		XmlRpcClient client;
		CommonsIniFileParser parser;
		ServerSettings settings;
		
		String status;

		try {
			parser = new CommonsIniFileParser();
			parser.parseSettings(arguments[0],false);

			settings = ServerSettings.getInstance();
			client = initRpcClient();
			status = (String) client.execute("RPCAdministration.status",new Vector());
			System.out.println(status);
			logger.info("RPC Status is {}", status);
			status = ilServerStatus.getStatus();
			logger.info("Main Status is {}", status);
			return true;
		} 
		catch (ConfigurationException e) {
			logger.error("Configuration " + e.getMessage());
		} 
		catch (XmlRpcException e) {
			System.out.println(ilServerStatus.STOPPED);
			System.exit(1);
		} 
		catch (IOException e) {
			System.out.println(ilServerStatus.STOPPED);
			System.exit(1);
		}
		return false;
	}
	
	/**
	 * 
	 * @return String usage
	 */
	private String getUsage() {
		
		return "Usage: java -jar ilServer.jar PATH_TO_SERVER_INI start|stop|createIndex|updateIndex|search PARAMS";
	}
	
	/**
	 * 
	 * @return	XmlRpcClient
	 * @throws ConfigurationException 
	 * @throws MalformedURLException 
	 */
	private XmlRpcClient initRpcClient() throws ConfigurationException, MalformedURLException
	{
		XmlRpcClient client;
		XmlRpcClientConfigImpl config;
		ServerSettings settings;
		
		settings = ServerSettings.getInstance();
		config = new XmlRpcClientConfigImpl();
		config.setServerURL(new URL(settings.getServerUrl()));
		config.setConnectionTimeout(10000);
		config.setReplyTimeout(0);

		client = new XmlRpcClient();
		client.setTransportFactory(new XmlRpcCommonsTransportFactory(client));
		client.setConfig(config);
		
		return client;
	}
}


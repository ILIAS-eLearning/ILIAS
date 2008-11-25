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

import java.io.IOException;
import java.net.MalformedURLException;
import java.util.Vector;

import org.apache.log4j.BasicConfigurator;
import org.apache.log4j.Level;
import org.apache.log4j.Logger;
import org.apache.xmlrpc.XmlRpcClient;
import org.apache.xmlrpc.XmlRpcException;

import de.ilias.services.rpc.RPCServer;
import de.ilias.services.settings.ClientSettings;
import de.ilias.services.settings.ConfigurationException;
import de.ilias.services.settings.IniFileParser;
import de.ilias.services.settings.ServerSettings;


/**
 * 
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
public class ilServer {

	private String[] arguments;
	private String command;
	
	private static final Logger logger = Logger.getLogger(ilServer.class);
	
	/**
	 * @param args
	 */
	public ilServer(String[] args) {

		arguments = args;
	}

	/**
	 * @param args
	 */
	public static void main(String[] args) {
		
		ilServer server = null;
		
		BasicConfigurator.configure();
		logger.setLevel(Level.DEBUG);
		
		server = new ilServer(args);
		server.handleRequest();
	}

	/**
	 * @return success status
	 */
	private boolean handleRequest() {
		
		if(arguments.length < 1) {
			logger.error("Usage: java -jar ilServer.jar start|stop|index|search PARAMS");
			return false;
		}
		command = arguments[0];
		if(command.compareTo("start") == 0) {
			if(arguments.length != 2) {
				logger.error("Usage: java -jar ilServer.jar start PATH_TO_SERVER_INI");
				return false;
			}
			return startServer();
		}
		else if(command.compareTo("stop") == 0) {
			if(arguments.length != 2) {
				logger.error("Usage: java -jar ilServer.jar stop PATH_TO_SERVER_INI");
				return false;
			}
			return stopServer();
		}
		else if(command.compareTo("index") == 0) {
			if(arguments.length != 3) {
				logger.error("Usage java -jar ilServer.jar index PATH_TO_SERVER_INI CLIENT_KEY");
			}
			return startIndexer();
		}
		else if(command.compareTo("search") == 0) {
			if(arguments.length != 4) {
				logger.error("Usage java -jar ilServer.jar search PATH_TO_SERVER_INI CLIENT_KEY QUERY_STRING");
			}
			return startSearch();
			
		}
		else {
			logger.error("Usage: java -jar ilServer.jar start|stop|index|search PARAMS");
			return false;
		}
	}

	/**
	 * @return
	 */
	@SuppressWarnings("unchecked")
	private boolean startIndexer() {

		ServerSettings settings;
		XmlRpcClient client;
		IniFileParser parser;
		
		
		try {
			parser = new IniFileParser();
			parser.parseServerSettings(arguments[1],true);
			
			if(!ClientSettings.exists(arguments[2])) {
				throw new ConfigurationException("Unknown client given: " + arguments[2]);
			}

			settings = ServerSettings.getInstance();
			
			client = new XmlRpcClient(settings.getHost().getHostAddress(),settings.getPort());
			Vector params = new Vector();
			params.add(arguments[2]);
			client.execute("index.refreshIndex",params);
			return true;
		} 
		catch (Exception e) {
			System.err.println(e);
			logger.fatal(e.getMessage());
			System.exit(1);
		}
		return false;
	}

	/**
	 * @return
	 */
	@SuppressWarnings("unchecked")
	private boolean startSearch() {

		ServerSettings settings;
		XmlRpcClient client;
		IniFileParser parser;
		
		
		try {
			parser = new IniFileParser();
			parser.parseServerSettings(arguments[1],true);
			
			if(!ClientSettings.exists(arguments[2])) {
				throw new ConfigurationException("Unknown client given: " + arguments[2]);
			}

			settings = ServerSettings.getInstance();
			
			client = new XmlRpcClient(settings.getHost().getHostAddress(),settings.getPort());
			Vector params = new Vector();
			params.add(arguments[2]);
			params.add(arguments[3]);
			client.execute("search.search",params);
			return true;
		} 
		catch (Exception e) {
			System.err.println(e);
			logger.fatal(e.getMessage());
			System.exit(1);
		}
		return false;
	}

	
	/**
	 * 
	 */
	private boolean startServer() {
		
		RPCServer rpc;
		ServerSettings settings;
		IniFileParser parser;
		
		try {

			parser = new IniFileParser();
			parser.parseServerSettings(arguments[1],true);
			
			settings = ServerSettings.getInstance();
			
			rpc = RPCServer.getInstance(settings.getHost(),settings.getPort());
			rpc.start();
			
			// Check if webserver is alive
			// otherwise stop execution
			while(true) {
				Thread.sleep(3000);
				//logger.debug("Still alive...");
				if(!rpc.isAlive()) {
					rpc.shutdown();
					break;
				}
			}
			logger.info("WebServer shutdown. Aborting...");
			return true;
			
		} 
		catch (ConfigurationException e) {
			logger.error(e.getMessage());
			return false;
		} 
		catch (InterruptedException e) {
			logger.error("VM did not allow to sleep. Aborting!");
			e.printStackTrace();
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
		IniFileParser parser;
		ServerSettings settings;

		try {
			parser = new IniFileParser();
			parser.parseServerSettings(arguments[1],false);
			
			settings = ServerSettings.getInstance();

			client = new XmlRpcClient(settings.getHost().getHostAddress(),settings.getPort());
			client.execute("administration.stop",new Vector());
			return true;
		} 
		catch (MalformedURLException e) {
			logger.error("Malformed URL " + e.getMessage());
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

}
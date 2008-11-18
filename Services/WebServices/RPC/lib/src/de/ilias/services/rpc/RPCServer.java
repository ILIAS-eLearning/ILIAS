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

package de.ilias.services.rpc;

import java.net.InetAddress;

import org.apache.log4j.Logger;
import org.apache.xmlrpc.WebServer;

import de.ilias.lucene.services.lucene.index.RPCIndexHandler;
import de.ilias.lucene.services.lucene.search.RPCSearchHandler;
import de.ilias.services.settings.ConfigurationException;

/**
 * 
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
public class RPCServer {
	
	private static RPCServer instance = null;
	private Logger logger = Logger.getLogger(this.getClass().getName());

	private WebServer server;
	private InetAddress host = null;
	private int port = 0;
	private boolean alive = false;
	

	private RPCServer(InetAddress host, int port) {
		
		setHost(host);
		setPort(port);
		initServer();
	}

	private RPCServer() {
		
	}
	
	public static RPCServer getInstance(InetAddress host, int port) {
		
		if(RPCServer.instance == null) {
			return RPCServer.instance = new RPCServer(host,port);
		}
		return instance;
	}
	
	public static RPCServer getInstance() {
		
		return RPCServer.instance;
	}
	
	/**
	 * @return the host
	 */
	public InetAddress getHost() {
		return host;
	}

	/**
	 * @param host the host to set
	 */
	public void setHost(InetAddress host) {
		this.host = host;
	}

	/**
	 * @return the port
	 */
	public int getPort() {
		return port;
	}

	/**
	 * @param port the port to set
	 */
	public void setPort(int port) {
		this.port = port;
	}

	/**
	 * @return the alive
	 */
	public boolean isAlive() {
		return alive;
	}

	/**
	 * @param alive the alive to set
	 */
	public void setAlive(boolean alive) {
		this.alive = alive;
	}
	
	
	/**
	 * Init WebServer
	 */
	private void initServer() {
		
		logger.debug("Init Webserver...");
		server = new WebServer(port,host);
		addHandlers();
	}
	
	/**
	 * Start Webserver 
	 * @throws ConfigurationException 
	 */
	public void start() throws ConfigurationException {

		try {
			logger.info("Starting ILIAS RPC-Server...");
			server.start();
			logger.debug("Using host :" + getHost().toString());
			logger.debug("Using port :" + getPort());
			logger.info("Waiting for connections...");
			alive = true;
		}
		catch(Exception e) {
			logger.error("Cannot bind to host: " + getHost() + ", port: " + port);
			throw new ConfigurationException(e.getMessage());
		}
	}
	
	/**
	 * Stop Webserver 
	 */
	public void shutdown() {

		logger.debug("Stopping Webserver...");
		server.shutdown();
		alive = false;
	}


	/**
	 * 
	 */
	private void addHandlers() {

		this.server.addHandler("debug",new RPCDebug());
		this.server.addHandler("administration",new RPCAdministration());
		this.server.addHandler("index", new RPCIndexHandler());
		this.server.addHandler("search",new RPCSearchHandler());
		logger.debug("Added RPC-Handlers");
		
	}
}

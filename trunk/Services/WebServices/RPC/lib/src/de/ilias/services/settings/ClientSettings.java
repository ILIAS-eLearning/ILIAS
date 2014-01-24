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

package de.ilias.services.settings;

import java.io.File;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.Map;

import org.apache.log4j.Logger;

/**
 * A singleton for each client configuration 
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
public class ClientSettings {

	protected static Logger logger = Logger.getLogger(ClientSettings.class);
	private static HashMap<String, ClientSettings> instances = new HashMap<String, ClientSettings>();
	

	private String client;
	private String nic;
	private File iliasIniFile;
	private File dataDirectory;
	private File clientIniFile;
	private File absolutePath;
	private File indexPath;
	
	private String dbType;
	private String dbHost;
	private String dbPort;
	private String dbName;
	private String dbUser;
	private String dbPass;
	
	

	/**
	 * @param string
	 * @param string2
	 */
	public ClientSettings(String client, String nic) {
		
		this.client = client;
		this.nic = nic;
	}

	public static synchronized ClientSettings getInstance(String client, String nic) throws ConfigurationException {
		
		return getInstance(client + '_' + nic);
	}

	public static synchronized ClientSettings getInstance(String clientKey) throws ConfigurationException {
		
		
		logger.debug("Using client key " + clientKey);
		if(instances.containsKey(clientKey)) {
			return instances.get(clientKey);
		}
		int posUnderscore;
		if((posUnderscore = clientKey.lastIndexOf("_")) == -1) {
			logger.error("Cannot parse client key: " + clientKey);
			throw new ConfigurationException("Cannot parse client key: " + clientKey);
		}
		
		String nic = clientKey.substring(posUnderscore + 1);
		String client = clientKey.substring(0,posUnderscore);
		logger.debug("Orig: " + clientKey + " Client: " + client + " NIC: " + nic);
		
		instances.put(clientKey,new ClientSettings(client,nic));
		return instances.get(clientKey);
	}
	
	/**
	 * @param string
	 * @return
	 */
	public static boolean exists(String clientKey) {

		return instances.containsKey(clientKey);
	}

	/**
	 * get all clients
	 * @return
	 */
	public static ArrayList<String> getClients() {

		ArrayList<String> clients = new ArrayList<String>();

		for(Map.Entry<String, ClientSettings> entry : instances.entrySet()) {
			clients.add(entry.getKey());
		}
		return clients;
	}

	
	/**
	 * @return the client
	 */
	public String getClient() {
		return client;
	}
	
	public String getClientKey() {
		return client + '_' + getNic();
	}

	/**
	 * @param client the client to set
	 */
	public void setClient(String client) {
		this.client = client;
	}

	/**
	 * @return the nic
	 */
	public String getNic() {
		return nic;
	}

	/**
	 * @param nic the nic to set
	 */
	public void setNic(String nic) {
		this.nic = nic;
	}
	
	/**
	 * @return the iliasIniFile
	 */
	public File getIliasIniFile() {
		return iliasIniFile;
	}
	
	/**
	 * @return the dataDirectory
	 */
	public File getDataDirectory() {
		return dataDirectory;
	}

	/**
	 * @param dataDirectory the dataDirectory to set
	 * @throws ConfigurationException 
	 */
	public void setDataDirectory(String dataDirectory) throws ConfigurationException {

		logger.debug("ILIAS data directory: " + dataDirectory);
		this.dataDirectory = new File(dataDirectory);
		if(!this.dataDirectory.canRead()) {
			logger.error("Cannot read ILIAS data directory: " + this.dataDirectory.getAbsolutePath());
			throw new ConfigurationException("Error reading ILIAS data directory.");
		}
	}
	
	/**
	 * @return the absolutePath
	 */
	public File getAbsolutePath() {
		return absolutePath;
	}

	/**
	 * @param absolutePath the absolutePath to set
	 * @throws ConfigurationException 
	 */
	public void setAbsolutePath(String absolutePath) throws ConfigurationException {
		
		logger.debug("ILIAS absolute path: " + absolutePath);
		this.absolutePath = new File(absolutePath);
		if(!this.absolutePath.canRead()) {
			logger.error("Cannot read ILIAS absolute path: " + this.absolutePath.getAbsolutePath());
			throw new ConfigurationException("Error reading ILIAS absolute path.");
		}
	}


	/**
	 * @return the clientIniFile
	 */
	public File getClientIniFile() {
		return clientIniFile;
	}

	/**
	 * @param the clientIniFile to set
	 * @throws ConfigurationException 
	 */
	public void setClientIniFile(String clientIniPath) throws ConfigurationException {
		
		this.clientIniFile = new File(clientIniPath);
		logger.debug("ILIAS client ini path: " + clientIniFile.getAbsolutePath());
		
		if(!clientIniFile.canRead()) {
			logger.error("Error reading client ini file: " + clientIniFile.getAbsolutePath());
			throw new ConfigurationException("Cannot read ILIAS client ini file.");
		}
	}

	/**
	 * @param iliasIniFile the iliasIniFile to set
	 * @throws ConfigurationException 
	 */
	public void setIliasIniFile(String iliasIniFile) throws ConfigurationException {
		
		this.iliasIniFile = new File(iliasIniFile);

		if (!this.iliasIniFile.isAbsolute()) {
			logger.error("Absolute path required: " + iliasIniFile);
			throw new ConfigurationException("Absolute path required: " + iliasIniFile);
		}
		if (!this.iliasIniFile.canRead()) {
			logger.error("Path not readable: " + iliasIniFile);
			throw new ConfigurationException("Path not readable: " + iliasIniFile);
		}
		if (this.iliasIniFile.isDirectory()) {
			logger.error("Directory name given: " + iliasIniFile);
			throw new ConfigurationException("Directory name given: " + iliasIniFile);
		}
	}
	
	/**
	 * @return the indexPath
	 */
	public File getIndexPath() {
		return indexPath;
	}

	/**
	 * @param indexPath the indexPath to set
	 */
	public void setIndexPath(String indexPath) {

		this.indexPath = new File(indexPath);

		if (!this.indexPath.isDirectory()) {

			this.indexPath.mkdir();
			
		}
	}

	/**
	 * get db url
	 * @return
	 */
	public String getDbUrl() {

		if(getDbType().equalsIgnoreCase("mysql") || getDbType().equalsIgnoreCase("innodb")) {
			
			if(getDbPort().length() > 0) {
				return "jdbc:mysql://" + getDbHost() + ":" + getDbPort() + "/" + getDbName();
			}
			else {
				return "jdbc:mysql://" + getDbHost() + "/" + getDbName();
			}
		}
		else {
			
			StringBuffer url = new StringBuffer();
			url.append("jdbc:oracle:thin:" + getDbUser() + "/" + getDbPass() + "@//" + getDbHost());
			if(getDbPort().length() > 0) {
				url.append(":" + getDbPort());
			}
			url.append("/" + getDbName());
			return url.toString();
		}
	}
	
	/**
	 * @return the dbType
	 */
	public String getDbType() {

		// handle innodb as mysql
		if(dbType.equalsIgnoreCase("innodb")) {
			return "mysql";
		}
		return dbType;
	}

	/**
	 * @param dbType the dbType to set
	 */
	public void setDbType(String dbType) {
		this.dbType = dbType;
	}

	/**
	 * @return the dbHost
	 */
	public String getDbHost() {
		return dbHost;
	}

	/**
	 * @param dbHost the dbHost to set
	 */
	public void setDbHost(String dbHost) {
		this.dbHost = dbHost;
	}

	/**
	 * @return the dbName
	 */
	public String getDbName() {
		return dbName;
	}

	/**
	 * @param dbName the dbName to set
	 */
	public void setDbName(String dbName) {
		this.dbName = dbName;
	}

	/**
	 * @return the dbUser
	 */
	public String getDbUser() {
		return dbUser;
	}

	/**
	 * @param dbUser the dbUser to set
	 */
	public void setDbUser(String dbUser) {
		this.dbUser = dbUser;
	}

	/**
	 * @return the dbPass
	 */
	public String getDbPass() {
		return dbPass;
	}

	/**
	 * @param dbPass the dbPass to set
	 */
	public void setDbPass(String dbPass) {
		this.dbPass = dbPass;
	}

	/**
	 * set Db port
	 * @param purgeString
	 */
	public void setDbPort(String purgeString) {
		this.dbPort = purgeString;
	}
	
	/**
	 * get db port
	 * @return
	 */
	public String getDbPort() {
		return this.dbPort;
	}
}

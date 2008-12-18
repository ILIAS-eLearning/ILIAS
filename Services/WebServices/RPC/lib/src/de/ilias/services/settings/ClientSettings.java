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
import java.util.HashMap;

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

	public static ClientSettings getInstance(String client, String nic) throws ConfigurationException {
		
		return getInstance(client + '_' + nic);
	}

	public static ClientSettings getInstance(String clientKey) throws ConfigurationException {
		
		if(instances.containsKey(clientKey)) {
			return instances.get(clientKey);
		}
		int posUnderscore;
		if((posUnderscore = clientKey.lastIndexOf("_")) == -1) {
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
			logger.error("Error reading ILIAS ini file.");
			throw new ConfigurationException("Cannot read ILIAS data directory");
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
			logger.error("Error reading absolute path.");
			throw new ConfigurationException("Cannot read ILIAS absolute path.");
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
			logger.error("Error reading client ini file.");
			throw new ConfigurationException("Cannot read ILIAS absolute path.");
		}
	}

	/**
	 * @param iliasIniFile the iliasIniFile to set
	 * @throws ConfigurationException 
	 */
	public void setIliasIniFile(String iliasIniFile) throws ConfigurationException {
		
		this.iliasIniFile = new File(iliasIniFile);

		if (!this.iliasIniFile.isAbsolute()) {
			throw new ConfigurationException("Absolute path required: " + iliasIniFile);
		}
		if (!this.iliasIniFile.canWrite()) {
			throw new ConfigurationException("Path not writable: " + iliasIniFile);
		}
		if (this.iliasIniFile.isDirectory()) {
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

	public String getDbUrl() {

		if(getDbType().equals("mysql")) {
			return "jdbc:mysql://" + getDbHost() + "/" + getDbName();	
		}
		else {
			return "jdbc:mysql://" + getDbHost() + "/" + getDbName();	
		}
	}
	
	/**
	 * @return the dbType
	 */
	public String getDbType() {
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
}

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

import org.apache.logging.log4j.Level;
import org.apache.logging.log4j.Logger;

import java.io.File;
import java.io.IOException;
import java.net.InetAddress;
import java.net.UnknownHostException;

/**
 * Stores general server settings like rpc host and port, global log file and
 * log level.
 * 
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
public class ServerSettings {

	private static Logger logger;
	private static ServerSettings instance = null;

	public static final long DEFAULT_MAX_FILE_SIZE = 500 * 1024 * 1024;

	private InetAddress host;
	private String hostString;
	private int port;
	
	private String tnsAdmin = "";

	private File indexPath;
	private File logFile;
	private Level logLevel;
	private int numThreads = 1;
	private double RAMSize = 500;
	private int indexMaxFileSizeMB = 500;





	/**
     */
	private ServerSettings() {

	}
	
	/**
	 * Global singleton for all threads
	 * @return
	 * @throws ConfigurationException
	 */
	public static synchronized ServerSettings getInstance() throws ConfigurationException {

		if (instance == null) {
			instance = new ServerSettings();
		}
		return instance;
	}
	
	/**
	 * Get TNS admin directory
	 */
	public String lookupTnsAdmin() {
		
		if(getTnsAdmin().length() > 0) {
			return getTnsAdmin();
		}
		try {
		
			if(System.getenv("TNS_ADMIN").length() > 0) 
				return System.getenv("TNS_ADMIN");
		}
		catch(SecurityException e) {
			logger.error("Cannot access environment variable TNS_ADMIN due to security manager limitations: " + e);
			throw e;
		}
		return "";
	}
	
	public String getServerUrl() {

		String builder = "http://" +
				getHostString() +
				":" + getPort() +
				"/xmlrpc";
		return builder;
	}

	/**
	 * @return the host
	 */
	public InetAddress getHost() {
		return host;
	}
	
	public String getHostString() {
		return hostString;
	}

	/**
	 * @param host
	 *            The host to set.
	 * @throws ConfigurationException
	 */
	public void setHost(String host) throws ConfigurationException {

		try {
			this.host = InetAddress.getByName(host);
			this.hostString = host;
		} 
		catch (UnknownHostException e) {
			logger.fatal("Unknown host given: " + host);
			throw new ConfigurationException(e);
		}
	}

	/**
	 * @return the port
	 */
	public int getPort() {
		return port;
	}

	/**
	 * @param port
	 *            the port to set
	 */
	public void setPort(String port) {
		this.port = Integer.parseInt(port);
	}

	/**
	 * @return the indexPath
	 */
	public File getIndexPath() {
		return indexPath;
	}
	
	/**
	 * @return the logFile
	 */
	public File getLogFile() {
		return logFile;
	}

	/**
	 * @param logFile the logFile to set
	 * @throws ConfigurationException 
	 * @throws IOException , ConfigurationException
	 */
	public void setLogFile(String logFile) throws ConfigurationException, IOException {

		this.logFile = new File(logFile);
		if(!this.logFile.isAbsolute()) {
			logger.error("Absolute path to logfile required: " + logFile);
			throw new ConfigurationException("Absolute path to logfile required: " + logFile);
		}
		if(this.logFile.isDirectory()) {
			logger.error("Absolute path to logfile required. Directory name given: " + logFile);
			throw new ConfigurationException("Absolute path to logfile required: " + logFile);
		}
		if(this.logFile.createNewFile()) {
			//System.out.println("Created new log file: " + this.logFile.getAbsolutePath());
		}
		else {
			//System.out.println("Using existing log file: " + this.logFile.getAbsolutePath());
		}
		if(!this.logFile.canWrite()) {
			throw new ConfigurationException("Cannot write to log file: " + logFile);
		}
	}
	

	/**
	 * @return the logLevel
	 */
	public Level getLogLevel() {
		return logLevel;
	}

	/**
	 * @param logLevel the logLevel to set
	 */
	public void setLogLevel(String logLevel) {

		this.logLevel = Level.toLevel(logLevel.trim(),Level.INFO);
	}

	/**
	 * Get tns admin directory
	 * @return
	 */
	public String getTnsAdmin() {
		return tnsAdmin;
	}

	/**
	 * Set tns admin directory
	 * @param tnsAdmin
	 */
	public void setTnsAdmin(String tnsAdmin) {
		this.tnsAdmin = tnsAdmin;
	}

	
	/**
	 * @param indexPath
	 *            the indexPath to set
	 * @throws ConfigurationException
	 */
	public void setIndexPath(String indexPath) throws ConfigurationException {

		this.indexPath = new File(indexPath);

		if (!this.indexPath.isAbsolute()) {
			throw new ConfigurationException("Absolute path required: " + indexPath);
		}
		if (!this.indexPath.canWrite()) {
			throw new ConfigurationException("Path not writable: " + indexPath);
		}
		if (!this.indexPath.isDirectory()) {
			throw new ConfigurationException("Directory name required: " + indexPath);
		}
	}
	

	/**
	 * @param purgeString
	 */
	public void setThreadNumber(String purgeString) {

		this.numThreads = Integer.valueOf(purgeString);
	}
	
	public int getNumThreads() {
		return numThreads;
	}

	public double getRAMSize() {
		return RAMSize;
	}

	public void setRAMSize(String purgedString) {

		RAMSize = Double.valueOf(purgedString);
	}

	public int getMaxFileSizeMB()
	{
		return indexMaxFileSizeMB;
	}

	public long getMaxFileSize()
	{
		return (long) indexMaxFileSizeMB * 1024 * 1024;
	}

	public void setMaxFileSizeMB(String mb)
	{
		this.indexMaxFileSizeMB = Integer.valueOf(mb);
	}
}

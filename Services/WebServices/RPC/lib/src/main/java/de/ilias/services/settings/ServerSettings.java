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
import java.net.InetAddress;
import java.net.UnknownHostException;

import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;

/**
 * Stores general server settings like rpc host and port, global log file and
 * log level.
 * 
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
public class ServerSettings {

	private static Logger logger = LogManager.getLogger(ServerSettings.class);
	private static ServerSettings instance = null;

	public static final long DEFAULT_MAX_FILE_SIZE = 500 * 1024 * 1024;

	private InetAddress host;
	private String hostString;
	private int port;

	private File indexPath;
	private int numThreads = 1;
	private double RAMSize = 500;
	private int indexMaxFileSizeMB = 500;

	private boolean ignoreDocAndXlsFiles = true;




	/**
	 * @param properties
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
	
	public String getServerUrl() {
		
		StringBuilder builder = new StringBuilder();
		
		builder.append("http://");
		builder.append(getHostString());
		builder.append(":" + getPort());
		builder.append("/xmlrpc");
		
		logger.info("Using RPC Url: " + builder);
		return builder.toString();
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

	public void setIgnoreDocAndXlsFiles(boolean ignoreDocAndXlsFiles) {
	  this.ignoreDocAndXlsFiles = ignoreDocAndXlsFiles;
	}

	public boolean getIgnoreDocAndXlsFiles() {
	  return ignoreDocAndXlsFiles;
	}
}

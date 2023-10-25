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

package de.ilias.services.db;

import de.ilias.services.settings.ClientSettings;
import de.ilias.services.settings.ConfigurationException;
import de.ilias.services.settings.LocalSettings;
import de.ilias.services.settings.ServerSettings;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;

import java.sql.*;
import java.util.HashMap;

/**
 * A thread local singleton for db connections
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
public class DBFactory {

	private static final Logger logger = LogManager.getLogger(DBFactory.class);
	
	private static final String MARIA_DB_CONNECTOR = "jdbc:mariadb://";

	// valid db types
	private static final String DB_TYPE_MYSQL = "mysql";
	private static final String DB_TYPE_INNODB = "innodb";

	
	private static final ThreadLocal<HashMap<String, PreparedStatement>> ps = new ThreadLocal<HashMap<String,PreparedStatement>>() {
		protected HashMap<String, PreparedStatement> initialValue() {
			
			return new HashMap<String, PreparedStatement>();
		}
		
		public void remove() {
			super.remove();
		}
	};
	
	private static final ThreadLocal<Connection> connection = new ThreadLocal<Connection>() {

		protected Connection initialValue() {
			try {
				ClientSettings client = ClientSettings.getInstance(LocalSettings.getClientKey());
				ServerSettings server = ServerSettings.getInstance();
				
				logger.info("+++++++++++++++++++++++++++++++++++++++++++ New Thread local " + LocalSettings.getClientKey());

				if (!isValidDatabaseType(client.getDbType())) {
					logger.error("Unsupported db type given." + client.getDbType());
					throw new ConfigurationException("Unsupported db type given." + client.getDbType());
				}
				logger.info("Loading maria db driver...");
				logger.info("Using jdbc url: " +
						client.getDbUrl() + "/" +
						client.getDbUser() + "/" +
						"******" + "?autoReconnect=true"
				);

				return DriverManager.getConnection(
					DBFactory.MARIA_DB_CONNECTOR +
					client.getDbUrl() + "?autoReconnect=true",
					client.getDbUser(),
					client.getDbPass()
				);
			}
			catch (SQLException | ConfigurationException e) {
				logger.error("Cannot connect to database: " + e);
			}
			return null;
		}

		protected boolean isValidDatabaseType(String dbType)
		{
			if (dbType.equalsIgnoreCase(DBFactory.DB_TYPE_MYSQL) || dbType.equalsIgnoreCase(DBFactory.DB_TYPE_INNODB)) {
				return true;
			}
			return false;
		}

		/**
		 * @see java.lang.ThreadLocal#remove()
		 */
		@Override
		public void remove() {
			super.remove();
		}
		
	
	};

	
	/**
	 * get singleton db connection for each url
	 * @return Connection
	 * @throws SQLException 
	 */
	public static Connection factory() throws SQLException {
		
		logger.debug("====================================== Used cached DB connector.");
		return connection.get();
	}
	
	public static void init() {
		
		logger.debug("------------------------------------- Destroying cached DB connector.");
		connection.remove();
		ps.remove();
	}
	
	/**
	 * get prepared statement
	 * @param query
	 * @return
	 * @throws SQLException
	 */
	public static PreparedStatement getPreparedStatement(String query) throws SQLException {
		
		if(ps.get().containsKey(query)) {
			
			logger.debug("Reusing prepared statement: " + query);
			return ps.get().get(query);
		}
		
		// Create new Prepared statement
		logger.debug("Creating new prepared statement: " + query);
		ps.get().put(query, DBFactory.factory().prepareStatement(query));
		return ps.get().get(query);
	}
	
	/**
	 * Close prepared statement
	 * @param query
	 */
	public static void closePreparedStatement(String query) {
		
		try {
			if(ps.get().containsKey(query)) {
				ps.get().get(query).close();
			}
		}
		catch (Throwable t) {
		}
		finally {
			ps.get().remove(query);
		}
	}
	
	/**
	 * close all statements
	 */
	public static void closeAll() {
		
		try {
			
			for(PreparedStatement pst : ps.get().values()) {
				
				// closing prepared statements
				logger.debug("Clossing prepared statement: " + pst.toString());
				try {
					// Close prepared statements
					pst.close();
					// Close connection
				}
				catch (SQLException e) {
					logger.warn("Cannot close prepared statement: " + pst);
					logger.warn(e);
				}
				catch (Throwable t) {
					logger.warn(t);
				}
			}
			
		}
		catch (Throwable t) {
			logger.warn(t);
		}
		finally {
			
			try {
				connection.get().close();
			}
			catch (Throwable e) {
				logger.error("Cannot release db connection: ",e);
			}
		}
	}
	
	/**
	 * set string overwritten for oracle
	 * @param ps
	 * @param index
	 * @param str
	 * @return
	 * @throws SQLException
	 */
	public static PreparedStatement setString(PreparedStatement ps,int index,String str) throws SQLException {
		
		ClientSettings client;
		try {
			client = ClientSettings.getInstance(LocalSettings.getClientKey());
			if(client.getDbType().equals("mysql")) {
				
				ps.setString(index, str);
				return ps;
			}
		}
		catch (ConfigurationException e) {
			// shouldn't happen here
			logger.error(e);
		}
		return ps;
	}
	
	/**
	 * get string overwritten for oracle
	 * @param res
	 * @param name
	 * @throws SQLException 
	 */
	public static String getString(ResultSet res,String name) throws SQLException {
		
		ClientSettings client;
		try {
			
			client = ClientSettings.getInstance(LocalSettings.getClientKey());
			if(client.getDbType().equals("mysql")) {
				
				return res.getString(name);
			}
			else {
				
				String ret = res.getString(name);
				if(ret == null) {
					return "";
				}
				return ret.trim();
			}
		}
		catch (ConfigurationException e) {
			// shouldn't happen here
			logger.error(e);
		}
		return "";
	}

	/**
	 * Get clob value
	 * @param res
	 * @param name
	 * @return
	 * @throws SQLException 
	 */
	public static String getCLOB(ResultSet res, String name) throws SQLException {

		if(getDbType().equalsIgnoreCase("mysql")) {
			return DBFactory.getString(res, name);
		}
		else {
			return DBFactory.getString(res, name);
		}
	}

	/**
	 * Get integer value and parse it to int
	 * @param res
	 * @param name
	 * @return
	 * @throws SQLException
	 */
	public static String getInt(ResultSet res, String name) throws SQLException {

		return String.valueOf(res.getInt(name));
	}
	
	/**
	 * get db type
	 * @return
	 */
	public static String getDbType() {
		
		try {
			return ClientSettings.getInstance(LocalSettings.getClientKey()).getDbType();
		}
		catch (ConfigurationException e) {
			// shouldn't happen here
			logger.error(e);
		}
		return "";
	}
}

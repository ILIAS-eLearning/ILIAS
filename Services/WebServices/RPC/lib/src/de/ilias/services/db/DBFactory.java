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

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.PreparedStatement;
import java.sql.SQLException;
import java.util.HashMap;
import java.util.Vector;


import org.apache.log4j.Logger;

import de.ilias.services.settings.ClientSettings;
import de.ilias.services.settings.ConfigurationException;
import de.ilias.services.settings.LocalSettings;

/**
 * A thread local singleton for db connections
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
public class DBFactory {

	private static Logger logger = Logger.getLogger(DBFactory.class);
	
	private static ThreadLocal<HashMap<String, PreparedStatement>> ps = new ThreadLocal<HashMap<String,PreparedStatement>>() {
		protected HashMap<String, PreparedStatement> initialValue() {
			
			return new HashMap<String, PreparedStatement>();
		}
		
		public void remove() {
			super.remove();
		}
	};
	
	private static ThreadLocal<Connection> connection = new ThreadLocal<Connection>() {

		protected Connection initialValue() {
			try {
				ClientSettings client = ClientSettings.getInstance(LocalSettings.getClientKey());
				logger.info("+++++++++++++++++++++++++++++++++++++++++++ New Thread local " + LocalSettings.getClientKey());

				// MySQL
				if(client.getDbType().equalsIgnoreCase("mysql")) {

					logger.info("Loading Mysql driver...");
					Class.forName( "com.mysql.jdbc.Driver");
					return DriverManager.getConnection(
							client.getDbUrl(),
							client.getDbUser(),
							client.getDbPass());
				}
				// Oracle
				if(client.getDbType().equalsIgnoreCase("oracle")) {
					
					logger.info("Loading Oracle driver...");
					Class.forName("oracle.jdbc.driver.OracleDriver");
					
					logger.info(
							"jdbc:oracle:thin:" +
							client.getDbUser() + "/" +
							client.getDbPass() + "@" + 
							client.getDbHost() + "/" +
							client.getDbName()
					);
					return DriverManager.getConnection(
							"jdbc:oracle:thin:" +
							client.getDbUser() + "/" +
							client.getDbPass() + "@" + 
							client.getDbHost() + "/" +
							client.getDbName()
					);
				}
			} 
			catch (SQLException e) {
				logger.error("Cannot connect to database.");
			} 
			catch (ConfigurationException e) {
				logger.error("Cannot connect to database.");
			} 
			catch (ClassNotFoundException e) {
				// no oracle driver!
				logger.error("Could not load the JDBC driver. Check your CLASSPATH for a proper Oracle JDBC driver.");
			}
			return null;
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
	 * @param url
	 * @param user
	 * @param pass
	 * @return Connection
	 * @throws SQLException 
	 */
	public static Connection factory() throws SQLException {
		
		logger.debug("====================================== Used cached DB connector.");
		return (Connection) connection.get();
	}
	
	public static void init() {
		
		logger.debug("------------------------------------- Destroying cached DB connector.");
		connection.remove();
		ps.remove();
	}
	
	public static PreparedStatement getPreparedStatement(String query) throws SQLException {
		
		// Delete, if satement is closed
		if(ps.get().containsKey(query) && ps.get().get(query).isClosed()) {
			ps.get().remove(query);
		}
		// Return if exists 
		if((ps.get().containsKey(query))) {
			return ps.get().get(query);
		}
		// Create new Prepared statement
		ps.get().put(query, DBFactory.factory().prepareStatement(query));
		return ps.get().get(query);
	}
}

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
import java.sql.SQLException;

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

	private static class ThreadLocalConnection extends ThreadLocal<Connection> {
		public Connection initialValue() {
			try {
				ClientSettings client = ClientSettings.getInstance(LocalSettings.getClientKey());
				// TODO: receive from local settings
				Class.forName( "com.mysql.jdbc.Driver");
				return DriverManager.getConnection(
						client.getDbUrl(),
						client.getDbUser(),
						client.getDbPass());
			} 
			catch (SQLException e) {
				logger.error("Cannot connect to database.");
			} 
			catch (ConfigurationException e) {
				logger.error("Cannot connect to database.");
			} 
			catch (ClassNotFoundException e) {
				logger.error("Cannot connect to database.");
			}
			return null;
		}
	}
	private static ThreadLocalConnection connection = new ThreadLocalConnection();
	
	/**
	 * get singleton db connection for each url
	 * @param url
	 * @param user
	 * @param pass
	 * @return Connection
	 * @throws SQLException 
	 */
	public static Connection factory() throws SQLException {
		
		return (Connection) connection.get();
	}	
}

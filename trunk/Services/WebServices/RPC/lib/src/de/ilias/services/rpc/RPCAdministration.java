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

import java.sql.SQLException;

import org.apache.log4j.Logger;

import de.ilias.ilServerStatus;
import de.ilias.services.lucene.index.IndexHolder;
import de.ilias.services.lucene.settings.LuceneSettings;
import de.ilias.services.settings.ConfigurationException;
import de.ilias.services.settings.LocalSettings;


/**
 * 
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
public class RPCAdministration {

	private Logger logger = Logger.getLogger(this.getClass().getName());

	
	/**
	 * 
	 */
	public RPCAdministration() {

	}
	
	/**
	 * Stop RPC server and application 
	 * @throws ConfigurationException 
	 */
	public boolean stop() throws ConfigurationException {
		
		RPCServer server;
		
		logger.info("Received stop request");

		// Closing all index writers
		IndexHolder.closeAllWriters();

		// TODO: add more security. 
		// It shouldn't be possible for every client to stop the rpc server.
		server = RPCServer.getInstance();
		server.setAlive(false);

		// Set server status inactive
		ilServerStatus.setActive(false);
		
		return true;
	}
	
	/**
	 * Refresh settings
	 * @param clientKey
	 * @return
	 */
	public boolean refreshSettings(String clientKey) {
		
		LuceneSettings settings = null;
		LocalSettings.setClientKey(clientKey);
		
		try {
			logger.info("Reading lucene client settings from database.");
			logger.info("Client key: " + clientKey);
			settings = LuceneSettings.getInstance(clientKey);
			settings.refresh();
			return true;
		} 
		catch (SQLException e) {
			logger.error(e.getMessage());
			return false;
		}
	}
	
	public String status() {
		
		return ilServerStatus.getStatus();
	}
	
	public boolean start() {
		
		ilServerStatus.setActive(true);
		return true;
	}

}

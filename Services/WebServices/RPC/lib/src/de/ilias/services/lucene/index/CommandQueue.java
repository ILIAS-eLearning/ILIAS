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

package de.ilias.services.lucene.index;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Statement;
import java.util.Vector;

import org.apache.log4j.Logger;

import de.ilias.services.db.DBFactory;
import de.ilias.services.object.ObjectDefinition;
import de.ilias.services.object.ObjectDefinitions;
import de.ilias.services.settings.ClientSettings;
import de.ilias.services.settings.ConfigurationException;
import de.ilias.services.settings.LocalSettings;


/**
 * @todo make this class thread safe 
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
public class CommandQueue {

	
	protected Logger logger = Logger.getLogger(CommandQueue.class);
	
	private Connection db = null;
	private Vector<CommandQueueElement> elements = new Vector<CommandQueueElement>();
	
	/**
	 * @throws SQLException 
	 * 
	 */
	public CommandQueue() throws SQLException {

		db = DBFactory.factory();
		
		loadFromDb();
	}
	
	/**
	 * 
	 * @param el
	 * @throws SQLException, IllegalArgumentException 
	 */
	public void setFinished(CommandQueueElement el) throws SQLException, IllegalArgumentException {
		
		if(getElements().removeElement(el) == false) {
			throw new IllegalArgumentException("Cannot find element!");
		}
		
		PreparedStatement sta = db.prepareStatement("UPDATE search_command_queue " +
				"SET finished = 1 " +
				"WHERE  obj_id = ? " +
				"AND obj_type = ? " +
				"AND sub_id = ? " +
				"AND sub_type = ? ");
		sta.setInt(1, el.getObjId());
		sta.setString(2, el.getObjType());
		sta.setInt(3, el.getSubId());
		sta.setString(4, el.getSubType());
		
		sta.executeUpdate();
	}
	
	/**
	 * 
	 * @param objIds
	 * @throws SQLException
	 */
	public void setFinished(Vector<Integer> objIds) throws SQLException {
		
		if(objIds.size() == 0) {
			return;
		}
		PreparedStatement psta = db.prepareStatement("UPDATE search_command_queue SET finished = 1 WHERE obj_id = ?");
		for(int i = 0; i < objIds.size(); i++) {
			psta.setInt(1,objIds.get(i));
			psta.addBatch();
		}
		psta.executeBatch();
		
		return;
	}


	/**
	 * @throws SQLException 
	 * 
	 */
	private synchronized void loadFromDb() throws SQLException {

		logger.debug("Start reading command queue");
		
		// Handle rebuild_index
		substituteRebuildCommand();
		
		
		// Substitute all reset_all commands withc reset command for each undeleted object id 
		substituteResetCommands();
		
		
		Statement sta = db.createStatement();
		// TODO: Only retrieve data sets newer than date XYZ
		ResultSet res = sta.executeQuery("SELECT * FROM search_command_queue " +
				"WHERE finished = 0 " +
				"ORDER BY last_update ");
		
		
		int counter = 0;
		while(res.next()) {
			
			CommandQueueElement element = new CommandQueueElement();

			element.setObjId(res.getInt("obj_id"));
			element.setObjType(res.getString("obj_type"));
			element.setSubId(res.getInt("sub_id"));
			element.setSubType(res.getString("sub_type"));
			element.setCommand(res.getString("command"));
			element.setFinished(false);
			
			getElements().add(element);
			counter++;
		}
		logger.info("Found " + counter + " new update events!");
	}
	
	/**
	 * 
	 * @throws SQLException
	 */
	private void substituteRebuildCommand() throws SQLException {
		
		try {
			PreparedStatement sta = db.prepareStatement("SELECT COUNT(*) num FROM search_command_queue " +
				"WHERE command = ?");
			sta.setString(1, "rebuild_index");
			
			ResultSet res = sta.executeQuery();
			while(res.next()) {
				if(res.getInt("num") <= 0) {
					return;
				}
			}
			
			// Received rebuildIndex command
			Statement delete = db.createStatement();
			delete.executeUpdate("DELETE FROM search_command_queue");
			
			ClientSettings client = ClientSettings.getInstance(LocalSettings.getClientKey());
			for(Object def : ObjectDefinitions.getInstance(client.getAbsolutePath()).getDefinitions()) {
				
				logger.info("Adding reset command for " + ((ObjectDefinition) def).getType());
				PreparedStatement pst = db.prepareStatement("INSERT INTO search_command_queue " +
					"SET obj_id = ?,obj_type = ?, sub_id = ?, sub_type = ?, command = ?, last_update = ?, finished = ? ");
					pst.setInt(1,0);
					pst.setString(2, ((ObjectDefinition) def).getType());
					pst.setInt(3,0);
					pst.setString(4,"");
					pst.setString(5,"reset_all");
					pst.setDate(6,new java.sql.Date(new java.util.Date().getTime()));
					pst.setInt(7,0);
				pst.executeUpdate();
			}
			return;
		} 
		catch(SQLException e) {
			logger.fatal("Invalid SQL statement!");
			throw e;
		} 
		catch (ConfigurationException e) {
			logger.error("Missing client key. " + e.getMessage());
		}
		
	}


	/**
	 * @throws SQLException 
	 * 
	 */
	private void substituteResetCommands() throws SQLException {

		try {
			PreparedStatement sta = db.prepareStatement("SELECT * FROM search_command_queue " +
					"WHERE command = ? " +
					"AND obj_id = 0");
			sta.setString(1, "reset_all");
			
			ResultSet res = sta.executeQuery();
			while(res.next()) {
				
				logger.debug("Start substituting obj_type " + res.getString("obj_type"));
				deleteCommandsByType(res.getString("obj_type"));
				addCommandsByType(res.getString("obj_type"));
				deleteResetCommandByType(res.getString("obj_type"));
			}
		} catch(SQLException e) {
			logger.fatal("Invalid SQL statement!");
			throw e;
		}
	}

	/**
	 * @param string
	 * @throws SQLException 
	 */
	private void deleteResetCommandByType(String objType) throws SQLException {

		try {
			PreparedStatement sta = db.prepareStatement("DELETE FROM search_command_queue " +
				"WHERE obj_type = ? " + 
				"AND obj_id = 0 ");
			sta.setString(1, objType);
			sta.executeUpdate();
			return;
		}
		catch(SQLException e) {
			logger.fatal("Cannot delete reset commands!");
			throw e;
		}
	}

	/**
	 * @param string
	 * @throws SQLException 
	 */
	private void deleteCommandsByType(String objType) throws SQLException {

		try {
			PreparedStatement sta = db.prepareStatement("DELETE FROM search_command_queue " + 
				"WHERE obj_type = ? " +
				"AND obj_id > 0");
			sta.setString(1, objType);
			sta.executeUpdate();
			return;
		} catch (SQLException e) {
			logger.fatal("Cannot delete reset commands!");
			throw e;
		}
	}

	/**
	 * @param string
	 * @throws SQLException 
	 */
	private void addCommandsByType(String objType) throws SQLException {

		// TODO: Error handling
		
		PreparedStatement sta = db.prepareStatement(
			"SELECT DISTINCT(oda.obj_id) FROM object_data oda JOIN object_reference ore ON oda.obj_id = ore.obj_id WHERE deleted = '0000-00-00 00:00:00' AND type = ?");
		sta.setString(1, objType);
		ResultSet res = sta.executeQuery();
		
		logger.debug("Adding commands for object type: " + objType);
		
		while(res.next()) {
			
			logger.debug("Added new reset command");
			
			// Add each single object
			PreparedStatement objReset = db.prepareStatement(
					"INSERT INTO search_command_queue SET obj_id = ?,obj_type = ?, sub_id = ?, sub_type = ?, command = ?, last_update = ?, finished = ? ");
			objReset.setInt(1,res.getInt("obj_id"));
			objReset.setString(2, objType);
			objReset.setInt(3,0);
			objReset.setString(4,"");
			objReset.setString(5,"reset");
			objReset.setDate(6,new java.sql.Date(new java.util.Date().getTime()));
			objReset.setInt(7,0);
			
			objReset.executeUpdate();
		}
		return;
		
	}


	/**
	 * @return the elements
	 */
	public Vector<CommandQueueElement> getElements() {
		return elements;
	}


}
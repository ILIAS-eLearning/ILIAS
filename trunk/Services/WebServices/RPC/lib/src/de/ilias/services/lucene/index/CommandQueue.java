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
import de.ilias.services.lucene.settings.LuceneSettings;
import de.ilias.services.object.ObjectDefinition;
import de.ilias.services.object.ObjectDefinitions;
import de.ilias.services.settings.ClientSettings;
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
	private int currentIndex = 0;
	
	/**
	 * @throws SQLException 
	 * 
	 */
	public CommandQueue() throws SQLException {

		db = DBFactory.factory();
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
		
		PreparedStatement sta = DBFactory.getPreparedStatement("UPDATE search_command_queue " +
				"SET finished = 1, " +
				"last_update = ? " +
				"WHERE  obj_id = ? " +
				"AND obj_type = ? " +
				"AND sub_id = ? " +
				"AND sub_type = ? ");
		sta.setInt(1, el.getObjId());
		sta.setTimestamp(2,new java.sql.Timestamp(new java.util.Date().getTime()));
		DBFactory.setString(sta, 3, el.getObjType());
		sta.setInt(4, el.getSubId());
		DBFactory.setString(sta, 5, el.getSubType());
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
		PreparedStatement psta = DBFactory.getPreparedStatement(
				"UPDATE search_command_queue SET finished = 1 WHERE obj_id = ?"
		);
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
	public synchronized void loadFromDb() throws SQLException {

		logger.info("Start reading command queue");
		
		
		// Substitute all reset_all commands withc reset command for each undeleted object id 
		substituteResetCommands();
		
		PreparedStatement pst = DBFactory.getPreparedStatement("SELECT * FROM search_command_queue " +
				"WHERE finished = 0 " +
				"OR last_update >= ? " + 
				"ORDER BY last_update ");
		pst.setTimestamp(
				1,
				new java.sql.Timestamp(LuceneSettings.getInstance().getLastIndexTime().getTime()));
		ResultSet res = pst.executeQuery();
		
		
		
		int counter = 0;
		while(res.next()) {
			
			CommandQueueElement element = new CommandQueueElement();

			logger.debug("Found type: " + res.getString("obj_type") + " with id " + res.getInt("obj_id"));
			element.setObjId(res.getInt("obj_id"));
			element.setObjType(DBFactory.getString(res, "obj_type"));
			element.setSubId(res.getInt("sub_id"));
			element.setSubType(DBFactory.getString(res, "sub_type"));
			element.setCommand(DBFactory.getString(res, "command"));
			element.setFinished(false);
			
			getElements().add(element);
			counter++;
		}
		try {
			res.close();
		} catch (SQLException e) {
			logger.warn(e);
		}
		logger.info("Found " + counter + " new update events!");
	}
	
	
	/**
	 * 
	 * @param objIds 
	 */
	public synchronized void loadFromObjectList(Vector<Integer> objIds) throws SQLException {
		
		
		PreparedStatement pst = DBFactory.getPreparedStatement(
				"SELECT obj_id,type FROM object_data " +
				"WHERE obj_id = ? ");
		
		int counter = 0;
		for(int objId : objIds) {
			
			pst.setInt(1, objId);
			ResultSet res = pst.executeQuery();
			
			while(res.next()) {
				
				CommandQueueElement element = new CommandQueueElement();

				//logger.debug("Found type: " + res.getString("obj_type") + " with id " + res.getInt("obj_id"));
				element.setObjId(res.getInt("obj_id"));
				element.setObjType(DBFactory.getString(res, "type"));
				element.setSubId(0);
				element.setSubType("");
				element.setCommand("reset");
				element.setFinished(false);

				getElements().add(element);
				counter++;
			}
			try {
				res.close();
			} catch (SQLException e) {
				logger.warn(e);
			}
		}
		logger.info("Found " + counter + " new update events!");
	}
	


	/**
	 * @throws SQLException 
	 * 
	 */
	private synchronized void substituteResetCommands() throws SQLException {

		String query = "SELECT * FROM search_command_queue WHERE command = ? AND obj_id = 0";

		try {
			logger.info("Substituting reset commands");

			PreparedStatement sta = DBFactory.getPreparedStatement(query);
			DBFactory.setString(sta, 1, "reset_all");
			logger.debug("Substitution query: " + sta.toString());
			ResultSet res = sta.executeQuery();
			while(res.next()) {
				
				logger.info("Start substituting obj_type " + res.getString("obj_type"));
				deleteCommandsByType(DBFactory.getString(res, "obj_type"));
				addCommandsByType(DBFactory.getString(res, "obj_type"));
				deleteResetCommandByType(DBFactory.getString(res, "obj_type"));
			}
			res.close();
		} 
		catch(SQLException e) {
			logger.error("Invalid SQL statement: " + query);
			logger.error("Cannot substitute reset commands", e);
			throw e;
		}
		catch(Throwable e) {
			logger.fatal("Cannot substitute reset commands",e);
		}
	}

	/**
	 * @param string
	 * @throws SQLException 
	 */
	private synchronized void deleteResetCommandByType(String objType) throws SQLException {

		try {

			logger.info("Deleting reset command");
			PreparedStatement sta = DBFactory.getPreparedStatement(
				"DELETE FROM search_command_queue " +
				"WHERE obj_type = ? " + 
				"AND obj_id = 0 ");
			DBFactory.setString(sta, 1, objType);
			sta.executeUpdate();
			return;
		}
		catch(SQLException e) {
			logger.error("Cannot delete reset commands!",e);
			throw e;
		}
	}

	/**
	 * @param string
	 * @throws SQLException 
	 */
	public synchronized void deleteCommandsByType(String objType) throws SQLException {

		try {
			
			logger.info("Deleting old commands from command queue");
			PreparedStatement sta = DBFactory.getPreparedStatement("DELETE FROM search_command_queue " +
				"WHERE obj_type = ? " +
				"AND obj_id > 0");
			DBFactory.setString(sta, 1, objType);
			sta.executeUpdate();
			return;
		} 
		catch (SQLException e) {
			logger.fatal("Cannot delete reset commands! ",e);
			throw e;
		}
	}

	/**
	 * @param string
	 * @throws SQLException 
	 */
	private synchronized void addCommandsByType(String objType) throws SQLException {

		try {
			
			ResultSet res = null;
			PreparedStatement sta = null;
			
			if(objType.equalsIgnoreCase("usr") != true) 
			{
				sta = DBFactory.getPreparedStatement(
					"SELECT DISTINCT(oda.obj_id) FROM object_data oda JOIN object_reference ore ON oda.obj_id = ore.obj_id " +
					"WHERE (deleted IS NULL) AND type = ? " +
					"GROUP BY oda.obj_id");
			}
			else {
				sta = DBFactory.getPreparedStatement(
					"SELECT obj_id FROM object_data " + 
					"WHERE type = ? "
				);
			}

			DBFactory.setString(sta, 1, objType);
			res = sta.executeQuery();

			logger.info("Adding new commands for object type: " + objType);

			// Add each single object
			PreparedStatement objReset = DBFactory.getPreparedStatement(
					"INSERT INTO search_command_queue (obj_id, obj_type, sub_id, sub_type, command, last_update, finished) " + 
					"VALUES (?, ?, ?, ?, ?, ?, ?)");

			while(res.next()) {

				logger.debug("Added new reset command");

				objReset.setInt(1,res.getInt("obj_id"));
				objReset.setString(2, objType);
				objReset.setInt(3,0);
				objReset.setString(4,"");
				objReset.setString(5,"reset");
				objReset.setTimestamp(6,new java.sql.Timestamp(new java.util.Date().getTime()));
				objReset.setInt(7,0);

				objReset.executeUpdate();
			}
			
			try {
				if(res != null)
				{
					res.close();
				}
			} 
			catch (SQLException e) {
				logger.warn("Cannot close result set: " + e);
			}
		}
		catch(SQLException e) {
			
			logger.fatal("Cannot build index ",e);
			throw e;
		}
		
	}
	
	
	/**
	 * Thread safe 
	 * @return
	 */
	public synchronized CommandQueueElement nextElement() {
		
		try {
			return elements.get(currentIndex++);
		}
		catch(IndexOutOfBoundsException e) {
			return null;
		}
	}


	/**
	 * Not thread save
	 * @return the elements
	 */
	public synchronized Vector<CommandQueueElement> getElements() {
		return elements;
	}
	
	/**
	 * 
	 * @param type
	 * @throws SQLException 
	 */
	public synchronized void debug(String type) throws SQLException {
		
		PreparedStatement resetType = DBFactory.getPreparedStatement(
				"INSERT INTO search_command_queue SET obj_id = ?,obj_type = ?, sub_id = ?, sub_type = ?, command = ?, last_update = ?, finished = ? ");
		resetType.setInt(1,0);
		resetType.setString(2,type);
		resetType.setInt(3,0);
		resetType.setString(4,"");
		resetType.setString(5,"reset_all");
		resetType.setTimestamp(6,new java.sql.Timestamp(new java.util.Date().getTime()));
		resetType.setInt(7,0);

		resetType.executeUpdate();
	}

	/**
	 * Delete  and add all types
	 * @param type
	 * @throws SQLException 
	 */
	public synchronized void addAll() throws SQLException {
		
		try {

			Statement delete = db.createStatement();
			delete.executeUpdate("DELETE FROM search_command_queue");
			
			try {
				if(delete != null) {
					delete.close();
				}
			}
			catch (SQLException e) {
				logger.warn(e);
			}
			
			ClientSettings client = ClientSettings.getInstance(LocalSettings.getClientKey());

			PreparedStatement pst = DBFactory.getPreparedStatement("INSERT INTO search_command_queue (obj_id,obj_type,sub_id,sub_type,command,last_update,finished ) " +
			"VALUES ( ?, ?, ?, ?, ?, ?, ?) ");

			for(Object def : ObjectDefinitions.getInstance(client.getAbsolutePath()).getDefinitions()) {
				
				logger.info("Adding reset command for " + ((ObjectDefinition) def).getType());
					pst.setInt(1,0);
					pst.setString(2, ((ObjectDefinition) def).getType());
					pst.setInt(3,0);
					pst.setString(4,"");
					pst.setString(5,"reset_all");
					pst.setTimestamp(6,new java.sql.Timestamp(new java.util.Date().getTime()));
					pst.setInt(7,0);
				pst.executeUpdate();
			}
		}
		catch (Exception e) {
			logger.error("Cannot add to command queue",e);
		}
	}
	
	
	/**
	 * Delete command queue
	 * @throws SQLException
	 */
	public synchronized void deleteAll() throws SQLException {
		
		logger.info("Deleting search_command_queue");
		Statement delete = db.createStatement();
		delete.execute("DELETE FROM search_command_queue");
		
		try {
			delete.close();
		}
		catch (SQLException e) {
			logger.warn(e);
		}
		logger.info("Search command queue deleted");
	}

	/**
	 * 
	 */
	public synchronized void deleteNonIncremental()  throws SQLException {

		try {
			ClientSettings client = ClientSettings.getInstance(LocalSettings.getClientKey());
			
			PreparedStatement pst = DBFactory.getPreparedStatement("DELETE FROM search_command_queue " +
					"WHERE obj_type = ?");
			for(Object def : ObjectDefinitions.getInstance(client.getAbsolutePath()).getDefinitions()) {
				
				if(((ObjectDefinition) def).getIndexType() == ObjectDefinition.TYPE_FULL) {
					
					DBFactory.setString(pst, 1, ((ObjectDefinition) def).getType());
					//pst.setString(1, ((ObjectDefinition) def).getType());
					pst.executeUpdate();
				}
			}
		}
		catch (Exception e) {
			logger.error("Error deleting from command queue", e);
		}
		
	}

	/**
	 * 
	 */
	public synchronized void addNonIncremental() throws SQLException {

		try {

			ClientSettings client = ClientSettings.getInstance(LocalSettings.getClientKey());
			
			PreparedStatement pst = DBFactory.getPreparedStatement("INSERT INTO search_command_queue " +
					"(obj_id, obj_type,sub_id,sub_type,command,last_update,finished) " +
					"VALUES (?,?,?,?,?,?,?)");
			for(Object def : ObjectDefinitions.getInstance(client.getAbsolutePath()).getDefinitions()) {

				if(((ObjectDefinition) def).getIndexType() == ObjectDefinition.TYPE_FULL) {
				
					logger.info("Adding reset command for " + ((ObjectDefinition) def).getType());
					pst.setInt(1,0);
					pst.setString(2, ((ObjectDefinition) def).getType());
					pst.setInt(3,0);
					pst.setString(4,"");
					pst.setString(5,"reset_all");
					pst.setTimestamp(6,new java.sql.Timestamp(new java.util.Date().getTime()));
					pst.setInt(7,0);
					pst.executeUpdate();
				}
			}
		}
		catch (Exception e) {
			logger.error("Error updating command queue", e);
		}
	}

}
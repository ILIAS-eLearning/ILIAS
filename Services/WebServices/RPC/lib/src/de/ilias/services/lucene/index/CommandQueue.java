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


/**
 * int obj_id
 * string obj_type
 * int sub_id
 * string sub_type
 * string command
 * 
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
	 * @throws SQLException 
	 * 
	 */
	private void loadFromDb() throws SQLException {

		logger.debug("Start reading command queue");
		
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
	 * @return the elements
	 */
	public Vector<CommandQueueElement> getElements() {
		return elements;
	}


}
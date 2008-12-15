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

import java.sql.SQLException;
import java.util.Vector;

import org.apache.log4j.Logger;
import org.apache.lucene.document.Document;

import de.ilias.services.object.ObjectDefinition;
import de.ilias.services.object.ObjectDefinitionException;
import de.ilias.services.object.ObjectDefinitions;
import de.ilias.services.settings.ClientSettings;
import de.ilias.services.settings.LocalSettings;

/**
 * Handles command queue events
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
public class CommandScheduler {

	protected static Logger logger = Logger.getLogger(CommandScheduler.class);
	
	private CommandQueue queue;
	private ObjectDefinitions objDefinitions;
	
	/**
	 * @throws SQLException 
	 * 
	 */
	public CommandScheduler(ObjectDefinitions objDefinitions) throws SQLException {

		setQueue(new CommandQueue());
		this.objDefinitions = objDefinitions;
	}
	
	/**
	 * 
	 */
	public void schedule() {
		
		ObjectDefinition definition;
		
		for(Object el : queue.getElements()) {
			
			try {
				if(((CommandQueueElement) el).getCommand().equals("delete"))
					deleteDocument((CommandQueueElement) el);
				else if(((CommandQueueElement) el).getCommand().equals("reset"))
					deleteDocument((CommandQueueElement) el);
				
				definition = objDefinitions.getDefinitionByType(((CommandQueueElement) el).getObjType());
				addDocuments(definition.getDocuments());
			} 
			catch (ObjectDefinitionException e) {
				logger.warn("No definition found for objType: " + ((CommandQueueElement) el).getObjType());
			}
		}
	}	

	/**
	 * @param el
	 */
	private void deleteDocument(CommandQueueElement el) {
		// TODO Auto-generated method stub
		
	}

	/**
	 * @param documents
	 */
	private void addDocuments(Vector<Document> documents) {

		for(Object el : documents) {
			
			// TODO: add document to index
		}
	
	}




	/**
	 * @param queue the queue to set
	 */
	public void setQueue(CommandQueue queue) {
		this.queue = queue;
	}

	/**
	 * @return the queue
	 */
	public CommandQueue getQueue() {
		return queue;
	}

}
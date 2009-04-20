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

import java.io.IOException;
import java.sql.SQLException;
import java.util.Vector;

import org.apache.log4j.Logger;
import org.apache.lucene.index.CorruptIndexException;
import org.apache.lucene.index.Term;
import org.apache.lucene.store.LockObtainFailedException;

import de.ilias.services.lucene.search.SearchHolder;
import de.ilias.services.lucene.settings.LuceneSettings;
import de.ilias.services.object.ObjectDefinition;
import de.ilias.services.object.ObjectDefinitionException;
import de.ilias.services.object.ObjectDefinitions;
import de.ilias.services.settings.ClientSettings;
import de.ilias.services.settings.ConfigurationException;
import de.ilias.services.settings.LocalSettings;

/**
 * Handles command queue events
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
public class CommandController {

	private static CommandController instance = null;
	protected static Logger logger = Logger.getLogger(CommandController.class);
	
	private Vector<Integer> finished = new Vector<Integer>();
	private CommandQueue queue;
	private ObjectDefinitions objDefinitions;
	private IndexHolder holder;
	
	/**
	 * @throws SQLException 
	 * @throws ConfigurationException 
	 * @throws IOException 
	 * @throws LockObtainFailedException 
	 * @throws CorruptIndexException 
	 * 
	 */
	private CommandController(ObjectDefinitions objDefinitions) throws 
		SQLException,
		CorruptIndexException, 
		LockObtainFailedException, 
		IOException, 
		ConfigurationException {

		queue = CommandQueue.getInstance();
		queue.loadFromDb();
		
		this.objDefinitions = objDefinitions;
		
		holder = IndexHolder.getInstance();
		holder.init();
	}
	
	/**
	 * @throws ConfigurationException 
	 * @throws IOException 
	 * @throws SQLException 
	 * @throws LockObtainFailedException 
	 * @throws CorruptIndexException 
	 * 
	 */
	public CommandController() 
	throws CorruptIndexException, LockObtainFailedException, SQLException, IOException, ConfigurationException {

		this(ObjectDefinitions.getInstance(
				ClientSettings.getInstance(
						LocalSettings.getClientKey()).getAbsolutePath()));
	}

	/**
	 * 
	 * @return
	 * @throws CorruptIndexException
	 * @throws LockObtainFailedException
	 * @throws SQLException
	 * @throws IOException
	 * @throws ConfigurationException
	 */
	public static CommandController getInstance() throws CorruptIndexException, LockObtainFailedException, SQLException, IOException, ConfigurationException {
		
		if(instance == null) {
			return instance = new CommandController();
		}
		return instance;
	}
	
	/**
	 * @param finished the finished to set
	 */
	public void setFinished(Vector<Integer> finished) {
		this.finished = finished;
	}

	/**
	 * @return the finished
	 */
	public Vector<Integer> getFinished() {
		return finished;
	}

	/**
	 * Reset instance
	 */
	public static void reset() {
		
		instance = null;
	}
	
	/**
	 * Init command queue for new index
	 * @throws SQLException
	 */
	public void initCreate() throws SQLException {
	
		queue.deleteAll();
		queue.addAll();
		queue.loadFromDb();
	}
	

	public void initRefresh() throws SQLException {
		
		queue.deleteNonIncremental();
		queue.addNonIncremental();
		queue.loadFromDb();
	}
	
	
	/**
	 * handle command queue.
	 */
	public void start() {
		
		CommandQueueElement currentElement = null;
		
		try {
			while((currentElement = queue.nextElement()) != null) {
			
				String command = currentElement.getCommand();
				
				if(command.equals("reset")) {
					
					// Delete document
					deleteDocument(currentElement);
					addDocument(currentElement);
				}
				else if(command.equals("create")) {
					
					// Create a new document
					// Called for new objects or objects restored from trash
					addDocument(currentElement);
				}
				else if(command.equals("update")) {
					
					// content changed
					deleteDocument(currentElement);
					addDocument(currentElement);
				}
				else if(command.equals("delete")) {
					
					// only delete it
					deleteDocument(currentElement);
				}
				getFinished().add(currentElement.getObjId());
			}
		}
		catch (ObjectDefinitionException e) {
			logger.warn("No definition found for objType: " + currentElement.getObjType());
		} 
		catch (CorruptIndexException e) {
			logger.error(e);
		} 
		catch (IOException e) {
			logger.error(e);
		}
		
	}

	/**
	 * @param finished
	 */
	public synchronized void writeToIndex() {

		try {
			logger.info("Closing writer.");
			holder.getWriter().commit();
			logger.info("Optimizing writer...");
			holder.getWriter().optimize();
			logger.info("Writer optimized!");
			holder.close();
			
			// Finally update status in search_command_queue
			queue.setFinished(getFinished());
			
			LuceneSettings.writeLastIndexTime();
			LuceneSettings.getInstance().refresh();
			
			// Refresh index reader
			SearchHolder.getInstance().init();
			
			// Set object ids finished
			//queue.setFinished(finished);

		} 
		catch (ConfigurationException e) {
			logger.error("Cannot refresh index reader: " + e);
		}
		catch (CorruptIndexException e) {
			logger.fatal("Index Corrupted. Aborting!" + e);
		} 
		catch (IOException e) {
			logger.fatal("Index Corrupted. Aborting!" + e);
		} 
		catch (SQLException e) {
			logger.error("Cannot update search_command_queue: " + e);
		}
	}

	/**
	 * @param el
	 * @throws CorruptIndexException 
	 * @throws ObjectDefinitionException 
	 * @throws DocumentHandlerException 
	 */
	private void addDocument(CommandQueueElement el) throws CorruptIndexException, ObjectDefinitionException {

		ObjectDefinition definition;
		
		try {
			logger.debug("Adding new document!");
			definition = objDefinitions.getDefinitionByType(el.getObjType());
			definition.writeDocument(el);
		}
		catch (DocumentHandlerException e) {
			logger.warn(e);
		}
		catch (IOException e) {
			logger.warn(e);
		}
	}

	/**
	 * @param el
	 * @throws IOException 
	 * @throws CorruptIndexException 
	 */
	private void deleteDocument(CommandQueueElement el) throws CorruptIndexException, IOException {

		logger.debug("Deleteing document with objId: " + String.valueOf(el.getObjId()));
		holder.getWriter().deleteDocuments(new Term("objId",String.valueOf(el.getObjId())));
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
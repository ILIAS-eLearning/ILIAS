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
import java.util.HashMap;
import java.util.Vector;
import java.util.logging.Level;

import org.apache.logging.log4j.LogManager;
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
import org.apache.logging.log4j.Logger;
import org.apache.lucene.index.IndexWriter;

/**
 * Handles command queue events
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
public class CommandController {

	private static final ThreadLocal<CommandController> instance = new ThreadLocal<CommandController>() {
		
		/**
		 *  Init value 
		 */
		protected CommandController initialValue() {
			
			try {
				return new CommandController();
			}
			catch(Throwable t) {
				logger.error(t);
			}
			return null;
		}
	};
	
	private static final int MAX_ELEMENTS = 100;

	protected static Logger logger = LogManager.getLogger(CommandController.class);
	
	private Vector<Integer> finished = new Vector<Integer>();
	private CommandQueue queue;
	private final ObjectDefinitions objDefinitions;
	private final IndexHolder holder;
	
	/**
     *
	 */
	private CommandController(ObjectDefinitions objDefinitions) throws 
		SQLException,
		CorruptIndexException, 
		LockObtainFailedException, 
		IOException, 
		ConfigurationException {

		queue = new CommandQueue();
		
		this.objDefinitions = objDefinitions;
		
		holder = IndexHolder.getInstance();
		holder.init();

		logger.info("New command controller created.");
	}
	
	/**
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
     */
	public static CommandController getInstance() {
		
		try {
			logger.info("Creating new command controller...");
			return new CommandController();
		}
		catch(Throwable t) {
			logger.error(t);
		}
		return null;
		//return instance.get();
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
	 * Init command queue for new index
     */
	public void initCreate() throws SQLException {
	
		queue.deleteAll();
		queue.addAll();
		queue.loadFromDb();
	}
	

	public void initRefresh() throws SQLException, ConfigurationException {
		
		queue.deleteNonIncremental();
		queue.addNonIncremental();
		queue.loadFromDb();
	}

	/**
	 * Load queue elements from given obj ids list
     */
	public void initObjects(Vector<Integer> objIds)  throws SQLException {
		
		queue.loadFromObjectList(objIds);
	}
	
	
	/**
	 * handle command queue.
	 */
	public void start() {
		
		CommandQueueElement currentElement = null;
		
		int elementCounter = 0;
		try {
			while((currentElement = queue.nextElement()) != null) {
			
				logger.info("Current element id: " + currentElement.getObjId() + " " + currentElement.getObjType());
				String command = currentElement.getCommand();
				
				logger.debug("Handling command: " + command + "!");
				
				if(command.equals("reset")) {
					
					// Delete document
					deleteDocument(currentElement);
					try {
						addDocument(currentElement);
					}
					catch(ObjectDefinitionException e) {
						logger.warn("Ignoring deprecated object type " + currentElement.getObjType());
						getQueue().deleteCommandsByType(currentElement.getObjType());
					}
				}
				else if(command.equals("create")) {
					
					// Create a new document
					// Called for new objects or objects restored from trash
					try {
						addDocument(currentElement);
					}
					catch(ObjectDefinitionException e) {
						logger.warn("Ignoring deprecated object type " + currentElement.getObjType());
						getQueue().deleteCommandsByType(currentElement.getObjType());
					}
				}
				else if(command.equals("update")) {
					
					// content changed
					deleteDocument(currentElement);
					try {
						addDocument(currentElement);
					}
					catch(ObjectDefinitionException e) {
						logger.warn("Ignoring deprecated object type " + currentElement.getObjType());
						getQueue().deleteCommandsByType(currentElement.getObjType());
					}
				}
				else if(command.equals("delete")) {
					
					// only delete it
					deleteDocument(currentElement);
				}
				getFinished().add(currentElement.getObjId());
				
				// Update command queue if MAX ELEMENTS is reached.
				if(++elementCounter > MAX_ELEMENTS) { 
					
					synchronized(this) {
						queue.setFinished(this.getFinished());
						this.setFinished(new Vector<Integer>());
						elementCounter = 0;
					}
				}
			}
		}
		catch (SQLException e) {
			logger.error(e);
		}
		catch (CorruptIndexException e) {
			logger.error(e);
		} 
		catch (IOException e) {
			logger.error(e);
		}
		
	}

	/**
     */
	public synchronized boolean writeToIndex() {

		try {
			logger.info("Writer commit.");
			holder.getWriter().commit();
			logger.info("Writer forcing merge...");
			holder.getWriter().forceMerge(IndexHolder.MAX_NUM_SEGMENTS);
			logger.info("Writer forced merge");
			
			// Finally update status in search_command_queue
			queue.setFinished(getFinished());
			
			LuceneSettings.writeLastIndexTime();
			
			// Refresh index reader
			SearchHolder.getInstance().getSearcher().getIndexReader().close();
			SearchHolder.getInstance().init();
			
			// Set object ids finished
			//queue.setFinished(finished);
			return true;

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
		return false;
	}
	
	/**
	 * Close index
	 */
	public synchronized void closeIndex() {
	
		try {
			logger.info("Closing writer");
			holder.getWriter().close();
			logger.info("Writer closed");
			
			// reopen index reader
			logger.info("Reopening index reader...");
			SearchHolder.getInstance().getSearcher().getIndexReader().close();
			SearchHolder.getInstance().init();
			LuceneSettings.getInstance().refresh();
		} 
		catch (ConfigurationException e) {
			logger.error("Cannot close index reader/writer: " + e);
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
     */
	private void deleteDocument(CommandQueueElement el) throws CorruptIndexException, IOException {

		logger.debug("Deleteing document with objId: " + el.getObjId());
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
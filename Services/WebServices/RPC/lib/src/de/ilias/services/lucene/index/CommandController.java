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

import de.ilias.services.object.ObjectDefinition;
import de.ilias.services.object.ObjectDefinitionException;
import de.ilias.services.object.ObjectDefinitions;
import de.ilias.services.settings.ConfigurationException;

/**
 * Handles command queue events
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
public class CommandController {

	protected static Logger logger = Logger.getLogger(CommandController.class);
	
	private CommandQueue queue;
	private ObjectDefinitions objDefinitions;
	private IndexHolder holder;
	private DocumentHolder documentHolder;
	
	/**
	 * @throws SQLException 
	 * @throws ConfigurationException 
	 * @throws IOException 
	 * @throws LockObtainFailedException 
	 * @throws CorruptIndexException 
	 * 
	 */
	public CommandController(ObjectDefinitions objDefinitions) throws 
		SQLException, 
		CorruptIndexException, 
		LockObtainFailedException, 
		IOException, 
		ConfigurationException {

		queue = new CommandQueue();
		queue.debugDelete();
		//queue.debug("frm");
		//queue.debug("frm");
		//queue.debug("cat");
		//queue.debug("crs");
		//queue.debug("fold");
		//queue.debug("grp");
		//queue.debug("lm");
		//queue.debug("glo");
		//queue.debug("svy");
		//queue.debug("wiki");
		//queue.debug("mep");
		queue.debugAll();
		queue.loadFromDb();
		
		this.objDefinitions = objDefinitions;
		
		holder = IndexHolder.getInstance();
		holder.init();
		documentHolder = DocumentHolder.factory();
		
		
	}
	
	/**
	 * @throws IOException, CorruptIndexException 
	 * @throws DocumentHandlerException 
	 * 
	 */
	public void start() throws CorruptIndexException, IOException {
		
		Vector<Integer> finished = new Vector<Integer>();
		
		for(Object el : queue.getElements()) {
			
			try {
				String command = ((CommandQueueElement) el).getCommand();
				
				if(command.equals("reset")) {
					
					// Delete document
					deleteDocument((CommandQueueElement) el);
					addDocument((CommandQueueElement) el);
				}
				else if(command.equals("create")) {
					
					// Create a new document
					// Called for new objects or objects restored from trash
					addDocument((CommandQueueElement) el);
				}
				else if(command.equals("update")) {
					
					// content changed
					deleteDocument((CommandQueueElement) el);
					addDocument((CommandQueueElement) el);
				}
				else if(command.equals("delete")) {
					
					// only delete it
					deleteDocument((CommandQueueElement) el);
				}
				
				finished.add(((CommandQueueElement) el).getObjId());
			} 
			catch (ObjectDefinitionException e) {
				logger.warn("No definition found for objType: " + ((CommandQueueElement) el).getObjType());
			} 
			catch (IOException e) {
				logger.error("Cought IOException" + e);
				e.printStackTrace();
				throw e;
			} 
			catch (DocumentHandlerException e) {
				logger.error("Cought IOException" + e);
			}
		}
		// TODO: write index earlier
		writeToIndex(finished);
	}	

	/**
	 * @param finished
	 */
	private void writeToIndex(Vector<Integer> finished) {

		try {
			logger.info("Closing writer.");
			holder.getWriter().commit();
			logger.info("Optimizing writer...");
			holder.getWriter().optimize();
			logger.info("Writer optimized!");
			holder.close();
			
			// Set object ids finished
			//queue.setFinished(finished);

		} 
		catch (CorruptIndexException e) {
			logger.fatal("Index Corrupted. Aborting!" + e);
		} 
		catch (IOException e) {
			logger.fatal("Index Corrupted. Aborting!" + e);
		} 
		/*
		catch (SQLException e) {
			logger.error("Cannot modify command queue",e);
		}
		*/
	}

	/**
	 * @param el
	 * @throws CorruptIndexException 
	 * @throws ObjectDefinitionException 
	 * @throws DocumentHandlerException 
	 */
	private void addDocument(CommandQueueElement el) throws CorruptIndexException, IOException, ObjectDefinitionException, DocumentHandlerException {

		ObjectDefinition definition;
		
		logger.debug("Adding new document!");
		definition = objDefinitions.getDefinitionByType(el.getObjType());
		definition.writeDocument(el);
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
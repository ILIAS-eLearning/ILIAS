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

import org.apache.log4j.Logger;

import de.ilias.services.db.DBFactory;
import de.ilias.services.settings.LocalSettings;

/**
 * 
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
public class CommandControllerThread extends Thread {

	protected Logger logger = Logger.getLogger(CommandControllerThread.class);
	protected String clientKey = null;
	
	protected CommandController controller = null;
	
	/**
	 * Constructor
	 */
	public CommandControllerThread(String ck, CommandController con) {
		
		clientKey = ck;
		controller = con;
		
		this.setUncaughtExceptionHandler(new Thread.UncaughtExceptionHandler() {

			/**
			 * Overwrite uncuaght exception handler
			 */
			public void uncaughtException(Thread t, Throwable e) {
				
				logger.error("Caught uncaught error: " + e);

				try {
					
					CommandControllerThread nt = new CommandControllerThread(clientKey,controller);
					nt.start();
					nt.join();
				}
				catch(Exception ex) {
					logger.error("New error " + ex);
				}
			}
		});
		
		
	}
	
	/**
	 * Initialize the thread 
	 */
	public void run() {
	
		logger.info("Started new indexer thread...");
		
		// Initialize thread local settings
		LocalSettings.setClientKey(clientKey);
		DBFactory.init();
		
		try {
			controller.start();
		} 
		catch (Exception e) {
			logger.error("Cannot start indexer thread: " + e);
			this.interrupt();
		}
		finally {
			DBFactory.closeAll();
		}
	}
			
}

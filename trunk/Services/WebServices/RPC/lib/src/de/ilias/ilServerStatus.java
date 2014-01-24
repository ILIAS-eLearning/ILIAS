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

package de.ilias;

import java.util.HashMap;

import org.apache.log4j.Logger;

public class ilServerStatus {
	
	public static final String RUNNING = "Runnning";
	public static final String STOPPED = "Stopped";
	public static final String INDEXING = "Indexing";
	
	private static Logger logger = Logger.getLogger(ilServerStatus.class);
	
	private static HashMap<String, Boolean> indexer = new HashMap<String, Boolean>();
	private static boolean active = false;

	
	/**
	 * Check if server is active
	 * @return
	 */
	public static boolean isActive() {
		return active;
	}
	
	/**
	 * Set server active
	 * @param active
	 */
	public static void setActive(boolean active) {
		ilServerStatus.active = active;
	}
	
	/**
	 * Enable an indexer for a specific client
	 * @param clientKey
	 */
	public static void addIndexer(String clientKey) {
		
		indexer.put(clientKey, true);
		setActive(true);
	}
	
	public static boolean isIndexerActive(String clientKey) {
		
		return indexer.containsKey(clientKey);
	}
	
	/**
	 * Remove indexer for a specific client
	 * @param clientKey
	 */
	public static void removeIndexer(String clientKey) {
		
		if(indexer.containsKey(clientKey)) {
			
			indexer.remove(clientKey);
		}
	}
	
	/**
	 * Get current number of running indexers
	 * @return
	 */
	public static int getCountActiveIndexer() {
		
		return indexer.size();
	}
	
	public static String getStatus() {
		
		if(getCountActiveIndexer() != 0) {
			
			return INDEXING + " (" + getCountActiveIndexer() + ")"; 
		}
		if(isActive()) {
			
			return RUNNING;
		}
		return STOPPED;
	}
	
	
	

}

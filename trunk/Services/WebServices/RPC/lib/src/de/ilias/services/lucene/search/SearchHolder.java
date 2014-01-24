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

package de.ilias.services.lucene.search;

import java.io.IOException;
import java.util.HashMap;

import org.apache.log4j.Logger;
import org.apache.lucene.search.IndexSearcher;
import org.apache.lucene.store.FSDirectory;

import de.ilias.services.lucene.index.IndexHolder;
import de.ilias.services.settings.ClientSettings;
import de.ilias.services.settings.ConfigurationException;
import de.ilias.services.settings.LocalSettings;

/**
 * 
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
public class SearchHolder {

	public static int SEARCH_LIMIT = 100;
	
	protected static Logger logger = Logger.getLogger(IndexHolder.class);
	
	private static HashMap<String, SearchHolder> instances = new HashMap<String, SearchHolder>();
	
	private IndexSearcher searcher = null;

	
	/**
	 * @param indexPath
	 * @param indexType
	 * @throws ConfigurationException 
	 * @throws IOException 
	 * @throws IOException 
	 */
	private SearchHolder() throws ConfigurationException, IOException {
		
		init();

	}

	/**
	 * @throws ConfigurationException 
	 * @throws IOException 
	 * 
	 */
	public void init() throws ConfigurationException, IOException {

		ClientSettings client = ClientSettings.getInstance(LocalSettings.getClientKey());
		FSDirectory directory = FSDirectory.getDirectory(client.getIndexPath());
		searcher = new IndexSearcher(directory);
	}

	/**
	 * 
	 * @param clientKey
	 * @return
	 * @throws IOException
	 * @throws ConfigurationException 
	 */
	public static synchronized SearchHolder getInstance(String clientKey) throws 
		IOException, ConfigurationException { 
		
		String hash = clientKey;
		
		if(instances.containsKey(hash)) {
			return instances.get(hash);
		}
		instances.put(hash,new SearchHolder());
		return instances.get(hash);
	}
	
	/**
	 * 
	 * @param indexType
	 * @return
	 * @throws IOException 
	 * @throws IOException
	 * @throws ConfigurationException 
	 */
	public static synchronized SearchHolder getInstance() throws IOException, ConfigurationException {
		
		return getInstance(LocalSettings.getClientKey());
	}

	/**
	 * @return the searcher
	 */
	public IndexSearcher getSearcher() {
		return searcher;
	}


}

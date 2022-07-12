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

import de.ilias.services.lucene.index.IndexDirectoryFactory;
import java.io.IOException;
import java.util.HashMap;

import org.apache.logging.log4j.LogManager;
import org.apache.lucene.search.IndexSearcher;

import de.ilias.services.lucene.index.IndexHolder;
import de.ilias.services.settings.ClientSettings;
import de.ilias.services.settings.ConfigurationException;
import de.ilias.services.settings.LocalSettings;
import org.apache.logging.log4j.Logger;
import org.apache.lucene.index.DirectoryReader;
import org.apache.lucene.index.IndexReader;
import org.apache.lucene.index.IndexWriter;
import org.apache.lucene.store.FSDirectory;

/**
 * 
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
public class SearchHolder {

	public static int SEARCH_LIMIT = 100;
	
	protected static Logger logger = LogManager.getLogger(IndexHolder.class);
	
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
	 * Init searcher
	 * 
	 * @throws ConfigurationException 
	 * @throws IOException 
	 * 
	 */
	public void init() throws ConfigurationException, IOException {

		ClientSettings client = ClientSettings.getInstance(LocalSettings.getClientKey());
		FSDirectory directory = IndexDirectoryFactory.getDirectory(client.getIndexPath());
		IndexReader reader = DirectoryReader.open(directory);
		searcher = new IndexSearcher(reader);
	}
	
	
	/**
	 * Reinit searcher with new indexReader to load new index entries.
	 * Normally called after Index write
	 * We use DirectoryReader.open(IndexWriter) in this case
	 * @throws ConfigurationException
	 * @throws IOException 
	 */
	public void reInit(IndexWriter writer) throws ConfigurationException, IOException {
		
		IndexReader reader = DirectoryReader.open(writer);
		searcher = new IndexSearcher(reader);
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

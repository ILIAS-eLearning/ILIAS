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

package de.ilias.lucene.services.lucene.search;

import java.io.IOException;

import org.apache.log4j.Logger;
import org.apache.lucene.analysis.standard.StandardAnalyzer;
import org.apache.lucene.document.Document;
import org.apache.lucene.queryParser.MultiFieldQueryParser;
import org.apache.lucene.queryParser.ParseException;
import org.apache.lucene.queryParser.QueryParser;
import org.apache.lucene.search.BooleanClause;
import org.apache.lucene.search.IndexSearcher;
import org.apache.lucene.search.Query;
import org.apache.lucene.search.ScoreDoc;
import org.apache.lucene.search.TopDocCollector;
import org.apache.lucene.search.TopDocs;
import org.apache.lucene.store.Directory;
import org.apache.lucene.store.FSDirectory;

import de.ilias.services.settings.ClientSettings;
import de.ilias.services.settings.ConfigurationException;
import de.ilias.services.settings.LocalSettings;

/**
 * 
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
public class RPCSearchHandler {

	Logger logger = Logger.getLogger(RPCSearchHandler.class);
	
	/**
	 * 
	 */
	public RPCSearchHandler() {

	}
	
	/**
	 * 
	 * @param clientKey
	 * @param query
	 */
	public boolean search(String clientKey, String queryString) {

		LocalSettings.setClientKey(clientKey);
		ClientSettings client;
		Directory directory;
		IndexSearcher searcher;
		QueryParser parser;
		
		try {
			
			client = ClientSettings.getInstance(LocalSettings.getClientKey());
			
			logger.info("Searching for: " + queryString);
			directory = FSDirectory.getDirectory(client.getIndexPath());
			searcher = new IndexSearcher(directory);
			
			//parser = new QueryParser("title",new StandardAnalyzer());
			
			String[] fields = {"description","title"};
			BooleanClause.Occur[] flags = {BooleanClause.Occur.SHOULD,BooleanClause.Occur.SHOULD};
			
			Query query = MultiFieldQueryParser.parse(queryString,
					fields, 
					flags,
					new StandardAnalyzer());
			
			//parser.parse(queryString);
			//Query query = parser.parse(queryString);
			
			
			TopDocCollector collector = new TopDocCollector(1000);
			searcher.search(query,collector);
			ScoreDoc[] hits = collector.topDocs().scoreDocs;
			logger.info("Found " + hits.length + " matches");
			
			
			for(int i = 0; i < hits.length;i++) {
				
				Document hitDoc = searcher.doc(hits[i].doc);
				logger.info("Found title: " + hitDoc.get("title") + 
						" description: " + hitDoc.get("description"));
				logger.info("Score: "+ hits[i].score);
			}
			
			logger.debug("Index directory = " + client.getIndexPath().getCanonicalPath());
			return true;
		}
		catch(ConfigurationException e) {
			logger.error(e);
		} 
		catch (IOException e) {
			logger.error(e);	
		} 
		catch (ParseException e) {
			logger.error(e);
		}
		return false;
	}
	
}

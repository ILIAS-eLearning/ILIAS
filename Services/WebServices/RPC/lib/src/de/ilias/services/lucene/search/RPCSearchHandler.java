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
import java.io.StringReader;
import java.util.HashMap;
import java.util.Vector;

import org.apache.log4j.Logger;
import org.apache.lucene.analysis.SimpleAnalyzer;
import org.apache.lucene.analysis.standard.StandardAnalyzer;
import org.apache.lucene.analysis.TokenStream;
import org.apache.lucene.document.Document;
import org.apache.lucene.queryParser.MultiFieldQueryParser;
import org.apache.lucene.queryParser.ParseException;
import org.apache.lucene.search.BooleanClause;
import org.apache.lucene.search.IndexSearcher;
import org.apache.lucene.search.Query;
import org.apache.lucene.search.ScoreDoc;
import org.apache.lucene.search.Scorer;
import org.apache.lucene.search.TopDocCollector;
import org.apache.lucene.search.BooleanClause.Occur;
import org.apache.lucene.search.highlight.Fragmenter;
import org.apache.lucene.search.highlight.Highlighter;
import org.apache.lucene.search.highlight.QueryScorer;
import org.apache.lucene.search.highlight.SimpleFragmenter;
import org.apache.lucene.search.highlight.SimpleHTMLFormatter;
import org.apache.lucene.store.Directory;
import org.apache.lucene.store.FSDirectory;

import de.ilias.services.lucene.index.FieldInfo;
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
	 * Multi field searcher
	 * Searches in all defined fields.
	 * @todo allow configuration of searchable fields.
	 * 
	 * 
	 * @param clientKey
	 * @param query
	 */
	public Vector<Integer> search(String clientKey, String queryString) {

		LocalSettings.setClientKey(clientKey);
		ClientSettings client;
		Directory directory;
		IndexSearcher searcher;
		FieldInfo fieldInfo;
		
		Vector<Integer> results = new Vector<Integer>();
		
		try {
			
			client = ClientSettings.getInstance(LocalSettings.getClientKey());
			fieldInfo = FieldInfo.getInstance(LocalSettings.getClientKey());
			
			// Append doctype
			queryString += " AND docType:combined";
			
			logger.info("Searching for: " + queryString);
			directory = FSDirectory.getDirectory(client.getIndexPath());
			searcher = new IndexSearcher(directory);
			
			Vector<Occur> occurs = new Vector<Occur>();
			for(int i = 0; i < fieldInfo.getFieldSize(); i++) {
				occurs.add(BooleanClause.Occur.SHOULD);
			}
			
			Query query = MultiFieldQueryParser.parse(queryString,
					fieldInfo.getFieldsAsStringArray(),
					occurs.toArray(new Occur[0]),
					new StandardAnalyzer());

			for(Object f : fieldInfo.getFields()) {
				logger.info(((String) f).toString());
			}
			
			TopDocCollector collector = new TopDocCollector(1000);
			searcher.search(query,collector);
			ScoreDoc[] hits = collector.topDocs().scoreDocs;
			
			logger.info("Found " + hits.length + " matches");
			for(int i = 0; i < hits.length;i++) {
				
				Document hitDoc = searcher.doc(hits[i].doc);
				//Explanation expl = searcher.explain(query,hits[i].doc);
				//logger.info("Explaination: " + expl.toString());
				logger.debug("Found objId: " + hitDoc.get("objId") + 
						" type: " + hitDoc.get("type") + " title: " + hitDoc.get("title"));
				logger.debug("Score: "+ hits[i].score);
				
				results.add(Integer.parseInt(hitDoc.get("objId")));
			}
			return results;
		}
		catch(ConfigurationException e) {
			logger.error(e);
		} 
		catch (IOException e) {
			logger.warn(e);	
		} 
		catch (ParseException e) {
			logger.info(e);
		}
		catch(Exception e) {
			logger.error(e);
		}
		return null;
	}

	/**
	 * 
	 * @param clientKey
	 * @param objIds
	 * @return
	 */
	public Vector<HashMap<String, String>> highlight(String clientKey, Vector<Integer> objIds, String queryString) {

		LocalSettings.setClientKey(clientKey);
		ClientSettings client;
		FieldInfo fieldInfo;
		FSDirectory directory;
		IndexSearcher searcher;
		
		Vector<HashMap<String, String> > results = new Vector<HashMap<String,String>>();
		
		// Rewrite query
		StringBuffer newQuery = new StringBuffer();
		newQuery.append(queryString);
		newQuery.append('(');
		for(int i = 0; i < objIds.size(); i++) {
			newQuery.append("+objId:");
			newQuery.append(objIds.get(i));
			newQuery.append(' ');
		}
		newQuery.append(')');
		
		try {
			
			client = ClientSettings.getInstance(LocalSettings.getClientKey());
			fieldInfo = FieldInfo.getInstance(LocalSettings.getClientKey());
			
			logger.info("Searching for: " + newQuery);
			directory = FSDirectory.getDirectory(client.getIndexPath());
			searcher = new IndexSearcher(directory);
			
			Vector<Occur> occurs = new Vector<Occur>();
			for(int i = 0; i < fieldInfo.getFieldSize(); i++) {
				occurs.add(BooleanClause.Occur.SHOULD);
			}
			
			Query query = searcher.rewrite(MultiFieldQueryParser.parse(newQuery.toString(),
					fieldInfo.getFieldsAsStringArray(),
					occurs.toArray(new Occur[0]),
					new StandardAnalyzer()));
			
			TopDocCollector collector = new TopDocCollector(500);
			searcher.search(query,collector);
			ScoreDoc[] hits = collector.topDocs().scoreDocs;

			QueryScorer queryScorer = new QueryScorer(query);
			SimpleHTMLFormatter formatter = new SimpleHTMLFormatter(
					"<strong>", "</strong>"); 
			Highlighter highlighter = new Highlighter(formatter,
					queryScorer);
			Fragmenter fragmenter = new SimpleFragmenter(100);
			highlighter.setTextFragmenter(fragmenter);

			for(int i = 0; i < hits.length;i++) {
				
				Document hitDoc = searcher.doc(hits[i].doc);
				String title = hitDoc.get("title");
				if(title == null) {
					continue;
				}
			
				TokenStream token =
					new StandardAnalyzer().tokenStream("title", new StringReader(title));
				String fragment = highlighter.getBestFragment(token, title);
				logger.info("Fragmented: " + fragment);
			}
		}
		catch(Exception e) {
			logger.error(e);
			e.printStackTrace();
		}
		return null;
	}
	
}

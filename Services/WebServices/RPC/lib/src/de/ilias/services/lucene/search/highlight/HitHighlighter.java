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

package de.ilias.services.lucene.search.highlight;

import java.io.IOException;
import java.io.StringReader;
import java.util.HashMap;
import java.util.Vector;

import org.apache.log4j.Logger;
import org.apache.lucene.analysis.TokenStream;
import org.apache.lucene.analysis.standard.StandardAnalyzer;
import org.apache.lucene.document.Document;
import org.apache.lucene.index.CorruptIndexException;
import org.apache.lucene.search.IndexSearcher;
import org.apache.lucene.search.Query;
import org.apache.lucene.search.ScoreDoc;
import org.apache.lucene.search.highlight.Fragmenter;
import org.apache.lucene.search.highlight.Highlighter;
import org.apache.lucene.search.highlight.QueryScorer;
import org.apache.lucene.search.highlight.SimpleFragmenter;
import org.apache.lucene.search.highlight.SimpleHTMLFormatter;

import de.ilias.services.lucene.index.FieldInfo;
import de.ilias.services.lucene.search.SearchHolder;
import de.ilias.services.settings.ConfigurationException;
import de.ilias.services.settings.LocalSettings;

/**
 * 
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
public class HitHighlighter {

	private static int NUM_HIGHLIGHT = 3;
	private static String HIGHLIGHT_SEPARATOR = "...";
	
	private static String HIGHLIGHT_BEGIN_TAG = "<span class=\"ilSearchHighlight\">";
	private static String HIGHLIGHT_END_TAG = "</span>";
	
	protected static Logger logger = Logger.getLogger(HitHighlighter.class);
	
	private IndexSearcher searcher;
	private Query query;
	private ScoreDoc[] hits;
	
	private Highlighter highlighter;
	private FieldInfo fieldInfo;
	private HighlightHits result;
	
	
	/**
	 * @throws IOException 
	 * @throws ConfigurationException 
	 * 
	 */
	public HitHighlighter(Query query,ScoreDoc[] hits) throws ConfigurationException, IOException {

		this.query = query;
		this.hits = hits;
		init();
	}
	
	/**
	 * @throws IOException 
	 * @throws CorruptIndexException 
	 * 
	 */
	public void highlight() throws CorruptIndexException, IOException {

		result = new HighlightHits();
		HighlightObject resObject;
		HighlightItem resItem;
		HighlightField resField;
		
		TokenStream token;
		String fragment;
		
		String[] fields = fieldInfo.getFieldsAsStringArray();
		for(int i = 0; i < hits.length;i++) {
			
			StringBuffer allContent = new StringBuffer();
			Document hitDoc = searcher.doc(hits[i].doc);

			int objId;
			int subItem;
			// Add result object
			try {
				objId = Integer.parseInt(hitDoc.get("objId"));
			}
			catch(NumberFormatException e) {
				logger.warn("Found invalid document with title " + hitDoc.get("title"));
				continue;
			}
			try {
				subItem = Integer.parseInt(hitDoc.get("subItem"));
			}
			catch(NumberFormatException e) {
				subItem = 0;
			}
			
			
			resObject = result.initObject(objId);
			resItem = resObject.addItem(subItem);
			
			// Title
			if(hitDoc.get("title") != null ) { 
				token = new StandardAnalyzer().tokenStream("title", new StringReader(hitDoc.get("title")));
				fragment = highlighter.getBestFragments(token,hitDoc.get("title"), NUM_HIGHLIGHT, HIGHLIGHT_SEPARATOR);
				if(fragment.length() != 0) {
					resItem.addField(new HighlightField("title",fragment));
				}
			}
			
			// Description
			if(hitDoc.get("description") != null) {
				token = new StandardAnalyzer().tokenStream("description", new StringReader(hitDoc.get("description")));
				fragment = highlighter.getBestFragments(token,hitDoc.get("description"), NUM_HIGHLIGHT, HIGHLIGHT_SEPARATOR);
				if(fragment.length() != 0) {
					resItem.addField(new HighlightField("description",fragment));
				}
			}
			// All content
			for(int j = 0; j < fields.length; j++) {
				
				if(fields[j].equals("title") || fields[j].equals("description")) {
					continue;
				}
				
				if(hitDoc.get(fields[j]) != null) {
					allContent.append(hitDoc.get(fields[j]));
					allContent.append(' ');
				}
			}
		
			token =	new StandardAnalyzer().tokenStream("allContent", new StringReader(allContent.toString()));
			fragment = highlighter.getBestFragments(token,allContent.toString(), NUM_HIGHLIGHT, HIGHLIGHT_SEPARATOR);
			//logger.debug("Fragmented: " + fragment);
			
			if(fragment.length() != 0) {
				resItem.addField(new HighlightField("content",fragment));
			}
		}
	}

	/**
	 * @throws ConfigurationException 
	 * @throws IOException 
	 * 
	 */
	private void init() throws ConfigurationException, IOException {

		// init highlighter
		QueryScorer queryScorer = new QueryScorer(query);
		SimpleHTMLFormatter formatter = new SimpleHTMLFormatter(HIGHLIGHT_BEGIN_TAG,HIGHLIGHT_END_TAG);
		highlighter = new Highlighter(formatter,
				queryScorer);
		Fragmenter fragmenter = new SimpleFragmenter(100);
		highlighter.setTextFragmenter(fragmenter);
		
		// init fieldinfo
		fieldInfo = FieldInfo.getInstance(LocalSettings.getClientKey());
		
		// init searcher
		searcher = SearchHolder.getInstance().getSearcher();
		
		return;
	}

	/**
	 * @return
	 */
	public String toXML() {

		return result.toXML();
	}


}

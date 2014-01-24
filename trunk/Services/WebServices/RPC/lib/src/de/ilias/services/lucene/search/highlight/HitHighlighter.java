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
import java.sql.SQLException;

import org.apache.log4j.Logger;
import org.apache.lucene.analysis.TokenStream;
import org.apache.lucene.analysis.standard.StandardAnalyzer;
import org.apache.lucene.document.Document;
import org.apache.lucene.document.Field;
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
import de.ilias.services.lucene.settings.LuceneSettings;
import de.ilias.services.settings.ConfigurationException;
import de.ilias.services.settings.LocalSettings;

/**
 * 
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
public class HitHighlighter {

	private static int FRAGMENT_TITLE_SIZE = 10000;
	private static String HIGHLIGHT_SEPARATOR = "...";
	
	private static String HIGHLIGHT_BEGIN_TAG = "<span class=\"ilSearchHighlight\">";
	private static String HIGHLIGHT_END_TAG = "</span>";
	
	protected static Logger logger = Logger.getLogger(HitHighlighter.class);
	
	private IndexSearcher searcher;
	private Query query;
	private ScoreDoc[] hits;
	
	private Highlighter highlighter;
	private Highlighter titleHighlighter;
	private FieldInfo fieldInfo;
	private HighlightHits result;
	private LuceneSettings luceneSettings;
	
	
	/**
	 * @throws IOException 
	 * @throws ConfigurationException 
	 * @throws SQLException 
	 * 
	 */
	public HitHighlighter(Query query,ScoreDoc[] hits) throws ConfigurationException, IOException, SQLException {

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
			
			// first score is max score
			if(i == 0) {
				result.setMaxScore(hits[i].score);
			}
			
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
			resItem.setAbsoluteScore(hits[i].score);
			
			// Title
			if(hitDoc.get("title") != null ) { 
				token = new StandardAnalyzer().tokenStream("title", new StringReader(hitDoc.get("title")));
				fragment = titleHighlighter.getBestFragments(
						token,
						hitDoc.get("title"),
						luceneSettings.getNumFragments(),
						HIGHLIGHT_SEPARATOR);
				if(fragment.length() != 0) {
					resItem.addField(new HighlightField("title",fragment));
				}
			}
			
			// Description
			if(hitDoc.get("description") != null) {
				token = new StandardAnalyzer().tokenStream("description", new StringReader(hitDoc.get("description")));
				fragment = titleHighlighter.getBestFragments(
						token,
						hitDoc.get("description"),
						luceneSettings.getNumFragments(),
						HIGHLIGHT_SEPARATOR);
				if(fragment.length() != 0) {
					resItem.addField(new HighlightField("description",fragment));
				}
			}
			// All content
			for(int j = 0; j < fields.length; j++) {
				
				// Do not add metaData Field, since this information is stored redundant in lom* fields
				if(fields[j].equals("metaData")) {
					continue;
				}
				
				if(fields[j].equals("title") || fields[j].equals("description")) {
					continue;
				}
				
				Field[] separatedFields = hitDoc.getFields(fields[j]);
				for(int k = 0; k < separatedFields.length; k++) {
					allContent.append(separatedFields[k].stringValue());
					allContent.append(" ");
				}
			}
			//logger.debug("All content" + allContent.toString());
			token =	new StandardAnalyzer().tokenStream("content", new StringReader(allContent.toString()));
			fragment = highlighter.getBestFragments(
					token,
					allContent.toString(),
					luceneSettings.getNumFragments(),
					HIGHLIGHT_SEPARATOR);
			//logger.debug("Fragmented: " + fragment);
			
			if(fragment.length() != 0) {
				//logger.debug("Found fragment: " + fragment);
				resItem.addField(new HighlightField("content",fragment));
			}
		}
	}

	/**
	 * @throws ConfigurationException 
	 * @throws IOException 
	 * @throws SQLException 
	 * 
	 */
	private void init() throws ConfigurationException, IOException, SQLException {

		// init lucene settings
		luceneSettings = LuceneSettings.getInstance();

		// init highlighter
		QueryScorer queryScorer = new QueryScorer(query);
		SimpleHTMLFormatter formatter = new SimpleHTMLFormatter(HIGHLIGHT_BEGIN_TAG,HIGHLIGHT_END_TAG);
		
		// Default highlighter
		highlighter = new Highlighter(formatter,queryScorer);
		Fragmenter fragmenter = new SimpleFragmenter(luceneSettings.getFragmentSize());
		highlighter.setTextFragmenter(fragmenter);
		highlighter.setMaxDocCharsToAnalyze(Integer.MAX_VALUE);
		
		// Title description highlighter -> bigger FRAGMENT SIZE
		titleHighlighter = new Highlighter(formatter,queryScorer);
		Fragmenter titleFragmenter = new SimpleFragmenter(FRAGMENT_TITLE_SIZE);
		titleHighlighter.setTextFragmenter(titleFragmenter);
		
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
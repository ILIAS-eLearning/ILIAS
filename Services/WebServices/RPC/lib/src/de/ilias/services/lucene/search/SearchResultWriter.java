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
import java.util.Vector;

import org.apache.log4j.Logger;
import org.apache.lucene.document.Document;
import org.apache.lucene.index.CorruptIndexException;
import org.apache.lucene.search.IndexSearcher;
import org.apache.lucene.search.ScoreDoc;
import org.jdom.output.XMLOutputter;

import de.ilias.services.settings.ConfigurationException;

/**
 * 
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
public class SearchResultWriter {

	protected Logger logger = Logger.getLogger(SearchResultWriter.class);
	
	private IndexSearcher searcher = null;
	private ScoreDoc[] hits = null;
	private SearchHits result = null;
	private int offset = 0;

	/**
	 * @param hits
	 * @throws ConfigurationException 
	 * @throws IOException 
	 */
	public SearchResultWriter(ScoreDoc[] hits) throws IOException, ConfigurationException {
		
		this.hits = hits;
		
		searcher = SearchHolder.getInstance().getSearcher();
		result = new SearchHits();
	}

	/**
	 * @throws IOException 
	 * @throws CorruptIndexException 
	 * 
	 */
	public void write() throws CorruptIndexException, IOException {

		result.setTotalHits(hits.length);
		logger.info("Found " + result.getTotalHits() + " hits!");
		result.setLimit(SearchHolder.SEARCH_LIMIT);

		SearchObject object;
		Document hitDoc;
		for(int i = 0; i < hits.length;i++) {
			// Set max score
			if(i == 0) {
				result.setMaxScore(hits[i].score);
			}
			if(i < getOffset()) {
				continue;
			}
			if(i >= (getOffset() + SearchHolder.SEARCH_LIMIT)) {
				logger.debug("Reached result limit. Aborting!");
				break;
			}
			try {
				logger.debug("Added object");
				object = new SearchObject();
				hitDoc = searcher.doc(hits[i].doc);
				object.setId(Integer.parseInt(hitDoc.get("objId")));
				object.setAbsoluteScore(hits[i].score);
				result.addObject(object);
			}
			catch (NumberFormatException e) {
				logger.warn("Found invalid document (missing objId) with document id: " + hits[i].doc);
			}
		}
	}

	/**
	 * @return
	 */
	public String toXML() {

		org.jdom.Document doc = new org.jdom.Document(result.addXML());
		XMLOutputter outputter = new XMLOutputter();
		return outputter.outputString(doc);
		
	}

	/**
	 * @param offset the offset to set
	 */
	public void setOffset(int offset) {
		this.offset = offset;
	}

	/**
	 * @return the offset
	 */
	public int getOffset() {
		return offset;
	}

}

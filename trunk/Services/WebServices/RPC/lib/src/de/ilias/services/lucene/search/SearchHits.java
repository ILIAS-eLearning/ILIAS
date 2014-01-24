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

import java.util.Vector;

import org.apache.log4j.Logger;
import org.jdom.Element;


/**
 * 
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
public class SearchHits implements ResultExport {

	protected static Logger logger = Logger.getLogger(SearchHits.class);
	
	private int totalHits = 0;
	private int limit = 0;
	private double maxScore = 0.0;
	private Vector<SearchObject> objects = new Vector<SearchObject>();

	/**
	 *  
	 */
	public SearchHits() {

	}
	
	/**
	 * 
	 * @param object
	 */
	public void addObject(SearchObject object) {
		
		objects.add(object);
	}

	
	/**
	 * @return the totalHits
	 */
	public int getTotalHits() {
		return totalHits;
	}
	/**
	 * @param totalHits the totalHits to set
	 */
	public void setTotalHits(int totalHits) {
		this.totalHits = totalHits;
	}
	/**
	 * @return the limit
	 */
	public int getLimit() {
		return limit;
	}
	/**
	 * @param limit the limit to set
	 */
	public void setLimit(int limit) {
		this.limit = limit;
	}

	/**
	 * @see de.ilias.services.lucene.search.ResultExport#addXML()
	 */
	public Element addXML() {
		
		Element hits = new Element("Hits");
		hits.setAttribute("totalHits", String.valueOf(getTotalHits()));
		hits.setAttribute("maxScore", String.valueOf(getMaxScore()));
		hits.setAttribute("limit", String.valueOf(getLimit()));
		
		for(Object obj : objects) {
			hits.addContent(((ResultExport) obj).addXML());
		}
		return hits;
	}
	/**
	 * @param maxScore the maxScore to set
	 */
	public void setMaxScore(double maxScore) {
		this.maxScore = maxScore;
	}
	/**
	 * @return the maxScore
	 */
	public double getMaxScore() {
		return maxScore;
	}

}

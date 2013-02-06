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

import java.util.HashMap;

import org.apache.log4j.Logger;
import org.jdom.Document;
import org.jdom.Element;
import org.jdom.output.XMLOutputter;

import de.ilias.services.lucene.search.ResultExport;

/**
 * Highlight results (top most xml element)
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
public class HighlightHits implements ResultExport {

	protected static Logger logger = Logger.getLogger(HighlightHits.class);
	
	private HashMap<Integer, HighlightObject> objects = new HashMap<Integer, HighlightObject>();
	
	private double maxScore = 0;
	
	/**
	 * 
	 */
	public HighlightHits() {
	}

	public HighlightObject initObject(int objId) {
		
		if(objects.containsKey(objId)) {
			//logger.debug("Reusing object with id: " + String.valueOf(objId));
			return objects.get(objId);
		}
		//logger.debug("New object with id: " + String.valueOf(objId));
		objects.put(objId, new HighlightObject(objId));
		return objects.get(objId);
	}
	
	
	/**
	 * @return the objects
	 */
	public HashMap<Integer, HighlightObject> getObjects() {
		return objects;
	}
	
	/**
	 * Set score
	 * @param score 
	 */
	public void setMaxScore(double score) {
		maxScore = score;
	}
	
	/**
	 * Get max score
	 * @return 
	 */
	public double getMaxScore() {
		return maxScore;
	}
	
	
	public String toXML() {
		
		Document doc = new Document(addXML()); 
		
		XMLOutputter outputter = new XMLOutputter();
		return outputter.outputString(doc);
		
	}

	/**
	 * Add xml
	 * @see de.ilias.services.lucene.search.highlight.HighlightResultExport#addXML(org.jdom.Element)
	 */
	public Element addXML() {

		Element hits = new Element("Hits");
		hits.setAttribute("maxScore",String.valueOf(getMaxScore()));
		
		for(Object obj : objects.values()) {
			
			hits.addContent(((ResultExport) obj).addXML());
		}
		return hits;
	}
}

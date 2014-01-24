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

import java.util.Vector;

import org.apache.log4j.Logger;
import org.jdom.Element;

import de.ilias.services.lucene.search.ResultExport;

/**
 * 
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
public class HighlightItem implements ResultExport {

	protected static Logger logger = Logger.getLogger(HighlightItem.class); 
	
	private int subId;
	private double absoluteScore = 0;
	private Vector<HighlightField> fields = new Vector<HighlightField>();
	

	/**
	 * 
	 */
	public HighlightItem() {

	
	}

	/**
	 * @param subId
	 */
	public HighlightItem(int subId) {
		
		this.setSubId(subId);
	}

	/**
	 * @param subId the subId to set
	 */
	public void setSubId(int subId) {
		this.subId = subId;
	}

	/**
	 * @return the subId
	 */
	public int getSubId() {
		return subId;
	}
	
	/**
	 * Score
	 * @param score 
	 */
	public void setAbsoluteScore(double score) {
		this.absoluteScore = score;
	}
	
	/**
	 * Get absolute score
	 * @return 
	 */
	public double getAbsoluteScore() {
		return absoluteScore;
	}

	/**
	 * 
	 * @param field
	 * @return
	 */
	public HighlightField addField(HighlightField field) {
		
		fields.add(field);
		return field;
	}
	
	/**
	 * @return the fields
	 */
	public Vector<HighlightField> getFields() {
		return fields;
	}

	/**
	 * Add xml
	 * @see de.ilias.services.lucene.search.highlight.HighlightResultExport#addXML(org.jdom.Element)
	 */
	public Element addXML() {

		Element item = new Element("Item").setAttribute("id", String.valueOf(getSubId()));
		item.setAttribute("id",String.valueOf(getSubId()));
		item.setAttribute("absoluteScore",String.valueOf(getAbsoluteScore()));
		
		for(Object field : fields) {
			
			item.addContent(((ResultExport) field).addXML());
		}
		return item;
	}

}

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


import org.apache.log4j.Logger;
import org.jdom.Element;

import de.ilias.services.lucene.search.ResultExport;
import java.util.Comparator;
import java.util.TreeMap;

/**
 * 
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
public class HighlightObject implements ResultExport, Comparator {

	protected static Logger logger = Logger.getLogger(HighlightObject.class);
	
	private TreeMap<Integer, HighlightItem> items = new TreeMap<Integer, HighlightItem>();
	private TreeMap<Integer, HighlightItem> sortedItems = new TreeMap<Integer, HighlightItem>();
	
	private int objId;
	/**
	 * 
	 */
	public HighlightObject() {

	}

	/**
	 * @param objId
	 */
	public HighlightObject(int objId) {
		
		this.setObjId(objId);
	}

	public HighlightItem addItem(int subId) {
		
		if(items.containsKey(subId)) {
			return items.get(subId);
		}
		items.put(subId, new HighlightItem(subId));
		return items.get(subId);
	}
	/**
	 * @return the items
	 */
	public TreeMap<Integer, HighlightItem> getItems() {
		return items;
	}

	/**
	 * @param objId the objId to set
	 */
	public void setObjId(int objId) {
		this.objId = objId;
	}

	/**
	 * @return the objId
	 */
	public int getObjId() {
		return objId;
	}

	/**
	 * Add xml
	 * @see de.ilias.services.lucene.search.highlight.HighlightResultExport#addXML(org.jdom.Element)
	 */
	public Element addXML() {

		Element obj = new Element("Object");
		obj.setAttribute("id",String.valueOf(getObjId()));
		
		sortedItems = new TreeMap(this);
		sortedItems.putAll(items);
		
		for(Object item : sortedItems.values()) {
			
			obj.addContent(((ResultExport) item).addXML());
		}
		return obj;
	}

	/**
	 * Compare items by absolute score
	 * @param o1
	 * @param o2
	 * @return 
	 */
	public int compare(Object o1, Object o2) {
		
		int index1 = (Integer) o1;
		int index2 = (Integer) o2;
		
		if(items.get(index1).getAbsoluteScore() < items.get(index2).getAbsoluteScore()) {
			return 1;
		}
		if(items.get(index1).getAbsoluteScore() > items.get(index2).getAbsoluteScore()) {
			return -1;
		}
		return 0;
	}
}

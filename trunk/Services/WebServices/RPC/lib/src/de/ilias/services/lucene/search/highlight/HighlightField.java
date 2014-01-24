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
import de.ilias.services.xml.XMLUtils;

/**
 * 
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
public class HighlightField implements ResultExport {

	protected static Logger logger = Logger.getLogger(HighlightField.class);
	
	private String name;
	private String highlight;
	
	/**
	 * 
	 */
	public HighlightField(String name, String highlight) {
		
		setName(name);
		setHighlight(highlight);
	}

	/**
	 * @param name the name to set
	 */
	public void setName(String name) {
		this.name = name;
	}

	/**
	 * @return the name
	 */
	public String getName() {
		return name;
	}

	/**
	 * @param highlight the highlight to set
	 */
	public void setHighlight(String highlight) {
		this.highlight = highlight;
	}

	/**
	 * @return the highlight
	 */
	public String getHighlight() {
		return highlight;
	}

	/**
	 * Add xml
	 * @see de.ilias.services.lucene.search.highlight.HighlightResultExport#addXML(org.jdom.Element)
	 */
	public Element addXML() {

		Element field = new Element("Field");
		field.setAttribute("name", getName());
		//field.addContent(getHighlight());
		field.setText(XMLUtils.stripNonValidXMLCharacters(getHighlight()));
		
		return field;
	}

}

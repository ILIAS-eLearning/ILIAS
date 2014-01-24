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

import org.apache.log4j.Logger;
import org.jdom.Element;

/**
 * 
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
public class SearchObject implements ResultExport {

	protected static Logger logger = Logger.getLogger(SearchObject.class);
	
	private double absoluteScore = 0;
	private String relativeScore = "100%";
	private int id;
	
	
	/**
	 * @param absoluteScore the absoluteScore to set
	 */
	public void setAbsoluteScore(double absoluteScore) {
		this.absoluteScore = absoluteScore;
	}

	/**
	 * @return the absoluteScore
	 */
	public double getAbsoluteScore() {
		return absoluteScore;
	}

	/**
	 * @param relativeScore the relativeScore to set
	 */
	public void setRelativeScore(String relativeScore) {
		this.relativeScore = relativeScore;
	}

	/**
	 * @return the relativeScore
	 */
	public String getRelativeScore() {
		return relativeScore;
	}

	/**
	 * @param id the id to set
	 */
	public void setId(int id) {
		this.id = id;
	}

	/**
	 * @return the id
	 */
	public int getId() {
		return id;
	}

	/**
	 * @see de.ilias.services.lucene.search.ResultExport#addXML()
	 */
	public Element addXML() {
		
		Element object = new Element("Object");
		object.setAttribute("id", String.valueOf(getId()));
		object.setAttribute("absoluteScore", String.valueOf(getAbsoluteScore()));
		return object;
	}
}

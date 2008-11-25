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

package de.ilias.services.object;

import java.util.Vector;

import org.apache.log4j.Logger;

/**
 * 
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
public class ObjectDefinition {

	protected Logger logger = Logger.getLogger(ObjectDefinition.class);
	
	private String type;
	private Vector<DocumentDefinition> documents = new Vector<DocumentDefinition>();
	
	/**
	 * 
	 */
	public ObjectDefinition(String type) {
		
		this();
		this.setType(type);
	}

	/**
	 * 
	 */
	public ObjectDefinition() {

	}

	/**
	 * @param type the type to set
	 */
	public void setType(String type) {
		
		logger.debug("Found new definition for type: " + type);
		this.type = type;
	}

	/**
	 * @return the type
	 */
	public String getType() {
		return type;
	}

	/**
	 * @return the documents
	 */
	public Vector<DocumentDefinition> getDocuments() {
		return documents;
	}

	public void addDocument(DocumentDefinition doc) {
		
		documents.add(doc);
	}
	
	public void removeDocument(DocumentDefinition doc) {
		
		int index;
		
		while((index = documents.indexOf(doc)) != -1) {
			documents.remove(index);
		}
		return;
	}
	
	
}

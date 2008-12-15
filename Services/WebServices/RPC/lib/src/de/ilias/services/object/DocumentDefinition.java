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
import org.apache.lucene.document.Document;

import de.ilias.services.lucene.index.DocumentHandler;
import de.ilias.services.lucene.index.DocumentHandlerException;

/**
 * 
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
public class DocumentDefinition implements DocumentHandler {

	protected Logger logger = Logger.getLogger(DocumentDefinition.class);

	private String type;
	private Vector<DataSource> dataSource = new Vector<DataSource>();
	
	/**
	 * 
	 */
	public DocumentDefinition(String type) {
		this.type = type;
	}

	/**
	 * @param type the type to set
	 */
	public void setType(String type) {
		this.type = type;
	}

	/**
	 * @return the type
	 */
	public String getType() {
		return type;
	}

	/**
	 * @return the dataSource
	 */
	public Vector<DataSource> getDataSource() {
		return dataSource;
	}

	/**
	 * @param dataSource the dataSource to set
	 */
	public void setDataSource(Vector<DataSource> dataSource) {
		this.dataSource = dataSource;
	}
	
	/**
	 * 
	 * @param source
	 */
	public void addDataSource(DataSource source) {
		this.dataSource.add(source);
	}

	/* (non-Javadoc)
	 * @see java.lang.Object#toString()
	 */
	@Override
	public String toString() {
		
		StringBuffer out = new StringBuffer();
		
		out.append("Document of type = " + getType());
		out.append("\n");
		
		for(Object doc : getDataSource()) {
			
			out.append(doc.toString());
			out.append("\n");
		}
		return out.toString();
	}

	/* (non-Javadoc)
	 * @see de.ilias.services.lucene.index.DocumentHandler#getDocument()
	 */
	public Document getDocument() throws DocumentHandlerException {

		
		
		return null;
	}
}

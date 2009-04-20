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

import java.io.IOException;
import java.sql.ResultSet;
import java.util.Vector;

import org.apache.log4j.Logger;
import org.apache.lucene.document.Document;

import de.ilias.services.lucene.index.CommandQueueElement;
import de.ilias.services.lucene.index.DocumentHandler;
import de.ilias.services.lucene.index.DocumentHandlerException;
import de.ilias.services.lucene.index.DocumentHolder;
import de.ilias.services.lucene.index.IndexHolder;

/**
 * 
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
public class ObjectDefinition implements DocumentHandler {

	public static final String TYPE_FULL = "full";
	public static final String TYPE_INCREMENTAL = "incremental";

	protected Logger logger = Logger.getLogger(ObjectDefinition.class);
	
	private String type;
	private String indexType = "full";
	private Vector<DocumentDefinition> documents = new Vector<DocumentDefinition>();
	
	/**
	 * 
	 * @param type
	 * @param indexType
	 */
	public ObjectDefinition(String type,String indexType) {
		
		this(type);
		this.setIndexType(indexType);
	}
	
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
	 * @param indexType the indexType to set
	 */
	public void setIndexType(String indexType) {
		this.indexType = indexType;
	}

	/**
	 * @return the indexType
	 */
	public String getIndexType() {
		return indexType;
	}

	/**
	 * @return the documents
	 */
	public Vector<DocumentDefinition> getDocumentDefinitions() {
		return documents;
	}

	public void addDocumentDefinition(DocumentDefinition doc) {
		
		documents.add(doc);
	}
	
	public void removeDocumentDefinition(DocumentDefinition doc) {
		
		int index;
		
		while((index = documents.indexOf(doc)) != -1) {
			documents.remove(index);
		}
		return;
	}

	/* (non-Javadoc)
	 * @see java.lang.Object#toString()
	 */
	@Override
	public String toString() {

		StringBuffer out = new StringBuffer();
		
		out.append("Object Definition for type = " + getType());
		out.append("\n");
		
		for(Object doc : getDocumentDefinitions()) {
			
			out.append(doc);
			out.append("\n");
		}
		return out.toString();
	}

	/**
	 * create/write documents for this element.
	 * @see de.ilias.services.lucene.index.DocumentHandler#writeDocument(de.ilias.services.lucene.index.CommandQueueElement)
	 */
	public void writeDocument(CommandQueueElement el)
			throws DocumentHandlerException, IOException {

		DocumentHolder docs = DocumentHolder.factory();
		docs.newGlobalDocument();
		
		for(Object def : getDocumentDefinitions()) {
			logger.debug("1. New document definition");
			((DocumentDefinition) def).writeDocument(el);
		}
		
		IndexHolder writer = IndexHolder.getInstance();
		writer.getWriter().addDocument(docs.getGlobalDocument());
	}

	/**
	 * @see de.ilias.services.lucene.index.DocumentHandler#writeDocument(de.ilias.services.lucene.index.CommandQueueElement, java.sql.ResultSet)
	 */
	public void writeDocument(CommandQueueElement el, ResultSet res)
			throws DocumentHandlerException {
		// TODO Auto-generated method stub
		
	}

	
	
	
	
}

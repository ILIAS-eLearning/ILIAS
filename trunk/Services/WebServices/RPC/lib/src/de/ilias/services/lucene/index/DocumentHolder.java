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

package de.ilias.services.lucene.index;

import org.apache.log4j.Logger;
import org.apache.lucene.document.Document;
import org.apache.lucene.document.Field;
import org.apache.lucene.document.Field.Index;
import org.apache.lucene.document.Field.Store;

/**
 * Thread local singleton
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
public class DocumentHolder {

	
	protected static Logger logger = Logger.getLogger(DocumentHolder.class);
	
	private static ThreadLocal<DocumentHolder> thDocumentHolder = new ThreadLocal<DocumentHolder>() {

		/**
		 * init document holder
		 * @see java.lang.ThreadLocal#initialValue()
		 */
		@Override
		protected DocumentHolder initialValue() {

			return new DocumentHolder();
		}
	};
	
	
	private Document globalDoc = null;
	private Document doc = null;
	
	
	/**
	 * 
	 */
	private DocumentHolder() {
		
		newGlobalDocument();
		newDocument();
	}
	
	/**
	 * 
	 * @return
	 */
	public static DocumentHolder factory() {
		
		return thDocumentHolder.get();
	}
	
	/**
	 * 
	 * @return create new global document
	 */
	public Document newGlobalDocument() {
		globalDoc = new Document();
		globalDoc.add(new Field("docType","combined",Store.YES,Field.Index.NOT_ANALYZED));
		return globalDoc;
	}
	
	/**
	 * 
	 * @return create a new document
	 */
	public Document newDocument() {
		doc = new Document();
		doc.add(new Field("docType","separated",Store.YES,Field.Index.NOT_ANALYZED));
		return doc;
	}
	
	/**
	 * @return get current global document 
	 * 
	 */
	public Document getGlobalDocument() {
		return globalDoc;
	}
	
	/**
	 * 
	 * @return return current document
	 */
	public Document getDocument() {
		return doc;
	}
	
	/**
	 * 
	 * @param name
	 * @param value
	 * @param store
	 * @param index
	 */
	public void add(String name, String value,boolean isGlobal,Store store,Index index) {
		
		getDocument().add(new Field(name,value,store,index));
		
		if(isGlobal) {
			getGlobalDocument().add(new Field(name,value,store,index));
		}
		return;
	}
}
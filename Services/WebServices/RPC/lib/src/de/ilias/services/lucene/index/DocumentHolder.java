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

import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;
import org.apache.lucene.document.Document;
import org.apache.lucene.document.Field;
import org.apache.lucene.document.Field.Store;
import org.apache.lucene.document.StringField;
import org.apache.lucene.document.TextField;

/**
 * Thread local singleton
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
public class DocumentHolder {

	protected static Logger logger = LogManager.getLogger(DocumentHolder.class);
	
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
		globalDoc.add(new StringField("docType","combined",Field.Store.YES));
		return globalDoc;
	}
	
	/**
	 * 
	 * @return create a new document
	 */
	public Document newDocument() {
		doc = new Document();
		// new string fields are in contrast to TextFields not analyzed. Ensure STORE.YES
		doc.add(new StringField("docType", "separated", Field.Store.YES));
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
	 * @param String name
	 * @param String value
	 * @param Field.Store store
	 * @param boolean index
	 */
	public void add(String name, String value,boolean isGlobal,Field.Store store, boolean indexed) {
		
		if(indexed) {
			getDocument().add(new TextField(name, value, store));
		} else {
			getDocument().add(new StringField(name, value, store));
		}
		
		if(isGlobal) {
			if(indexed) {
				getGlobalDocument().add(new TextField(name, value, store));
			} else {
				getGlobalDocument().add(new StringField(name, value, store));
			}
		}
		return;
	}
}
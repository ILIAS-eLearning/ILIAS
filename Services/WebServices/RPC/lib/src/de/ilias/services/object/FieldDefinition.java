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

import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.Vector;

import org.apache.log4j.Logger;
import org.apache.lucene.document.Field;
import org.apache.lucene.document.Field.Store;

import de.ilias.services.lucene.index.DocumentHolder;
import de.ilias.services.lucene.transform.ContentTransformer;
import de.ilias.services.lucene.transform.TransformerFactory;

/**
 * 
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
public class FieldDefinition {

	protected static Logger logger = Logger.getLogger(FieldDefinition.class);
	
	private Field.Index index;
	private Store store;
	private String column;
	private String name;
	
	Vector<TransformerDefinition> transformers = new Vector<TransformerDefinition>();
	
	
	
	/**
	 * 
	 * @param store
	 * @param index
	 * @param name
	 * @param column
	 */
	public FieldDefinition(Store store,Field.Index index,String name,String column) {

		this.store = store;
		this.index = index;
		this.name = name;
		this.column = column;
	
	}
	
	/**
	 * 
	 * @param store
	 * @param index
	 * @param name
	 */
	public FieldDefinition(Store store,Field.Index index,String name) {
		
		this(store,index,name,"");
	}
	
	/**
	 * 
	 * @param store
	 * @param index
	 * @param name
	 * @param column
	 */
	public FieldDefinition(String store, String index, String name, String column) {
		
		if(store.equalsIgnoreCase("YES")) {
			this.store = Store.YES;
		}
		else if(store.equalsIgnoreCase("NO")) {
			this.store = Store.NO;
		}
		if(index.equalsIgnoreCase("NO")) {
			this.index = Field.Index.NO;
		}
		else if(index.equalsIgnoreCase("ANALYZED")) {
			this.index = Field.Index.ANALYZED;
		}
		else if(index.equalsIgnoreCase("NOT_ANALYZED")) {
			this.index = Field.Index.NOT_ANALYZED;
		}
		this.name = name;
		this.column = column;
	}
	
	/**
	 * 
	 * @param store
	 * @param index
	 * @param name
	 */
	public FieldDefinition(String store, String index, String name) {
		this(store,index,name,"");
	}
	


	/**
	 * @return the index
	 */
	public Field.Index getIndex() {
		return index;
	}


	/**
	 * @param index the index to set
	 */
	public void setIndex(Field.Index index) {
		this.index = index;
	}


	/**
	 * @return the store
	 */
	public Store getStore() {
		return store;
	}


	/**
	 * @param store the store to set
	 */
	public void setStore(Field.Store store) {
		this.store = store;
	}


	/**
	 * @return the column
	 */
	public String getColumn() {
		return column;
	}


	/**
	 * @param column the column to set
	 */
	public void setColumn(String column) {
		this.column = column;
	}


	/**
	 * @return the name
	 */
	public String getName() {
		return name;
	}


	/**
	 * @param name the name to set
	 */
	public void setName(String name) {
		this.name = name;
	}

	/**
	 * @return the transformers
	 */
	public Vector<TransformerDefinition> getTransformers() {
		return transformers;
	}


	/**
	 * @param transformers the transformers to set
	 */
	public void setTransformers(Vector<TransformerDefinition> transformers) {
		this.transformers = transformers;
	}

	/**
	 * 
	 * @param trans
	 */
	public void addTransformer(TransformerDefinition trans) {
		
		this.transformers.add(trans);
	}

	/* (non-Javadoc)
	 * @see java.lang.Object#toString()
	 */
	@Override
	public String toString() {
		
		StringBuffer out = new StringBuffer();

		out.append("Field: " + getStore() + " " + getIndex() + " " + getColumn() + " " + getName());
		out.append("\n");
		
		for(Object tr : getTransformers()) {
			
			out.append(tr.toString());
			out.append("\n");
		}
		
		return out.toString();
	}

	/**
	 * @param res
	 * @throws SQLException 
	 */
	public void writeDocument(ResultSet res) throws SQLException {

		// TODO: call transformer
		try {
			Object value = res.getObject(getColumn());
			String purged = callTransformers(value.toString());
			
			
			if(value != null && value.toString() != "") {
				logger.debug("Found value: " + purged + " for name: " + getName());
				DocumentHolder.factory().add(getName(),purged, store, index);
			}
			return;
		}
		catch(NullPointerException e) {
			logger.error(e.getMessage());
		}
	}

	/**
	 * @param string
	 * @return
	 */
	private String callTransformers(String value) {

		for(int i = 0; i < getTransformers().size(); i++) {
			
			logger.info(getTransformers().get(i).getName());
			ContentTransformer trans = TransformerFactory.factory(getTransformers().get(i).getName());
			if(trans != null) 
				value = trans.transform(value);
		}
		return value;
	}
}

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
public class FieldDefinition {

	public static int STORE_YES = 1;
	public static int STORE_NO = 2;
	
	public static int INDEX_NO = 1;
	public static int INDEX_ANALYZED = 2;
	public static int INDEX_NOT_ANALYZED = 3;
	
	protected static Logger logger = Logger.getLogger(FieldDefinition.class);
	
	private int index;
	private int store;
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
	public FieldDefinition(int store,int index,String name,String column) {

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
	public FieldDefinition(int store,int index,String name) {
		
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
			this.store = STORE_YES;
		}
		else if(store.equalsIgnoreCase("NO")) {
			this.store = STORE_NO;
		}
		if(index.equalsIgnoreCase("NO")) {
			this.index = INDEX_NO;
		}
		else if(index.equalsIgnoreCase("ANALYZED")) {
			this.index = INDEX_ANALYZED;
		}
		else if(index.equalsIgnoreCase("NOT_ANALYZED")) {
			this.index = INDEX_NOT_ANALYZED;
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
	public int getIndex() {
		return index;
	}


	/**
	 * @param index the index to set
	 */
	public void setIndex(int index) {
		this.index = index;
	}


	/**
	 * @return the store
	 */
	public int getStore() {
		return store;
	}


	/**
	 * @param store the store to set
	 */
	public void setStore(int store) {
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
}

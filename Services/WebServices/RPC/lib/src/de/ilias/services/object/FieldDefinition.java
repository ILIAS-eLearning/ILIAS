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

import org.apache.logging.log4j.LogManager;
import org.apache.lucene.document.Field;
import de.ilias.services.db.DBFactory;
import de.ilias.services.lucene.index.DocumentHolder;
import de.ilias.services.lucene.index.transform.ContentTransformer;
import de.ilias.services.lucene.index.transform.TransformerFactory;
import org.apache.logging.log4j.Logger;

/**
 * 
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
public class FieldDefinition {
	
	protected static Logger logger = LogManager.getLogger(FieldDefinition.class);

	/**
	 * Possible index types
	 */
	public static final int INDEX_NO = 0;
	public static final int INDEX_ANALYZED = 1;
	public static final int INDEX_NOT_ANALYZED = 2;

	/**
	 * Is stored
	 */
	private Field.Store store = Field.Store.NO;
		

	/**
	 * Index field type
	 */
	public int indexType = FieldDefinition.INDEX_NO;
	
	/**
	 * Name if field
	 */
	private String name;
	
	/**
	 * Column name
	 */
	private String column;
	
	/**
	 * Type
	 */
	private String type = "text";
	
	/**
	 * Is global document type
	 */
	private boolean global = false;
	
	/**
	 * Is dynamic
	 */
	private boolean isDynamic = false;
	
	Vector<TransformerDefinition> transformers = new Vector<TransformerDefinition>();
	
	
	/**
	 * @param store
	 * @param index
	 * @param name
	 * @param column
	 * @param type;
	 * @param isGlobal
	 * @param dynamicName
	 */
	public FieldDefinition(String store, String index, String name, String column, String type, String isGlobal, String dynamicName) {

		if(store.equalsIgnoreCase("YES")) {
			this.store = Field.Store.YES;
		}
		
		if(index.equalsIgnoreCase("NO")) {
			this.indexType = INDEX_NO;
		}
		else if(index.equalsIgnoreCase("ANALYZED")) {
			this.indexType = INDEX_ANALYZED;
		}
		else if(index.equalsIgnoreCase("NOT_ANALYZED")) {
			this.indexType = INDEX_NOT_ANALYZED;
		}
		if(isGlobal == null || isGlobal.equalsIgnoreCase("YES")) {
			this.global = true;
		}

		if(dynamicName != null) {
			this.name = dynamicName;
			this.isDynamic = true;
		}
		else {
			this.name = name;
		}

		this.column = column;
		
		if(type != null && type.length() != 0) {
			this.type = type;
		}
	}
	
	/**
	 * @return the index
	 */
	public Integer getIndexType() {
		return indexType;
	}


	/**
	 * @param index the index to set
	 */
	public void setIndexType(int type) {
		this.indexType = type;
	}

	
	public Field.Store getStore() {
		return this.store;
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
	 * set type (one of char,text,clob)
	 * @param type
	 */
	public void setType(String type) {
		this.type = type;
	}

	/**
	 * 
	 * @return
	 */
	public String getType() {
		return type;
	}

	/**
	 * 
	 * @param res
	 * @return String name of lucene field
	 * @throws SQLException 
	 */
	public String parseName(ResultSet res) throws SQLException {
		
		if(isDynamic == false) {
			return getName();
		}
		if(res != null) {

			String value;
			
			if(getType().equalsIgnoreCase("clob")) {
				value = DBFactory.getCLOB(res,getName());
			}
			else if(getType().equalsIgnoreCase("text")) {
				value = DBFactory.getString(res,getName());
			}
			else if(getType().equalsIgnoreCase("integer")) {
				value = DBFactory.getInt(res,getName());
			}
			else {
				logger.warn("Unknown type given for Field name: " + getName());
				return "";
			}

			if(value != null) {
				logger.debug("Dynamic name value: " + value);
				logger.debug("Dynamic name:" + getName());
				return value;
			}
		}
		throw new SQLException("Invalid result set for dynamic field name: " + getName());
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
	
	/**
	 * @param global the global to set
	 */
	public void setGlobal(boolean global) {
		this.global = global;
	}

	/**
	 * @return the global
	 */
	public boolean isGlobal() {
		return global;
	}
	
	/**
	 * @param isDynamic the isDynamic to set
	 */
	public void setDynamic(boolean isDynamic) {
		this.isDynamic = isDynamic;
	}


	/**
	 * @return the isDynamic
	 */
	public boolean isDynamic() {
		return isDynamic;
	}

	/* (non-Javadoc)
	 * @see java.lang.Object#toString()
	 */
	@Override
	public String toString() {
	
		StringBuffer out = new StringBuffer();

		out.append(
			getIndexType() + " " + getColumn() + " " + getName());
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

		try {
			String value;
			boolean indexed = false;
			
			if(getType().equalsIgnoreCase("clob")) {
				value = DBFactory.getCLOB(res,getColumn());
			}
			else if(getType().equalsIgnoreCase("text")) {
				value = DBFactory.getString(res,getColumn());
			}
			else if(getType().equalsIgnoreCase("integer")) {
				value = DBFactory.getInt(res,getColumn());
			}
			else {
				logger.warn("Unknown type given for Field name: " + getName());
				return;
			}

			if(value != null && value.length() > 0) {
				String purged = callTransformers(value);
				String fieldName = parseName(res);

				logger.debug("Found value: " + purged + " for name: " + fieldName);
				
				if(getIndexType().equals(INDEX_ANALYZED)) {
					indexed = true;
				}
				DocumentHolder.factory().add(fieldName, purged, isGlobal(), store, indexed);
			}
			return;
		}
		catch(NullPointerException e) {
			logger.error("Caught NullPointerException: " + e.getMessage());
		}
	}

	/**
	 * @param content
	 */
	public void writeDocument(String content) {

		boolean indexed = false;
		
		if(content != null && content.length() != 0) {

			String purged = callTransformers(content);
			
			if(getIndexType().equals(INDEX_ANALYZED)) {
				indexed = true;
			}
			DocumentHolder.factory().add(getName(),purged, isGlobal(), store, indexed);
		}
	}

	
	/**
	 * @param string
	 * @return
	 */
	private String callTransformers(String value) {

		// Default whitespace sanitizer
		ContentTransformer trans = TransformerFactory.factory("WhitespaceSanitizer");
		value = trans.transform(value);
		
		for(int i = 0; i < getTransformers().size(); i++) {
			
			logger.debug(getTransformers().get(i).getName());
			trans = TransformerFactory.factory(getTransformers().get(i).getName());
			if(trans != null) 
				value = trans.transform(value);
		}
		
		// Delete html tags
		trans = TransformerFactory.factory("TagSanitizer");
		
		return trans.transform(value);
	}





}
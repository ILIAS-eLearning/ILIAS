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

import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;

import org.apache.log4j.Logger;

import de.ilias.services.db.DBFactory;
import de.ilias.services.lucene.index.CommandQueueElement;
import de.ilias.services.lucene.index.DocumentHandlerException;

/**
 * 
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
public class ParameterDefinition {

	public static final int FORMAT_LIST = 1;
	
	public static final int TYPE_INT = 1;
	public static final int TYPE_STRING = 2;

	protected Logger logger = Logger.getLogger(ParameterDefinition.class);
	
	private int format;
	private int type;
	private String value;
	
	
	/**
	 * 
	 */
	public ParameterDefinition(int format,int type,String value) {
		
		this.format = format;
		this.type = type;
		this.value = value;
	}
	
	/**
	 * 
	 * @param format
	 * @param type
	 * @param value
	 */
	public ParameterDefinition(String format,String type, String value) {
		
		if(format.equals("format")) {
			this.format = FORMAT_LIST;
		}
		if(type.equals("int")) {
			this.type = TYPE_INT;
		}
		if(type.equals("string")) {
			this.type = TYPE_STRING;
		}
		this.value = value;
	}
	
	/**
	 * @return the format
	 */
	public int getFormat() {
		return format;
	}


	/**
	 * @param format the format to set
	 */
	public void setFormat(int format) {
		this.format = format;
	}


	/**
	 * @return the type
	 */
	public int getType() {
		return type;
	}


	/**
	 * @param type the type to set
	 */
	public void setType(int type) {
		this.type = type;
	}


	/**
	 * @return the value
	 */
	public String getValue() {
		return value;
	}


	/**
	 * @param value the value to set
	 */
	public void setValue(String value) {
		this.value = value;
	}

	/* (non-Javadoc)
	 * @see java.lang.Object#toString()
	 */
	@Override
	public String toString() {

		StringBuffer out = new StringBuffer();
		
		out.append("Parameter " + format + " " + type + " " + value);
		out.append("\n");
		return out.toString();
	}

	/**
	 * @param pst
	 * @param el
	 * @param parentResult 
	 * @throws SQLException 
	 * @throws DocumentHandlerException 
	 */
	public void writeParameter(PreparedStatement pst, int index, CommandQueueElement el, ResultSet parentResult) 
		throws SQLException, DocumentHandlerException {

		switch(getType()) {
		case TYPE_INT:
			logger.debug("ID: " + getParameterValue(el,parentResult));
			pst.setInt(index,getParameterValue(el,parentResult));
			break;
			
		case TYPE_STRING:
			logger.debug("ID: " + getParameterValue(el, parentResult));
			pst.setString(index, getParameterString(el,parentResult));
			break;
		
		default:
			throw new DocumentHandlerException("Invalid parameter type given. Type " + getType());
		}
	}

	/**
	 * @param el
	 * @param parentResult 
	 * @return
	 * @throws SQLException 
	 */
	private int getParameterValue(CommandQueueElement el, ResultSet parentResult) throws SQLException {
		
		// Check for parent result (e.g. pg,st)
		if(parentResult != null) {

			logger.debug("Trying to read parameter from parent result set...");
			try {
				logger.debug(parentResult.getInt(getValue()));
				return parentResult.getInt(getValue());
			}
			catch(SQLException e) {
				// ignoring this error
				// and trying to fetch objId and metaObjId
			}
			
		}

		if(getValue().equals("objId")) {
			logger.debug(el.getObjId());
			return el.getObjId();
		}
		
		if(getValue().equals("metaObjId")) {
			logger.debug(el.getObjId());
			return el.getObjId();
		}
		
		if(getValue().equals("metaRbacId")) {
			logger.debug(el.getObjId());
			return el.getObjId();
		}
		
		return 0;
	}
	
	/**
	 * @param el
	 * @param parentResult 
	 * @return
	 * @throws SQLException 
	 */
	private String getParameterString(CommandQueueElement el, ResultSet parentResult) throws SQLException {
		
		if(parentResult != null) {
			logger.debug("Trying to read parameter from parent result set...");
			
			try {
				logger.debug(parentResult.getString(getValue()).trim());
				return DBFactory.getString(parentResult, getValue());
			}
			catch (SQLException e) {
				// ignoring this error
				// and trying to fetch objId and metaObjId
			}
		}

		if(getValue().equals("objType")) {
			logger.debug(el.getObjType());
			return el.getObjType();
		}
		
		if(getValue().equals("metaType")) {
			logger.debug(el.getObjType());
			return el.getObjType();
		}
		return "";
	}
	

}

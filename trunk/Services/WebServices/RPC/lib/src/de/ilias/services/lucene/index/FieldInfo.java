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

import java.util.HashMap;
import java.util.Vector;

import org.apache.log4j.Logger;

import de.ilias.services.settings.LocalSettings;

/**
 * 
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
public class FieldInfo {
	
	protected static Logger logger = Logger.getLogger(FieldInfo.class);
	
	private static HashMap<String, FieldInfo> instances = new HashMap<String, FieldInfo>();
	private Vector<String> fields = new Vector<String>();
	
	/**
	 * 
	 */
	protected FieldInfo() {
		
		initDefaultFields();
	}


	/**
	 * Get singleton instance for a client
	 * @return FieldInfo 
	 */
	protected static FieldInfo getInstance() {
		
		return getInstance(LocalSettings.getClientKey());
	}

	/**
	 * @param clientKey
	 * @return
	 */
	public static FieldInfo getInstance(String clientKey) {

		if(instances.containsKey(clientKey)) {
			return instances.get(clientKey);
		}
		
		instances.put(clientKey, new FieldInfo());
		return instances.get(clientKey);
	}
	
	/**
	 * Add field (if not already appended)
	 * @param field
	 */
	public void addField(String field) {
		
		if(!fields.contains(field)) {
			fields.add(field);
		}
		return;
	}
	
	/**
	 * Get fields as Vector
	 * @return Vector fields
	 */
	public Vector<String> getFields() {
		return fields;
	}
	
	/**
	 * Return fields as string array
	 * @return
	 */
	public String[] getFieldsAsStringArray() {
		return fields.toArray(new String[0]);
	}

	/**
	 * @return
	 */
	public int getFieldSize() {

		return fields.size();
	}
	
	/**
	 * Load default fields 
	 */
	protected void initDefaultFields() {

		addField("title");
		addField("description");
		addField("lomKeyword");
		addField("metaData");
		addField("tag");
		addField("propertyHigh");
		addField("propertyMedium");
		addField("propertyLow");
		addField("content");
		
	}
	
}

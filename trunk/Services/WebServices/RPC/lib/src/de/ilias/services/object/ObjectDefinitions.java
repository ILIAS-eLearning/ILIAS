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

import java.io.File;
import java.util.HashMap;
import java.util.Vector;

import org.apache.log4j.Logger;

/**
 * 
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
public class ObjectDefinitions {

	protected static Logger logger = Logger.getLogger(ObjectDefinitions.class);
	private static HashMap<File, ObjectDefinitions> instances = new HashMap<File, ObjectDefinitions>();
	
	private File absolutePath;
	private Vector<ObjectDefinition> definitions = new Vector<ObjectDefinition>();
	
	/**
	 * Singleton
	 * 
	 */
	public ObjectDefinitions(File absolutePath) {
		
		this.setAbsolutePath(absolutePath);
	}

	/**
	 * 
	 * @param absolutePath
	 * @return
	 */
	public static synchronized ObjectDefinitions getInstance(File absolutePath) {
		
		if(instances.containsKey(absolutePath)) {
			return instances.get(absolutePath);
		}
		instances.put(absolutePath, new ObjectDefinitions(absolutePath));
		return instances.get(absolutePath);
	}
	
	/**
	 * reset object definitons
	 */
	public void reset() {
		
		this.definitions = new Vector<ObjectDefinition>(); 
	}
	
	/**
	 * @param absolutePath the absolutePath to set
	 */
	public void setAbsolutePath(File absolutePath) {
		this.absolutePath = absolutePath;
	}

	/**
	 * @return the absolutePath
	 */
	public File getAbsolutePath() {
		return absolutePath;
	}

	/**
	 * @return the definitions
	 */
	public Vector<ObjectDefinition> getDefinitions() {
		return definitions;
	}
	
	/**
	 * 
	 * @param def
	 */
	public void addDefinition(ObjectDefinition def) {
		definitions.add(def);
	}
	
	/**
	 * Get object definition by object type 
	 * @param objType
	 * @return
	 * @throws ObjectDefinitionException
	 */
	public ObjectDefinition getDefinitionByType(String objType) throws ObjectDefinitionException {
		
		for(int i = 0; i < definitions.size(); i++) {
			if(definitions.get(i).getType().equals(objType)) {
				return definitions.get(i);
			}
		}
		throw new ObjectDefinitionException("Invalid type given. Cannot find obj type " + objType);
	}

	/* (non-Javadoc)
	 * @see java.lang.Object#toString()
	 */
	@Override
	public String toString() {

		StringBuffer out = new StringBuffer();
		
		for(Object defs : this.getDefinitions()) {
			
			out.append("Object definitions for: " + getAbsolutePath().getAbsolutePath());
			out.append("\n");
			out.append(defs);
		}
		return out.toString();
	}
}

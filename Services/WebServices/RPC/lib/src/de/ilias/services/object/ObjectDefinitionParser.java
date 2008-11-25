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
import java.io.IOException;
import java.util.Vector;

import org.apache.log4j.Logger;
import org.jdom.Document;
import org.jdom.Element;
import org.jdom.JDOMException;
import org.jdom.input.SAXBuilder;

import de.ilias.services.settings.ClientSettings;
import de.ilias.services.settings.ConfigurationException;
import de.ilias.services.settings.LocalSettings;

/**
 * 
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
public class ObjectDefinitionParser {

	protected Logger logger = Logger.getLogger(ObjectDefinition.class);
	private Vector<File> objectPropertyFiles = new Vector<File>();
	private ClientSettings settings;
	private ObjectDefinitions definitions;
	
	
	/**
	 * @throws ConfigurationException 
	 * 
	 */
	public ObjectDefinitionParser() throws ConfigurationException {

		settings = ClientSettings.getInstance(LocalSettings.getClientKey());
		definitions = ObjectDefinitions.getInstance(settings.getAbsolutePath());
	}

	/**
	 * @param objectPropertyFiles
	 * @throws ConfigurationException 
	 */
	public ObjectDefinitionParser(Vector<File> objectPropertyFiles) throws ConfigurationException {

		this();
		this.objectPropertyFiles = objectPropertyFiles;
		
	}
	
	public boolean parse() throws ObjectDefinitionException {
		
		for(int i = 0; i < objectPropertyFiles.size(); i++) {
			
			parseFile(objectPropertyFiles.get(i));
		}
		return true;
	}

	/**
	 * @param file
	 * @throws ObjectDefinitionException 
	 */
	private void parseFile(File file) throws ObjectDefinitionException {

		Document doc = null;
		
		try {
			
			ObjectDefinition def = new ObjectDefinition();
			SAXBuilder builder = new SAXBuilder();
			doc = builder.build(file);
			
			Element root = doc.getRootElement();
			def.setType(root.getAttributeValue("type"));
			
			definitions.addDefinition(def);
		} 
		catch (JDOMException e) {
			logger.error("Cannot parse file: " + file.getAbsolutePath());
			throw new ObjectDefinitionException(e);
			
		} catch (IOException e) {
			logger.error("Cannot handle file: " + file.getAbsolutePath());
			throw new ObjectDefinitionException(e);
		}	
	}
}
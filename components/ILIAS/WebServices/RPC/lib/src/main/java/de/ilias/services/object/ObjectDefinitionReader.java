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
import java.io.FileFilter;
import java.util.HashMap;
import java.util.Vector;

import de.ilias.services.settings.ClientSettings;
import org.apache.logging.log4j.LogManager;

import de.ilias.services.settings.ConfigurationException;
import org.apache.logging.log4j.Logger;

public class ObjectDefinitionReader {

	private static final Logger logger = LogManager.getLogger(ObjectDefinitionReader.class);
	private static final HashMap<File, ObjectDefinitionReader> instances = new HashMap<File, ObjectDefinitionReader>();
	
	public static final String objectPropertyName = "LuceneObjectDefinition.xml";
	public static final String pluginPath = "Customizing/global/plugins";
	public static final String componentPath = "components";

	private final Vector<File> objectPropertyFiles = new Vector<File>();
	

	File absolutePath;
	ClientSettings clientSettings;
	
	private ObjectDefinitionReader(ClientSettings settings) throws ConfigurationException {
		this.clientSettings = settings;
		this.absolutePath = settings.getAbsolutePath();
		read();
	}

	public static ObjectDefinitionReader getInstance(ClientSettings settings) throws ConfigurationException {
		if (instances.containsKey(settings.getAbsolutePath())) {
			return instances.get(settings.getAbsolutePath());
		}
		instances.put(settings.getAbsolutePath(), new ObjectDefinitionReader(settings));
		return instances.get(settings.getAbsolutePath());
	}

	/**
	 * @return the absolutePath
	 */
	public File getAbsolutePath() {
		return clientSettings.getAbsolutePath();
	}

	/**
	 * @return the objectPropertyFiles
	 */
	public Vector<File> getObjectPropertyFiles() {
		return objectPropertyFiles;
	}

	/**
	 * 
	 * @throws ConfigurationException
	 */
	private void read() throws ConfigurationException  {
		
		logger.debug("Start reading search index definitions...");
		if(!absolutePath.isDirectory()) {
			throw new ConfigurationException("Absolute path required. Path: " + absolutePath.getAbsolutePath());
		}
		traverseByVersion();
	}

	private void traverseByVersion() {
		if (clientSettings.getIliasMajorVersion() == 0 || clientSettings.getIliasMajorVersion() >= 10) {
			traverseByVersion10();
		} else {
			traverseByVersion8();
		}
	}

	private void traverseByVersion8() {

		// Traverse through Modules
		File start = new File(absolutePath.getAbsoluteFile() + System.getProperty("file.separator") + "Modules");
		logger.debug("Start path is : " + start.getAbsoluteFile());
		traverse(start);

		// Traverse through Modules
		File services = new File(absolutePath.getAbsoluteFile() + System.getProperty("file.separator") + "Services");
		logger.debug("Start path is : " + start.getAbsoluteFile());
		traverse(services);

		// Traverse through Plugins
		File plugin = new File(absolutePath.getAbsoluteFile() + System.getProperty("file.separator") + ObjectDefinitionReader.pluginPath);
		logger.debug("Start path is : " + plugin.getAbsoluteFile());
		traverse(plugin);
	}

	private void traverseByVersion10() {
		// Traverse through Plugins
		File plugin = new File(absolutePath.getAbsoluteFile() + System.getProperty("file.separator") + ObjectDefinitionReader.componentPath);
		logger.debug("Start path is : " + plugin.getAbsoluteFile());
		traverse(plugin);
	}
	
	private void traverse(File dir) {
		
		if(dir == null) {
			return;
		}
		logger.debug("Start path is : " + dir.getAbsoluteFile());

		File[] entries = dir.listFiles(
				new FileFilter()
				{
					public boolean accept(File path) {
						
						if(path.isDirectory()) {
                            //logger.debug("Found new directory: " + path.getAbsolutePath());
                            return !path.getName().equals(".svn");
                        }
						//logger.debug(path.getName() + " <-> " + objectPropertyName);
						if(path.getName().equalsIgnoreCase(objectPropertyName)) {
							logger.info("Found: " + path.getAbsolutePath());
							objectPropertyFiles.add(path);
						}
						return false;
					}
				});

		if(entries == null) {
			return;
		}
		for(int i = 0; i < entries.length; i++) {
			// there are only directories
			traverse(entries[i]);
		}
	}

}

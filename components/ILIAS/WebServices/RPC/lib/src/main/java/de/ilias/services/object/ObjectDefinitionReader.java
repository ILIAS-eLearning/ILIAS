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

import org.apache.logging.log4j.LogManager;

import de.ilias.services.settings.ConfigurationException;
import org.apache.logging.log4j.Logger;

/**
 * 
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
public class ObjectDefinitionReader {

	
	private static final Logger logger = LogManager.getLogger(ObjectDefinitionReader.class);
	private static final HashMap<File, ObjectDefinitionReader> instances = new HashMap<File, ObjectDefinitionReader>();
	
	public static final String objectPropertyName = "LuceneObjectDefinition.xml";
	public static final String customPluginPath = "Customizing/global/plugins";
	public static final String componentsPath = "components";
	public static final String modulesPath = "Modules";
	public static final String servicesPath = "Services";


	private final Vector<File> objectPropertyFiles = new Vector<File>();
	

	File absolutePath;
	
	/**
	 * Singleton constructor 
	 * @throws ConfigurationException 
	 */
	private ObjectDefinitionReader(File absolutePath) throws ConfigurationException {
		this.absolutePath = absolutePath;
		read();
	}
	
	/**
	 * 
	 * @param absolutePath
	 * @return
	 * @throws ConfigurationException 
	 */
	public static ObjectDefinitionReader getInstance(File absolutePath) throws ConfigurationException {
		
		if(instances.containsKey(absolutePath)) {
			logger.debug("Using cached properties.");
			return instances.get(absolutePath);
		}
		instances.put(absolutePath, new ObjectDefinitionReader(absolutePath));
		return instances.get(absolutePath);
	}
	
	/**
	 * @return the absolutePath
	 */
	public File getAbsolutePath() {
		return absolutePath;
	}


	/**
	 * @param absolutePath the absolutePath to set
	 */
	public void setAbsolutePath(File absolutePath) {
		this.absolutePath = absolutePath;
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

		// Traverse through former modules path
		File allModules = new File(absolutePath.getAbsoluteFile() + System.getProperty("file.separator") + ObjectDefinitionReader.modulesPath);
		logger.debug("Start path is : " + allModules.getAbsoluteFile());
		traverse(allModules);

		// Traverse through former services path
		File allServices = new File(absolutePath.getAbsoluteFile() + System.getProperty("file.separator") + ObjectDefinitionReader.servicesPath);
		logger.debug("Start path is : " + allServices.getAbsoluteFile());
		traverse(allServices);

		// Traverse through plugins in former custom plugin path
		File plugin = new File(absolutePath.getAbsoluteFile() + System.getProperty("file.separator") + ObjectDefinitionReader.customPluginPath);
		logger.debug("Start path is : " + plugin.getAbsoluteFile());
		traverse(plugin);

		// Traverse through components path including the components/PLUGINNAME path
		File allComponents = new File(absolutePath.getAbsoluteFile() + System.getProperty("file.separator") + ObjectDefinitionReader.componentsPath);
		logger.debug("Start path is : " + allComponents.getAbsoluteFile());
		traverse(allComponents);
	}
	
	/**
	 * 
	 * @param dir
	 */
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

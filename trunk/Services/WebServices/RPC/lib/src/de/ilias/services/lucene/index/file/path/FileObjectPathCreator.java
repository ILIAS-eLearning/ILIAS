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

package de.ilias.services.lucene.index.file.path;

import java.io.File;
import java.sql.ResultSet;
import java.sql.SQLException;

import de.ilias.services.db.DBFactory;
import de.ilias.services.lucene.index.CommandQueueElement;
import de.ilias.services.settings.ClientSettings;
import de.ilias.services.settings.ConfigurationException;
import de.ilias.services.settings.LocalSettings;

/**
 * 
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
public class FileObjectPathCreator implements PathCreator {
	
	protected String basePath = "ilFiles";

	/**
	 * Default constructor
	 */
	public FileObjectPathCreator() {

	}
	
	/**
	 * Set bas path
	 * @param bp
	 * @return 
	 */
	public void setBasePath(String bp) {
		
		this.basePath = bp;
	}
	

	/**
	 * Get base path
	 * ILIAS version <= 4.0 (ilFiles)
	 * ILIAS version >= 4.1 (ilFile) 
	 * 
	 * @return String basePath
	 */
	public String getBasePath() {
		
		return this.basePath;
	}
	
	
	/**
	 * @see de.ilias.services.lucene.index.file.path.PathCreator#buildPath(de.ilias.services.lucene.index.CommandQueueElement, java.sql.ResultSet)
	 */
	public File buildFile(CommandQueueElement el, ResultSet res)
			throws PathCreatorException {

		int objId = el.getObjId();
		StringBuilder fullPath = new StringBuilder();
		StringBuilder versionPath = new StringBuilder();
		
		File file;
		
		try {
			fullPath.append(ClientSettings.getInstance(LocalSettings.getClientKey()).getDataDirectory().getAbsolutePath());
			fullPath.append(System.getProperty("file.separator"));
			fullPath.append(ClientSettings.getInstance(LocalSettings.getClientKey()).getClient());
			fullPath.append(System.getProperty("file.separator"));
			fullPath.append(getBasePath());
			fullPath.append(System.getProperty("file.separator"));
			fullPath.append(PathUtils.buildSplittedPathFromId(objId,"file"));
			
			versionPath.append(fullPath);
			versionPath.append(PathUtils.buildVersionDirectory(res.getInt("version")));
			versionPath.append(System.getProperty("file.separator"));
			versionPath.append(DBFactory.getString(res,"file_name"));

			file = new File(versionPath.toString());
			if(file.exists() && file.canRead()) {
				return file;
			}

			// Older versions do not store the files in version directories
			fullPath.append(DBFactory.getString(res, "file_name"));
			file = new File(fullPath.toString());
			if(file.exists() && file.canRead()) {
				return file;
			}
			if(!file.exists()) {
				throw new PathCreatorException("Cannot find file: " + fullPath.toString());
			}
			if(!file.canRead()) {
				throw new PathCreatorException("Cannot read file: " + fullPath.toString());
			}
			return null;
		} 
		catch (ConfigurationException e) {
			throw new PathCreatorException(e);
		}
		catch (SQLException e) {
			throw new PathCreatorException(e);
		}
		catch (NullPointerException e) {
			throw new PathCreatorException(e);
		} 
	}

	/**
	 * @see de.ilias.services.lucene.index.file.path.PathCreator#buildPath(de.ilias.services.lucene.index.CommandQueueElement)
	 */
	public File buildFile(CommandQueueElement el) throws PathCreatorException {

		return buildFile(el, null);
	}
}

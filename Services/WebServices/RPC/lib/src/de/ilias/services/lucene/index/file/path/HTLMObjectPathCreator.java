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
public class HTLMObjectPathCreator implements PathCreator {

	/**
	 * @see de.ilias.services.lucene.index.file.path.PathCreator#buildFile(de.ilias.services.lucene.index.CommandQueueElement, java.sql.ResultSet)
	 */
	public File buildFile(CommandQueueElement el, ResultSet res)
			throws PathCreatorException {

		int objId = el.getObjId();
		StringBuilder fullPath = new StringBuilder();
		
		File file;
		
		try {
			fullPath.append(ClientSettings.getInstance(LocalSettings.getClientKey()).getAbsolutePath());
			fullPath.append(System.getProperty("file.separator"));
			fullPath.append("data");
			fullPath.append(System.getProperty("file.separator"));
			fullPath.append(ClientSettings.getInstance(LocalSettings.getClientKey()).getClient());
			fullPath.append(System.getProperty("file.separator"));
			fullPath.append("lm_data");
			fullPath.append(System.getProperty("file.separator"));
			fullPath.append("lm_");
			fullPath.append(String.valueOf(objId));
			
			file = new File(fullPath.toString());
			if(file.exists() && file.canRead()) {
				return file;
			}
			throw new PathCreatorException("Cannot access directory: " + fullPath.toString());
		}
		catch (ConfigurationException e) {
			throw new PathCreatorException(e);	
		}
	}
	/**
	 * @see de.ilias.services.lucene.index.file.path.PathCreator#buildFile(de.ilias.services.lucene.index.CommandQueueElement)
	 */
	public File buildFile(CommandQueueElement el) throws PathCreatorException {

		return buildFile(el, null);
	}

}

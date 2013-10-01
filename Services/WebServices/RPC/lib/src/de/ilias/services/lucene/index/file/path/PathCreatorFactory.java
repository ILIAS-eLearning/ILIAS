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

import org.apache.log4j.Logger;

import de.ilias.services.object.ObjectDefinitionException;

/**
 * 
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
public class PathCreatorFactory {
	
	private static Logger logger = Logger.getLogger(PathCreator.class);
	
	public static PathCreator factory(String name) throws ObjectDefinitionException {
		
		if(name.equalsIgnoreCase("FileObjectPathCreator")) {
			return (PathCreator) new FileObjectPathCreator();
		}
		if(name.equalsIgnoreCase("FileListPathCreator")) {
			return (PathCreator) new FileListPathCreator();
		}
		if(name.equalsIgnoreCase("FileObjectPathCreator41")) {
			return (PathCreator) new FileObjectPathCreator41();
		}
		if(name.equalsIgnoreCase("FileListPathCreator41")) {
			return (PathCreator) new FileListPathCreator41();
		}
		if(name.equalsIgnoreCase("HTLMObjectPathCreator")) {
			return (PathCreator) new HTLMObjectPathCreator();
		}
		if(name.equalsIgnoreCase("ExerciseAssignmentPathCreator")) {
			return (PathCreator) new ExerciseAssignmentPathCreator();
		}
		if(name.equalsIgnoreCase("MailAttachmentPathCreator")) {
			return (PathCreator) new MailAttachmentPathCreator();
		}
		
		throw new ObjectDefinitionException("Invalid path creator name given: " + name);
	}
}

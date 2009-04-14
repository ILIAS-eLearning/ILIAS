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

import org.apache.log4j.Logger;

import de.ilias.services.lucene.index.CommandQueueElement;

/**
 * 
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
public interface PathCreator {

	public static Logger logger = Logger.getLogger(PathCreator.class);
	
	
	/**
	 * Build absolute file path
	 * @param el
	 * @param res
	 * @return
	 * @throws PathCreatorException
	 */
	public File buildFile(CommandQueueElement el, ResultSet res) throws PathCreatorException;

	/**
	 * Build absolute file path
	 * @param el
	 * @return
	 * @throws PathCreatorException
	 */
	public File buildFile(CommandQueueElement el) throws PathCreatorException;
}
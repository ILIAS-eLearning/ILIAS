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

import org.apache.log4j.Logger;

/**
 * 
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
public class DataSourceFactory {
	
	protected static Logger logger = Logger.getLogger(DataSourceFactory.class);
	
	public static DataSource factory(int type) throws ObjectDefinitionException {
		
		switch(type) {
		
		case DataSource.TYPE_JDBC:
			return new JDBCDataSource(type);
			
		case DataSource.TYPE_FILE:
			return new FileDataSource(type);
			
		case DataSource.TYPE_DIRECTORY:
			return new DirectoryDataSource(type);
		}
		
		throw new ObjectDefinitionException("Invalid type: " + type);
		
	}

	public static DataSource factory(String type) throws ObjectDefinitionException {
		
		logger.info("Type: " + type);
		
		if(type.equalsIgnoreCase("JDBC")) {
			return factory(DataSource.TYPE_JDBC);
		}
		else if(type.equalsIgnoreCase("File")) {
			return factory(DataSource.TYPE_FILE);
		}
		else if(type.equalsIgnoreCase("Directory")) {
			logger.info("Directory DataSource");
			return factory(DataSource.TYPE_DIRECTORY);
		}
		throw new ObjectDefinitionException("Invalid type: " + type);
		
	}
}

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

import java.io.IOException;
import java.io.PrintWriter;
import java.io.StringWriter;
import java.sql.SQLException;

import org.apache.log4j.Logger;
import org.apache.lucene.index.CorruptIndexException;
import org.apache.lucene.store.LockObtainFailedException;

import de.ilias.services.db.DBFactory;
import de.ilias.services.object.ObjectDefinitionException;
import de.ilias.services.object.ObjectDefinitionParser;
import de.ilias.services.object.ObjectDefinitionReader;
import de.ilias.services.object.ObjectDefinitions;
import de.ilias.services.settings.ClientSettings;
import de.ilias.services.settings.ConfigurationException;
import de.ilias.services.settings.LocalSettings;

/**
 * 
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
public class RPCIndexHandler {

	protected static Logger logger = Logger.getLogger(RPCIndexHandler.class);
	

	public boolean refreshIndex(String clientKey) {
		
		// Set client key
		LocalSettings.setClientKey(clientKey);
		DBFactory.init();
		ClientSettings client;
		ObjectDefinitionReader properties;
		ObjectDefinitionParser parser;
		
		CommandController controller;
		
		
		try {
			client = ClientSettings.getInstance(LocalSettings.getClientKey());
			properties = ObjectDefinitionReader.getInstance(client.getAbsolutePath());
			parser = new ObjectDefinitionParser(properties.getObjectPropertyFiles());
			parser.parse();
			
			controller = new CommandController(ObjectDefinitions.getInstance(client.getAbsolutePath()));
			controller.start();
			
			logger.debug(client.getIndexPath());
			
		} 
		catch (ConfigurationException e) {
			logger.error(e);
		} 
		catch (CorruptIndexException e) {
			logger.fatal(e);
		} 
		catch (LockObtainFailedException e) {
			logger.error(e);
		} 
		catch (IOException e) {
			logger.error(e);
		} 
		catch (ObjectDefinitionException e) {
			logger.error(e);
		} 
		catch (SQLException e) {
			logger.error(e);
		}
		catch (Exception e) {
			
			StringWriter writer = new StringWriter();
			e.printStackTrace(new PrintWriter(writer));
			logger.error(writer.toString());
		}
		return true;
	}


}

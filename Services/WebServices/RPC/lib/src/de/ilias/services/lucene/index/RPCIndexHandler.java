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
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Statement;

import org.apache.log4j.Logger;
import org.apache.lucene.analysis.standard.StandardAnalyzer;
import org.apache.lucene.document.Document;
import org.apache.lucene.document.Field;
import org.apache.lucene.document.Field.Index;
import org.apache.lucene.index.CorruptIndexException;
import org.apache.lucene.index.IndexWriter;
import org.apache.lucene.index.IndexWriter.MaxFieldLength;
import org.apache.lucene.store.FSDirectory;
import org.apache.lucene.store.LockObtainFailedException;

import de.ilias.services.db.DBFactory;
import de.ilias.services.object.ObjectDefinitionException;
import de.ilias.services.object.ObjectDefinitionParser;
import de.ilias.services.object.ObjectDefinitionReader;
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
	

	@SuppressWarnings("deprecation")
	public boolean refreshIndex(String clientKey) {
		
		// Set client key
		LocalSettings.setClientKey(clientKey);
		ClientSettings client;
		ObjectDefinitionReader properties;
		ObjectDefinitionParser parser;
		
		try {
			client = ClientSettings.getInstance(LocalSettings.getClientKey());
			properties = ObjectDefinitionReader.getInstance(client.getAbsolutePath());
			parser = new ObjectDefinitionParser(properties.getObjectPropertyFiles());
			parser.parse();
			
			IndexWriter writer = new IndexWriter(FSDirectory.getDirectory(client.getIndexPath(),true),
					new StandardAnalyzer(),
					MaxFieldLength.UNLIMITED);
			
			logger.debug(client.getIndexPath());
			//indexTitleAndDescription(writer);
			writer.optimize();
			writer.close();
			
		} 
		catch (ConfigurationException e) {
			logger.error(e);
		} 
		catch (CorruptIndexException e) {
			logger.error(e);
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
		logger.debug("Start connection");

		
		
		return true;
		
		
		
	}


	/**
	 * @param writer
	 * @return 
	 */
	private boolean indexTitleAndDescription(IndexWriter writer) {
	
		Connection con;
		try {
			con = DBFactory.factory();
			Statement stmt = con.createStatement();
			ResultSet res = stmt.executeQuery("select * from object_data where type in " + 
					"('crs','grp','file','cat','fold','webr','lm','htlm','slm','tst'," + 
					"'surv','sql','qpl','sess')");
			
			PreparedStatement pstmt = con.prepareStatement("SELECT * FROM object_data WHERE obj_id IN(?)");
			
			
			while(res.next()) {
				
				Document doc = new Document();
				doc.add(new Field("obj_id",
							res.getString("obj_id"),
							Field.Store.YES,
							Index.NOT_ANALYZED));
				doc.add(new Field("title",
							res.getString("title"),
							Field.Store.YES,
							Index.ANALYZED));

				doc.add(new Field("type",
						res.getString("type"),
						Field.Store.YES,
						Index.ANALYZED));
				
				doc.add(new Field("description",
						res.getString("description"),
						Field.Store.YES,
						Index.ANALYZED));
				
				writer.addDocument(doc);

				logger.debug("Added title: " + res.getString("title"));
				logger.debug("Added description " + res.getString("description"));
			}
		} 
		catch (SQLException e) {
			logger.error("Cannot handle refreshIndex()" + e);
			return false;
		}
		catch (CorruptIndexException e) {
			logger.fatal("Error writing to index: " + e);
		} 
		catch (IOException e) {
			logger.fatal("Error writing to index: " + e);
		} 
		return false;
	}
}

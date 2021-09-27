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

import java.io.IOException;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.Vector;

import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;

import de.ilias.services.db.DBFactory;
import de.ilias.services.lucene.index.CommandQueueElement;
import de.ilias.services.lucene.index.DocumentHandler;
import de.ilias.services.lucene.index.DocumentHandlerException;
import de.ilias.services.lucene.index.DocumentHolder;
import de.ilias.services.lucene.index.IndexHolder;

/**
 * 
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
public class JDBCDataSource extends DataSource {

  private static Logger logger = LogManager.getLogger(JDBCDataSource.class);

	String query;
	Vector<ParameterDefinition> parameters = new Vector<ParameterDefinition>();
	

	/**
	 * @param type
	 */
	public JDBCDataSource(int type) {

		super(type);
	}

	/**
	 * @return the query
	 */
	public String getQuery() {
		return query;
	}

	/**
	 * @param query the query to set
	 */
	public void setQuery(String query) {
		this.query = query;
	}

	/**
	 * 
	 * @return {@link PreparedStatement} 
	 * @throws SQLException
	 */
	public PreparedStatement getStatement() throws SQLException {
		
		return DBFactory.getPreparedStatement(getQuery());
	}
	
	
	/**
	 * @return the parameters
	 */
	public Vector<ParameterDefinition> getParameters() {
		return parameters;
	}

	/**
	 * @param parameters the parameters to set
	 */
	public void setParameters(Vector<ParameterDefinition> parameters) {
		this.parameters = parameters;
	}
	
	/**
	 * 
	 * @param parameter
	 */
	public void addParameter(ParameterDefinition parameter) {
		this.parameters.add(parameter);
	}

	/**
	 * @see java.lang.Object#toString()
	 */
	@Override
	public String toString() {
		
		StringBuffer out = new StringBuffer();
		
		out.append("New JDBC Data Source" );
		out.append("\n");
		out.append("Query: " + getQuery());
		out.append("\n");
		
		for(Object param : getParameters()) {
			
			out.append(param.toString());
		}
		out.append(super.toString());
		
		return out.toString();
	}
	
	/**
	 * 
	 * @see de.ilias.services.lucene.index.DocumentHandler#writeDocument(de.ilias.services.lucene.index.CommandQueueElement)
	 */
	public void writeDocument(CommandQueueElement el)
			throws DocumentHandlerException, IOException {

		writeDocument(el,null);
	}

	/**
	 * @todo remove the synchronized (cannot use more than one result set for one prepared statement)
	 * @see de.ilias.services.lucene.index.DocumentHandler#writeDocument(de.ilias.services.lucene.index.CommandQueueElement, java.sql.ResultSet)
	 */
	public void writeDocument(CommandQueueElement el, ResultSet parentResult)
			throws DocumentHandlerException {
		
		logger.trace("Handling data source: " + getType());
		
		try {
			// Create Statement for JDBC data source
			DocumentHolder doc = DocumentHolder.factory();
			
			int paramNumber = 1;
			for(ParameterDefinition param : getParameters()) {
				
				param.writeParameter(getStatement(),paramNumber++,el,parentResult);
			}
			
			logger.trace(getStatement());
			ResultSet res = getStatement().executeQuery();
			
			while(res.next()) {
				
				logger.trace("Found new result");
				for(FieldDefinition field : getFields()) {
					field.writeDocument(res);
				}
				
				// Add subitems from additional data sources
				for(DocumentHandler ds : getDataSources()) {
					
					ds.writeDocument(el,res);
				}

				// Finally addDocument to index
				if(getAction().equalsIgnoreCase(ACTION_CREATE)) {
					logger.trace("Adding new document...");
					IndexHolder writer = IndexHolder.getInstance();
					writer.addDocument(doc.getDocument());
					doc.newDocument();
				}
			}
			try {
				res.close();
			} catch (SQLException e) {
				logger.warn("Cannot close result set", e);
			}
		}
		catch (SQLException | IOException e) {
			logger.error("Cannot parse data source.", e);
			throw new DocumentHandlerException(e);
		} 

	}
}
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
import java.io.PrintWriter;
import java.io.StringWriter;
import java.sql.ResultSet;
import java.util.Vector;

import org.apache.log4j.Logger;

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
public class DocumentDefinition implements DocumentHandler {

	protected Logger logger = Logger.getLogger(DocumentDefinition.class);

	private String type;
	private Vector<DataSource> dataSource = new Vector<DataSource>();
	
	/**
	 * 
	 */
	public DocumentDefinition(String type) {
		this.type = type;
	}

	/**
	 * @param type the type to set
	 */
	public void setType(String type) {
		this.type = type;
	}

	/**
	 * @return the type
	 */
	public String getType() {
		return type;
	}

	/**
	 * @return the dataSource
	 */
	public Vector<DataSource> getDataSource() {
		return dataSource;
	}

	/**
	 * @param dataSource the dataSource to set
	 */
	public void setDataSource(Vector<DataSource> dataSource) {
		this.dataSource = dataSource;
	}
	
	/**
	 * 
	 * @param source
	 */
	public void addDataSource(DataSource source) {
		this.dataSource.add(source);
	}

	/* (non-Javadoc)
	 * @see java.lang.Object#toString()
	 */
	@Override
	public String toString() {
		
		StringBuffer out = new StringBuffer();
		
		out.append("Document of type = " + getType());
		out.append("\n");
		
		for(Object doc : getDataSource()) {
			
			out.append(doc.toString());
			out.append("\n");
		}
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
	 * @see de.ilias.services.lucene.index.DocumentHandler#writeDocument(de.ilias.services.lucene.index.CommandQueueElement, java.sql.ResultSet)
	 */
	public void writeDocument(CommandQueueElement el, ResultSet res)
			throws DocumentHandlerException {

		DocumentHolder doc = DocumentHolder.factory();
		doc.newDocument();

		for(int i = 0; i < getDataSource().size();i++) {
			
			try {
				getDataSource().get(i).writeDocument(el);
			}
			catch(IOException e) {
				logger.warn("Cannot parse data source: " + e);
			}
			catch( DocumentHandlerException e) {
				logger.warn(e);
			}
		}
		
		IndexHolder writer;
		try {
			writer = IndexHolder.getInstance();
			if(doc.getDocument() == null) {
				logger.warn("Found empty document.");
			}
			else {
				writer.getWriter().addDocument(doc.getDocument());
			}
		}
		catch (IOException e) {
			logger.warn(e);
		}
	}
}

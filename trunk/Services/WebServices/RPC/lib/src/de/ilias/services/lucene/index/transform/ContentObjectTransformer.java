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

package de.ilias.services.lucene.index.transform;

import java.io.IOException;
import java.io.StringReader;

import org.apache.log4j.Logger;
import org.xml.sax.InputSource;
import org.xml.sax.SAXException;
import org.xml.sax.XMLReader;
import org.xml.sax.helpers.XMLReaderFactory;

/**
 * 
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
public class ContentObjectTransformer implements ContentTransformer {

	protected Logger logger = Logger.getLogger(ContentObjectTransformer.class);
	
	

	/**
	 * Extract text from page_objects
	 * @see de.ilias.services.lucene.index.transform.ContentTransformer#transform(java.lang.String)
	 */
	public String transform(String content) {

		XMLReader reader = null;
		PageObjectHandler handler = null;
		StringReader stringReader = new StringReader(content);
		
		try {
			reader = XMLReaderFactory.createXMLReader();
			handler = new PageObjectHandler();
			
			reader.setContentHandler(handler);
			reader.parse(new InputSource(stringReader));
			
			return handler.getContent();
			
		} 
		catch (SAXException e) {
			logger.warn("Cannot parse page_object content." + e);
		} 
		catch (IOException e) {
			logger.warn("Found invalid content." + e);
		}
		
		return "";
	}


}

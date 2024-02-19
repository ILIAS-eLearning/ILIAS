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

import java.util.Stack;

import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;
import org.xml.sax.Attributes;
import org.xml.sax.SAXException;
import org.xml.sax.helpers.DefaultHandler;

public class PageObjectHandler extends DefaultHandler {

	protected Logger logger = LogManager.getLogger(PageObjectHandler.class);
	
	private StringBuffer buffer = new StringBuffer();
	private boolean isContent = false;
	
	public void endDocument() {
	}
    
	/**
	 * 
	 */
    public void startElement (String uri, String localName, String qName, Attributes attributes)
	throws SAXException
	{
    	if(qName.equalsIgnoreCase("Paragraph")) {
    		isContent = true;
    	}
	}

	/**
	 * 
	 */
	public void endElement (String uri, String localName, String qName)
	throws SAXException
    {
		if(qName.equalsIgnoreCase("Paragraph")) {
			isContent = false;
		}
    }

	/**
	 * 
	 */
    public void characters (char[] ch, int start, int length)
	throws SAXException
    {
    	if(!isContent) {
    		return;
    	}
		for (int i = start; i < start + length; i++) {
			switch (ch[i]) {
				case '\\':
				case '"':
				case '\r':
				case '\n':
				case '\t':
					break;
				default:
					buffer.append(ch[i]);
			}
		}
    	buffer.append(' ');
    }

	public String getContent() {
		return buffer.toString();
	}

}

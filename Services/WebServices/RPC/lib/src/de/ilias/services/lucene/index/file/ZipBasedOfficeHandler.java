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

package de.ilias.services.lucene.index.file;

import java.io.ByteArrayInputStream;
import java.io.ByteArrayOutputStream;
import java.io.IOException;
import java.io.InputStream;
import java.util.List;
import java.util.zip.ZipEntry;
import java.util.zip.ZipException;
import java.util.zip.ZipInputStream;

import org.apache.log4j.Logger;
import org.jdom.Element;
import org.jdom.JDOMException;
import org.jdom.input.SAXBuilder;
import org.jdom.xpath.XPath;

/**
 * 
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
public abstract class ZipBasedOfficeHandler {

	protected static Logger logger = Logger.getLogger(ZipBasedOfficeHandler.class); 
	protected static final int BUFFER = 2048;
	
	/**
	 * get name of content file 
	 * E.g content.xml for .odt files
	 * @return
	 */
	abstract protected String getContentFileName();
	abstract protected String getXPath();
	
	protected InputStream extractContentStream(InputStream is) throws FileHandlerException	{
	
		ByteArrayOutputStream bout = new ByteArrayOutputStream();
		try {
			ZipInputStream zip = new ZipInputStream(is);
			ZipEntry entry;
			
			while((entry = zip.getNextEntry()) != null) {
				
				if(entry.getName().equalsIgnoreCase(getContentFileName())) {
					int count;
					byte data[] = new byte[BUFFER];
					while((count = zip.read(data,0,BUFFER)) != -1) {
						bout.write(data, 0, count);
					}
					break;
				}
			}
			is.close();
			return new ByteArrayInputStream(bout.toByteArray());
		} 
		catch(ZipException e) {
			logger.info("Cannot extract " + getContentFileName() + " " + e.getMessage());
			throw new FileHandlerException(e);
		}
		catch (IOException e) {
			logger.info("Cannot extract " + getContentFileName() + " " + e.getMessage());
			throw new FileHandlerException(e);
		}
		finally {
			try {
				bout.close();
			} 
			catch (IOException e) {
				// Yepp
			}
		}
	}
	
	public String extractContent(InputStream is) {
		
		SAXBuilder builder = new SAXBuilder();
		StringBuilder content = new StringBuilder();
		
		try {
			org.jdom.Document doc = builder.build(is);
			XPath xpath = XPath.newInstance(getXPath());
			List res = xpath.selectNodes(doc);
			
			for(Object element : res) {
				Element el = (Element) element;
				content.append(" ");
				content.append(el.getTextTrim());
			}
			return content.toString();

		}
		catch (NullPointerException e) {
			logger.warn("Caught NullPointerException: " + e);
		}
		catch (JDOMException e) {
			logger.info("Cannot parse OO content: " + e);
		} 
		catch (IOException e) {
			logger.info("Cannot parse OO content: " + e);
		}
		return "";
	}
	
}

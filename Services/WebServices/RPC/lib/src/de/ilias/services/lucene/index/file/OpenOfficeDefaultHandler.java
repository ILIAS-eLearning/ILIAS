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

import java.io.IOException;
import java.io.InputStream;

/**
 * 
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
public class OpenOfficeDefaultHandler extends ZipBasedOfficeHandler  implements FileHandler {

	/**
	 * @see de.ilias.services.lucene.index.file.FileHandler#getContent(java.io.InputStream)
	 */
	public String getContent(InputStream is) throws FileHandlerException, IOException {

		InputStream contentStream = extractContentStream(is);
		StringBuilder content = new StringBuilder();
		content.append(extractContent(contentStream));
		logger.debug(content.toString());
		
		if(contentStream != null) {
			try {
				contentStream.close();
			}
			catch(IOException e) {
				// Nothing
			}
		}
		
		return content.toString();
	}

	/**
	 * @see de.ilias.services.lucene.index.file.FileHandler#transformStream(java.io.InputStream)
	 */
	public InputStream transformStream(InputStream is) {
		return null;
	}

	/**
	 * @see de.ilias.services.lucene.index.file.ZipBasedOfficeHandler#getContentFileName()
	 */
	protected String getContentFileName() {
		
		return "content.xml";
	}

	/**
	 * @see de.ilias.services.lucene.index.file.ZipBasedOfficeHandler#getXPath()
	 */
	protected String getXPath() {

		return "//text:p";
	}
	
	
	
	

}

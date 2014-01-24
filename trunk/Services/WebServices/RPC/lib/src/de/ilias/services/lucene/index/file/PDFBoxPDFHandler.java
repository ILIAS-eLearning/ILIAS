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
import java.io.StringWriter;
import java.util.logging.Level;

import org.apache.log4j.Logger;
import org.apache.pdfbox.pdmodel.PDDocument;
import org.apache.pdfbox.util.PDFTextStripper;

/**
 * 
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
public class PDFBoxPDFHandler implements FileHandler {

	protected Logger logger = Logger.getLogger(PDFBoxPDFHandler.class);
	
	
	/**
	 * @throws IOException 
	 * @see de.ilias.services.lucene.index.file.FileHandler#getContent(java.io.InputStream)
	 */
	public String getContent(InputStream is) throws FileHandlerException {

		PDDocument pddo = null;
		PDFTextStripper stripper = null;
		String str = new String("");
		
		try {

			pddo = PDDocument.load(is);

			if(pddo.isEncrypted()) {
				logger.warn("PDF Document is encrypted. Trying empty password...");
				return "";
			}
			stripper = new PDFTextStripper();
			str = stripper.getText(pddo);
		}
		catch (NumberFormatException e) {
			logger.warn("Invalid PDF version number given. Aborting");
		}
		catch (IOException e) {
			logger.warn(e.getMessage());
			throw new FileHandlerException(e);
		}
		catch (Exception e) {
			logger.error(e.getMessage());
			throw new FileHandlerException(e);			
		}
		finally {
			try {
				if(pddo != null)
					pddo.close();
			}
			catch (IOException e) {
				;
			}
		}
		return str;
	}

	/**
	 * @see de.ilias.services.lucene.index.file.FileHandler#transformStream(java.io.InputStream)
	 */
	public InputStream transformStream(InputStream is) {
		// TODO Auto-generated method stub
		return null;
	}

}

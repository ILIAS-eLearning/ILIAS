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


import de.ilias.services.settings.ConfigurationException;
import de.ilias.services.settings.ServerSettings;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;
import org.apache.tika.exception.TikaException;
import org.apache.tika.metadata.Metadata;
import org.apache.tika.parser.AutoDetectParser;
import org.apache.tika.sax.BodyContentHandler;
import org.xml.sax.SAXException;

import java.io.File;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.IOException;

/**
 * 
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
public class ExtensionFileHandler {

    protected static Logger logger = LogManager.getLogger(ExtensionFileHandler.class);

    
    public ExtensionFileHandler() {
        // Do nothing here
    }

    /**
     * 
     * @param file
     * @return
     * @throws FileHandlerException
     */
    public String getContent(File file, String extension) throws FileHandlerException {

    	// Stop here if no read permission is given
        if(!file.canRead()) {
            throw new FileHandlerException("No permission to read file: " + file.getAbsolutePath());
        }

		// Check file size
		if(!checkFileSizeLimit(file)) {
			throw new FileHandlerException("File size limit exceeded. Ignoring file " + file.getAbsolutePath());
		}
       	logger.info("Current file is: " + file.getAbsolutePath());
		String content = tryTikaParser(file);
		if (content != "") {
			return content;
		}
		try {
			String fname = file.getName();
			int dotIndex = fname.lastIndexOf(".");
			if ((extension.length() == 0)
				&& (dotIndex > 0)
				&& (dotIndex < fname.length())) {
				extension = fname.substring(dotIndex + 1);
			}
			if (extension.equalsIgnoreCase("")) {
				logger.warn("no valid extension found for: " + file.getName());
				return "";
			}
        }
    	catch(Exception e) {
        	logger.warn("Parsing failed with message: " + e);
        	return "";
        }
		return "";
    }

	private String tryTikaParser(File file) {
		FileInputStream is = null;
		BodyContentHandler handler = new BodyContentHandler(-1);
		Metadata md = new Metadata();
		AutoDetectParser parser = new AutoDetectParser();

		try {
			is = new FileInputStream(file);
			parser.parse(is, handler, md);
			logger.info("Parsed content: {}", handler.toString());
			return handler.toString();
		} catch (FileNotFoundException e) {
			logger.warn(e);
		} catch (TikaException e) {
			logger.warn(e);
		} catch (IOException e) {
			logger.warn(e);
		} catch (SAXException e) {
			logger.warn(e);
		} finally {
			try {
				if(is != null) {
					is.close();
				}
			}
			catch(IOException e) {
				// Nothing
			}
		}
		return "";
	}
    
	/**
	 * Check file size limit
	 * @param file
	 * @return bool
	 */
	private boolean checkFileSizeLimit(File file)
	{
		long maxFileSize = 0;

		try {
			maxFileSize = ServerSettings.getInstance().getMaxFileSize();
		}
		catch(ConfigurationException e) {
			maxFileSize = ServerSettings.DEFAULT_MAX_FILE_SIZE;
		}

		if(file.length() > maxFileSize) {
			logger.info("File size is " + file.length() + " bytes.");
			return false;
		}
		return true;
	}
}

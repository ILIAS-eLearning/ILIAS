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


import java.io.File;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.IOException;

import org.apache.log4j.Logger;
import org.apache.poi.POITextExtractor;
import org.apache.poi.extractor.ExtractorFactory;
import org.apache.poi.openxml4j.exceptions.InvalidFormatException;
import org.apache.poi.openxml4j.exceptions.OpenXML4JException;
import org.apache.xmlbeans.XmlException;

/**
 * 
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
public class ExtensionFileHandler {

    protected static Logger logger = Logger.getLogger(ExtensionFileHandler.class);

    
    public ExtensionFileHandler() {
        // Do nothing here
    }

    /**
     * 
     * @param file
     * @return
     * @throws FileHandlerException
     */
    public String getContent(File file) throws FileHandlerException {

    	
    	// Stop here if no read permission is given
        if(!file.canRead()) {
            throw new FileHandlerException("No permission to read file: " + file.getAbsolutePath());
        }
       
    	try {
	        String fname = file.getName();
	        int dotIndex = fname.lastIndexOf(".");
	        if((dotIndex > 0) && (dotIndex < fname.length())) {
	            String extension = fname.substring(dotIndex + 1, fname.length());
	            
	            // Handled extensions are: html,pdf,txt
	            if(extension.equalsIgnoreCase("pdf")) {
	                logger.info("Using getPDFDocument() for " + file.getName());
	                return getPDFDocument(file);
	            }
	            if(extension.equalsIgnoreCase("html") || extension.equalsIgnoreCase("htm")) {
	                logger.info("Using getHTMLDocument() for " + file.getName());
	                return getHTMLDocument(file);
	            }
	            if(extension.equalsIgnoreCase("txt") || extension.length() == 0) {
	                logger.info("Using getTextDocument() for: " + file.getName() );
	                return getTextDocument(file);
	            }
	            // Open office
	            if(extension.equalsIgnoreCase("odt")) {
	            	logger.info("Using getOpenOfficeDocument() for " + file.getName());
	            	return getOpenOfficeDocument(file);
	            }
	            if(extension.equalsIgnoreCase("ott")) {
	            	logger.info("Using getOpenOfficeDocument() for " + file.getName());
	            	return getOpenOfficeDocument(file);
	            }
	            if(extension.equalsIgnoreCase("stw")) {
	            	logger.info("Using getOpenOfficeDocument() for " + file.getName());
	            	return getOpenOfficeDocument(file);
	            }
	            if(extension.equalsIgnoreCase("sxw")) {
	            	logger.info("Using getOpenOfficeDocument() for " + file.getName());
	            	return getOpenOfficeDocument(file);
	            }
	            if(extension.equalsIgnoreCase("odg")) {
	            	logger.info("Using getOpenOfficeDocument() for " + file.getName());
	            	return getOpenOfficeDocument(file);
	            }
	            if(extension.equalsIgnoreCase("odp")) {
	            	logger.info("Using getOpenOfficeDocument() for " + file.getName());
	            	return getOpenOfficeDocument(file);
	            }
	            if(extension.equalsIgnoreCase("sti")) {
	            	logger.info("Using getOpenOfficeDocument() for " + file.getName());
	            	return getOpenOfficeDocument(file);
	            }
	            if(extension.equalsIgnoreCase("sxd")) {
	            	logger.info("Using getOpenOfficeDocument() for " + file.getName());
	            	return getOpenOfficeDocument(file);
	            }
	            if(extension.equalsIgnoreCase("sxw")) {
	            	logger.info("Using getOpenOfficeDocument() for " + file.getName());
	            	return getOpenOfficeDocument(file);
	            }
	            // Flat XML OO documents
	            if(extension.equalsIgnoreCase("fodt")) {
	            	logger.info("Using getOpenOfficeDocument() for " + file.getName());
	            	return getFlatOpenOfficeDocument(file);
	            }
	            if(extension.equalsIgnoreCase("fodp")) {
	            	logger.info("Using getOpenOfficeDocument() for " + file.getName());
	            	return getFlatOpenOfficeDocument(file);
	            }
	            
	        }
        }
        catch(Exception e) {
        	logger.info("Parsing failed with message: " + e);
        	logger.info("Current file is: " + file.getAbsolutePath());
        	return "";
        }
        
    	// Start with poi
    	try {
    		
    		StringBuilder content = new StringBuilder();
    		POITextExtractor extractor;
    		extractor = ExtractorFactory.createExtractor(file);
    		content.append(extractor.getText());
    		
    		if(content.length() > 0) {
    			logger.info("Parsed file: " + file.getName());
    		}
    		else {
    			logger.warn("No content found for" + file.getName());
    		}
    		logger.debug("Parsed content is: " + content.toString());
    		return content.toString();
    	}
    	catch(IOException e) {
    		logger.warn(e);
    	} 
    	catch (InvalidFormatException e) {
    		logger.debug("File is not a compatible POI file.");
    	}
    	catch(IllegalArgumentException e) {
    		logger.info("File is not a compatible POI file.");
    	}
    	catch (OpenXML4JException e) {
    		logger.info(e);
    	} 
    	catch (XmlException e) {
    		logger.info(e);
		}
    	catch (Exception e) {
    		logger.warn(e);
    	}
        
        return "";
    }
    

	/**
     * 
     * @param file
     * @return
     * @throws ilFileHandlerException
     */
    private String getTextDocument(File file) throws FileHandlerException {
        
        FileHandler doch = (FileHandler) new PlainTextHandler();
        
        try {
            return doch.getContent(new FileInputStream(file.getAbsolutePath()));
        }
        catch(FileNotFoundException e) {
            throw new FileHandlerException("Cannot find file: " + file.getAbsolutePath());
        }
        catch(FileHandlerException e) {
            throw e;
        } 
        catch (IOException e) {
            throw new FileHandlerException(e);
		}
    }

	private String getPDFDocument(File file) throws FileHandlerException {
        
    	FileHandler doch = (FileHandler) new PDFBoxPDFHandler();
        logger.debug("Start PDFBoxPDFHandler...");

        /*
        String name = file.getName();
        if(name.startsWith("Dive")) {
        	logger.info("DiveInside ignored");
        	return "";
        }
        if(name.startsWith("Anemonen")) {
        	logger.info("DiveInside ignored");
        	return "";
        }
        if(name.startsWith("DI")) {
        	logger.info("DiveInside ignored");
        	return "";
        }
        if(name.startsWith("hur")) {
        	logger.info("DiveInside ignored");
        	return "";
        }
        if(name.startsWith("Lucene")) {
        	logger.info("DiveInside ignored");
        	return "";
        }
        */
        
        try {
            logger.debug(file.getAbsolutePath());
        	return doch.getContent(new FileInputStream(file.getAbsolutePath()));
        }
        catch(IOException e) {
            throw new FileHandlerException("Caught unknown exception " + e.getMessage());
        }
        catch(FileHandlerException e) {
            throw e;
        }

        catch(Exception e) {
            throw new FileHandlerException("Caught unknown exception " + e.getMessage());
        }
	}

    /*
    private Document getHTMLDocument(File file)
    	throws ilFileHandlerException {
        
        Document doc = null;
        ilDocumentHandler doch = (ilDocumentHandler) new ilJTidyHTMLHandler();
        
        try {
            doc = doch.getDocument(new FileInputStream(file.getAbsolutePath()));
        }
        catch(FileNotFoundException e) {
            throw new ilFileHandlerException("Cannot find file: " + file.getAbsolutePath());
        }
        catch(ilDocumentHandlerException e) {
            throw new ilFileHandlerException(e.getMessage());
        }
        return doc;
    }
    */
    /**
	 * @param file
	 * @return
	 */
	private String getOpenOfficeDocument(File file) throws FileHandlerException {
		
		FileHandler doch = (FileHandler) new OpenOfficeDefaultHandler();
		
		try {
			return doch.getContent(new FileInputStream(file.getAbsolutePath()));
		}
		catch(IOException e) {
			throw new FileHandlerException(e);
		}
	}

    /**
     * Get 
	 * @param file
	 * @return
	 */
	private String getFlatOpenOfficeDocument(File file) throws FileHandlerException {
		
		OpenOfficeDefaultHandler doch =  new OpenOfficeDefaultHandler();
		
		try {
			return doch.extractContent(new FileInputStream(file.getAbsolutePath()));
		}
		catch(IOException e) {
			throw new FileHandlerException(e);
		}
	}
	
	/*
    private Document getFlashDocument(File file)
        throws ilFileHandlerException {
            
        Document doc = null;
        ilDocumentHandler doch = (ilDocumentHandler) new ilFlashHandler();
        
        try {
            doc = doch.getDocument(new FileInputStream(file.getAbsolutePath()));
        }
        catch(FileNotFoundException e) {
            throw new ilFileHandlerException("Cannot find file: " + file.getAbsolutePath());
        }
        catch(ilDocumentHandlerException e) {
            throw new ilFileHandlerException(e.getMessage());
        }
        return doc;
        
    }
    */
}

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

    	// TODO: handle extension read from database
    	
    	// Stop here if no read permission is given
        if(!file.canRead()) {
            throw new FileHandlerException("No permission to read file: " + file.getAbsolutePath());
        }
       
        String fname = file.getName();
        int dotIndex = fname.lastIndexOf(".");
        if((dotIndex > 0) && (dotIndex < fname.length())) {
            String extension = fname.substring(dotIndex + 1, fname.length());
            
            // Handled extensions are: html,pdf,txt
            if(extension.equalsIgnoreCase("pdf")) {
                logger.info("Using getPDFDocument() for " + file.getName());
                return getPDFDocument(file);
            }
            /*
            if(extension.equalsIgnoreCase("html") || extension.equalsIgnoreCase("htm")) {
                logger.info("Using getHTMLDocument() for " + file.getName());
                return getHTMLDocument(file);
            }
            */
            if(extension.equalsIgnoreCase("txt") || extension.length() == 0) {
                logger.info("Using getTextDocument() for: " + file.getName() );
                return getTextDocument(file);
            }
            else {
                logger.info("No file handler found for: " + fname);
            }
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

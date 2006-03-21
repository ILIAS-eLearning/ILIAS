/*
+------------------------------------------------------------------------------------------+
| ILIAS open source                                                                        |
+------------------------------------------------------------------------------------------+
| Copyright (c) 1998-2001 ILIAS open source, University of Cologne                         |
|                                                                                          |
| This program is free software; you can redistribute it and/or                            |
| modify it under the terms of the GNU General Public License                              |
| as published by the Free Software Foundation; either version 2                           |
| of the License, or (at your option) any later version.                                   |
|                                                                                          |
| This program is distributed in the hope that it will be useful,                          |
| but WITHOUT ANY WARRANTY; without even the implied warranty of                           |
| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the                            |
| GNU General Public License for more details.                                             |
|																						   |
| You should have received a copy of the GNU General Public License            			   |
| along with this program; if not, write to the Free Software                              |
| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. 			   |
+------------------------------------------------------------------------------------------+
*/

package ilias.lucene;

import java.io.File;
import java.io.FileInputStream;
import java.io.FileNotFoundException;

import org.apache.log4j.Logger;
import org.apache.lucene.document.Document;

/**
 * @author Stefan Meyer <smeyer@databay.de>
 * 
 */

public class ilExtensionFileHandler implements ilFileHandler {

    private Logger logger = Logger.getLogger(this.getClass().getName());

    
    public ilExtensionFileHandler() {
        // Do nothing here
    }

    public Document getDocument(File file)
    throws ilFileHandlerException {
        Document doc = null;

        // Stop here if no read permission is given
        if(!file.canRead()) {
            throw new ilFileHandlerException("No permission to read file: " + file.getAbsolutePath());
        }
       
        String fname = file.getName();
        int dotIndex = fname.indexOf(".");
        //logger.info("File: " + fname + "dotIndex: " + dotIndex);
        if((dotIndex > 0) && (dotIndex < fname.length())) {
            String extension = fname.substring(dotIndex + 1, fname.length());
            
            //logger.info("EXTENSION: " + extension );
            // Handled extensions are: html,pdf,txt
            if(extension.equalsIgnoreCase("pdf")) {
                logger.info("CALLED: getPDFDocument() for " + file.getName());
                return getPDFDocument(file);
            }
            else if(extension.equalsIgnoreCase("html") || extension.equalsIgnoreCase("htm")) {
                logger.info("CALLED: getHTMLDocument() for " + file.getName());
                return getHTMLDocument(file);
            }
            else if(extension.equalsIgnoreCase("txt") || extension.length() == 0){
                logger.info("CALLED: getTextDocument() for: " + file.getName() );
                return getTextDocument(file);
            }
            else {
                logger.info("Cannot parse file: " + fname);
            }
            /*
            if(extension.equalsIgnoreCase("swf")) {
                logger.info("CALLED: getFlashDocument() for " + file.getName());
                return getFlashDocument(file);
            }
            */
        }
        return doc;
    }
    private Document getTextDocument(File file)
    throws ilFileHandlerException {
        
        Document doc = null;
        ilDocumentHandler doch = (ilDocumentHandler) new ilPlainTextHandler();
        
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
    private Document getPDFDocument(File file)
    	throws ilFileHandlerException {
        
        Document doc = null;
        ilDocumentHandler doch = (ilDocumentHandler) new ilPDFBoxPDFHandler();
        
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
}
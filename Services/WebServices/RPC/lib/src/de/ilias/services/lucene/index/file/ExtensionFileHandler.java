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
import java.io.File;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.IOException;

import org.apache.log4j.Logger;
import org.apache.poi.POITextExtractor;
import org.apache.poi.extractor.ExtractorFactory;
import org.apache.poi.openxml4j.exceptions.InvalidFormatException;

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

		// Check file size
		if(!checkFileSizeLimit(file)) {
			throw new FileHandlerException("File size limit exceeded. Ignoring file " + file.getAbsolutePath());
		}
		
       	logger.info("Current file is: " + file.getAbsolutePath());
		
       
    	try {
	        String fname = file.getName();
	        int dotIndex = fname.lastIndexOf(".");
	        if((dotIndex > 0) && (dotIndex < fname.length())) {
	            String extension = fname.substring(dotIndex + 1, fname.length());
				
				
				// Do not index xslx
				if(extension.equalsIgnoreCase("xlsx")) {
					logger.info("Ignoring xslx: " + file.getName());
					return "";
				}
	            // Handled extensions are: html,pdf,txt
	            if(extension.equalsIgnoreCase("pdf")) {
	                logger.info("Using getPDFDocument() for " + file.getName());
	                return getPDFDocument(file);
	            }
	            // HTML
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
	            
	            // RTF
	            if(extension.equalsIgnoreCase("rtf")) {
	            	logger.info("Using getRTFDocument() for " + file.getName());
	            	return getRTFDocument(file);
	            }
	        }
        }
    	catch (FileHandlerException e) {
        	logger.warn("Parsing failed with message: " + e);
        	return "";
    	}
    	
    	catch(Exception e) {
        	logger.warn("Parsing failed with message: " + e);
        	return "";
        }
    	
    	return tryPOIDocument(file);
    }
    
    /**
     * Try to extract POI content
     * @param file
     */
    private String tryPOIDocument(File file) {
    	
    	FileInputStream fis = null;
    	
    	try {
    		StringBuilder content = new StringBuilder();
    		POITextExtractor extractor = null;
    		
    		extractor = ExtractorFactory.createExtractor(fis = new FileInputStream(file));
    		content.append(extractor.getText());

    		if(content.length() > 0) {
    			logger.info("Parsed file: " + file.getName());
    		}
    		else {
    			logger.warn("No content found for" + file.getName());
    		}
    		//logger.debug("Parsed content is: " + content.toString());
    		return content.toString();
    	}
    	catch (InvalidFormatException e) {
    		logger.info("File is not a compatible POI file.");
        	logger.info("Current file is: " + file.getAbsolutePath());
    	}
    	catch(IllegalArgumentException e) {
    		logger.info("No handler found.");
        	logger.info("Current file is: " + file.getAbsolutePath());
    	}
    	catch (Exception e) {
        	logger.warn("Parsing failed with message: " + e);
        	logger.info("Current file is: " + file.getAbsolutePath());
    	}
    	finally {
    		try {
    			if(fis != null) {
    				fis.close();
    			}
    		}
    		catch(IOException e) {
    			// Nothing
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
        
        FileInputStream fis = null;
    	FileHandler doch = (FileHandler) new PlainTextHandler();
        
        try {
            return doch.getContent(fis = new FileInputStream(file.getAbsolutePath()));
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
        finally {
        	try {
				if(fis != null)
					fis.close();
			} 
        	catch (IOException e) {
			}
        }
    }

    /**
     * 
     * @param file
     * @return
     * @throws FileHandlerException
     */
	private String getPDFDocument(File file) throws FileHandlerException {
        
    	FileHandler doch = (FileHandler) new PDFBoxPDFHandler();
    	FileInputStream fis = null;
        logger.debug("Start PDFBoxPDFHandler...");

        try {
        
        	logger.debug(file.getAbsolutePath());
        	return doch.getContent(fis = new FileInputStream(file.getAbsolutePath()));
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
        finally {
        	try {
				if(fis != null)
					fis.close();
			} 
        	catch (IOException e) {
			}
        }
	}

	/**
	 * 
	 * @param file
	 * @return
	 * @throws FileHandlerException
	 */
    private String getHTMLDocument(File file) throws FileHandlerException {
        
    	FileInputStream fis = null;
        FileHandler doch = (FileHandler) new JTidyHTMLHandler();
        
        try {
            return doch.getContent(fis = new FileInputStream(file.getAbsolutePath()));
        }
        catch(FileHandlerException e) {
            throw e;
        }
        catch(IOException e) {
            throw new FileHandlerException(e);
        }
        finally {
        	try {
				if(fis != null)
					fis.close();
			} 
        	catch (IOException e) {
			}
        }
    }
    
    /**
	 * @param file
	 * @return
	 */
	private String getOpenOfficeDocument(File file) throws FileHandlerException {

		FileInputStream fis = null;
		FileHandler doch = (FileHandler) new OpenOfficeDefaultHandler();
		
		try {
			return doch.getContent(fis = new FileInputStream(file.getAbsolutePath()));
		}
		catch(IOException e) {
			throw new FileHandlerException(e);
		}
        finally {
        	try {
				if(fis != null)
					fis.close();
			} 
        	catch (IOException e) {
			}
        }
	}

    /**
     * Get 
	 * @param file
	 * @return
	 */
	private String getFlatOpenOfficeDocument(File file) throws FileHandlerException {
		
		FileInputStream fis = null;
		OpenOfficeDefaultHandler doch =  new OpenOfficeDefaultHandler();
		
		try {
			return doch.extractContent(fis = new FileInputStream(file.getAbsolutePath()));
		}
		catch(IOException e) {
			throw new FileHandlerException(e);
		}
        finally {
        	try {
				if(fis != null)
					fis.close();
			} 
        	catch (IOException e) {
			}
        }
	}
	
    /**
     * Get rtf document
	 * @param file
	 * @return
	 */
	private String getRTFDocument(File file) throws FileHandlerException {
		
		FileInputStream fis = null;
		FileHandler doch = (FileHandler) new RTFHandler();
		
		try {
			return doch.getContent(fis = new FileInputStream(file.getAbsolutePath()));
		}
		catch(IOException e) {
			throw new FileHandlerException(e);
		}
        finally {
        	try {
				if(fis != null)
					fis.close();
			} 
        	catch (IOException e) {
			}
        }
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

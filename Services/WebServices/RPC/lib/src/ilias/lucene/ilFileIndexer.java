/*
+-----------------------------------------------------------------------------------------+
| ILIAS open source                                                                                           |
+-----------------------------------------------------------------------------------------+
| Copyright (c) 1998-2001 ILIAS open source, University of Cologne             |
|                                                                                                                         |
| This program is free software; you can redistribute it and/or                         |
| modify it under the terms of the GNU General Public License                      |
| as published by the Free Software Foundation; either version 2                   |
| of the License, or (at your option) any later version.                                     |
|                                                                                                                         |
| This program is distributed in the hope that it will be useful,                          |
| but WITHOUT ANY WARRANTY; without even the implied warranty of          |
| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the  |
| GNU General Public License for more details.                                                |
|                                                                                                                          |
| You should have received a copy of the GNU General Public License            |
| along with this program; if not, write to the Free Software                            |
| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
+------------------------------------------------------------------------------------------+
*/

package ilias.lucene;

import java.io.File;
import java.io.IOException;
import java.util.Date;
import java.util.Enumeration;
import java.util.Hashtable;

import org.apache.log4j.Logger;
import org.apache.lucene.document.Document;
import org.apache.lucene.document.Field;
import org.apache.lucene.index.IndexReader;

import ilias.utils.ilEncodingException;

/**
 * @author Stefan Meyer <smeyer@databay.de>
 * 
 */

public class ilFileIndexer extends ilIndexer {

    private Logger logger = Logger.getLogger(this.getClass().getName());
    private Hashtable files = null;
    private ilExtensionFileHandler fileHandler;
    private long start;
    private long end;

    /**
     * @param indexDirectory
     * @param client
     * @throws Exception
     */
    public ilFileIndexer(File indexDirectory, String client) throws Exception {
        super(indexDirectory, client);

    }
    
    /**
     * @return Returns the files.
     */
    public Hashtable getFiles() {
        return files;
    }
    /**
     * @param files The files to set.
     */
    public void setFiles(Hashtable files) {
        this.files = files;
    }
    
    public boolean indexFiles() {

        this.fileHandler = new ilExtensionFileHandler();
        
        Enumeration e = getFiles().keys();
        this.start = new Date().getTime();
        while(e.hasMoreElements()) {
            String obj_id = (String) e.nextElement();
            String fname = (String) getFiles().get(obj_id);
            try {
                index(fname,obj_id);
            }
            catch(ilFileHandlerException err) {
                logger.info(err.getMessage() + ":" + fname);
            }
        }
        // Close index writer and log index files
        try {
            closeIndexWriter();
            this.end = new Date().getTime();
            logStatistics();
        }
        catch(IOException err) {
            logger.error("Error closing index writer " + e);
        }
        return true;
    }

    /**
     * @param fname
     * @return
     * @throws ilFileHandlerException
     */
    private boolean index(String fname,String obj_id) 
    	throws ilFileHandlerException {
        
        File file = new File(fname);
        
        if(!file.canRead()) {
            throw new ilFileHandlerException("No permission to read file: " + file.getAbsolutePath());
        }
        try {
            logger.debug("File: " + file.getAbsolutePath());
            Document doc  = fileHandler.getDocument(file);
            if(doc != null) {
                logger.info("Added to index: FILENAME: " + file.getName() + " obj_id: " + obj_id);
                doc.add(Field.UnIndexed("obj_id",obj_id));
                doc.add(Field.Keyword("obj_type","file"));
                getWriter().addDocument(doc);
            }
       	}
        catch(IOException e) {
            throw new ilFileHandlerException("Error indexing files: " + e);
        }
        catch(ilFileHandlerException e1) {
            throw new ilFileHandlerException(e1.getMessage());
        }
        return true;
    }
    private void logStatistics()
    throws IOException {
        IndexReader reader = IndexReader.open(getClientIndexDir());
        
        logger.info("Documents indexed: " + reader.numDocs() + " in " + (end - start)/1000 + " seconds.");
        reader.close();
        
        return;
    }

}

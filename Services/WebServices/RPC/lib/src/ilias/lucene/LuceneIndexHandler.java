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

/**
 * @author Stefan Meyer <smeyer@databay.de>
 * 
 */
import java.io.File;
import java.util.Hashtable;

import org.apache.log4j.Logger;

public class LuceneIndexHandler {
    
   	private Logger logger = Logger.getLogger(this.getClass().getName());
    private File indexPath = null;
    
    public LuceneIndexHandler(File indexPath)
    {
        this.indexPath = indexPath;
    }
    // RCP Methods
    public synchronized boolean ilFileIndexer(String client,Hashtable files) {
 
        logger.info("Called Indexer.ilFileIndexer");
        try {
            ilFileIndexer fileIndexer = new ilFileIndexer(indexPath,client);
            fileIndexer.setFiles(files);            
            fileIndexer.indexFiles();
        } 
        catch (Exception e1) {
            logger.error("Error starting file indexer: " + e1);
            return false;
        }
        return true;
    }
    
    public synchronized boolean ilHTLMIndexer(String client,Hashtable baseDirectories) {
        
        logger.info("Called Indexer.ilHTLMIndexer");
        try {
            ilHTLMDirectoryIndexer htlmIndexer = new ilHTLMDirectoryIndexer(indexPath,client);
            htlmIndexer.setBaseDirs(baseDirectories);
            htlmIndexer.start();
        }
        catch(Exception e) {
            logger.error(e.getMessage());
            return false;
        }
        return true;
    }
    
    public synchronized boolean ilClearIndex(String client) {
        
        logger.info("Deleting index for client: " + client);
        
        try {
            ilIndexer indexer = new ilIndexer(indexPath,client,true);
            indexer.closeIndexWriter();
        }
        catch(Exception e) {
            logger.error(e.getMessage());
            return false;
        }
        return true;
    }
    
}
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

import org.apache.log4j.Logger;
import org.apache.lucene.analysis.Analyzer;
import org.apache.lucene.analysis.SimpleAnalyzer;
import org.apache.lucene.analysis.standard.StandardAnalyzer;
import org.apache.lucene.index.IndexWriter;

/**
 * @author Stefan Meyer <smeyer@databay.de>
 * 
 */

public class ilIndexer {
    private Logger logger = Logger.getLogger(this.getClass().getName());
    
    private String client = "";
    private File indexDir = null;
    private File clientIndexDir = null;
    private IndexWriter writer = null;
    private boolean createIndex = true;
    
    public ilIndexer(File indexDirectory, String client)
    	throws Exception {
        this(indexDirectory,client,false);
   	}
    public ilIndexer(File indexDirectory, String client, boolean create)
    	throws Exception {
        
        createIndex = create;
        try {
            this.indexDir = indexDirectory;
            this.client = client;
            initClientDirectory();
            initIndexWriter();
        }
        catch(Exception e) {
            throw e;
        }	
    }

    /**
     * @return Returns the clientIndexDir.
     */
    public File getClientIndexDir() {
        return clientIndexDir;
    }
    /**
     * @param clientIndexDir The clientIndexDir to set.
     */
    public void setClientIndexDir(File clientIndexDir) {
        this.clientIndexDir = clientIndexDir;
    }
    /**
     * @return Returns the indexDir.
     */
    public File getIndexDir() {
        return indexDir;
    }
    /**
     * @param indexDir The indexDir to set.
     */
    public void setIndexDir(File indexDir) {
        this.indexDir = indexDir;
    }
    /**
     * @return Returns the writer.
     */
    public IndexWriter getWriter() {
        return writer;
    }

    private boolean initClientDirectory() 
    throws Exception { 
    
        File clientIndexDir = new File(indexDir,client);
        // Create it if it not exists
        if(!clientIndexDir.exists()) {
            try {
                clientIndexDir.mkdir();
            }
            catch(SecurityException e) {
                logger.error(e.getMessage());
                throw e;
            }
            catch(Exception e) {
                logger.error(e.getMessage());
                throw e;
            }
        }
        this.clientIndexDir = clientIndexDir;
    
        return true;
    }

    private boolean initIndexWriter() 
    throws IOException {
        
        Analyzer analyzer = new StandardAnalyzer();
        try {
            this.writer = new IndexWriter(getClientIndexDir().getAbsolutePath(),analyzer,createIndex);
        }
        catch(IOException e) {
            throw e;
        }

        return true;
    }
    protected boolean closeIndexWriter() 
        throws IOException {
        this.writer.optimize();
        this.writer.close();
       
        return true;
    }
}

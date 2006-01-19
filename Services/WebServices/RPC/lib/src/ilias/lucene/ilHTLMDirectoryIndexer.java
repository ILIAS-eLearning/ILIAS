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
import java.util.Vector;

import org.apache.log4j.Logger;
import org.apache.lucene.document.Field;
import org.apache.lucene.index.IndexReader;

/**
 * @author Stefan Meyer <smeyer@databay.de>
 * 
 */

public class ilHTLMDirectoryIndexer extends ilDirectoryIndexer {

    private Logger logger = Logger.getLogger(this.getClass().getName());
    private Hashtable baseDirs = null;
    private long end;
    private long start;
    /**
     * @param indexPath
     * @param client
     * @throws Exception
     */
    public ilHTLMDirectoryIndexer(File indexPath, String client) 
    	throws Exception {
        super(indexPath, client);
    }

    /**
     * @return Returns the baseDirs.
     */
    public Hashtable getBaseDirs() {
        return baseDirs;
    }
    /**
     * @param baseDirs The baseDirs to set.
     */
    public void setBaseDirs(Hashtable baseDirs) {
        this.baseDirs = baseDirs;
    }
    
    public boolean start() 
    	throws ilFileHandlerException {

        Enumeration e = getBaseDirs().keys();
        this.start = new Date().getTime();
        while(e.hasMoreElements()) {
            String obj_id = (String) e.nextElement();
            String baseDir = (String) getBaseDirs().get(obj_id);
            try {
                prepareFields(obj_id,"htlm");
                index(new File(baseDir));
            }
            catch(ilFileHandlerException err) {
                logger.error("Error indexing htlm directory");
            }
        }
        this.end = new Date().getTime();
        try {
            logger.info("Closing index writer");
            closeIndexWriter();
            logStatistics();
        }
        catch(Exception e1) {
            throw new ilFileHandlerException("Error closing index writer: " + e1);
        }
        
        return true;
    }

    /**
     * @param obj_id
     * @param string
     */
    private void prepareFields(String obj_id, String obj_type) {

        Vector  v = new Vector();
        
        v.add(Field.UnIndexed("obj_id",obj_id));
        v.add(Field.Keyword("obj_type",obj_type));
        super.setFields(v);
        
        return;
    }
    private void logStatistics()
    throws IOException {
        IndexReader reader = IndexReader.open(getClientIndexDir());
        
        logger.info("Documents indexed: " + reader.numDocs() + " in " + (end - start)/1000 + " seconds.");
        reader.close();
        
        return;
    }
    
}

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
import java.util.Enumeration;
import java.util.Vector;

import org.apache.log4j.Logger;
import org.apache.lucene.document.Document;
import org.apache.lucene.document.Field;

/**
 * @author Stefan Meyer <smeyer@databay.de>
 * 
 */

public class ilDirectoryIndexer extends ilIndexer{

   	private Logger logger = Logger.getLogger(this.getClass().getName());

    private String baseDir = "";
    private ilExtensionFileHandler fileHandler = null;
    private Vector fields = null;
    
    public ilDirectoryIndexer(File indexPath,String client)
    throws Exception {
        super(indexPath,client);
        this.fileHandler = new ilExtensionFileHandler();
    }

    /**
     * @return Returns the baseDir.
     */
    public String getBaseDir() {
        return baseDir;
    }
    /**
     * @param baseDir The baseDir to set.
     */
    public void setBaseDir(String baseDir) {
        this.baseDir = baseDir;
    }
    
    protected boolean index(File file)
    	throws ilFileHandlerException {
        
        if(file.canRead()) {
            if(file.isDirectory()) {
                //logger.debug("Directory: " + file.getAbsolutePath());
                String[] files = file.list();
                if(files != null) {
                    for(int i = 0; i < files.length; i++) {
                        //logger.debug("   File = " + files[i]);
                        index(new File(file.getAbsolutePath(),files[i]));
                    }
                }
            }
            else {
                try {
                    Document doc  = fileHandler.getDocument(file);
                    if(doc != null) {
                        logger.info("ADDED " + file.getName());
                        addFields(doc);
                        getWriter().addDocument(doc);
                    }
                }
                catch(Exception e) {
                    throw new ilFileHandlerException(e.getMessage());
                }
            }
        }
        return true;
    }

    /**
     * @param doc
     */
    private void addFields(Document doc) {
        
        for(Enumeration el = fields.elements();el.hasMoreElements();) {
            doc.add((Field) el.nextElement());
        }
        return;
    }
    /**
     * @return Returns the fields.
     */
    public Vector getFields() {
        return fields;
    }
    /**
     * @param v The fields to set.
     */
    public void setFields(Vector v) {
        this.fields = v;
    }
}

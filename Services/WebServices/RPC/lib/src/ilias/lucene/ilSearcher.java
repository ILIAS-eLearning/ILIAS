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
import java.util.Hashtable;
import java.util.Vector;

import org.apache.log4j.Logger;
import org.apache.lucene.analysis.standard.StandardAnalyzer;
import org.apache.lucene.document.Document;
import org.apache.lucene.document.Field;
import org.apache.lucene.index.Term;
import org.apache.lucene.queryParser.QueryParser;
import org.apache.lucene.search.BooleanQuery;
import org.apache.lucene.search.Filter;
import org.apache.lucene.search.Hits;
import org.apache.lucene.search.IndexSearcher;
import org.apache.lucene.search.Query;
import org.apache.lucene.search.QueryFilter;
import org.apache.lucene.search.TermQuery;
import org.apache.lucene.store.Directory;
import org.apache.lucene.store.FSDirectory;

/**
 * @author Stefan Meyer <smeyer@databay.de>
 *  
 */

public class ilSearcher {
    private Logger logger = Logger.getLogger(this.getClass().getName());

    private File indexDir = null;
    private String client = "";
    private File clientIndexDir = null;
    private Vector objectTypeFilter = null;

    public ilSearcher(File indexDir, String client) throws ilSearchException {
        try {
            this.indexDir = indexDir;
            this.client = client;
            initClientDirectory();
        } catch (Exception e) {
            throw new ilSearchException("Error starting ilSearcher: " + e);
        }
    }
    
    public void setObjectTypeFilter(Vector obj_types) {
        objectTypeFilter = obj_types;
    }
    public Vector getObjectTypeFilter() {
        return objectTypeFilter;
    }

    public Hashtable search(String q) throws ilSearchException {
        Hashtable result = new Hashtable();
        Directory fsDir;
        Hits hits = null;
        
        long start = new Date().getTime();
        try {
            fsDir = FSDirectory.getDirectory(getClientIndexDir(), false);
            IndexSearcher indexSearcher = new IndexSearcher(fsDir);
            
            QueryFilter filter = new QueryFilter(new TermQuery(new Term("obj_type","file")));
            
            Query query = QueryParser.parse(q, "content",
                    new StandardAnalyzer());
            logger.info("Query was: " + query);

            hits = indexSearcher.search(query,getFilter());
        } catch (Exception e) {
            throw new ilSearchException("Error parsing query: " + e);
        }

        for (int i = 0; i < hits.length(); i++) {
            try {
                Document doc = hits.doc(i);
                Field f1 = doc.getField("obj_id");
                Field f2 = doc.getField("obj_type");
                
                result.put(f1.stringValue(),f2.stringValue());
                logger.debug("Field: " + f1.stringValue() + " " + f2.stringValue());
            } catch (IOException e1) {
                e1.printStackTrace();
            }

        }
        long end = new Date().getTime();
        logger.info("Search found " + hits.length() + " entries in " + (end -start) + " ms");
        return result;
    }
    
    private Filter getFilter() {
        // Return only obj_type filter in the moment
        if(objectTypeFilter.size() == 0) {
            return null;
        }
        if(objectTypeFilter.size() == 1) {
            return new QueryFilter(new TermQuery(new Term("obj_type",(String) objectTypeFilter.get(0))));
        }
        if(objectTypeFilter.size() > 1) {
            
            BooleanQuery booleanQuery = new BooleanQuery();
            for(int i = 0; i < objectTypeFilter.size();i++) {
                // perform 'or' search
                booleanQuery.add(new TermQuery(new Term("obj_type",(String) objectTypeFilter.get(i))),false,false);
            }
            return new QueryFilter(booleanQuery);
        }
        return null;
    }

    private boolean initClientDirectory() throws ilSearchException {

        File clientIndexDir = new File(indexDir, client);
        // Create it if it not exists
        if (!clientIndexDir.exists()) {
            try {
                clientIndexDir.mkdir();
            } catch (SecurityException e) {
                throw new ilSearchException(
                        "Error initiating client index directory: " + e);
            }
        }
        this.clientIndexDir = clientIndexDir;

        return true;
    }

    /**
     * @return Returns the clientIndexDir.
     */
    public File getClientIndexDir() {
        return clientIndexDir;
    }
}

class ilSearchException extends Exception {
    public ilSearchException(String message) {
        super(message);
    }
}
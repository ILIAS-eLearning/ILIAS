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
import java.util.Hashtable;
import java.util.Vector;

import org.apache.log4j.Logger;

/**
 * @author Stefan Meyer <smeyer@databay.de>
 * 
 */

public class LuceneQueryHandler {
   	private Logger logger = Logger.getLogger(this.getClass().getName());
    private File indexPath = null;
    
    public LuceneQueryHandler(File indexPath)
    {
        this.indexPath = indexPath;
    }
    // RCP Methods
    public Hashtable ilSearch(String client,String query,Vector obj_types)
    {
        logger.info("ObjectTypes" + obj_types.toString());
        Hashtable results = new Hashtable();
        try {
            ilSearcher searcher = new ilSearcher(indexPath,client);
            searcher.setObjectTypeFilter(obj_types);
            
            logger.info("Start searching...");
            results = searcher.search(query);
            
            return results;
        }
        catch(ilSearchException e) {
            logger.error(e.getMessage());
        }
        return results;
    }
    public boolean ilPing() {
        return true;
    }
    

}

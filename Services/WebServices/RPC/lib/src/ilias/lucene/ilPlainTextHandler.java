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

import ilias.utils.ilEncodingTransformer;

import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.util.Date;

import org.apache.log4j.Logger;
import org.apache.lucene.document.Document;
import org.apache.lucene.document.Field;

/**
 * @author Stefan Meyer <smeyer@databay.de>
 * 
 */

public class ilPlainTextHandler implements ilDocumentHandler {
   	private Logger logger = Logger.getLogger(this.getClass().getName());


    /* (non-Javadoc)
     * @see databay.ilias.lucene.ilDocumentHandler#getDocument(java.io.InputStream)
     */
    public Document getDocument(InputStream is)
            throws ilDocumentHandlerException {
        
        is = transformStream(is);
 
        StringBuffer bodyText = new StringBuffer();
        // Get body text
        try {
            long start = new Date().getTime();
          
            BufferedReader br = new BufferedReader(new InputStreamReader(is));
            String line = null;
            
            while((line = br.readLine()) != null) {
                bodyText.append(line);
            }
            
            br.close();
            long end = new Date().getTime();
            logger.info("Reading file took " + (end - start) + " ms");
        }
        catch(IOException e) {
            throw new ilDocumentHandlerException("Cannot read plain text file: " + e); 
        }
        // Return new lucene document, if body isn't empty
        if(!bodyText.equals("")) {
            long start = new Date().getTime();
            Document doc = new Document();
            doc.add(Field.Text("content",bodyText.toString()));
            long end = new Date().getTime();
            logger.info("Adding document took " + (end - start) + " ms");
            
            return doc;
        }
        return null;
    }

    public InputStream transformStream(InputStream is) {

        return ilEncodingTransformer.transform(is);
        
    }
}

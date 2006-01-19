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

import java.io.InputStream;

import org.apache.lucene.document.Document;

/**
 * Creates a lucene document from an input stream
 *  Interface for HTML, PDF, PlainText ... document handlers
 *  * @author Stefan Meyer <smeyer@databay.de>
 * 
 */

public interface ilDocumentHandler {
    Document getDocument(InputStream is)
    throws ilDocumentHandlerException;        
}

class ilDocumentHandlerException extends Exception {
    public ilDocumentHandlerException(String message) {
        super(message);
    }
}

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
import org.apache.lucene.document.Field;
import org.w3c.dom.Element;
import org.w3c.dom.Node;
import org.w3c.dom.NodeList;
import org.w3c.dom.Text;
import org.w3c.tidy.Tidy;

import ilias.utils.ilNullPrintWriter;

/**
 * @author Stefan Meyer <smeyer@databay.de>
 * 
 */

public class ilJTidyHTMLHandler implements ilDocumentHandler {
    
    private Tidy tidy = null;

    /* (non-Javadoc)
     * @see databay.ilias.lucene.ilDocumentHandler#getDocument(java.io.InputStream)
     */
    public Document getDocument(InputStream is)
            throws ilDocumentHandlerException {

        org.apache.lucene.document.Document doc = new Document();
        
        
        try {
            this.initTidy();
            
            // Parse is as DOM
            org.w3c.dom.Document root = this.tidy.parseDOM(is,null);
            Element rawDoc = root.getDocumentElement();
            
            String title = getTitle(rawDoc);
            String body = getBody(rawDoc);
            
            if(title != null && !title.equals("")) {
                doc.add(Field.Text("content",title));
            }
            if(body != null && !body.equals("")) {
                doc.add(Field.Text("content",body));
            }
            return doc;
        }
        catch(Exception e) {
            throw new ilDocumentHandlerException("HTML Document seems to be malformed");
        }
    }
    
    private void initTidy() {

        this.tidy = new Tidy();
        this.tidy.setQuiet(true);
        this.tidy.setShowWarnings(false);

        // Set Tidy's Errout to something like /dev/null
        // I do not want to know anything about malformed HTML
        this.tidy.setErrout(new ilNullPrintWriter(System.err,true));
    }
    
    
    // Get title text of html document
    private String getTitle(Element rawDoc) {
        if(rawDoc == null) {
            return null;
        }
        
        String title = "";
        NodeList children = rawDoc.getElementsByTagName("title");
        if(children.getLength() > 0) {
            Element titleElement = (Element) children.item(0);
            Text text = (Text) titleElement.getFirstChild();
            if(text != null) {
                title = text.getData();
            }
        }
        return title;
    }
    private String getBody(Element rawDoc) {
        if(rawDoc == null) {
            return null;
        }
        String body = "";
        NodeList children = rawDoc.getElementsByTagName("body");
        if(children.getLength() > 0) {
            body = getText(children.item(0));
        }
        return body;
    }
    private String getText(Node node) {
        NodeList children = node.getChildNodes();
        StringBuffer sb = new StringBuffer();
        
        for(int i = 0; i < children.getLength(); i++) {
            Node child = children.item(i);
            switch(child.getNodeType()) {
            	case Node.ELEMENT_NODE:
            	    sb.append(getText(child));
            		sb.append(" ");
            		break;
            	case Node.TEXT_NODE:
            	    sb.append(((Text) child).getData());
            		break;
            }
        }
        return sb.toString();
    }
}

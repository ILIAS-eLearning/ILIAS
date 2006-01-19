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

import java.io.IOException;
import java.io.InputStream;

import org.apache.log4j.Logger;
import org.apache.lucene.document.Document;
import org.apache.lucene.document.Field;
import org.pdfbox.cos.COSDocument;
import org.pdfbox.pdfparser.PDFParser;
import org.pdfbox.pdmodel.PDDocument;
import org.pdfbox.util.PDFTextStripper;

/**
 * @author Stefan Meyer <smeyer@databay.de>
 * 
 */

public class ilPDFBoxPDFHandler implements ilDocumentHandler {
   	private Logger logger = Logger.getLogger(this.getClass().getName());


    public ilPDFBoxPDFHandler() {
    }
    /* (non-Javadoc)
     * @see databay.ilias.lucene.ilDocumentHandler#getDocument(java.io.InputStream)
     */
    public Document getDocument(InputStream is)
            throws ilDocumentHandlerException {
        
        float version = 0;
        COSDocument cosDOC = null;
        
        try {
            cosDOC = parseDocument(is);
            
            version = cosDOC.getVersion();

            if(version >= 1.3f && false) {
                closeCOSDocument(cosDOC);
                throw new ilDocumentHandlerException("Cannot handle version: " + version);
            }
        }
        catch(IOException e) {
            closeCOSDocument(cosDOC);
            throw new ilDocumentHandlerException("Cannot parse PDF document: " + e);
        }
        if(cosDOC.isEncrypted()) {
            closeCOSDocument(cosDOC);
            throw new ilDocumentHandlerException("Cannot parse encrypted PDF docuent");
        }
        // Extract textual content
        String docText = null;
        try {
            PDFTextStripper stripper = new PDFTextStripper();
            docText = stripper.getText(new PDDocument(cosDOC));
        }
        catch(IOException e) {
            closeCOSDocument(cosDOC);
            throw new ilDocumentHandlerException("Cannot strip PDF document" + e);
        }
        Document doc = new Document();
        if(docText != null) {
            doc.add(Field.UnStored("content",docText));
        }
        closeCOSDocument(cosDOC);
        return doc;
    }
    
    private COSDocument parseDocument(InputStream is)
    	throws IOException {
        PDFParser parser = new PDFParser(is);
        parser.parse();
        
        return parser.getDocument();
    }
    private void closeCOSDocument(COSDocument cosDOC) {
        if(cosDOC != null) {
            try {
                cosDOC.close();
            }
            catch(IOException e) {
                logger.error("Cannot close COSDocument");
            }
        }
    }
}

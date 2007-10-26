/*
+-----------------------------------------------------------------------------------------+
| ILIAS open source                                                                       |
+-----------------------------------------------------------------------------------------+
| Copyright (c) 1998-2001 ILIAS open source, University of Cologne                        |
|                                                                                         |
| This program is free software; you can redistribute it and/or                           |
| modify it under the terms of the GNU General Public License                             |
| as published by the Free Software Foundation; either version 2                          |
| of the License, or (at your option) any later version.                                  |
|                                                                                         |
| This program is distributed in the hope that it will be useful,                         |
| but WITHOUT ANY WARRANTY; without even the implied warranty of                          |
| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the                           |
| GNU General Public License for more details.                                            |
|                                                                                         |
| You should have received a copy of the GNU General Public License                       |
| along with this program; if not, write to the Free Software                             |
| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.             |
+-----------------------------------------------------------------------------------------+
*/

package ilias.transformation;

import java.io.ByteArrayInputStream;
import java.io.ByteArrayOutputStream;
import java.io.InputStream;
import java.io.UnsupportedEncodingException;

import javax.xml.transform.Result;
import javax.xml.transform.Source;
import javax.xml.transform.Transformer;
import javax.xml.transform.TransformerException;
import javax.xml.transform.TransformerFactory;
import javax.xml.transform.sax.SAXResult;
import javax.xml.transform.stream.StreamSource;

import org.apache.fop.apps.FOPException;
import org.apache.fop.apps.FOUserAgent;
import org.apache.fop.apps.Fop;
import org.apache.fop.apps.FopFactory;
import org.apache.fop.apps.FormattingResults;
import org.apache.fop.apps.MimeConstants;
import org.apache.fop.apps.PageSequenceResults;
import org.apache.log4j.Level;
import org.apache.log4j.Logger;

public class ilFO2PDF {
    
    private Logger logger = Logger.getLogger(this.getClass().getName());
    private String foString = null;
    private byte[] pdfByteArray = null;

    public ilFO2PDF() {

        
    }
    
    public void transform()
        throws ilTransformerException {
       
        try {
            logger.info("Started transformation. FO -> PDF.");
            FopFactory fopFactory = FopFactory.newInstance();
            FOUserAgent foUserAgent = fopFactory.newFOUserAgent();
//            foUserAgent.setTargetResolution(300);
            ByteArrayOutputStream out = new ByteArrayOutputStream();
            
            Fop fop = fopFactory.newFop(MimeConstants.MIME_PDF, foUserAgent, out);
            
//          Setup JAXP using identity transformer
            TransformerFactory factory = TransformerFactory.newInstance();
            Transformer transformer = factory.newTransformer(); // identity transformer
            
            Source src = new StreamSource(getFoInputStream());
            Result res = new SAXResult(fop.getDefaultHandler());
            
            transformer.transform(src,res);
            
            FormattingResults foResults = fop.getResults();
            java.util.List pageSequences = foResults.getPageSequences();
            for (java.util.Iterator it = pageSequences.iterator(); it.hasNext();) {
                PageSequenceResults pageSequenceResults = (PageSequenceResults)it.next();
                logger.debug("PageSequenze "
                        + (String.valueOf(pageSequenceResults.getID()).length() > 0 
                                ? pageSequenceResults.getID() : "<no id>") 
                        + " generated " + pageSequenceResults.getPageCount() + " pages.");
            }
            logger.info("Generated " + foResults.getPageCount() + " pages in total.");
            
            this.setPdf(out.toByteArray());

        } catch (UnsupportedEncodingException e) {
            throw new ilTransformerException(e);
        } catch (FOPException e) {
            throw new ilTransformerException(e);
        } catch (TransformerException e) {
            throw new ilTransformerException(e);
        }
    }


    /**
     * @return Returns the foString.
     */
    public String getFoString() {
        return foString;
    }
    
    
    /**
     * @param foString The foString to set.
     */
    public void setFoString(String foString) {
        this.foString = foString;
    }

    public byte[] getPdf() {
        return this.pdfByteArray;
    }
    
    public void setPdf(byte[] ba) {
        
        this.pdfByteArray = ba;
    }

    
    private InputStream getFoInputStream() throws UnsupportedEncodingException { 
        
        return new ByteArrayInputStream(getFoString().getBytes("utf8"));
    }
}

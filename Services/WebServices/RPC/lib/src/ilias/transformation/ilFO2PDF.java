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
import java.io.IOException;
import java.io.InputStream;
import java.io.OutputStream;
import java.io.UnsupportedEncodingException;

import org.apache.fop.apps.Driver;
import org.apache.fop.apps.FOPException;
import org.apache.fop.messaging.MessageHandler;
import org.apache.log4j.Logger;
import org.xml.sax.InputSource;

public class ilFO2PDF {
    
    private Logger logger = Logger.getLogger(this.getClass().getName());
    private String foString = null;
    private String pdfString = null;

    public ilFO2PDF() {

        
    }
    
    public void transform()
        throws ilTransformerException {
        

        
        try {
            OutputStream out = new java.io.FileOutputStream("/home/smeyer/1.pdf");
            
            Driver driver = new Driver();
            logger.info("Started driver");
            driver.setOutputStream(out);
            //driver.setLogger((org.apache.avalon.framework.logger.Logger) logger);
            //driver.setLogger(logger);
            //MessageHandler.setScreenLogger((org.apache.avalon.framework.logger.Logger) logger);
            
            driver.setRenderer(Driver.RENDER_PDF);
            driver.setInputSource(new InputSource(getFoInputStream()));
            logger.info("Driver run()");
            driver.run();
            
            setPdfString(out.toString());

        } catch (UnsupportedEncodingException e) {
            throw new ilTransformerException(e);
        } catch (IOException e) {
            throw new ilTransformerException(e);
        } catch (FOPException e) {
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


    /**
     * @return Returns the pdfString.
     */
    public String getPdfString() {
        return pdfString;
        
    }


    /**
     * @param pdfString The pdfString to set.
     */
    public void setPdfString(String pdfString) {
        this.pdfString = pdfString;
    }
    
    
    private InputStream getFoInputStream() throws UnsupportedEncodingException { 
        
        return new ByteArrayInputStream(getFoString().getBytes("utf8"));
    }


}

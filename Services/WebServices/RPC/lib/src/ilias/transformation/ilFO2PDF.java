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
import java.io.IOException;
import java.io.InputStream;
import java.io.UnsupportedEncodingException;

import org.apache.avalon.framework.logger.Log4JLogger;
import org.apache.fop.apps.Driver;
import org.apache.fop.apps.FOPException;
import org.apache.fop.messaging.MessageHandler;
import org.apache.log4j.Logger;
import org.xml.sax.InputSource;

public class ilFO2PDF {
    
    private Logger logger = Logger.getLogger(this.getClass().getName());
    private String foString = null;
    private byte[] pdfByteArray = null;

    public ilFO2PDF() {

        
    }
    
    public void transform()
        throws ilTransformerException {
        

        
        try {
            Log4JLogger fopLogger = new Log4JLogger(logger);
            ByteArrayOutputStream out = new ByteArrayOutputStream();
            
            Driver driver = new Driver();

            logger.info("Started driver");
            driver.setLogger(fopLogger);
            MessageHandler.setScreenLogger(fopLogger);
            driver.setInputSource(new InputSource(getFoInputStream()));
            driver.setRenderer(Driver.RENDER_PDF);
            driver.setOutputStream(out);
            logger.info("Driver run()");
            driver.run();
            
            // Set pdf byte array
            this.setPdf(out.toByteArray());
            //logger.info(getPdfString());

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

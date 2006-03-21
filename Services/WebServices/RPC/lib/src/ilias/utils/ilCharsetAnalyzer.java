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

package ilias.utils;

import ilias.utils.*;
import java.io.BufferedInputStream;
import java.io.ByteArrayInputStream;
import java.io.IOException;
import java.io.InputStream;

import org.apache.log4j.Level;
import org.apache.log4j.Logger;
import org.mozilla.intl.chardet.nsDetector;
import org.mozilla.intl.chardet.nsICharsetDetectionObserver;

import com.ibm.icu.text.CharsetDetector;
import com.ibm.icu.text.CharsetMatch;

/**
 * @author Stefan Meyer <smeyer@databay.de>
 * 
 */

public class ilCharsetAnalyzer {
    
    public static Logger logger = Logger.getLogger("ilCharsetAnalyzer");

    static String CHARSET = "";
    static boolean FOUND = false;

    private nsDetector getDetector() {
        
        nsDetector nsDetector = new nsDetector();
        
        // Set an observer...
        // notify will be called when a matching charset is found
        nsDetector.Init(new nsICharsetDetectionObserver() {
            public void Notify(String charset) {
                ilCharsetAnalyzer.CHARSET = charset;
                ilCharsetAnalyzer.FOUND = true;
            }
        });
        return nsDetector;
    }
    
    public InputStream transformICU(InputStream inputStream) 
        throws ilCharsetAnalyzerException {
        
        
        //logger.setLevel(Level.DEBUG);
        
        BufferedInputStream bufferedStream = new BufferedInputStream(inputStream);
        
        if(bufferedStream.markSupported()) {
            bufferedStream.mark(Integer.MAX_VALUE);
        }
        else {
            logger.info("Mark not supported");
        }
        
        CharsetDetector detector = null;
        CharsetMatch match = null;
        CharsetMatch[] matches = null;
        
        try {

            detector = new CharsetDetector();
            detector.setText(bufferedStream);
            match = detector.detect();
            matches = detector.detectAll();
            
            for(int m = 0;m < matches.length; m++) {
                
                logger.debug("Confidence " + matches[m].getConfidence() + " ,charset: " + matches[m].getName());
            }

            bufferedStream.reset();
            if(match.getConfidence() >= 30) {
                logger.info("Assume encoding : " + match.getName() + ", confidence: " + match.getConfidence());
                return  new ByteArrayInputStream(match.getString().getBytes());
            }
            else {
                logger.info("Cannot read encoding. Assuming utf-8.");
                return bufferedStream;
            }

        } catch (IOException e) {
            throw new ilCharsetAnalyzerException("Error parsing inputStream: " + e);
        }
    }
    
    public InputStream readCharset(InputStream inputStream)
    	throws ilCharsetAnalyzerException {
        
        logger.setLevel(Level.DEBUG);
        
        nsDetector detector = getDetector();
        byte[] buffer = new byte[1024];
        int length = 0;
        boolean done = false;
        boolean isASCII = true;

        BufferedInputStream bufferedStream = new BufferedInputStream(inputStream);
        
        if(bufferedStream.markSupported()) {
            bufferedStream.mark(Integer.MAX_VALUE);
        }
        else {
            logger.info("Mark not supported");
        }
        
        try {
            while((length = bufferedStream.read(buffer,0,buffer.length)) != -1) {
                if(isASCII) {
                    isASCII = detector.isAscii(buffer,length);
                }
                if(!isASCII && !done) {
                    done = detector.DoIt(buffer,length,false);
                }
            }
            detector.DataEnd();
            bufferedStream.reset();
            
            String[] charsets = detector.getProbableCharsets();
            for(int i = 0; i < charsets.length;i++) {
                logger.debug("Found probable charset: " + charsets[i]);
            }
            if(charsets.length >= 1) {
                ilCharsetAnalyzer.CHARSET = charsets[0];
            }
            logger.debug("Found charset: " + ilCharsetAnalyzer.CHARSET);
            return bufferedStream;
        }
        catch(IOException e1) {
            throw new ilCharsetAnalyzerException("Error parsing inputStream: " + e1);
        }
    }

}
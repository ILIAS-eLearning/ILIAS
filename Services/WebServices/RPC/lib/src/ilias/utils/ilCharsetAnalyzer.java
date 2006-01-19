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

import java.io.BufferedInputStream;
import java.io.File;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.IOException;

import org.mozilla.intl.chardet.nsDetector;
import org.mozilla.intl.chardet.nsICharsetDetectionObserver;

/**
 * @author Stefan Meyer <smeyer@databay.de>
 * 
 */

public class ilCharsetAnalyzer {
    
    static String CHARSET = "UTF-8";
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
    
    public String getCharset(File file)
    	throws ilCharsetAnalyzerException {
        
        nsDetector detector = getDetector();
        BufferedInputStream inputStream = null;
        byte[] buffer = new byte[1024];
        int length = 0;
        boolean done = false;
        boolean isASCII = true;
        try {
            inputStream = new BufferedInputStream(new FileInputStream(file));
            while((length = inputStream.read(buffer,0,buffer.length)) != -1) {
                if(isASCII) {
                    isASCII = detector.isAscii(buffer,length);
                }
                if(!isASCII && !done) {
                    done = detector.DoIt(buffer,length,false);
                }
            }
            detector.DataEnd();
        }
        catch(FileNotFoundException e) {
            throw new ilCharsetAnalyzerException("Cannot find file: " + file.getName());
        }
        catch(IOException e1) {
            throw new ilCharsetAnalyzerException("Error parsing file: " + file.getName());
        }
        
        return ilCharsetAnalyzer.CHARSET;
    }

}
class ilCharsetAnalyzerException extends Exception {
    ilCharsetAnalyzerException(String message) {
        super(message);
    }
}

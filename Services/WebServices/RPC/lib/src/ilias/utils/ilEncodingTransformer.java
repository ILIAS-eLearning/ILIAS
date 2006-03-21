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
import java.io.BufferedReader;
import java.io.BufferedWriter;
import java.io.ByteArrayInputStream;
import java.io.ByteArrayOutputStream;
import java.io.File;
import java.io.FileNotFoundException;
import java.io.FileOutputStream;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.io.OutputStreamWriter;
import java.io.UnsupportedEncodingException;

import org.apache.log4j.Logger;

/**
 * @author Stefan Meyer <smeyer@databay.de>
 * 
 */

public class ilEncodingTransformer {

    public static Logger logger = Logger.getLogger("ilEncodingTransformer");

    public static final String DEFAULT_ENCODING = "UTF-8";
    public static final int BUFFER_SIZE = 1024;

   	private static ilCharsetAnalyzer getDefaultAnalyzer() {
        
        return new ilCharsetAnalyzer();
    }
    
    
    public static InputStream transform(InputStream inputStream) {


        try {
            ilCharsetAnalyzer analyzer = ilEncodingTransformer.getDefaultAnalyzer();
            // ICU library detects charset and converts it
            return analyzer.transformICU(inputStream);
        }
        catch(ilCharsetAnalyzerException e) {
            logger.info("Cannot transform file: ERROR " + e.getMessage());
        }
        return inputStream;
            /*
            
            String charset = ilCharsetAnalyzer.CHARSET;

            if(charset.equalsIgnoreCase(ilEncodingTransformer.DEFAULT_ENCODING)) {
                
                logger.info("Ok, encoding is UTF-8");
                return is;
                // only for testing
                //return ilEncodingTransformer.decode(inputStream,charset);
            }
            else if(charset.length() == 0) {

                logger.info("Cannot read charset information. Assuming file is UTF-8");
                return is;
            }
            else {
                return ilEncodingTransformer.decode(is,charset);
            }
        }
        catch(ilCharsetAnalyzerException e) {
            logger.info("Cannot transform file: ERROR " + e.getMessage());
        }
        return inputStream;
        */
    }
    
    private static InputStream decode(InputStream is,String charset) {
        
        BufferedInputStream bis = new BufferedInputStream(is);
        BufferedReader bReader = null;
        BufferedWriter bWriter = null;
        BufferedWriter bWriterDebug = null;
        
        File tmpFile = null;

        bis.mark(Integer.MAX_VALUE);
        
        try {
            tmpFile = File.createTempFile("decoded",null);
            ByteArrayOutputStream byteOutput = new ByteArrayOutputStream();

            bReader = new BufferedReader(new InputStreamReader(bis,charset));
            bWriterDebug = new BufferedWriter(new OutputStreamWriter(new FileOutputStream(tmpFile),ilEncodingTransformer.DEFAULT_ENCODING));
            bWriter = new BufferedWriter(new OutputStreamWriter(byteOutput,ilEncodingTransformer.DEFAULT_ENCODING));
            
            // Convert file
            int length = 0;
            char[] buffer = new char[ilEncodingTransformer.BUFFER_SIZE];
            while((length = bReader.read(buffer,0,ilEncodingTransformer.BUFFER_SIZE)) != -1)
            {
                bWriter.write(buffer,0,length);
                bWriterDebug.write(buffer,0,length);
            }
            
            logger.info("Converted from encoding: " + charset);

            return new ByteArrayInputStream(byteOutput.toByteArray());
        }
        catch (UnsupportedEncodingException e)  {
            logger.error("Unsupported encoding given. Encoding seems to be \"" + charset + "\"" + e.getMessage());
            return bis;
        }
        catch (FileNotFoundException e) {
            logger.error("Cannot create temporary file. Character encoding transformation aborted.");
            return bis;
        } 
        catch (IOException e) {
            logger.error(e.getMessage());
            return bis;
        }
        finally {
            
            try {
                bis.reset();
            }
            catch (IOException e) {
                logger.error(e.getMessage());
            }
        }
    }
    

}
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

import java.io.InputStream;

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
    }
}
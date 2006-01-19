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

import java.io.OutputStream;
import java.io.PrintWriter;

import org.apache.log4j.Logger;

public final class ilNullPrintWriter extends PrintWriter {

    private Logger logger = Logger.getLogger(this.getClass().getName());

    public ilNullPrintWriter(OutputStream arg0, boolean arg1) {
        super(arg0, arg1);
    }

    // Something like /dev/null
    
    public void print(String str) {
        
        this.write(str);
    }
    
    public void println(String str) {
        
        this.write(str);
    }
    
    public void write(String str) {
        
        this.write(str,0,str.length());
    }

    public void write(String str, int offset, int count) {

        logger.debug("Tidy says: " + str.substring(offset,count));
    }
    
}

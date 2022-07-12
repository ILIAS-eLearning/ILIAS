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

package de.ilias.services.transformation;



import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;

public class RPCTransformationHandler {

    protected static Logger logger = LogManager.getLogger(RPCTransformationHandler.class);
	
    public RPCTransformationHandler() {
        

    }
    
    public boolean ping() {
        
        return true;
    }
    
    public byte[] ilFO2PDF(String foString) { 
        
	FO2PDF fo = null;

    	try {
		
		fo = new FO2PDF();
		fo.clearCache();
		fo.setFoString(foString);
		fo.transform();
		
		return fo.getPdf();
		} 
		catch (TransformationException e) {
			
			logger.warn("Transformation failed:" + e);
		}
        return null;
    }
}

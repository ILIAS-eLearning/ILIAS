/*
	+-----------------------------------------------------------------------------+
	| LT4eL - Language Technology for e-Learning                                  |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 2006 LT4eL Consortium                                         |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

package lt4el;

import org.apache.log4j.Logger;

/**
 * @author Stefan Meyer <smeyer@databay.de>
 * @author Alex Killing <alex.killing@gmx.de>
 * 
 */

public class ltServer {
    
    private static Logger logger = Logger.getLogger(ltServer.class);
    
   
    public static void main(String[] args)
	{
	    ltServerSettings settings = null;
	    ltRPCServer server = null;
	    
	    System.out.println("Starting server...");
	    try {
            settings = ltServerSettings.getInstance(args);
        } catch (ltConfigurationException e) {
            System.err.println("Error: " + e);
            System.exit(1);
        }

        try {
            server = new ltRPCServer();
            System.out.println("Waiting for connections ...");
            logger.info("Started");
            server.start();
        }
        catch(RuntimeException e) {
            logger.fatal("Cannot bind to port: " + e.getMessage());
            server.shutdown();
            System.exit(1);
            
        }
	}
}

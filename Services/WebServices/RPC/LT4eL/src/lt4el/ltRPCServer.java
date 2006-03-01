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

/**
 * @author Stefan Meyer <smeyer@databay.de>
 * @author Alex Killing <alex.killing@databay.de>
 * 
 */
package lt4el;

import lt4el.ltDummyHandler;

import org.apache.log4j.Logger;
import org.apache.xmlrpc.WebServer;

public class ltRPCServer {

   	private Logger logger = Logger.getLogger(this.getClass().getName());

    private ltServerSettings settings = null;
    private WebServer server = null;
    
    
    public ltRPCServer()
    {
        this.settings = ltServerSettings.getInstance();
        this.server = new WebServer(settings.getPort(),settings.getHost());
    }
    
    public void start()
    	throws RuntimeException
    {
        addHandlers();
        server.start();
    }
    public void shutdown() {
        server.shutdown();
    }
    // PRIVATE METHODS
    private void addHandlers()
    {
        addDummyHandler();
    }

    private void addDummyHandler()
    {
        this.server.addHandler("Dummy",new ltDummyHandler(settings.getIndexPath()));
        logger.info("Added dummy handler");
        return;
    }
}
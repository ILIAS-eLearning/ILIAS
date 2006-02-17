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

/**
 * @author Stefan Meyer <smeyer@databay.de>
 * 
 */
package ilias;


import ilias.lucene.LuceneIndexHandler;
import ilias.lucene.LuceneQueryHandler;
import ilias.transformation.ilTransformationHandler;

import org.apache.log4j.Logger;
import org.apache.xmlrpc.WebServer;

public class ilRPCServer {

   	private Logger logger = Logger.getLogger(this.getClass().getName());

    private ilServerSettings settings = null;
    private WebServer server = null;
    
    
    public ilRPCServer()
    {
        this.settings = ilServerSettings.getInstance();
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
        addIndexHandler();
        addQueryHandler();
        addTransformationHandler();
    }

    private void addIndexHandler()
    {
        this.server.addHandler("Indexer",new LuceneIndexHandler(settings.getIndexPath()));
        logger.info("Added RPC index handler");
        return;
    }
    private void addQueryHandler()
    {
        server.addHandler("Searcher",new LuceneQueryHandler(settings.getIndexPath()));
        logger.info("Added RPC search handler");
        return;
    }

    private void addTransformationHandler() {

        server.addHandler("Transformer",new ilTransformationHandler());
        logger.info("Added RPC file transformation handler");
    }
}
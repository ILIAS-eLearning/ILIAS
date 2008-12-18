/*
        +-----------------------------------------------------------------------------+
        | ILIAS open source                                                           |
        +-----------------------------------------------------------------------------+
        | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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

package de.ilias.services.lucene.index;

import java.io.IOException;
import java.util.HashMap;

import org.apache.log4j.Logger;
import org.apache.lucene.analysis.standard.StandardAnalyzer;
import org.apache.lucene.index.CorruptIndexException;
import org.apache.lucene.index.IndexWriter;
import org.apache.lucene.store.FSDirectory;

import de.ilias.services.settings.ClientSettings;
import de.ilias.services.settings.ConfigurationException;
import de.ilias.services.settings.LocalSettings;

/**
 * Capsulates the interaction between IndexReader and IndexWriter
 * This class is a singleton for each index path.
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
public class IndexHolder {
	
	protected static Logger logger = Logger.getLogger(IndexHolder.class);
	
	private static HashMap<String, IndexHolder> instances = new HashMap<String, IndexHolder>();
	private ClientSettings settings;
	private IndexWriter writer;
	
	

	/**
	 * @param indexPath
	 * @param indexType
	 * @throws IOException 
	 */
	private IndexHolder(String clientKey) throws IOException {
			try {
				settings = ClientSettings.getInstance(clientKey);
				logger.debug("Adding new separated index for " + LocalSettings.getClientKey());
				
				if(IndexWriter.isLocked(FSDirectory.getDirectory(settings.getIndexPath()))) {
					logger.warn("Index writer is locked. Forcing unlock...");
					IndexWriter.unlock(FSDirectory.getDirectory(settings.getIndexPath()));
				}
				
				writer = new IndexWriter(
						FSDirectory.getDirectory(settings.getIndexPath()),
						new StandardAnalyzer(),
						IndexWriter.MaxFieldLength.UNLIMITED);
			}
			catch (ConfigurationException e) {
				throw new IOException("Caught configuration exception: " + e.getMessage());
			}
			catch(IOException e) {
				throw e;
			}
	}

	/**
	 * 
	 * @param clientKey
	 * @return
	 * @throws IOException
	 */
	public static IndexHolder getInstance(String clientKey) throws 
		IOException { 
		
		String hash = clientKey;
		
		if(instances.containsKey(hash)) {
			return instances.get(hash);
		}
		instances.put(hash,new IndexHolder(clientKey));
		return instances.get(hash);
	}
	
	/**
	 * 
	 * @param indexType
	 * @return
	 * @throws IOException
	 */
	public static IndexHolder getInstance() throws IOException  {
		
		return getInstance(LocalSettings.getClientKey());
	}
	
	/**
	 * @return the writer
	 */
	public IndexWriter getWriter() {
		return writer;
	}

	/**
	 * @param writer the writer to set
	 */
	public void setWriter(IndexWriter writer) {
		this.writer = writer;
	}

	/**
	 * Close writer 
	 */
	public void close() {
		
		try {
			getWriter().close();
		} catch (CorruptIndexException e) {
			logger.fatal("Index corrupted." + e);
		} catch (IOException e) {
			logger.fatal("Error closing writer." + e);
		}
	}

	/* (non-Javadoc)
	 * @see java.lang.Object#finalize()
	 */
	@Override
	protected void finalize() throws Throwable {
		
		try {
			close();
		}
		finally {
			super.finalize();
		}
	}
}

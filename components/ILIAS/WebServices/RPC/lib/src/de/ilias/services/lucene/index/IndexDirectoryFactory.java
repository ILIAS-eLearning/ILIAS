/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package de.ilias.services.lucene.index;

import java.io.File;
import java.io.IOException;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;
import org.apache.lucene.store.FSDirectory;
import org.apache.lucene.store.NIOFSDirectory;

/**
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
public class IndexDirectoryFactory {
	
	protected static Logger logger = LogManager.getLogger(IndexDirectoryFactory.class);
	
	
	/**
	 * Get fs directory
	 * Uses NIOFSDirectory with possible bug under win but better support for 
	 * multi threading.
	 * @return FSDirectory
	 */
	public static FSDirectory getDirectory(File indexPath) throws IOException {
	
		try {
			// think about requirements of a singleton per 
			return NIOFSDirectory.open(indexPath.toPath());
		}
		catch(IOException e) {
			logger.warn("Cannot create path for file: " + indexPath.toString());
			throw e;
		}
	}
	
}

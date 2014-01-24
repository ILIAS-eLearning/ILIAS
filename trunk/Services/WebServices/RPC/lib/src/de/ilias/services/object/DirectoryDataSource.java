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

package de.ilias.services.object;

import java.io.File;
import java.io.FileFilter;
import java.sql.ResultSet;
import java.util.Vector;

import de.ilias.services.lucene.index.CommandQueueElement;
import de.ilias.services.lucene.index.DocumentHandlerException;
import de.ilias.services.lucene.index.file.ExtensionFileHandler;
import de.ilias.services.lucene.index.file.FileHandlerException;
import de.ilias.services.lucene.index.file.path.PathCreatorException;

/**
 * 
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
public class DirectoryDataSource extends FileDataSource {

	/**
	 * @param type
	 */
	public DirectoryDataSource(int type) {
		super(type);
		
	}

	
	/**
	 * write Document
	 */
	public void writeDocument(CommandQueueElement el, ResultSet res) 
		throws DocumentHandlerException {
		
		File start;
		ExtensionFileHandler handler = new ExtensionFileHandler();
		StringBuilder content = new StringBuilder();
		
		Vector<File> files;

		logger.info("Start scanning directory...");
		
		try {
			if(getPathCreator() == null) {
				logger.info("No path creator defined");
				return;
			}
			start = getPathCreator().buildFile(el,res);

			FileReader reader = new FileReader();
			reader.traverse(start);
			files = reader.getFiles();
			
			logger.info("Found " + files.size() + " new files.");
			
			for(int i = 0; i < files.size(); i++) {
				// Analyze encoding (transfer encoding), parse file extension
				// and finally read content
				try {
					content.append(" " + handler.getContent(files.get(i)));
				} 
				catch (FileHandlerException e) {
					logger.warn("Cannot parse file " + files.get(i).getAbsolutePath());
				}
			}
			
			
			// Write content
			for(Object field : getFields()) {
				((FieldDefinition) field).writeDocument(content.toString());
			}
			logger.debug("Content is : " + content.toString());
		}
		catch (PathCreatorException e) {
			throw new DocumentHandlerException(e);
		}
	}
	
	/**
	 * Read all files in a directory 
	 */
	class FileReader
	{
		Vector<File> files = new Vector<File>();
		
		public Vector<File> getFiles() {
			return files;
		}
		
		public void traverse(File dir) {
			
			File[] entries = dir.listFiles(
					new FileFilter()
					{
						public boolean accept(File path) {
							
							if(path.isDirectory()) {
								if(!path.getName().equals(".svn")) {
									return true;
								}
								return false;
							}
							else
							{
								//getCandidates().add(path);
								files.add(path);
								return false;
							}
						}
					});
			
			for(int i = 0; i < entries.length; i++) {
				// there are only directories
				traverse(entries[i]);
			}
		}
	}
}

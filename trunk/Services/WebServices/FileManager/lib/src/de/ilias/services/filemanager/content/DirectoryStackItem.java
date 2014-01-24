/*
 * Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE
 */
package de.ilias.services.filemanager.content;

import java.io.File;
import java.util.logging.Logger;

/**
 * Class DirectoryStackItem
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
public class DirectoryStackItem {
	
	private static final Logger logger = Logger.getLogger(DirectoryStackItem.class.getName());
	
	public static final int TYPE_FILE = 1;
	public static final int TYPE_ID = 2;
	public static final int TYPE_ROOTS = 3;
	
	private int type;
	private int id;
	private File file;
	
	/**
	 * Set type
	 * @param type 
	 */
	public void setType(int type) {
		this.type = type;
	}
	
	/**
	 * get type
	 * @return 
	 */
	public int getType() {
		return this.type;
	}
	
	/**
	 * set id
	 * @param id
	 * @return 
	 */
	public void setId(int id) {
		this.id = id;
	}
	
	/**
	 * get id
	 * @return 
	 */
	public int getId() {
		return this.id;
	}
	
	/**
	 * set file
	 * @param file 
	 */
	public void setFile(File file) {
		logger.info(new StringBuffer("New directory stack item: ").append(file.getAbsolutePath()).toString());
		this.file = file;
	}
	
	/**
	 * get file
	 * @return 
	 */
	public File getFile() {
		return this.file;
	}
}
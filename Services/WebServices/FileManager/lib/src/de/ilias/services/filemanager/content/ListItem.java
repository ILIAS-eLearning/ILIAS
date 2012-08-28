/*
 * Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE
 */
package de.ilias.services.filemanager.content;

import de.ilias.services.filemanager.soap.api.SoapClientObject;
import java.text.DecimalFormat;
import java.util.ArrayList;
import java.util.Date;
import java.util.List;
import java.util.logging.Logger;

/**
 * Class ListItem
 * Base class for remote and local list items
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
public abstract class ListItem implements Comparable<ListItem> {
	
	private static final Logger logger = Logger.getLogger(ListItem.class.getName());
	
	
	private ListItem parent = null;
	private boolean isUpperLink  = false;
	
	private int index = 0;
	private String filePath = "";
	private int refId = 0;
	private int objId = 0;
	private String title = "";
	private String type = "";
	private String fileType = "";
	private String description = "";
	private long sizeBit = 0;
	private Date lastUpdate;
	private String permissions = "";
	private boolean isContainer = false;
	private boolean isReadable = false;
	private boolean isWritable = false;
	private boolean isDeletable = false;
	private boolean allowedCourse = false;
	private boolean allowedFolder = false;
	private boolean allowedGroup = false;
	private boolean allowedCategory = false;
	
	private List<ListItemProperty> properties = new ArrayList<ListItemProperty>();
	
	
	/**
	 * Get parent list item
	 * @param parent
	 * @return 
	 */
	public void setParent(ListItem parent) {
		this.parent = parent;
	}
	
	/**
	 * Get parent
	 * @return 
	 */
	public ListItem getParent() {
		return this.parent;
	}
	
	public boolean isUpperLink() {
		return this.isUpperLink;
	}
	
	public void setUpperLink(boolean stat) {
		this.isUpperLink = stat;
	}
	
	/**
	 * Compare objects (for sorting)
	 * @param ListItem
	 * @return 
	 */
	public int compareTo(ListItem other) {
		
		// .. at the beginning
		if(getTitle().equals("..")) {
			return -1;
		}
		// Container first
		if(isContainer()) {
			if(other.isContainer) {
				return getTitle().compareToIgnoreCase(other.getTitle());
			}
			else {
				return -1;
			}
		}
		else if(other.isContainer) {
			return 1;
		}
		// no container => compare lexically
		return getTitle().compareToIgnoreCase(other.getTitle());
	}
	
	/**
	 * Set index number
	 * @param index 
	 */
	public void setIndex(int index) {
		this.index = index;
	}
	
	
	/**
	 * get absloute path of file
	 * @return 
	 */
	public String getAbsolutePath() {
		return filePath;
	}
	
	/**
	 * Set absolute path
	 * @param path 
	 */
	public void setAbsolutePath(String path) {
		this.filePath = path;
	}
	
	/**
	 * Get ref id
	 * @return 
	 */
	public int getRefId() {
		return this.refId;
	}
	
	/**
	 * Set ref id
	 * @param refId 
	 */
	public void setRefId(int refId) {
		this.refId = refId;
	}
	
	/**
	 * Get obj id
	 * @return 
	 */
	public int getObjId() {
		return this.objId;
	}
	
	/**
	 * Set obj id
	 * @param id 
	 */
	public void setObjId(int id) {
		this.objId = id;
	}
	

	
	/**
	 * set title
	 * @param title 
	 */
	public void setTitle(String title) {
		this.title = title;
	}
	
	/**
	 * get title
	 * @return 
	 */
	public String getTitle() {
		return this.title;
	}
	
	/**
	 * set type
	 * @param type
	 * @return 
	 */
	public void setType(String type) {
		this.type = type;
	}
	
	/**
	 * get type
	 * @return 
	 */
	public String getType() {
		return this.type;
	}
	
	/**
	 * get file type
	 * @return 
	 */
	public String getFileType() {
		
		if(this.fileType.length() > 0) {
			return this.fileType;
		}
		return "    ";
	}
	
	/**
	 * Set file type
	 * @param type 
	 */
	public void setFileType(String type) {
		this.fileType = type;
	}
	
	/**
	 * Set description
	 * @param des 
	 */
	public void setDescription(String des) {
		this.description = des;
	}
	
	/**
	 * get description
	 * @return 
	 */
	public String getDescription() {
		
		if(description == null) {
			return this.description = "";
		}
		return this.description;
	}
	
	/**
	 * Set size
	 * @param size 
	 */
	public void setFileSize(long size) {
		this.sizeBit = size;
	}
	
	/**
	 * get file size
	 * @return 
	 */
	public long getFileSize() {
		return this.sizeBit;
	}
	
	/**
	 * Get readable file size
	 * @return 
	 */
	public String getReadableFileSize() {
		
		if(getFileSize() <= 0) 
			return "";
		
		final String[] units = new String[] { "B", "KB", "MB", "GB", "TB" };
		int digitGroups = (int) (Math.log10(getFileSize())/Math.log10(1024));
		
		return new DecimalFormat("#,##0.#").format(getFileSize()/Math.pow(1024, digitGroups)) + " " + units[digitGroups];
	}

	
	/**
	 * set date of last modification
	 * @param last 
	 */
	public void setLastUpdate(Date last) {
		this.lastUpdate = last;
	}
	
	/**
	 * get last update
	 * @return 
	 */
	public Date getLastUpdate() {
		return this.lastUpdate;
	}
	
	/**
	 * Set permission info string
	 * @param perms 
	 */
	public void setPermissions(String perms) {
		this.permissions = perms;
	}
	
	/**
	 * get permissions
	 * @return 
	 */
	public String getPermissions() {
		
		if(this.permissions.length() == 0) {
			return "  ";
		}
		return this.permissions;
	}
	
	
	/**
	 * Set container
	 * @param container 
	 */
	public void setContainer(boolean container) {
		this.isContainer = container;
	}
	
	/**
	 * Check if item is container
	 * @return 
	 */
	public boolean isContainer() {
		return isContainer;
	}
	
	/**
	 * Check if copy to clipboard is supported
	 * @return 
	 */
	public boolean supportsCopyToClipboard() {

		if(getType().equals("crs")) {
			return false;
		}
		if(getType().equals("grp")) {
			return false;
		}
		return true;
	}
	
	/**
	 * Set readable
	 * @param stat 
	 */
	public void setReadable(boolean stat) {
		this.isReadable = stat;
	}
	
	/**
	 * Check if object is readable
	 * @return 
	 */
	public boolean isReadable() {
		return isReadable;
	}
	
	public void setWritable(boolean write) {
		this.isWritable = write;
	}
	
	public boolean isWritable() {
		return isWritable;
	}
	
	public void setDeletable(boolean stat) {
		this.isDeletable = stat;
	}
	
	public boolean isDeletable() {
		return isDeletable;
	}
	
	public void setCourseAllowed(boolean stat) {
		this.allowedCourse = stat;
	}
	public void setFolderAllowed(boolean stat) {
		this.allowedFolder = stat;
	}
	public void setGroupAllowed(boolean stat) {
		this.allowedGroup = stat;
	}
	public void setCategoryAllowed(boolean stat) {
		this.allowedCategory = stat;
	}
	
	public boolean isCourseAllowed() {
		return this.allowedCourse;
	}
	public boolean isFolderAllowed() {
		return this.allowedFolder;
	}
	public boolean isGroupAllowed() {
		return this.allowedGroup;
	}
	public boolean isCategoryAllowed() {
		return this.allowedCategory;
	}
	
	public boolean isPasteAllowed() {
		
		if(this.isContainer() && this.isWritable()) {
			return true;
		}
		
		if(this.getType().equalsIgnoreCase("file") && this.isWritable()) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Get properties
	 * @return 
	 */
	public List<ListItemProperty> getProperties() {
		return this.properties;
	}
}


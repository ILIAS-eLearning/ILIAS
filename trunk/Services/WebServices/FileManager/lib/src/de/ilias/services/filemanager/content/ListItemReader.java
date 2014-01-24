/*
 * Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE
 */
package de.ilias.services.filemanager.content;

import de.ilias.services.filemanager.FileManager;
import de.ilias.services.filemanager.controller.MainController;
import de.ilias.services.filemanager.soap.SoapClientConnector;
import de.ilias.services.filemanager.soap.SoapClientConnectorException;
import de.ilias.services.filemanager.soap.api.*;
import de.ilias.services.filemanager.utils.FileManagerUtils;
import java.io.File;
import java.util.ArrayList;
import java.util.Date;
import java.util.Iterator;
import java.util.logging.Logger;

/**
 * Class ListItemReader
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
public class ListItemReader {
	
	private static final Logger logger = Logger.getLogger(ListItemReader.class.getName());
	
	private int source = 0;
	private ListItem parent = null;
	private ArrayList<ListItem> items = new ArrayList<ListItem>();
	
	private SoapClientObjects objs;
	
	
	/**
	 * Get list items
	 * @return 
	 */
	public ArrayList<ListItem> getListItems() {
		
		return items;
	}
	
	/**
	 * Read items
	 * @param sourceType 
	 */
	public void read(int sourceType) throws SoapClientConnectorException {
		
		this.source = sourceType;
		if(source == ContentFrame.FRAME_RIGHT) {
			readRemote();
		}
		else {
			readLocal();
		}
	}
	
	/**
	 * Set objects
	 * @param objs 
	 */
	public void setObjects(SoapClientObjects objs) {
		this.objs = objs;
	}
	
	/**
	 * Get objects
	 * @return 
	 */
	public SoapClientObjects getObjects() {
		return this.objs;
	}
	
	
	/**
	 * Read remote list items
	 */
	private void readRemote() throws SoapClientConnectorException {
		
		int id;
		
		ContentFrameDirectoryStack idStack = ContentFrameDirectoryStack.getInstance();
		id = idStack.getRemoteStack().peek().getId();
		
		readRemoteContainer(id,true);
	}
	
	/**
	 * Read local list items
	 */
	private void readLocal() {
		
		// Read from stack
		File dir;
		ContentFrameDirectoryStack dirStack = ContentFrameDirectoryStack.getInstance();
		DirectoryStackItem dirStackItem = dirStack.getLocalStack().peek();

		if(dirStackItem.getType() == DirectoryStackItem.TYPE_FILE) {
			logger.info("Reading local files");
			readLocalDir(dirStackItem.getFile());
		}
		else {
			logger.info("Reading local roots");
			readLocalRoots();
		}
	}
	
	/**
	 * Read and parse local dir
	 * @param dir 
	 */
	private void readLocalDir(File dir) {
		
		logger.info(new StringBuffer("Current directory is ").append(dir.getAbsolutePath()).toString());
		File[] files = dir.listFiles();

		if(files == null) {
			return;
		}
		
		// Parent of list item
		this.parent = new LocalListItem();
		this.parent.setAbsolutePath(dir.getAbsolutePath());
		this.parent.setTitle("..");
		this.parent.setFileSize(0);
		this.parent.setType("cat");
		this.parent.setReadable(dir.canRead());
		this.parent.setWritable(dir.canWrite());
		this.parent.setContainer(true);
				
		File parent = dir.getParentFile();
		
		// Add .. link
		if(parent != null) {
			
			ListItem listItem = new LocalListItem();
			listItem.setParent(this.parent);
			listItem.setAbsolutePath(parent.getAbsolutePath());
			listItem.setTitle("..");
			listItem.setFileSize(0);
			listItem.setType("cat");
			listItem.setReadable(parent.canRead());
			listItem.setWritable(parent.canWrite());
			listItem.setContainer(true);
			
			this.getListItems().add(listItem);
		}
		else {
			// no parent: for windows check if current directory is available
			// in list roots.
			// If yes, show upper link to list roots
			// If no, show contents of list roots
			if(isARoot(dir)) {
				ListItem listItem = new LocalListItem();
				listItem.setAbsolutePath(null);
				logger.info(new StringBuffer("Upper directory is ").append(listItem.getAbsolutePath()).toString());
				listItem.setTitle("..");
				listItem.setFileSize(0);
				listItem.setType("cat");
				listItem.setReadable(true);
				listItem.setWritable(false);
				listItem.setContainer(true);
				
				this.getListItems().add(listItem);
			}
		}
		this.parseLocalFiles(files, false);
	}
	
	/**
	 * Parse local files
	 * @param files 
	 */
	private void parseLocalFiles(File[] files, boolean absoluteName) {

		for(int i = 0; i < files.length; i++) {
			
			String fileName;
			
			if(absoluteName) {
				fileName = files[i].toString();
			}
			else {
				fileName = files[i].getName();
			}
			
			logger.finer(fileName);
			
			// Hide hidden files
			if(files[i].isHidden() && !isARoot(files[i])) {
				logger.finer("...is Hidden");
				continue;
			}
			// Hide dot-files
			if(fileName.substring(0,1).compareTo(".") == 0) {
				logger.finer("...is dot");
				continue;
			}
			
			ListItem listItem = new LocalListItem();
			listItem.setParent(this.parent);
			listItem.setAbsolutePath(files[i].getAbsolutePath());
			listItem.setTitle(fileName);
			listItem.setFileSize(files[i].length());
			listItem.setLastUpdate(new Date(files[i].lastModified()));
			
			int dot = files[i].getName().lastIndexOf(".");
			if(dot > 0)
				listItem.setFileType(files[i].getName().substring(dot + 1));
			
			if(files[i].isDirectory()) {
				listItem.setType("cat");
			}
			else {
				listItem.setType("file");
			}
			if(files[i].canRead() && files[i].canWrite()) {
				listItem.setPermissions("RW");
			}
			else if(files[i].canRead()) {
				listItem.setPermissions("R_");
			}
			else if(files[i].canWrite()) {
				listItem.setPermissions("W_");
			}
			
			listItem.setReadable(files[i].canRead());
			listItem.setWritable(files[i].canWrite());
			listItem.setContainer(files[i].isDirectory());
			
			this.getListItems().add(listItem);
		}
	}

	/**
	 * Read local roots
	 */
	private void readLocalRoots() {
		
		File[] roots = File.listRoots();
		this.parseLocalFiles(roots, true);
	}
	
	/**
	 * Check if directory is element of list roots
	 * @param dir
	 * @return 
	 */
	private boolean isARoot(File dir) {
		
		File[] roots = File.listRoots();
		
		for(int i = 0; i < roots.length; i++) {
			if(roots[i].getAbsolutePath().equals(dir.getAbsolutePath())) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Read remote container
	 * @param refId 
	 */
	public void readRemoteContainer(int refId, boolean withParent) throws SoapClientConnectorException {
		
		SoapClientObjects objects;
		boolean emptyContainer = false;
		
		if(this.getObjects() == null) {
			SoapClientConnector soap = SoapClientConnector.getInstance();
			objects = soap.getTreeChilds(refId);
			this.setObjects(objects);
		}
		else {
			objects = this.getObjects();
		}
		
		ListItem parent, container;
		parent = objects.getParentListItem();
		container = null;

		if(emptyContainer) {
			return;
		}
		
		Iterator objectIte = objects.getObjects().iterator();
		boolean first = true;
		while(objectIte.hasNext()) {
			
			SoapClientObject object = (SoapClientObject) objectIte.next();
			ListItem listItem = new RemoteListItem();
			
			try {			
				listItem.setFileSize(Long.parseLong(object.getPropertyByName(SoapClientObject.PROP_FILESIZE)));
			}
			catch(NumberFormatException e) {
				listItem.setFileSize(0);
			}
			listItem.setDescription(object.getDescription());
			listItem.setRefId(object.getRefId());
			listItem.setObjId(object.getObjId());
			listItem.setType(object.getType());
			listItem.setFileType(object.getPropertyByName(SoapClientObject.PROP_FILE_EXTENSION));
			
			/**
			 * Add version property
			 */
			if(object.getPropertyByName(SoapClientObject.PROP_FILE_VERSION).length() > 0) {
				ListItemProperty prop = new ListItemProperty();
				prop.setName(SoapClientObject.PROP_FILE_VERSION);
				prop.setValue(object.getPropertyByName(SoapClientObject.PROP_FILE_VERSION));
				listItem.getProperties().add(prop);
			}
			
			/*
			 * Add info property
			 */
			for(SoapClientObjectProperty objProp : object.getPropertiesByName(SoapClientObject.PROP_INFO)) {
				ListItemProperty prop = new ListItemProperty();
				prop.setName(SoapClientObject.PROP_INFO);
				prop.setValue(objProp.getValue());
				listItem.getProperties().add(prop);
			}
			
			listItem.setLastUpdate(object.getLastUpdate());
			
			// Itereate through object references -> operations
			Iterator referenceIterator = object.getReferences().iterator();
			while(referenceIterator.hasNext()) {
				SoapClientObjectReference references = (SoapClientObjectReference) referenceIterator.next();
				Iterator operationIterator = references.getOperations().iterator();
				while(operationIterator.hasNext()) {
					String operation = (String) operationIterator.next();
					if(operation.equalsIgnoreCase("write")) {
						listItem.setWritable(true);
					}
					if(operation.equalsIgnoreCase("read")) {
						listItem.setReadable(true);
					}
					if(operation.equalsIgnoreCase("delete")) {
						listItem.setDeletable(true);
					}
					if(operation.equalsIgnoreCase("create_cat")) {
						listItem.setCategoryAllowed(true);
					}
					if(operation.equalsIgnoreCase("create_crs")) {
						listItem.setCourseAllowed(true);
					}
					if(operation.equalsIgnoreCase("create_fold")) {
						listItem.setFolderAllowed(true);
					}
					if(operation.equalsIgnoreCase("create_grp")) {
						listItem.setGroupAllowed(true);
					}
				}
			}
			
			// Overwrite readable if file is locked.
			if(FileManager.getInstance().enabledFileLocks()) {
				if(listItem.getType().equalsIgnoreCase("file")) {
					if(!object.getPropertyByName(SoapClientObject.PROP_FILE_ACCESS).equalsIgnoreCase("1")) {
						listItem.setReadable(false);
					}
				}
			}
			
			listItem.setContainer(object.isContainer());
			
			if(first) {
				listItem.setTitle("..");
				listItem.setDescription("");
				listItem.setUpperLink(true);
				listItem.setParent(parent);
				parent = listItem;
				container = listItem;
				
				first = false;
			}
			else {
				listItem.setParent(container);
				listItem.setTitle(object.getTitle());
			}
			
			// Show upper link if list item is not root
			// Hide upper link if called not with param "withParent"
			if(listItem.getRefId() != FileManagerUtils.ROOT_FOLDER_ID) {
				if(!listItem.isUpperLink() || withParent) {
					this.getListItems().add(listItem);
				}
			}
		}
	}
	
}

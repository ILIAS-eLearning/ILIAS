/*
 * Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE
 */
package de.ilias.services.filemanager.soap.api;

import de.ilias.services.filemanager.content.ListItem;
import de.ilias.services.filemanager.content.RemoteListItem;
import de.ilias.services.filemanager.utils.FileManagerUtils;
import java.io.File;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.Iterator;
import java.util.List;
import java.util.logging.Logger;
import javafx.scene.layout.HBox;
import javax.xml.bind.annotation.XmlElement;
import javax.xml.bind.annotation.XmlRootElement;
import javax.xml.bind.annotation.XmlTransient;

/**
 * Class SoapClientObjects
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
@XmlRootElement( name = "Objects")
public class SoapClientObjects {
	
	@XmlTransient
	private static Logger logger = Logger.getLogger(SoapClientObjects.class.getName());
	
	
	private List<SoapClientObject> objects;
	
	/**
	 * get objects
	 * @return 
	 */
	@XmlElement( name = "Object")
	public List<SoapClientObject> getObjects() {
		if(objects == null) {
			return objects = new ArrayList<SoapClientObject>();
		}
		return objects;
	}
	
	/**
	 * Get list of path elements 
	 * 
	 * @param includeLeaf
	 * @return 
	 */
	public List<SoapClientObjectReferencePath> getFirstPath(boolean includeLeaf) {
		
		List<SoapClientObjectReferencePath> pathList = new ArrayList<SoapClientObjectReferencePath>();
		
		// choose first object
		for(SoapClientObject obj : getObjects()) {
			// choose first reference
			for(SoapClientObjectReference ref : obj.getReferences()) {
				pathList = ref.getPathElements();
				break;
			}
			break;
		}
		
		// Append a leaf the leaf node as path element if desired
		if(includeLeaf) {
			
			for(SoapClientObject obj : getObjects()) {
				for(SoapClientObjectReference ref : obj.getReferences()) {
					SoapClientObjectReferencePath leaf = new SoapClientObjectReferencePath();
					
					if(pathList.size() < 1) {
						leaf.setTitle("Repository");
					}
					else {
						leaf.setTitle(obj.getTitle());
					}
					leaf.setRefId(ref.getRefId());
					leaf.setType(obj.getType());
					pathList.add(leaf);
					break;
				}
				break;
			}
		}
		return pathList;
	}
	
	
	public ListItem getParentListItem() {
		
		int minElements = 1;
		
		Iterator objectIterator = getObjects().iterator();
		while(objectIterator.hasNext()) {
			SoapClientObject obj = (SoapClientObject) objectIterator.next();
			Iterator refIterator = obj.getReferences().iterator();
			while(refIterator.hasNext()) {
				SoapClientObjectReference ref = (SoapClientObjectReference) refIterator.next();
				ArrayList<SoapClientObjectReferencePath> pathElements = (ArrayList<SoapClientObjectReferencePath>) ref.getPathElements();

				if(pathElements.size() >= minElements) {
					
					SoapClientObjectReferencePath parent = pathElements.get(pathElements.size() - 1);
					RemoteListItem parentItem = new RemoteListItem();
					parentItem.setRefId(parent.getRefId());
					parentItem.setType(parent.getType());
					parentItem.setTitle(parent.getTitle());
					return parentItem;
				}
			}
		}
		return null;
	}

	public List<File> checkNamingConflicts(List<File> files) {
		
		ArrayList<File> conflicted = new ArrayList<File>();
		
		for(File file : files) {
			for(SoapClientObject obj : getObjects()) {
				if(
						file.getName().equalsIgnoreCase(obj.getTitle()) && 
						(obj.getType().equals("file") || obj.getType().equals("fold") || obj.getType().equals("cat")) &&
						obj.isWritable()) {
					logger.info("File names are equal!");
					conflicted.add(file);
				}
			}
		}
		return conflicted;
	}
	
	/**
	 * Check for a naming conflict e.g. when pasting files from clipboard
	 * @param files
	 * @return 
	 */
	public HashMap<File,SoapClientObject> checkNamingConflict(List<File> files) {
		
		HashMap<File,SoapClientObject> conflict = new HashMap<File, SoapClientObject>();
		
		for(File file : files) {
			for(SoapClientObject obj : getObjects()) {
				if(
						file.getName().equalsIgnoreCase(obj.getTitle()) && 
						(obj.getType().equals("file") || obj.getType().equals("fold") || obj.getType().equals("cat")) &&
						obj.isWritable()) {
					logger.info("File names are equal!");
					conflict.put(file, obj);
				}
			}
		}
		return conflict;
	}
	
	/**
	 * Create a unique file name
	 * @param file
	 * @return 
	 */
	public String createUniqueName(File file) {

		String newName;
		
		for(int i = 2; i < 50; i++) {
			
			newName = FileManagerUtils.increaseVersionName(file.getName(), i);
			
			// Check if file exists
			boolean exists = false;
			for(SoapClientObject obj : getObjects()) {
				
				if(obj.getTitle().equalsIgnoreCase(newName)) {
					exists = true;
				}
			}
			
			if(!exists) {
				logger.info("Using new name " + newName);
				return newName;
			}
		}
		return file.getName();
	}
}

/*
 * Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE
 */
package de.ilias.services.filemanager.action;

import de.ilias.services.filemanager.FileManager;
import de.ilias.services.filemanager.content.*;
import de.ilias.services.filemanager.controller.MainController;
import de.ilias.services.filemanager.dialog.PasteConflictDialog;
import de.ilias.services.filemanager.dialog.UploadLimitConflictDialog;
import de.ilias.services.filemanager.soap.SoapClientConnector;
import de.ilias.services.filemanager.soap.SoapClientConnectorException;
import de.ilias.services.filemanager.soap.api.SoapClientFile;
import de.ilias.services.filemanager.soap.api.SoapClientObject;
import de.ilias.services.filemanager.soap.api.SoapClientObjects;
import de.ilias.services.filemanager.utils.FileManagerUtils;
import java.io.File;
import java.io.FileNotFoundException;
import java.io.IOException;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.Iterator;
import java.util.List;
import java.util.Vector;
import java.util.logging.Logger;
import javafx.scene.input.Clipboard;
import javafx.scene.input.ClipboardContent;


/**
 * Class ActionHandler
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
public class ActionHandler {
	
	
	protected static final Logger logger = Logger.getLogger(ActionHandler.class.getName());
	
	protected static ArrayList<File> errorFiles = new ArrayList<File>();
	
	
	/**
	 * Delete selected items
	 * @param selectItems
	 * @return 
	 */
	public static boolean deleteItems(ArrayList<ListItem> selectItems) {
		
		Iterator selectedIterator = selectItems.iterator();
		while(selectedIterator.hasNext()) {
			
			ListItem item = (ListItem) selectedIterator.next();
			if(item instanceof LocalListItem) {
				deleteLocalFiles(new File(item.getAbsolutePath()));
			}
			if(item instanceof RemoteListItem) {
				deleteRemoteFiles(item.getRefId());
			}
		}
		return true;
	}
	
	/**
	 * Delete file
	 * @param source 
	 */
	public static void deleteLocalFiles(File source) {
		
		logger.info(source.getAbsolutePath());
		
		if(source.getAbsolutePath().length() > 1000)
		{
			return;
		}
		
		File[] files = source.listFiles();
		if(files != null) {
			for(int i = 0; i < files.length; i++) {

				if(files[i].isDirectory()) {
					deleteLocalFiles(files[i]);
				}
				else {
					files[i].delete();
				}
			}
		}
		// Delete source object
		source.delete();
	}
	
	/**
	 * Delete remote object
	 * @param refId 
	 */
	public static void deleteRemoteFiles(int refId) {
		
		logger.info("Deleting ref id: " + refId);
		if(refId > 1) {
			SoapClientConnector con = SoapClientConnector.getInstance(SoapClientConnector.FRAME_RIGHT);
			con.deleteObject(refId);
		}
	}
	
	/**
	 * Copy list items to clipboard
	 * @param source
	 * @param items 
	 */
	public static void copyRemoteToClipboard(ListItem source, ArrayList<ListItem> items) {
		
		// Filter groups and courses, since these items cannot be copied
		ArrayList<ListItem> filteredItems = new ArrayList<ListItem>();
		for(ListItem item : items) {
			if(item.supportsCopyToClipboard()) {
				filteredItems.add(item);
			}
		}
		
		if(filteredItems.isEmpty()) {
			return;
		}
		
		File tmpDir = FileManagerUtils.createTempDirectory("ilfm_");
		tmpDir.deleteOnExit();
		
		File newTempFile;
		
		Clipboard clip = Clipboard.getSystemClipboard();
		ClipboardContent clipContent = new ClipboardContent();
		ArrayList<File> files = new ArrayList<File>();
		
		Iterator itemsIte = filteredItems.iterator();
		while(itemsIte.hasNext()) {
			ListItem item = (ListItem) itemsIte.next();
			
			// Copy file and/or folders to temp directory
			newTempFile = copyRemoteToTempDirectory(item,tmpDir);
			newTempFile.deleteOnExit();
			
			// Add file to clipborad
			if(newTempFile != null) 
				files.add(newTempFile);
		}
		
		// Fill clipboard
		clipContent.putFiles(files);
		clip.setContent(clipContent);
	}
	
	/**
	 * copy to temp directory
	 * @param refId
	 * @param target
	 * @return 
	 */
	protected static File copyRemoteToTempDirectory(ListItem item, File target) {
		
		SoapClientConnector soap = SoapClientConnector.getInstance(SoapClientConnector.FRAME_RIGHT);
		File copied;
		
		if(item.getType().equalsIgnoreCase("file")) {

			try {
				SoapClientFile fileXML = soap.getFileXML(item.getRefId());
				copied = fileXML.writeToFile(new File(target, item.getTitle()));
				logger.info("Renamed file to " + copied.getAbsolutePath());
				return copied;
			} 
			catch (SoapClientConnectorException ex) {
				logger.warning("Cannot copy file to clipboard");
				return null;
			}
			catch(FileNotFoundException ex) {
				logger.warning("Cannot find file: " + target.getAbsolutePath() + "/" + item.getTitle());
			}
			catch(IOException ex) {
				logger.warning("Error copying file to: " + target.getAbsolutePath() + "/" + item.getTitle());
			}
		}
		
		if(item.getType().equals("fold") || item.getType().equals("cat")) {
			
			// For containers create a folder with the title
			File newDirectory = new File(target, item.getTitle());
			newDirectory.mkdir();
			
			ListItemReader reader = new ListItemReader();
			try {
				reader.readRemoteContainer(item.getRefId(), false);
				Iterator itemIterator = reader.getListItems().iterator();
				while(itemIterator.hasNext()) {
					ListItem child = (ListItem) itemIterator.next();
					// Start recursion
					copyRemoteToTempDirectory(child, newDirectory);
				}
			} 
			catch (SoapClientConnectorException ex) {
				logger.warning("cannot read remote items");
			}
			return newDirectory;
		}
		return target;
	}
	
	
	/**
	 * Paste from clipboard
	 * @param target
	 * @return 
	 */
	public static boolean pasteFromClipboard(ListItem target, List<File> files) {
		
		List<File> conflictFiles = null;
		
		// check if system clipboard contains files => paste these files
		if(target instanceof LocalListItem) {
			logger.info("Copying to local");
			copyFilesFromClipboardToLocal(target,files);
			return true;
		}
		if(target instanceof RemoteListItem) {
			
			if(handleRemoteNamingConflict(target,files,true)) {
				return true;
			}
			logger.info("Copying to remote");
			copyFilesFromClipboardToRemote(target,files, true);
		}
		return true;
	}
	
	/**
	 * Handle naming conflict while pasting from clipboard to remote
	 * @param target
	 * @param files
	 * @return 
	 */
	public static boolean handleRemoteNamingConflict(ListItem target, List<File> files, boolean showDialog) {

		SoapClientObjects objects = null;
		HashMap<File,SoapClientObject> conflictFiles = null;
		PasteConflictDialog dia = null;
		
		ListItem pasteTarget = target;
		
		if(
				FileManager.getInstance().getFmMode() == FileManager.FILE_MANAGER_MODE_EXPLORER &&
				!target.isContainer()
		)
		{
			pasteTarget = target.getParent();
		}
		
		try {
			objects = SoapClientConnector.getInstance(SoapClientConnector.FRAME_RIGHT).getTreeChilds(pasteTarget.getRefId());
			conflictFiles = objects.checkNamingConflict(files);
			
			if(conflictFiles.size() == 0) {
				return false;
			}
			if(!showDialog) {
				return true;
			}
			dia = new PasteConflictDialog(files, conflictFiles, pasteTarget, objects);
			dia.parse();
			MainController.getInstance().showModalDialog(dia);
		}
		catch (SoapClientConnectorException ex) {
			logger.warning(ex.getMessage());
		}
			
		return true;
	}
	
	
	
	/**
	 * copy from clipboard to local
	 */
	public static void copyFilesFromClipboardToLocal(ListItem target, List<File> files) {
		
		Iterator filesIterator = files.iterator();
		
		while(filesIterator.hasNext()) {
			
			File source = (File) filesIterator.next();
			try {
				FileManagerUtils.copyDirectory(
						source, new File(target.getAbsolutePath(), source.getName()));
			}
			catch(FileNotFoundException e) {
				logger.warning(e.getMessage());
			}
			catch(IOException e) {
				logger.warning(e.getMessage());
			}
		}
		MainController.getInstance().switchDirectory(target);
	}
	
	/**
	 * copy from clipboard to local
	 */
	public static void copyFilesFromClipboardToRemote(ListItem target, List<File> files, boolean switchDirectory) {
		
		Iterator filesIterator = files.iterator();
		
		int targetId = target.getRefId();
		
		logger.info("Target Id: " + targetId);
		logger.info("target type: " + target.getType());

		ListItem pasteTarget = target;
		
		// init error files
		errorFiles.clear();
		
		// update file object with new version
		if(target.getType().equalsIgnoreCase("file")) {
			logger.info("Target is file");
			if(FileManager.getInstance().getFmMode() == FileManager.FILE_MANAGER_MODE_EXPLORER) {
				logger.info("Explorer mode");
				pasteTarget = target.getParent();
				targetId = pasteTarget.getRefId();
			}
			else {
				logger.info("Default mode");
				copyFilesFromClipboardToRemoteFile(target, files, switchDirectory);
				return;
			}
		}
		logger.info("Target is container");
				
		while(filesIterator.hasNext()) {
			// Start recursion
			File source = (File) filesIterator.next();

			if(pasteTarget.getType().compareToIgnoreCase("root") == 0)
			{
				createRemoteItem(source, targetId, "cat");
			}
			else if(pasteTarget.getType().compareToIgnoreCase("cat") == 0)
			{
				createRemoteItem(source, targetId, "cat");
			}
			else
			{
				createRemoteItem(source, targetId, "fold");
			}
		}
		if(errorFiles.size() > 0) {
				
			UploadLimitConflictDialog dia = new UploadLimitConflictDialog(errorFiles);
			dia.parse();
			MainController.getInstance().showModalDialog(dia);
		}
		if(switchDirectory) {
			
			MainController.getInstance().switchDirectory(pasteTarget);
		}
	}
	
	public static void copyFilesFromClipboardToRemoteFile(ListItem target, List<File> files, boolean switchDirectory) {
		
		Iterator filesIterator = files.iterator();
		
		int targetId = target.getRefId();
		
		logger.info("Creating new file version");
		while(filesIterator.hasNext()) {

			File source = (File) filesIterator.next();
			
			if(source.isDirectory()) {
				continue;
			}
			logger.info("Adding new file version");
			logger.info("New file name " + source.getName());
			SoapClientFile file = new SoapClientFile();
			file.setFilename(source.getName());
			file.setTitle(source.getName());
			file.getContent().setContentFile(source);
			
			SoapClientConnector con = SoapClientConnector.getInstance(SoapClientConnector.FRAME_RIGHT);
			con.updateFile(file, targetId);
			
			if(switchDirectory)
				MainController.getInstance().switchDirectory(target.getParent());
			return;

		}
		
	}
	
	/**
	 * Create remote item
	 */
	public static void createRemoteItem(File source, int targetId, String containerType) {
		
		if(source.isDirectory()) {
			
			int newRef;
			SoapClientObjects objs = new SoapClientObjects();
			SoapClientObject obj = new SoapClientObject();
			
			obj.setTitle(source.getName());
			obj.setType(containerType);
			
			objs.getObjects().add(obj);
			
			SoapClientConnector con = SoapClientConnector.getInstance(SoapClientConnector.FRAME_RIGHT);
			newRef = con.addObject(objs, targetId);
			
			logger.info("New ref id is: " + newRef);
			
			// recurse through all childs
			String[] childs = source.list();
			for(int i = 0; i < childs.length; i++) {
				
				File child = new File(source.getAbsolutePath(),childs[i]);
				if(child.isHidden()) {
					continue;
				}
				if(childs[i].substring(0, 1).compareTo(".") == 0) {
					continue;
				}
				if(childs[i].substring(0, 1).compareTo("..") == 0) {
					continue;
				}
				// Start recursion
				createRemoteItem(child, newRef, containerType);
			}
		}
		else {
			
			// Check maximum file size
			if(!FileManagerUtils.checkAllowedFileSize(source, FileManager.getInstance().getUploadFilesize()))
			{
				errorFiles.add(source);
				return;
			}
			
			logger.info("Adding new file");
			logger.info("New file name " + source.getName());
			SoapClientFile file = new SoapClientFile();
			file.setFilename(source.getName());
			file.setTitle(source.getName());
			file.getContent().setContentFile(source);
			
			SoapClientConnector con = SoapClientConnector.getInstance(SoapClientConnector.FRAME_RIGHT);
			con.addFile(file, targetId);
		}
	}
	
	
	/**
	 * Start a search
	 * @param query 
	 */
	public static void searchRemote(String query) {
		
		SoapClientConnector con = SoapClientConnector.getInstance(SoapClientConnector.FRAME_RIGHT);
		SoapClientObjects objs = null;
		
		// Search and get objects
		try {
			objs = con.search(query);
		} 
		catch (SoapClientConnectorException ex) {
			logger.info("Searching failed");
		}
		
		// fill
		ListItemReader reader = new ListItemReader();
		reader.setObjects(objs);

		try 
		{
			reader.readRemoteContainer(1, false);
			ListViewItemParser parser = new ListViewItemParser(ContentFrame.FRAME_RIGHT);
			parser.setListItems(reader.getListItems());
			MainController.getInstance().populateSearchList(parser.parse());
			
		}
		catch (SoapClientConnectorException ex) {
			logger.warning(ex.getMessage());
			return;
		}
		
		
	}
}

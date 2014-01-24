/*
 * Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE
 */
package de.ilias.services.filemanager.events;

import de.ilias.services.filemanager.FileManager;
import de.ilias.services.filemanager.action.ActionHandler;
import de.ilias.services.filemanager.content.ListItem;
import java.io.File;
import java.util.Iterator;
import javafx.event.EventHandler;
import javafx.scene.Node;
import javafx.scene.input.DragEvent;
import javafx.scene.input.TransferMode;
import javax.swing.text.html.HTMLDocument;

/**
 * Class ListItemDragEventHandler
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
public class ListItemDragEventHandler extends ListItemEventHandler implements EventHandler<DragEvent> {

	/**
	 * Constructor
	 */
	public ListItemDragEventHandler(ListItem item) {
		super(item);
	}
	
	
	
	/**
	 * Handle drag event
	 * @param t 
	 */
	public void handle(DragEvent de) {
		
		// Check if target is accessible
		if(de.getEventType() == DragEvent.DRAG_OVER) {
			this.handleDragOver(de);
		}
		if(de.getEventType() == DragEvent.DRAG_DROPPED) {
			this.handleDropped(de);
		}
		de.consume();
	}
	
	/**
	 * Handle drag over, give visual feedback if dragging is possible
	 * @param de 
	 */
	protected void handleDragOver(DragEvent de) {

		Node pane = (Node) de.getGestureSource();
		
		/*
		if (pane == null) {
			logger.info("Source is null");
			de.consume();
			return;
		}
		*/

		/*
		ListItem source = (ListItem) pane.getUserData();

		if (source == null) {
			logger.info("Source does not contain user data");
			de.consume();
			return;
		}
		*/

		/*
		if (source == getListItem()) {
			logger.info(" No drag of source on source");
			de.consume();
			return;
		}
		*/
		
		if (!getListItem().isWritable()) {
			logger.info("Drag target is not writable");
			return;
		}
		
		if(!getListItem().isContainer()) {
			
			// If target is file
			if(FileManager.getInstance().getFmMode() == FileManager.FILE_MANAGER_MODE_DEFAULT) {
				if(getListItem().getType().equalsIgnoreCase("file") && de.getDragboard().getFiles().size() != 1) {
					logger.info("Drag only one file in file object");
					return;
				}
				Iterator fileIte = de.getDragboard().getFiles().iterator();
				while(fileIte.hasNext()) {
					File file = (File) fileIte.next();
					if(file.isDirectory()) {
						logger.info("Cannot replace file version with directory");
						return;
					}
				}
			}
		}
		
		if(!de.getDragboard().hasFiles()) {
			logger.info("Dragboard does not contain files");
			return;
		}

		// All checks done: accept transfer mode
		de.acceptTransferModes(TransferMode.COPY);
		return;
	}
	
	/**
	 * Handle drop
	 * @param de 
	 */
	protected void handleDropped(DragEvent de) {
		
		logger.info("New drop event");
		ActionHandler.pasteFromClipboard(getListItem(), de.getDragboard().getFiles());
		
		de.getDragboard().clear();
		de.setDropCompleted(true);
	}
}

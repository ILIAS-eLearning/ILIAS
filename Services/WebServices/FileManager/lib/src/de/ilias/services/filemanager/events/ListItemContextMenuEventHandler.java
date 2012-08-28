/*
 * Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE
 */
package de.ilias.services.filemanager.events;

import de.ilias.services.filemanager.action.ActionHandler;
import de.ilias.services.filemanager.content.ListItem;
import de.ilias.services.filemanager.content.LocalListItem;
import de.ilias.services.filemanager.content.RemoteListItem;
import de.ilias.services.filemanager.controller.MainController;
import de.ilias.services.filemanager.dialog.CreateDialog;
import de.ilias.services.filemanager.dialog.DeleteDialog;
import de.ilias.services.filemanager.dialog.RenameDialog;
import de.ilias.services.filemanager.soap.SoapClientConnector;
import de.ilias.services.filemanager.soap.api.SoapClientFile;
import de.ilias.services.filemanager.soap.api.SoapClientObject;
import de.ilias.services.filemanager.soap.api.SoapClientObjects;
import de.ilias.services.filemanager.utils.FileManagerUtils;
import java.io.File;
import java.io.FileNotFoundException;
import java.io.IOException;
import java.util.ArrayList;
import java.util.Iterator;
import java.util.logging.Logger;
import javafx.event.ActionEvent;
import javafx.event.EventHandler;
import javafx.scene.Node;
import javafx.scene.input.Clipboard;
import javafx.scene.input.ClipboardContent;

/**
 * Class ListItemContextMenuEventHandler
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
public class ListItemContextMenuEventHandler extends ListItemEventHandler implements EventHandler<ActionEvent> {

	private static final Logger logger = Logger.getLogger(ListItemContextMenuEventHandler.class.getName());

	public static final int ACTION_OPEN = 1;
	public static final int ACTION_COPY = 2;
	public static final int ACTION_PASTE = 3;
	public static final int ACTION_DELETE = 4;
	public static final int ACTION_RENAME = 5;
	public static final int ACTION_COPY_TO_CLIPBOARD = 6;
	
	public static final int ACTION_CREATE_CAT = 7;
	public static final int ACTION_CREATE_CRS = 8;
	public static final int ACTION_CREATE_FOLD = 9;
	public static final int ACTION_CREATE_GRP = 10;
	
	public static final int ACTION_EDIT_REVISION_STATE = 20;
	
	private int actionType = 0;
	
	
	/**
	 * Constructor
	 * @param item 
	 */
	public ListItemContextMenuEventHandler(ListItem item, int actionType) {
		super(item);
		this.actionType = actionType;
	}
	
	
	/**
	 * Action event handler
	 * @param t 
	 */
	public void handle(ActionEvent ae) {
		
		switch(actionType) {
			
			case ListItemContextMenuEventHandler.ACTION_OPEN:
				logger.info("Handling action 'open'");
				this.handleOpenAction(true);
				break;
			case ListItemContextMenuEventHandler.ACTION_COPY:
				logger.info("Handling action 'COPY'");
				this.handleCopyAction();
				break;
			case ListItemContextMenuEventHandler.ACTION_COPY_TO_CLIPBOARD:
				logger.info("Handling action 'COPY to CLIPBOARD'");
				this.handleCopyToClipboard();
				break;
			case ListItemContextMenuEventHandler.ACTION_PASTE:
				logger.info("Handling action 'PASTE'");
				handlePasteAction();
				break;
			case ListItemContextMenuEventHandler.ACTION_DELETE:
				logger.info("Handling action 'DELETE'");
				handleDeleteAction();
				break;
			case ListItemContextMenuEventHandler.ACTION_RENAME:
				logger.info("Handling action 'RENAME'");
				this.handleRenameAction();
				break;
			case ListItemContextMenuEventHandler.ACTION_CREATE_CAT:
				logger.info("Handling action 'CREATE CAT'");
				this.handleCreateAction(actionType);
				break;
			case ListItemContextMenuEventHandler.ACTION_CREATE_CRS:
				logger.info("Handling action 'CREATE CRS'");
				this.handleCreateAction(actionType);
				break;
			case ListItemContextMenuEventHandler.ACTION_CREATE_GRP:
				logger.info("Handling action 'CREATE GRP'");
				this.handleCreateAction(actionType);
				break;
			case ListItemContextMenuEventHandler.ACTION_CREATE_FOLD:
				logger.info("Handling action 'CREATE FOLD'");
				this.handleCreateAction(actionType);
				break;
			case ListItemContextMenuEventHandler.ACTION_EDIT_REVISION_STATE:
				logger.info("Handling action 'EDIT REVISION STATE'");
				this.handleRevisionStateEditingAction();
				break;
			
				
		}
	}
	
	/**
	 * Show create dialog
	 * @param type 
	 */
	protected void handleCreateAction(int type) {
		
		CreateDialog create = new CreateDialog(getListItem(), type);
		create.parse();
		MainController.getInstance().showModalDialog(create);
	}

	
	/**
	 * Handle copy action
	 */
	protected void handleCopyAction() {
		
		if(getListItem() instanceof LocalListItem) {
			
			// clear clipboard
			Clipboard clip = Clipboard.getSystemClipboard();
			ClipboardContent content = new ClipboardContent();
			ArrayList<File> files = new ArrayList<File>();
			
			clip.clear();
			Iterator selected = MainController.getInstance().getList(getListItem()).
					getSelectionModel().getSelectedItems().iterator();
			while(selected.hasNext()) {
				Node node = (Node) selected.next();
				ListItem selectedNode = (ListItem) node.getUserData();
				
				files.add(new File(selectedNode.getAbsolutePath()));
			}
			
			content.putFiles(files);
			clip.setContent(content);
		}
		if(getListItem() instanceof RemoteListItem) {
			
		}
			
	}	
}

/*
 * Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE
 */
package de.ilias.services.filemanager.events;

import de.ilias.services.filemanager.action.ActionHandler;
import de.ilias.services.filemanager.content.ListItem;
import de.ilias.services.filemanager.content.LocalListItem;
import de.ilias.services.filemanager.content.RemoteListItem;
import de.ilias.services.filemanager.controller.MainController;
import de.ilias.services.filemanager.dialog.DeleteDialog;
import de.ilias.services.filemanager.dialog.RenameDialog;
import de.ilias.services.filemanager.dialog.RevisionStateDialog;
import java.awt.Desktop;
import java.io.File;
import java.io.IOException;
import java.util.ArrayList;
import java.util.Iterator;
import java.util.logging.Logger;
import javafx.collections.ObservableList;
import javafx.scene.Cursor;
import javafx.scene.Node;
import javafx.scene.control.ListView;
import javafx.scene.input.Clipboard;

/**
 * Class ListItemEventHandler
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
public class ListItemEventHandler
{
	
	protected static final Logger logger = Logger.getLogger(ListItemEventHandler.class.getName());
	
	private ListItem item = null;

	public ListItemEventHandler(ListItem item) {
		this.item = item;
	}
	
	/**
	 * set list item
	 * @param li 
	 */
	public void setListItem(ListItem li) {
		
		this.item = li;
	}

	/**
	 * set list item
	 * @return 
	 */
	public ListItem getListItem() {
		return this.item;
	}
	
	/**
	 * Handle open action (triggered by mouse event or action event or key event)
	 * @param openContainer 
	 */
	protected void handleOpenAction(boolean openContainer) {
		
		// return if item is not readable
		if(!getListItem().isReadable()) {
			return;
		}
		
		// Check if container and switch directory or open using explorer
		if (getListItem().isContainer()) {
			
			if(openContainer && !(getListItem() instanceof RemoteListItem)) {
				Desktop desktop;
				try {
					desktop = Desktop.getDesktop();
					desktop.open(new File(getListItem().getAbsolutePath()));
				} 
				catch (IOException ex) {
					logger.warning("Cannot open file " + getListItem().getTitle());
				}
			}
			else {
				MainController.getInstance().getRoot().setCursor(Cursor.WAIT);
				if(getListItem().isUpperLink()) {
					MainController.getInstance().switchDirectory(getListItem().getParent());
				}
				else {
					MainController.getInstance().switchDirectory(getListItem());
				}
				MainController.getInstance().getRoot().setCursor(Cursor.DEFAULT);
			}
			return;
		} 
		else if (getListItem().isReadable() && !getListItem().getFileType().isEmpty()) {

			// @todo check cursor settings
			MainController.getInstance().getRoot().setCursor(Cursor.WAIT);

			if (getListItem() instanceof RemoteListItem) {
				MainController.getInstance().deliverRemoteItem(getListItem());
			}
			if (getListItem() instanceof LocalListItem) {
				Desktop desktop;
				try {
					desktop = Desktop.getDesktop();
					desktop.open(new File(getListItem().getAbsolutePath()));
				} catch (IOException ex) {
					logger.warning("Cannot open file " + getListItem().getTitle());
				}
			}
			MainController.getInstance().getRoot().setCursor(Cursor.DEFAULT);
		}
	}
	
	/**
	 * Copy selected to clipboard
	 */
	protected void handleCopyToClipboard() {
		
		if(getListItem() instanceof RemoteListItem) {
			
			logger.info("Copy remote list item to clipboard");
			
			// First clear the clipboard
			Clipboard clip = Clipboard.getSystemClipboard();
			clip.clear();
			
			Iterator selected = MainController.getInstance().getList(getListItem()).
					getSelectionModel().getSelectedItems().iterator();
			ArrayList<ListItem> items = new ArrayList<ListItem>();
			while(selected.hasNext()) {
				Node node = (Node) selected.next();
				ListItem selectedNode = (ListItem) node.getUserData();
				items.add(selectedNode);
			}
			ActionHandler.copyRemoteToClipboard(getListItem(),items);
		}
	}

	/**
	 * Select all items
	 * @param view 
	 */
	protected void selectAllItems(ListView view) {

		Iterator ite = view.getItems().iterator();
		int index = 0;
		while(ite.hasNext()) {
			ite.next();
			if(index > 0) {
				view.getSelectionModel().select(index);
			}
			else {
				view.getSelectionModel().clearSelection(index);
			}
			index++;
		}
	}

	/**
	 * Select all items
	 * @param view 
	 */
	protected void invertSelection(ListView view) {
		
		Iterator ite = view.getItems().iterator();
		int index = 0;
		while(ite.hasNext()) {
			ite.next();
			if(view.getSelectionModel().isSelected(index)) {
				view.getSelectionModel().clearSelection(index);
			}
			else {
				view.getSelectionModel().select(index);
			}
			index++;
		}
	}

	/**
	 * Handle paste action
	 */
	protected void handlePasteAction() {
		ActionHandler.pasteFromClipboard(getListItem(),Clipboard.getSystemClipboard().getFiles());
		Clipboard.getSystemClipboard().clear();
	}
	
	/**
	 * Show rename dialog
	 */
	protected void handleRenameAction() {
	
		RenameDialog rename = new RenameDialog(getListItem());
		rename.parse();
		MainController.getInstance().showModalDialog(rename);
	}
	
	/**
	 * Show delete confirmation dialog
	 */
	protected void handleDeleteAction() {
		
		if(!getListItem().isDeletable()) {
			return;
		}
		
		DeleteDialog delete = new DeleteDialog(getListItem());
		delete.parse();
		MainController.getInstance().showModalDialog(delete);
	}
	
	/**
	 * handle revision state
	 */
	protected void handleRevisionStateEditingAction() {
		
		if(!getListItem().isWritable()) {
			return;
		}
		if(!getListItem().getType().equals("file"))
		{
			return;
		}
		RevisionStateDialog rs = new RevisionStateDialog(getListItem());
		rs.parse();
		MainController.getInstance().showModalDialog(rs);
	}
}

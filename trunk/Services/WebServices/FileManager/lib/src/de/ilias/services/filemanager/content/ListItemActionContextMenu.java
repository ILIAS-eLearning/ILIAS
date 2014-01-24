/*
 * Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE
 */
package de.ilias.services.filemanager.content;

import de.ilias.services.filemanager.FileManager;
import de.ilias.services.filemanager.events.ListItemContextMenuEventHandler;
import de.ilias.services.filemanager.utils.FileManagerUtils;
import java.util.ArrayList;
import java.util.Iterator;
import java.util.logging.Logger;
import javafx.collections.ObservableList;
import javafx.scene.Node;
import javafx.scene.control.ContextMenu;
import javafx.scene.control.ListView;
import javafx.scene.control.MenuItemBuilder;
import javafx.scene.control.SeparatorMenuItemBuilder;
import javafx.scene.image.ImageViewBuilder;
import javafx.scene.input.*;

/**
 * Class ListItemActionContextMenu
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
public class ListItemActionContextMenu extends ContextMenu {
	
	private static final Logger logger = Logger.getLogger(ListItemActionContextMenu.class.getName());

	private ObservableList<Node> selected = null;
	private Node source = null;
	private ArrayList<ListItem> listItems = new ArrayList<ListItem>();
	
	private boolean allowOpen = false;
	private boolean allowCopy = false;
	private boolean allowPaste = false;
	private boolean allowDelete = false;
	private boolean allowRename = false;
	private boolean allowCopyToClipboard = false;
	
	private boolean allowCreate = false;
	private boolean allowCourse = false;
	private boolean allowFolder = false;
	private boolean allowCategory = false;
	private boolean allowGroup = false;
	
	public ListItemActionContextMenu(ObservableList<Node> sel, Node source) {
	
		selected = sel;
		this.source = source;
		parseSelected();
		populate();
	}
	
	/**
	 * get selected nodes
	 * @return 
	 */
	public ObservableList<Node> getSelectedNodes() {
		return selected;
	}
	
	/**
	 * Get array list of list items
	 * @return 
	 */
	public ArrayList<ListItem> getSelectedItems() {
		return listItems;
	}
	
	/**
	 * Get source
	 * @return 
	 */
	public Node getSource() {
		return source;
	}
	
	protected void parseSelected() {
		
		boolean multipleSelected;
		
		if(getSelectedNodes().size() > 1) {
			multipleSelected = true;
			allowCreate = false;
			allowOpen = false;
			allowCopy = true;
			allowCopyToClipboard = true;
			allowPaste = false;
			allowRename = false;
			allowDelete = true;
		}
		else {
			multipleSelected = false;
			allowOpen = true;
			allowCopy = true;
			allowCopyToClipboard = true;
			allowRename = true;
			allowPaste = true;
			allowDelete = true;
		}

		Iterator nodeIte = getSelectedNodes().iterator();
		
		while(nodeIte.hasNext()) {
			Node node = (Node) nodeIte.next();
			ListItem listItem = (ListItem) node.getUserData();
			
			getSelectedItems().add(listItem);
			
			if(
					listItem instanceof RemoteListItem &&
					(getSelectedNodes().size() == 1) &&
					(listItem.getType().equals("crs") ||
					listItem.getType().equals("grp"))
			) {
				allowCopyToClipboard = false;
			}
			
			// Currently no delete for local list
			if(listItem instanceof RemoteListItem && listItem.isContainer()) {
				//allowDelete = false;
			}
			if(!allowPaste(listItem)) {
				allowPaste = false;
			}
			/*
			if(listItem instanceof RemoteListItem && listItem.isContainer()) {
				allowOpen = false;
			}
			*/
			if(!listItem.isWritable()) {
				allowPaste = false;
				allowRename = false;
			}
			if(!listItem.isReadable()) {
				allowCopy = false;
				allowCopyToClipboard = false;
				allowOpen = false;
				allowPaste = false;
				allowRename = false;
			}
			if(!listItem.isDeletable()) {
				allowDelete = false;
			}
			if(listItem instanceof LocalListItem) {
				allowCopyToClipboard = false;
			}
			if(listItem.isCourseAllowed() && !multipleSelected) {
				allowCreate = true;
				allowCourse = true;
			}
			if(listItem.isCategoryAllowed() && !multipleSelected) {
				allowCreate = true;
				allowCategory = true;
			}
			if(listItem.isGroupAllowed() && !multipleSelected) {
				allowCreate = true;
				allowGroup = true;
			}
			if(listItem.isFolderAllowed() && !multipleSelected) {
				allowCreate = true;
				allowFolder = true;
			}
			// restrictive for upper link
			if(listItem.isUpperLink()) {
				allowCreate = false;
				allowOpen = false;
				allowCopy = false;
				allowCopyToClipboard = false;
				allowRename = false;
				allowPaste = false;
				allowDelete = false;
			}
		}
		
		/**
		 * Default action of list view
		 */
		if(getSource() instanceof ListView) {
			allowOpen = false;
			allowCopy = false;
			allowCopyToClipboard = false;
			allowRename = false;
			allowDelete = false;
			
			if(((ListItem)getSource().getUserData()).isCategoryAllowed()) {
				allowCreate = true;
				allowCategory = true;
			}
			if(((ListItem)getSource().getUserData()).isCourseAllowed()) {
				allowCreate = true;
				allowCourse = true;
			}
			if(((ListItem)getSource().getUserData()).isGroupAllowed()) {
				allowCreate = true;
				allowGroup = true;
			}
			if(((ListItem)getSource().getUserData()).isFolderAllowed()) {
				allowCreate = true;
				allowFolder = true;
			}
			
			logger.info("List view default id = " + String.valueOf(((ListItem)getSource().getUserData()).getRefId()));
		}
		
		// Hide paste if clipboard content id empty
		Clipboard clip = Clipboard.getSystemClipboard();
		if(!clip.hasFiles()) {
			allowPaste = false;
		}
	}
	
	
	/**
	 * Populate action menu
	 */
	protected void populate() {
		
		// Open
		this.getItems().addAll(
				MenuItemBuilder.create().
				text("Open").
				accelerator(new KeyCodeCombination(KeyCode.O,KeyCodeCombination.CONTROL_DOWN)).
				graphic(
					ImageViewBuilder.create().image(FileManagerUtils.getImageByName("edit.png")).build()).
				disable(!allowOpen).
				onAction(new ListItemContextMenuEventHandler((ListItem) getSource().getUserData(), ListItemContextMenuEventHandler.ACTION_OPEN)).
				build()
		);
		
		// Separator
		this.getItems().addAll(
				SeparatorMenuItemBuilder.create().build());
		
		// Copy disabled
		/*
		this.getItems().addAll(
				MenuItemBuilder.create().
				text("Copy").
				graphic(
					ImageViewBuilder.create().image(FileManagerUtils.getImageByName("edit-copy.png")).build()).
				disable(!allowCopy).
				onAction(new ListItemContextMenuEventHandler((ListItem) getSource().getUserData(), ListItemContextMenuEventHandler.ACTION_COPY)).
				build()
		);
		
		*/

		// Copy to clipboard
		this.getItems().addAll(
				MenuItemBuilder.create().
				text("Copy to Clipboard").
				accelerator(new KeyCodeCombination(KeyCode.C,KeyCodeCombination.CONTROL_DOWN)).
				graphic(
					ImageViewBuilder.create().image(FileManagerUtils.getImageByName("edit-copy.png")).build()).
				disable(!allowCopyToClipboard).
				onAction(new ListItemContextMenuEventHandler((ListItem) getSource().getUserData(), ListItemContextMenuEventHandler.ACTION_COPY_TO_CLIPBOARD)).
				build()
		);
		
		// Paste
		this.getItems().addAll(
				MenuItemBuilder.create().
				text("Paste").
				accelerator(new KeyCodeCombination(KeyCode.V,KeyCodeCombination.CONTROL_DOWN)).
				graphic(
					ImageViewBuilder.create().image(FileManagerUtils.getImageByName("edit-paste.png")).build()).
				disable(!allowPaste).
				onAction(new ListItemContextMenuEventHandler((ListItem) getSource().getUserData(), ListItemContextMenuEventHandler.ACTION_PASTE)).
				build()
		);
		
		// Seperator
		this.getItems().addAll(
				SeparatorMenuItemBuilder.create().build());

		// Delete
		this.getItems().addAll(
				MenuItemBuilder.create().
				accelerator(new KeyCodeCombination(KeyCode.DELETE)).
				text("Delete").
				graphic(
					ImageViewBuilder.create().image(FileManagerUtils.getImageByName("edit-delete.png")).build()).
				disable(!allowDelete).
				onAction(new ListItemContextMenuEventHandler((ListItem) getSource().getUserData(), ListItemContextMenuEventHandler.ACTION_DELETE)).
				build()
		);
		
		// Rename
		this.getItems().addAll(
				MenuItemBuilder.create().
				text("Rename").
				accelerator(new KeyCodeCombination(KeyCode.R,KeyCodeCombination.CONTROL_DOWN)).
				graphic(
					ImageViewBuilder.create().image(FileManagerUtils.getImageByName("edit-rename.png")).build()).
				disable(!allowRename).
				onAction(new ListItemContextMenuEventHandler((ListItem) getSource().getUserData(), ListItemContextMenuEventHandler.ACTION_RENAME)).
				build()
		);
		
		// File lock for files
		if(checkAllowLock()) {
			this.getItems().addAll(
				MenuItemBuilder.create().
				text("Edit Revision State").
				accelerator(new KeyCodeCombination(KeyCode.L,KeyCodeCombination.CONTROL_DOWN)).
				graphic(
					ImageViewBuilder.create().image(FileManagerUtils.getImageByName("edit-revision.png")).build()).
				disable(false).
				onAction(new ListItemContextMenuEventHandler((ListItem) getSource().getUserData(), ListItemContextMenuEventHandler.ACTION_EDIT_REVISION_STATE)).
				build()
			);
		}
		
		
		if(allowCreate) {
			// Seperator
			this.getItems().addAll(
					SeparatorMenuItemBuilder.create().build());
			
			// Category
			if(allowCategory) {
				this.getItems().addAll(
						MenuItemBuilder.create().
						text("Create Category").
						graphic(
							ImageViewBuilder.create().image(FileManagerUtils.getImageByName("icon_cat_s.gif")).build()).
						disable(false).
						onAction(new ListItemContextMenuEventHandler((ListItem) getSource().getUserData(), ListItemContextMenuEventHandler.ACTION_CREATE_CAT)).
						build()
				);
			}
			// Folder
			if(allowFolder) {
				this.getItems().addAll(
						MenuItemBuilder.create().
						text("Create Folder").
						graphic(
							ImageViewBuilder.create().image(FileManagerUtils.getImageByName("icon_fold_s.gif")).build()).
						disable(false).
						onAction(new ListItemContextMenuEventHandler((ListItem) getSource().getUserData(), ListItemContextMenuEventHandler.ACTION_CREATE_FOLD)).
						build()
				);
			}
			// Course
			if(allowCourse) {
				this.getItems().addAll(
						MenuItemBuilder.create().
						text("Create Course").
						graphic(
							ImageViewBuilder.create().image(FileManagerUtils.getImageByName("icon_crs_s.gif")).build()).
						disable(false).
						onAction(new ListItemContextMenuEventHandler((ListItem) getSource().getUserData(), ListItemContextMenuEventHandler.ACTION_CREATE_CRS)).
						build()
				);
			}
			// Group
			if(allowGroup) {
				this.getItems().addAll(
						MenuItemBuilder.create().
						text("Create Group").
						graphic(
							ImageViewBuilder.create().image(FileManagerUtils.getImageByName("icon_grp_s.gif")).build()).
						disable(false).
						onAction(new ListItemContextMenuEventHandler((ListItem) getSource().getUserData(), ListItemContextMenuEventHandler.ACTION_CREATE_GRP)).
						build()
				);
			}
			
		}
	}
	
	/**
	 * Check if lock action should be displayed.
	 * @return 
	 */
	protected boolean checkAllowLock() {
		
		if(this.getSelectedNodes().size() != 1) {
			return false;
		}
		
		Iterator nodeIte = getSelectedNodes().iterator();
		while(nodeIte.hasNext()) {
			Node node = (Node) nodeIte.next();
			ListItem item = (ListItem) node.getUserData();
			
			if(item.getType().equals("file") && item.isWritable()) {
				logger.info("Item allows file lock");
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Check if paste is allowed
	 * @param item
	 * @return 
	 */
	public static boolean allowPaste(ListItem item) {
		
		if(item.isContainer() && item.isWritable()) {
			return true;
		}
		if(item.getType().equalsIgnoreCase("file") && item.isWritable()) {
			return true;
		}
		return false;
	}
}

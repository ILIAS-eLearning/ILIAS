/*
 * Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE
 */
package de.ilias.services.filemanager.events;

import de.ilias.services.filemanager.content.ListItem;
import de.ilias.services.filemanager.content.ListItemActionContextMenu;
import de.ilias.services.filemanager.content.LocalListItem;
import de.ilias.services.filemanager.content.RemoteListItem;
import de.ilias.services.filemanager.controller.MainController;
import java.io.File;
import java.util.ArrayList;
import java.util.Iterator;
import javafx.collections.ObservableList;
import javafx.event.EventHandler;
import javafx.scene.Node;
import javafx.scene.control.ListView;
import javafx.scene.input.ClipboardContent;
import javafx.scene.input.Dragboard;
import javafx.scene.input.MouseEvent;
import javafx.scene.input.TransferMode;

/**
 * Class ListItemMouseEventHandler
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
public class ListItemMouseEventHandler extends ListItemEventHandler implements EventHandler<MouseEvent> {

	private Node source;
	private static ListItemActionContextMenu CURRENT_MENU;
	
	/**
	 * Constructor
	 * @param item 
	 */
	public ListItemMouseEventHandler(ListItem item, Node source) {
		super(item);
		this.source = source;
	}
	
	
	/**
	 * Get node
	 * @return 
	 */
	public Node getNode() {
		return source;
	}
	
	/**
	 * Handles mouse events
	 * Mouse events are: mouse is moved or button is pressed
	 * @param me
	 */
	public void handle(MouseEvent me) {

		logger.finer("New mouse event " + me.getEventType().getName());
		
		// Mouse pressed handles the multi selection of list items
		// Unfortunately right clicks deselect all previous selected items
		// Therefore the event is consumed and the selection of items is done
		// manually.
		if(me.getEventType() == MouseEvent.MOUSE_PRESSED) {
			this.handleSelectionModel(me);
			
			if(CURRENT_MENU != null) {
				CURRENT_MENU.hide();
			}
			// show context menu for selected items
			if(me.isSecondaryButtonDown()) {
				
				// for list views deselect all selected
				if(me.getSource() instanceof ListView) {

					ListItem currentItem = (ListItem) ((Node) me.getSource()).getUserData();
					MainController.getInstance().
							getList(getListItem()).
							getSelectionModel().
							clearSelection()
					;
				}
				
				ListItemActionContextMenu cm = new ListItemActionContextMenu(
						getSelectedNodes(),
						(Node) me.getSource()
				);
				cm.show((Node) me.getSource(), me.getScreenX(), me.getScreenY());
				CURRENT_MENU = cm;
			}
			me.consume();
			return;
		}
		
		// Mouse released is handled to deselect other items in case of
		// click on multi selected items
		if(me.getEventType() == MouseEvent.MOUSE_RELEASED) {
			this.handleSelectionModel(me);
			me.consume();
			return;
		}

		if(me.getEventType() == MouseEvent.MOUSE_CLICKED) {
			
			logger.fine("New click event for " + getListItem().getTitle());
			if(me.getClickCount() == 2) {
				this.handleOpenAction(false);
				me.consume();
			}
		}
		if(me.getEventType() == MouseEvent.DRAG_DETECTED) {
			
			logger.finer("Start drag detected");
			if(!getListItem().isReadable()) {
				logger.info("Source is not readable!");
				me.consume();
				return;
			}
			
			Dragboard db = getNode().startDragAndDrop(TransferMode.COPY);
			ClipboardContent content = new ClipboardContent();
			
			if(getListItem() instanceof LocalListItem) {
				ArrayList<File> files = new ArrayList<File>();
				ListView list = MainController.getInstance().getLocalList();
				
				list.getSelectionModel().getSelectedItems();
				Iterator iterator = list.getSelectionModel().getSelectedItems().iterator();
				while(iterator.hasNext()) {
					Node node = (Node) iterator.next();
					ListItem item = (ListItem) node.getUserData();
					files.add(new File(item.getAbsolutePath()));
					logger.info("Adding " + item.getAbsolutePath() + " to clipboard");
				}
				content.putFiles(files);
				db.setContent(content);
			}
			if(getListItem() instanceof RemoteListItem) {
				
				// @todo Add custom data to clipboard
				// meanwhile add string of ref ids to clipboard
				StringBuilder refs = new StringBuilder();
				ListView list = MainController.getInstance().getRemoteList();
				Iterator iterator = list.getSelectionModel().getSelectedItems().iterator();
				while(iterator.hasNext()) {
					Node node = (Node) iterator.next();
					ListItem item = (ListItem) node.getUserData();
					refs.append(" " + item.getRefId());
				}
				
				logger.info("Adding " + refs.toString() + " to clipboard");
				content.putString(refs.toString());
				db.setContent(content);
			}
			me.consume();
		}
	}
	
	/**
	 * Handle mouse pressed
	 * @param me 
	 */
	protected void handleSelectionModel(MouseEvent me) {
		
		((Node) me.getSource()).setFocusTraversable(true);
		logger.finer("Node is not focused");
		if(me.getEventType() == MouseEvent.MOUSE_RELEASED) {
			
			// if mouse is released on a selected node => deselect all 
			// other nodes
			if(isSourceSelected(me) && false) {
				MainController.getInstance().
						getList(getListItem()).
						getSelectionModel().
						clearSelection();
				MainController.getInstance().
						getList(getListItem()).
						getSelectionModel().
						select(me.getSource());
			}
			me.consume();
			return;
		}
		if(me.isControlDown() && me.isPrimaryButtonDown()) {

			// deselect item if mouse is pressed on a selected item 
			if(isSourceSelected(me)) {
				int currentIndex = MainController.getInstance().
						getList(getListItem()).getItems().indexOf(me.getSource());
				MainController.getInstance().getList(getListItem()).
						getSelectionModel().clearSelection(currentIndex);
				me.consume();
				return;
			}
		}
		
		if(me.isShiftDown())
		{
			int SelIndex = MainController.getInstance().
					getList(getListItem()).
					getSelectionModel().getSelectedIndex();
			

			int currentIndex = MainController.getInstance().
					getList(getListItem()).getItems().indexOf(me.getSource());
			
			logger.finer("Current index is: " + currentIndex);
					
			MainController.getInstance().getList(getListItem()).
					getSelectionModel().selectRange(SelIndex, currentIndex);
			MainController.getInstance().getList(getListItem()).
					getSelectionModel().select(currentIndex);
			
			me.consume();
			return;
		}
		
		// if left mouse is clicked => select this node
		if(me.isPrimaryButtonDown()) {
			
			// (it could be the start of a drag gesture)
			if(isSourceSelected(me)) {
				return;
			}
			
			// If no control is pressed => deselect all other selections
			if(!me.isControlDown()) {
				MainController.getInstance().
						getList(getListItem()).
						getSelectionModel().
						clearSelection();
			}
			// Select current item
			MainController.getInstance().
					getList(getListItem()).
					getSelectionModel().select(me.getSource());
			return;
		}
		if(me.isSecondaryButtonDown()) {
			
			// If control is pressed => add current node to selection
			if(me.isControlDown()) {
				MainController.getInstance().
						getList(getListItem()).
						getSelectionModel().
						select(me.getSource());
				return;
			}

			// Control is not down
			// remove previous selection only if target is not selected
			if(!isSourceSelected(me)) {
				MainController.getInstance().
						getList(getListItem()).
						getSelectionModel().
						clearSelection();
			}
			
			MainController.getInstance().
					getList(getListItem()).
					getSelectionModel().
					select(me.getSource());
		}
	}
	
	/**
	 * Check if current source node is selected by previous action
	 * @param me
	 * @return 
	 */
	protected boolean isSourceSelected(MouseEvent me) {
		
		Iterator selectedIterator = MainController.getInstance().
				getList(getListItem()).
				getSelectionModel().
				getSelectedItems().iterator();
		
		while (selectedIterator.hasNext()) {
			Node selectedNode = (Node) selectedIterator.next();
			if (selectedNode == me.getSource()) {
				return true;
			}
		}
		return false;
		
		
	}
	
	/**
	 * Get selected nodes (list items)
	 * @return 
	 */
	protected ObservableList<Node> getSelectedNodes() {
		
		return MainController.getInstance().
				getList(getListItem()).
				getSelectionModel().
				getSelectedItems();
	}
	
	
}

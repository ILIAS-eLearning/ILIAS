/*
 * Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE
 */
package de.ilias.services.filemanager.events;

import de.ilias.services.filemanager.content.ListItem;
import de.ilias.services.filemanager.controller.MainController;
import java.util.Iterator;
import java.util.logging.Logger;
import javafx.event.Event;
import javafx.event.EventHandler;
import javafx.scene.Node;
import javafx.scene.control.ListView;
import javafx.scene.input.KeyCode;
import javafx.scene.input.KeyEvent;

/**
 * Class ListItemKeyEventHandler
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
public class ListItemKeyEventHandler extends ListItemEventHandler implements EventHandler<KeyEvent> {
	
	protected static final Logger logger = Logger.getLogger(ListItemKeyEventHandler.class.getName());
	
	private boolean multipleSelected = false;
	
	/**
	 * Construcor
	 */
	public ListItemKeyEventHandler(ListItem item) {
		super(item);
	}
	
	/**
	 * Check if multiple items are selected
	 * @return 
	 */
	public boolean areMultipleSelected() {
		return this.multipleSelected;
	}

	/**
	 * Handles key event
	 * @param ke 
	 */
	public void handle(KeyEvent ke) {

		if(ke.getEventType() == KeyEvent.KEY_RELEASED) {
			// open action
			if((ke.isControlDown() && ke.getCode() == KeyCode.O) || ke.getCode() == KeyCode.ENTER) {
				// Handle open action
				logger.info("Handling open action");
				this.initListItem(ke);
				if(!areMultipleSelected()) {
					this.handleOpenAction(true);
				}
				ke.consume();
				return;
			}
			if(ke.isControlDown() && ke.getCode() == KeyCode.R) {
				// Handle open action
				logger.info("Handling rename");
				this.initListItem(ke);
				if(!areMultipleSelected()) {
					this.handleRenameAction();
				}
				ke.consume();
				return;
				
			}
			if(ke.isControlDown() && ke.getCode() == KeyCode.L) {
				// Handle revision state opening actions
				logger.info("Handling revision state editing");
				this.initListItem(ke);
				if(!areMultipleSelected()) {
					this.handleRevisionStateEditingAction();
				}
				ke.consume();
				return;
			}
			if(ke.isControlDown() && ke.getCode() == KeyCode.C) {
				// Handle open action
				logger.info("Handling copy to clipboard action");
				this.initListItem(ke);
				this.handleCopyToClipboard();
				ke.consume();
				return;
			}
			if(ke.isControlDown() && ke.getCode() == KeyCode.A) {
				logger.info("Select all");
				this.selectAllItems((ListView) ke.getSource());
				ke.consume();
				return;
			}
			if(ke.isShiftDown() && ke.isControlDown() && ke.getCode() == KeyCode.I) {
				logger.info("Invert selection");
				this.invertSelection((ListView) ke.getSource());
				ke.consume();
				return;
			}
			if(ke.isControlDown() && ke.getCode() == KeyCode.V) {
				logger.info("Handling paste");
				this.initListItem(ke);
				this.handlePasteAction();
				ke.consume();
				return;
			}
			if(ke.getCode() == KeyCode.DELETE) {
				logger.info("Handling delete");
				this.initListItem(ke);
				this.handleDeleteAction();
				ke.consume();
				return;
			}
		}
		ke.consume();
		return;
	}
	
	/**
	 * init list item from key event
	 * @param ke 
	 */
	protected void initListItem(KeyEvent ke) {
		
		// container as default list item
		this.setListItem((ListItem)((Node) ke.getSource()).getUserData());
		
		Iterator itemIte = ((ListView)ke.getSource()).getSelectionModel().getSelectedItems().iterator();
		int counter = 0;
		while(itemIte.hasNext()) {
			setListItem((ListItem) ((Node) itemIte.next()).getUserData());
			counter++;
		}
		if(counter > 1) {
			multipleSelected = true;
		}
	}

}

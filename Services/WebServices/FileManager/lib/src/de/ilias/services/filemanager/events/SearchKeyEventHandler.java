/*
 * Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE
 */
package de.ilias.services.filemanager.events;

import de.ilias.services.filemanager.action.ActionHandler;
import java.util.logging.Logger;
import javafx.event.Event;
import javafx.event.EventHandler;
import javafx.scene.control.TextField;
import javafx.scene.input.KeyCode;
import javafx.scene.input.KeyEvent;

/**
 * Class SearchKeyEventHandler
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
public class SearchKeyEventHandler implements EventHandler<KeyEvent> {
	
	protected static final Logger logger = Logger.getLogger(SearchKeyEventHandler.class.getName());

	/**
	 * Handle Key event
	 * @param t 
	 */
	public void handle(KeyEvent key) {
		
		// Do nothing if key is not released
		if(key.getEventType() != KeyEvent.KEY_RELEASED) {
			return;
		}
		// Handle return key down
		if(key.getCode() == KeyCode.ENTER) {
			logger.info("Starting search");
			ActionHandler.searchRemote(((TextField) key.getSource()).getText());
			key.consume();
		}
	}
}

/*
 * Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE
 */
package de.ilias.services.filemanager.events;

import de.ilias.services.filemanager.content.ListItem;
import de.ilias.services.filemanager.controller.MainController;
import java.util.logging.Logger;
import javafx.event.EventHandler;
import javafx.scene.Node;
import javafx.scene.input.MouseEvent;

/**
 * Class BreadcrumbMouseEventHandler
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
public class BreadcrumbMouseEventHandler implements EventHandler<MouseEvent> {

	protected static final Logger logger = Logger.getLogger(BreadcrumbMouseEventHandler.class.getName());

	public void handle(MouseEvent me) {
		
		if(me.getEventType() == MouseEvent.MOUSE_RELEASED) {
			MainController.getInstance().switchDirectory((ListItem) ((Node) me.getSource()).getUserData());
			me.consume();
		}
	}
	
}

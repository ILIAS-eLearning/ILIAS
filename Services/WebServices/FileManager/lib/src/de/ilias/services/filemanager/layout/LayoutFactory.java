/*
 * Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE
 */
package de.ilias.services.filemanager.layout;

import javafx.stage.Stage;

/**
 * Class LayoutFactory
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
public class LayoutFactory {
	
	private static LayoutMaster instance = null;
	
	public static LayoutMaster getInstance(Stage stage) {
		
		// Check if scene is running as applet
		if(instance != null) {
			return LayoutFactory.instance;
		}
		return LayoutFactory.instance = new LayoutMaster(stage);
	}
	
}

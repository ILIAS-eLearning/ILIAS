/*
 * Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE
 */
package de.ilias.services.filemanager.layout;

import de.ilias.services.filemanager.FileManager;
import java.util.logging.Logger;
import javafx.application.Platform;
import javafx.geometry.Rectangle2D;
import javafx.scene.input.MouseEvent;
import javafx.stage.Screen;

/**
 * Class WindowManager
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
public class WindowManager {
	
	private static final Logger logger = Logger.getLogger(WindowManager.class.getName());
	
	private static WindowManager instance = null;
	
	private boolean maximized = false;
	private Rectangle2D memSize = null;
	
	private double offsetX = 0;
	private double offsetY = 0;
	
	
	/**
	 * Private constructor
	 */
	private WindowManager() {
		
	}
	
	/**
	 * Get instance
	 * 
	 * @return WindowManager
	 */
	public static WindowManager getInstance() {
		
		if(WindowManager.instance != null) {
			return WindowManager.instance;
		}
		return WindowManager.instance = new WindowManager();
	}
	
	/**
	 * Store position offset
	 * @param event 
	 */
	public void storePosition(MouseEvent event) {
		
		offsetX = event.getSceneX();
		offsetY = event.getSceneY();
	}

	/**
	 * Move window
	 * @param event 
	 */
	public void move(MouseEvent event) {
		
		// Do not move maximzed windows
		if(!isMaximized()) {
		
			FileManager.getInstance().getStage().setX(
				event.getScreenX() - offsetX
			);
			FileManager.getInstance().getStage().setY(
					event.getScreenY() - offsetY
			);
		}
	}
	
	/**
	 * Close button pressed
	 */
	public void close() {
		Platform.exit();
	}
	
	/**
	 * Minimize application
	 */
	public void inconify() {

		FileManager.getInstance().getStage().setIconified(true);
	}
	
	public void toggle() {
		
		if(FileManager.getInstance().isApplet()) {
			return;
		}
		
		
		Screen screen = Screen.getScreensForRectangle(
				FileManager.getInstance().getStage().getX(),
				FileManager.getInstance().getStage().getY(),
				1,
				1).get(0);
		
		if(isMaximized()) {
			
			maximized = false;
			if(memSize != null) {
				FileManager.getInstance().getStage().setX(memSize.getMinX());
				FileManager.getInstance().getStage().setY(memSize.getMinY());
				FileManager.getInstance().getStage().setWidth(memSize.getWidth());
				FileManager.getInstance().getStage().setHeight(memSize.getHeight());
			}
		}
		else {
			
			maximized = true;
			memSize = new Rectangle2D(
					FileManager.getInstance().getStage().getX(),
					FileManager.getInstance().getStage().getY(),
					FileManager.getInstance().getStage().getWidth(),
					FileManager.getInstance().getStage().getHeight()
			);
			
			logger.finer("Width: " + memSize.getWidth());
			logger.finer("Height: " + memSize.getHeight());
			
			FileManager.getInstance().getStage().setX(screen.getVisualBounds().getMinX());
			FileManager.getInstance().getStage().setY(screen.getVisualBounds().getMinY());
			FileManager.getInstance().getStage().setWidth(screen.getVisualBounds().getWidth());
			FileManager.getInstance().getStage().setHeight(screen.getVisualBounds().getHeight());
		}
	}	
	
	/**
	 * Is screen maximized
	 * @return 
	 */
	public boolean isMaximized() {
		
		return maximized;
	}
	
}

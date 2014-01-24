/*
 * Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE
 */
package de.ilias.services.filemanager.skin;

import javafx.stage.Stage;

/**
 * Class SkinFactory
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
public class SkinFactory {
	
	public static final String DEFAULT_SKIN = SkinFactory.SKIN_ILIAS;
	
	public static final String SKIN_ILIAS = "ilias";
	public static final String SKIN_ENSEMBLE = "ensemble";
	
	public static String currentSkin = "";
	
	
	/**
	 * Load skin by name
	 * @param root
	 * @param skin 
	 */
	public static void loadSkin(Stage root, String skin) {
		
		System.out.println("Load Skin");
		if(skin.equals(SKIN_ENSEMBLE)) {
			
			currentSkin = skin;
			root.getScene().getStylesheets().add(SkinFactory.class.getResource("ensemble/ensemble.css").toExternalForm());
		}
		if(skin.equals(SKIN_ILIAS)) {
			currentSkin = skin;
			
			root.getScene().getStylesheets().add(SkinFactory.class.getResource("ilias/ilias.css").toExternalForm());
		}
		System.out.println("Current skin is " + currentSkin);
	}
	
	/**
	 * Load default skin
	 * @param root 
	 */
	public static void loadSkin(Stage root) {
		
		SkinFactory.loadSkin(root, DEFAULT_SKIN);
	}
	
	/**
	 * Switch skin
	 * @param root 
	 */
	public static void switchSkin(Stage root) {
		
		if(currentSkin.equals(SKIN_ILIAS)) {
			loadSkin(root, SKIN_ENSEMBLE);
		}
		else {
			loadSkin(root, SKIN_ILIAS);
		}
	}
}

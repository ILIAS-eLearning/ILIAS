/*
 * Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE
 */
package de.ilias.services.filemanager.layout;

import de.ilias.services.filemanager.FileManager;
import de.ilias.services.filemanager.content.*;
import de.ilias.services.filemanager.controller.MainController;
import de.ilias.services.filemanager.skin.SkinFactory;
import de.ilias.services.filemanager.soap.SoapClientConnectorException;
import java.io.File;
import java.io.IOException;
import java.util.logging.Level;
import java.util.logging.Logger;
import javafx.event.EventHandler;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.input.MouseEvent;
import javafx.scene.layout.StackPane;
import javafx.stage.Stage;
import javafx.stage.StageStyle;

/**
 * Class LayoutMaster
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
public class LayoutMaster {
	
	private static final Logger logger = Logger.getLogger(LayoutMaster.class.getName());
	
	private Stage stage = null;
	private boolean localFrameEnabled = false;

	/**
	 * Constructor
	 * @param stage 
	 */
	LayoutMaster(Stage stage) {
		
		this.stage = stage;
	}
	
	/**
	 * Check if local frame is enabled
	 */
	public boolean isLocalFrameEnabled() {
		return localFrameEnabled;
	}
	
	/**
	 * Set local frame enabled
	 * @param en 
	 */
	public void enableLocalFrame(boolean en) {
		this.localFrameEnabled = en;
	}
	
	public void init() throws IOException {
		
		Parent root = FXMLLoader.load(getClass().getResource("MainLayout.fxml"));
		root.setId("rootStack");
		
		stage.setScene(new Scene(root));
		stage.initStyle(StageStyle.DECORATED);
		SkinFactory.loadSkin(stage);
		
		ListItemReader reader;
		ContentFrameDirectoryStack dirStack = ContentFrameDirectoryStack.getInstance();
		ListItemReader listItemReader;
		ListViewItemParser parser;

		if(isLocalFrameEnabled()) {
			// ----------------- Left frame ----------------------------------------
			DirectoryStackItem home = new DirectoryStackItem();
			home.setType(DirectoryStackItem.TYPE_FILE);
			home.setFile(new File(System.getProperty("user.home")));
			logger.info("Starting with local directory: " + home.getFile().getAbsolutePath());
			dirStack.getLocalStack().add(home);

			// Read directory 
			reader = new ListItemReader();
			try {
				reader.read(ContentFrame.FRAME_LEFT);
			} 
			catch (SoapClientConnectorException ex) {
				logger.severe("Cannot read local content");
			}

			// Fill list view
			parser = new ListViewItemParser(ContentFrame.FRAME_LEFT);
			parser.setListItems(reader.getListItems());
			parser.sort();
			MainController.getInstance().populateLocalList(parser.parse());
		}
		else {
			// Resize split pane
			MainController.getInstance().switchOneFrame();
		}

		// ----------------- Right frame ---------------------------------------
		DirectoryStackItem repo = new DirectoryStackItem();
		repo.setType(DirectoryStackItem.TYPE_ID);
		repo.setId(FileManager.getInstance().getInitialRepositoryContainerId());
		logger.info("Starting with remote container: " + repo.getId());
		dirStack.getRemoteStack().add(repo);
		
		
		reader = new ListItemReader();
		try {
			reader.read(ContentFrame.FRAME_RIGHT);
		}
		catch (SoapClientConnectorException ex) {
			logger.severe("Cannot read remote content");
		}		
		parser = new ListViewItemParser(ContentFrame.FRAME_RIGHT);
		parser.setListItems(reader.getListItems());
		parser.sort();
		MainController.getInstance().populateRemoteList(parser.parse());
		MainController.getInstance().fillBreadcrumb(reader);
		MainController.getInstance().initModalDialog();
		
	}

	/**
	 * Show stage
	 */
	public void show() {
		stage.show();
	}			
}

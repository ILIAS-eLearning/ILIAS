/*
 * Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE
 */
package de.ilias.services.filemanager.controller;

import de.ilias.services.filemanager.FileManager;
import de.ilias.services.filemanager.content.*;
import de.ilias.services.filemanager.dialog.RenameDialog;
import de.ilias.services.filemanager.events.SearchKeyEventHandler;
import de.ilias.services.filemanager.layout.WindowManager;
import de.ilias.services.filemanager.skin.SkinFactory;
import de.ilias.services.filemanager.soap.SoapClientConnector;
import de.ilias.services.filemanager.soap.SoapClientConnectorException;
import de.ilias.services.filemanager.soap.api.SoapClientFile;
import de.ilias.services.filemanager.ui.BreadcrumbBuilder;
import java.awt.Desktop;
import java.io.File;
import java.net.URL;
import java.util.Iterator;
import java.util.ResourceBundle;
import java.util.logging.Logger;
import javafx.animation.Interpolator;
import javafx.animation.KeyFrame;
import javafx.animation.KeyValue;
import javafx.animation.TimelineBuilder;
import javafx.collections.ObservableList;
import javafx.event.ActionEvent;
import javafx.event.EventHandler;
import javafx.event.EventType;
import javafx.fxml.FXML;
import javafx.fxml.Initializable;
import javafx.scene.Node;
import javafx.scene.control.*;
import javafx.scene.input.MouseEvent;
import javafx.scene.layout.BorderPane;
import javafx.scene.layout.HBox;
import javafx.scene.layout.StackPane;
import javafx.scene.layout.VBox;
import javafx.util.Duration;

/**
 * Class Main Menu (loaded from mainMenu.fxml)
 *
 * Stefan Meyer <smeyer.ilias@gmx.de>
 */
public class MainController implements Initializable {

	private static MainController instance;
	
	protected static final Logger logger = Logger.getLogger(MainController.class.getName());
	
	private double windowDragOffsetX = 0;
	private double windowDragOffsetY = 0;
	
	@FXML
	private StackPane rootStack;
	
	@FXML
	private StackPane dialogStack;
	
	@FXML 
	private BorderPane root;

	@FXML
	private Label label;

	@FXML
	private VBox windowButtons;

	@FXML
	private SplitPane contentSplitPane;
	
	@FXML
	private ListView remoteList;
	
	@FXML
	private ListView localList;
	
	private ListView remoteSearchList;
	
	@FXML
	private BorderPane localFrame;
	
	@FXML
	private BorderPane remoteFrame;
	
	@FXML
	private TextField search;
	
	@FXML
	private TabPane remoteTabs;
	
	@FXML
	private Tab remoteRepositoryTab;
	
	@FXML
	private ListView remoteBreadcrumb;
	
	private Tab remoteSearchTab = new Tab("Search Results");
	
	@FXML
	private Label mainTitle;
	
	/**
	 * Get instance
	 * @return 
	 */
	public static MainController getInstance() {
		return instance;
	}
	
	/**
	 * Get root element
	 * @return 
	 */
	public BorderPane getRoot() {
		return root;
	}
	
	/**
	 * get remote list
	 * @return 
	 */
	public ListView getRemoteList() {
		return remoteList;
	}
	
	public ListView getLocalList() {
		return localList;
	}
	
	public ListView getRemoteSearchList() {
		return remoteSearchList;
	}
	
	
	/**
	 * Get list view for current node
	 * @param node
	 * @return 
	 */
	public ListView getList(ListItem item) {
		if(item instanceof LocalListItem) {
			return getLocalList();
		}
		// repository or search list depends on selection model
		if(this.getRemoteSearchTab().isSelected()) {
			return getRemoteSearchList();
		}
		return getRemoteList();
	}
	
	/**
	 * Get Search text field
	 * @return 
	 */
	public TextField getSearch() {
		return this.search;
	}
	
	
	public TabPane getRemoteTabPane() {
		return this.remoteTabs;
	}
	
	public Tab getRemoteRepositoryTab() {
		return remoteRepositoryTab;
	}
	
	public ListView getRemoteBreadcrumb() {
		return remoteBreadcrumb;
	}
	
	/**
	 * Get main title label
	 * @return 
	 */
	public Label getMainTitle() {
		return this.mainTitle;
	}
	
	/**
	 * Get search tab
	 * @return 
	 */
	public Tab getRemoteSearchTab() {
		return remoteSearchTab;
	}

	/**
	 * Debug
	 */
	@FXML
	private void handleButtonAction(ActionEvent event) {

		logger.fine("New skin!");
		SkinFactory.switchSkin(FileManager.getInstance().getStage());
	}

	//
	// Move main window triggered by mouse drag on top border pane
	// ...
	/**
	 * store x,y for window move
	 * @param event
	 */
	@FXML
	private void windowMoveInit(MouseEvent event) {

		WindowManager.getInstance().storePosition(event);
	}

	/**
	 * Move while mouse is dragged
	 * @param event 
	 */
	@FXML
	private void windowMove(MouseEvent event) {

		WindowManager.getInstance().move(event);
	}

	/**
	 * Toggle fullsize on double click
	 * @param event 
	 */
	@FXML
	private void windowToggleOnDoubleClick(MouseEvent event) {

		if (event.getClickCount() == 2) {

			WindowManager.getInstance().toggle();
		}
	}

	/**
	 * Close app
	 * @param event 
	 */
	@FXML
	private void windowClose(ActionEvent event) {

		WindowManager.getInstance().close();
	}

	/**
	 * Minimize window
	 * @param event 
	 */
	@FXML
	private void windowMinimize(ActionEvent event) {

		WindowManager.getInstance().inconify();
	}

	/**
	 * Toggle window
	 * @param event 
	 */
	@FXML
	private void windowToggle(ActionEvent event) {
		
		WindowManager.getInstance().toggle();
	}

	/**
	 * Init and modify elements
	 * @param url
	 * @param rb 
	 */
	@Override
	public void initialize(URL url, ResourceBundle rb) {

		System.out.println("Trying to disable vbox");
		
		// Create singleton instance
		instance = this;

		// Hide window buttons if context is applet
		if (FileManager.getInstance().isApplet()) {
			windowButtons.setVisible(false);
		}

		initializeContentSplitPane();
		initializeRemoteList();
		initializeLocalList();
		initializeSearch();
	}
	
	/**
	 * Configure split pane
	 */
	protected void initializeContentSplitPane() {
		
		// everyting done in MainLayout.fxml
	}
	
	/**
	 * Init remote list
	 */
	protected void initializeRemoteList() {
		
		remoteBreadcrumb.getItems().add(new Label());
		remoteBreadcrumb.setMinHeight(50);
		remoteBreadcrumb.setMaxHeight(80);
		remoteBreadcrumb.setEditable(false);
		remoteBreadcrumb.getSelectionModel().setSelectionMode(SelectionMode.SINGLE);
		remoteBreadcrumb.addEventFilter(
				MouseEvent.MOUSE_PRESSED,
				new EventHandler<MouseEvent>() {
					public void handle(MouseEvent me) {
						me.consume();
					}
				}
		);
		
		remoteList.getSelectionModel().setSelectionMode(SelectionMode.MULTIPLE);
		remoteList.setPrefHeight(1280);
		remoteList.setId("contentListView");
	}
	
	/**
	 * Init remote list
	 */
	protected void initializeLocalList() {
		localList.getSelectionModel().setSelectionMode(SelectionMode.MULTIPLE);
		localList.setId("contentListView");
	}
	
	/**
	 * Add key event handler
	 */
	protected void initializeSearch() {
		getSearch().setOnKeyReleased(new SearchKeyEventHandler());
	}

	public void populateRemoteList(ObservableList listItems) {
		remoteList.setItems(listItems);
	}
	
	public void populateLocalList(ObservableList listItems) {
		localList.setItems(listItems);
	}
	
	/**
	 * Fill search result list
	 * @param listItems 
	 */
	public void populateSearchList(ObservableList listItems) {
		
		// Add search tab
		this.remoteSearchTab.setClosable(true);
		
		this.remoteSearchList = new ListView();
		this.remoteSearchList.setItems(listItems);
		this.remoteSearchTab.setContent(this.remoteSearchList);

		if(getRemoteTabPane().getTabs().size() == 1) {
			getRemoteTabPane().getTabs().add(this.remoteSearchTab);
		}
		getRemoteTabPane().getSelectionModel().select(this.remoteSearchTab);
	}
		
	// List item events
	public void switchDirectory(ListItem item) {

		if(item instanceof LocalListItem) {

			logger.info("Switching to local node");
			DirectoryStackItem current = new DirectoryStackItem();
			
			if(item.getAbsolutePath() != null) {
				current.setType(DirectoryStackItem.TYPE_FILE);
				current.setFile(new File(item.getAbsolutePath()));
			}
			else {
				current.setType(DirectoryStackItem.TYPE_ROOTS);
			}
			ContentFrameDirectoryStack dirStack = ContentFrameDirectoryStack.getInstance();
			dirStack.getLocalStack().add(current);

			// Read directory 
			ListItemReader reader = new ListItemReader();
			try {
				reader.read(ContentFrame.FRAME_LEFT);
			} 
			catch (SoapClientConnectorException ex) {
				logger.severe("Cannote read content " + ex.getMessage());
			}
		
			// Fill list view
			ListViewItemParser parser = new ListViewItemParser(ContentFrame.FRAME_LEFT);
			parser.setListItems(reader.getListItems());
			parser.sort();
			MainController.getInstance().populateLocalList(parser.parse());
		}
		if(item instanceof RemoteListItem) {
			
			int targetId;
			
			// Target depends on list item type
			targetId = item.getRefId();
			
			logger.info("Switching to remote node with id " + item.getRefId());
			DirectoryStackItem current = new DirectoryStackItem();
			current.setType(DirectoryStackItem.TYPE_ID);
			current.setId(targetId);
			ContentFrameDirectoryStack dirStack = ContentFrameDirectoryStack.getInstance();
			dirStack.getRemoteStack().add(current);

			// Read directory 
			ListItemReader reader = new ListItemReader();
			try {
				reader.read(ContentFrame.FRAME_RIGHT);
			} 
			catch (SoapClientConnectorException ex) {
				logger.severe("Cannote read content " + ex.getMessage());
			}
		
			// Fill list view
			ListViewItemParser parser = new ListViewItemParser(ContentFrame.FRAME_RIGHT);
			parser.setListItems(reader.getListItems());
			parser.sort();
			populateRemoteList(parser.parse());
			fillBreadcrumb(reader);
			
			// Activate repository tab
			getRemoteTabPane().getSelectionModel().select(getRemoteRepositoryTab());
			
		}
	}
	
	/**
	 * Fill breadcrump
	 * @param reader 
	 */
	public void fillBreadcrumb(ListItemReader reader) {

		BreadcrumbBuilder builder = new BreadcrumbBuilder();
		HBox box = new HBox();
		if(reader.getObjects() != null) {
			box = builder.buildHBox(reader.getObjects().getFirstPath(true));
		}
		
		getRemoteBreadcrumb().getItems().remove(0, getRemoteBreadcrumb().getItems().size());
		getRemoteBreadcrumb().getItems().add(box);
	}
	
	/**
	 * Deliver remote item
	 * @param item 
	 */
	public void deliverRemoteItem(ListItem item) {
		
		if(item.getType().equals("file")) {

			File tmpFile;
			Desktop desktop;
			SoapClientFile fileXml;
			SoapClientConnector con = SoapClientConnector.getInstance();
			try {
				fileXml = con.getFileXML(item.getRefId());
				tmpFile = fileXml.writeToTempFile();
				desktop = Desktop.getDesktop();
				desktop.open(tmpFile);
			} 
			catch (SoapClientConnectorException ex) {
				ex.printStackTrace();
				logger.severe("Cannot deliver file " + ex.getMessage());
			}
			catch(Exception e) {
				e.printStackTrace();
				logger.severe("Cannot deliver file " + e.getMessage());
			}
		}
	}
	
	/** 
	 * add modal dialog
	 */
	public void initModalDialog() {

		dialogStack.setOnMouseClicked(
				new EventHandler<MouseEvent>() {
					public void handle(MouseEvent me) {
						me.consume();
						hideModalDialog();
					}
				}
		);
		dialogStack.setVisible(false);
	}
	
    /**
     * Show modal dialog
     */
    public void showModalDialog(Node dialog) {
		
		dialogStack.getChildren().add(dialog);
		
		dialogStack.setOpacity(0);
		dialogStack.setVisible(true);
			
        TimelineBuilder.create().keyFrames(
            new KeyFrame(Duration.millis(100),
                new EventHandler<ActionEvent>() {
                    public void handle(ActionEvent t) {
                        //dialogStack.setCache(false);
                    }
                },
                new KeyValue(dialogStack.opacityProperty(),1, Interpolator.EASE_BOTH)
        )).build().play();
    }
    
    /**
     * Hide visible dialogs
     */
    public void hideModalDialog() {
		
		TimelineBuilder.create().keyFrames(
            new KeyFrame(Duration.millis(100), 
                new EventHandler<ActionEvent>() {
                    public void handle(ActionEvent t) {
						
						// Drop only first node
						dialogStack.getChildren().remove(0);
						
						// if there is any children left do set the stack incisible
						if(dialogStack.getChildren().size() == 0)
							dialogStack.setVisible(false);
                    }
                },
                new KeyValue(dialogStack.opacityProperty(),0, Interpolator.EASE_BOTH)
        )).build().play();
		
    }
	
	/**
	 * Switch to on frame view
	 */
	public void switchOneFrame() {
		
		this.contentSplitPane.getItems().remove(this.localFrame);
	}

}

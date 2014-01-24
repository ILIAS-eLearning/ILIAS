/*
 * Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE
 */
package de.ilias.services.filemanager.dialog;

import de.ilias.services.filemanager.action.ActionHandler;
import de.ilias.services.filemanager.content.ListItem;
import de.ilias.services.filemanager.content.RemoteListItem;
import de.ilias.services.filemanager.controller.MainController;
import de.ilias.services.filemanager.soap.SoapClientConnector;
import de.ilias.services.filemanager.soap.api.SoapClientFile;
import de.ilias.services.filemanager.soap.api.SoapClientObject;
import de.ilias.services.filemanager.soap.api.SoapClientObjects;
import de.ilias.services.filemanager.utils.FileManagerUtils;
import java.io.File;
import java.util.*;
import java.util.logging.Logger;
import javafx.event.EventHandler;
import javafx.geometry.Pos;
import javafx.scene.control.Button;
import javafx.scene.control.CheckBox;
import javafx.scene.control.Label;
import javafx.scene.control.ListView;
import javafx.scene.image.Image;
import javafx.scene.image.ImageView;
import javafx.scene.input.Clipboard;
import javafx.scene.input.ClipboardContent;
import javafx.scene.input.MouseEvent;
import javafx.scene.layout.ColumnConstraints;
import javafx.scene.layout.GridPane;
import javafx.scene.layout.HBox;
import javafx.scene.layout.VBox;

/**
 * Class PasteConflictDialog
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
public class PasteConflictDialog extends VBox {
	
	private static final Logger logger = Logger.getLogger(PasteConflictDialog.class.getName());
	
	final private HashMap<ListItem, List<File>> conflictStack = new HashMap<ListItem, List<File>>();
	
	private List<File> files;
	private HashMap<File, SoapClientObject> conflictFiles;
	final private ListItem targetObject;
	private SoapClientObjects targetObjects;
	
	/**
	 * Constructor
	 * @param files
	 * @param conflictFiles
	 * @param targetObject
	 * @param targetObjects 
	 */
	public PasteConflictDialog(List<File> files, HashMap<File,SoapClientObject> conflictFiles, ListItem targetObject, SoapClientObjects targetObjects) {

		super();
		
		this.files = files;
		this.conflictFiles = conflictFiles;
		this.targetObject = targetObject;
		this.targetObjects = targetObjects;
		
		init();
	}
	
	/**
	 * Get conflict stack
	 * @return 
	 */
	public HashMap<ListItem, List<File>> getConflictStack() {
		return conflictStack;
	}
	
	/**
	 * Parse dialog content
	 */
	public void parse() {
		GridPane grid = new GridPane();
		grid.setHgap(10);
		grid.setVgap(12);
		
		
		// Add warning
		final Label warningLabel = new Label();
		warningLabel.setId("warning");
		warningLabel.setAlignment(Pos.CENTER);
		warningLabel.setVisible(true);
		warningLabel.setText("There are already file(s) with the same name at this location. Please choose one action.");
		getChildren().addAll(warningLabel);
				

		// Add item list
		grid.add(populateItemList(),0,1,2,1);
		
		// Add remember my decision for all subobjects
		final CheckBox checkboxNode = addSaveDecisionBox();
		grid.add(checkboxNode,0,2,2,1);

		// Cancel => close modal dialog
		Button cancel = new Button("Cancel");
		cancel.setOnMouseClicked(
				new EventHandler<MouseEvent>() {
					public void handle(MouseEvent me) {
						MainController.getInstance().hideModalDialog();
						me.consume();
					}
				}
			);
		
		Button create = new Button("Create New Versions");
		create.setOnMouseClicked(
				new EventHandler<MouseEvent>() {

					public void handle(MouseEvent me) {
						
						boolean replaceAllSubitems = false;
						
						if(checkboxNode.isVisible() && checkboxNode.isSelected()) {
							replaceAllSubitems = true;
						}
						
						// Replace conflict files
						for(Map.Entry<File, SoapClientObject> entry : conflictFiles.entrySet()) {
							
							if(!entry.getValue().isContainer()) {
								
								logger.info("Adding new file version for " + entry.getKey());

								SoapClientFile file = new SoapClientFile();
								file.setFilename(entry.getKey().getName());
								file.setTitle(entry.getKey().getName());
								file.getContent().setContentFile(entry.getKey());

								SoapClientConnector con = SoapClientConnector.getInstance(SoapClientConnector.FRAME_RIGHT);
								con.updateFile(file, entry.getValue().getRefId());

								// Remove file from files list
								files.remove(entry.getKey());
							}
						}
						// Replace conflict folders
						for(Map.Entry<File, SoapClientObject> entry : conflictFiles.entrySet()) {
							
							if(entry.getValue().isContainer()) {
								
								// Show new confirmation dialog if there is any conflict
								if(!replaceAllSubitems) {
									logger.info("Handling naming conflict");
									if(handleRemoteNamingConflict(entry.getKey(),entry.getValue()))
									{
										// Nothing special
									}
									files.remove(entry.getKey());
								}
								else {
									// Replace all recursive
								}
							}
						}
						
						// Copy all other files
						ActionHandler.copyFilesFromClipboardToRemote(targetObject, files, false);
						finishDialog(true);
						
						// Empty clipboard
						Clipboard clip = Clipboard.getSystemClipboard();
						ClipboardContent clipContent = new ClipboardContent();
						clipContent.clear();
					}
					
					/**
					 * Check and handle remote naming conflict for subcontainers
					 */
					private boolean handleRemoteNamingConflict(File source, SoapClientObject target) {
						
						logger.info("Handling source " + source.getPath());
						
						// Check if source has subitems
						File[] files = source.listFiles();
						List<File> subFiles = new ArrayList<File>();
						
						if(files == null) {
							return false;
						}
						for(File file : files) {
							if(file.getName().equals("..") || file.getName().equals(".")) {
								continue;
							}
							if(!file.isHidden()) {
								logger.info("Added " + file.getName());
								subFiles.add(file);
							}
						}
						
						if(subFiles.size() == 0) {
							return false;
						}
						
						RemoteListItem targetItem = new RemoteListItem();
						targetItem.setParent(targetObject);
						targetItem.setRefId(target.getRefId());
						targetItem.setType(target.getType());
						targetItem.setTitle(target.getTitle());
						targetItem.setWritable(target.isWritable());
						targetItem.setReadable(target.isReadable());
						targetItem.setContainer(target.isContainer());
						
						if(ActionHandler.handleRemoteNamingConflict(targetItem, subFiles, false)) {
							logger.info("Adding to stack");
							conflictStack.put(targetItem, subFiles);
							return true;
						}
						return false;
					}
				}
			);
		
		Button replace = new Button("Create New Copy");
		replace.setOnMouseClicked(
				new EventHandler<MouseEvent>() {
					public void handle(MouseEvent me) {
						
						// Replace conflict files
						for(Map.Entry<File, SoapClientObject> entry : conflictFiles.entrySet()) {
							
							String newName = targetObjects.createUniqueName(entry.getKey());
							
							if(!entry.getValue().isContainer()) {
								
								logger.info("Adding new file version for " + entry.getKey());
								SoapClientFile file = new SoapClientFile();
								file.setFilename(newName);
								file.setTitle(newName);
								file.getContent().setContentFile(entry.getKey());

								SoapClientConnector con = SoapClientConnector.getInstance(SoapClientConnector.FRAME_RIGHT);
								con.addFile(file,targetObject.getRefId());

								// Remove file from files list
								files.remove(entry.getKey());
							}
							else {
								String newType = "cat";
								int newRef;
								// Add container with copy name
								if(targetObject.getType().equals("crs") || targetObject.getType().equals("grp")) {
									newType = "fold";
								}
								SoapClientObjects newObjs = new SoapClientObjects();
								SoapClientObject newObject = new SoapClientObject();
								newObject.setType(newType);
								newObject.setTitle(newName);
								newObjs.getObjects().add(newObject);
								
								SoapClientConnector con = SoapClientConnector.getInstance(SoapClientConnector.FRAME_RIGHT);
								newRef = con.addObject(newObjs, targetObject.getRefId());

								// Copy all subitems
								ListItem newTarget = new RemoteListItem();
								newTarget.setRefId(newRef);
								newTarget.setTitle(newName);
								newTarget.setType(newType);
								newTarget.setContainer(true);
								newTarget.setParent(targetObject);
								ActionHandler.copyFilesFromClipboardToRemote(newTarget, Arrays.asList(entry.getKey().listFiles()), false);
								files.remove(entry.getKey());
							}
						}
						ActionHandler.copyFilesFromClipboardToRemote(targetObject, files, true);
						finishDialog(true);
					}
				}
			);

		HBox buttonBox = new HBox();
		buttonBox.setSpacing(10.0);
		buttonBox.setAlignment(Pos.BOTTOM_RIGHT);
		buttonBox.getChildren().addAll(cancel,create,replace);

		// Add to grid
		grid.add(buttonBox,0,3,2,1);

		// Add all to grid
		this.getChildren().add(grid);
	}
	
	/**
	 * save decision
	 */
	private CheckBox addSaveDecisionBox() {
		
		boolean hasContainerConflict = false;
		CheckBox box = null;
		
		for(Map.Entry<File, SoapClientObject> entry : conflictFiles.entrySet()) {
			if(entry.getValue().isContainer()) {
				hasContainerConflict = true;
			}
		}
		
		box = new CheckBox("Remember my decision for all subitems.");
		box.setIndeterminate(false);
		box.setSelected(true);
		
		if(!hasContainerConflict || true) {
			box.setVisible(false);
		}
		return box;
	}
	
	/**
	 * Finish dialog
	 */
	private void finishDialog(boolean redirect) {
		
		MainController.getInstance().hideModalDialog();
		
		if(redirect) {
			if(targetObject.isContainer()) {
				MainController.getInstance().switchDirectory(targetObject);
			}
			else {
				MainController.getInstance().switchDirectory(targetObject.getParent());
			}
		}
		
		for(Map.Entry<ListItem,List<File>> entry : getConflictStack().entrySet()) {
			ActionHandler.handleRemoteNamingConflict(entry.getKey(), entry.getValue(),true);
		}
	}
	
	/**
	 * Show conffirm items
	 * @return 
	 */
	private ListView populateItemList() {
		
		ListView listView = new ListView();
		listView.setPrefSize(600, 200);
		
		ColumnConstraints colImage;
		ColumnConstraints colTitle;
		
		// Column constraints
		colImage = new ColumnConstraints(16,16,16);
		colTitle = new ColumnConstraints(100,300,Double.MAX_VALUE);
		
		Label description;

		for(Map.Entry<File, SoapClientObject> entry : conflictFiles.entrySet()) {
		
			GridPane grid = new GridPane();
			
			grid.setVgap(2);
			grid.setHgap(20);

			grid.getColumnConstraints().addAll(
					colImage,
					colTitle
				);
			
			// Type image
			Image img = FileManagerUtils.getImageByType(entry.getValue().getType());
			ImageView view = new ImageView(img);
			view.setPreserveRatio(true);
			grid.add(view,0,0);

			// Title
			Label title = new Label(entry.getValue().getTitle());
			title.setId("listText");
			grid.add(title,1,0);

			if(entry.getValue().getDescription().length() > 0) {
				description = new Label(entry.getValue().getDescription());
				description.setId("listTextDescription");
				description.setWrapText(false);
				grid.add(description,1,1,4,1);
			}

			listView.getItems().add(grid);
		}
		return listView;
	}
	
	
	/**
	 * Init dialog
	 */
	private void init() {
		
		setId("ProxyDialog");
		setAlignment(Pos.CENTER);
		setSpacing(5);
		setMaxSize(600, USE_PREF_SIZE);
		
		// do not react on mouse clicks
		setOnMouseClicked(new EventHandler<MouseEvent>() {
            public void handle(MouseEvent t) {
                t.consume();
            }
        });		
		
		Label title = null;
		
		if(!targetObject.getTitle().equals("..")) {
			title = new Label("Copy Files to \"" + targetObject.getTitle() + "\"");
		}
		else {
			title = new Label("Copy Files");
		}
        title.setId("title");
        title.setMinHeight(22);
        title.setPrefHeight(22);
        title.setMaxWidth(Double.MAX_VALUE);
        title.setAlignment(Pos.CENTER);
		getChildren().addAll(title);
	}
}

/*
 * Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE
 */
package de.ilias.services.filemanager.dialog;

import de.ilias.services.filemanager.content.ListItem;
import de.ilias.services.filemanager.content.LocalListItem;
import de.ilias.services.filemanager.content.RemoteListItem;
import de.ilias.services.filemanager.controller.MainController;
import de.ilias.services.filemanager.events.ListItemContextMenuEventHandler;
import de.ilias.services.filemanager.soap.SoapClientConnector;
import de.ilias.services.filemanager.soap.api.SoapClientObject;
import de.ilias.services.filemanager.soap.api.SoapClientObjects;
import de.ilias.services.filemanager.utils.FileManagerUtils;
import java.util.logging.Logger;
import javafx.event.EventHandler;
import javafx.geometry.Pos;
import javafx.scene.control.*;
import javafx.scene.input.MouseEvent;
import javafx.scene.layout.GridPane;
import javafx.scene.layout.HBox;
import javafx.scene.layout.VBox;

/**
 * Class RenameDialog
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
public class CreateDialog extends VBox {
	
	protected static final Logger logger = Logger.getLogger(RenameDialog.class.getName());
	
	private ListItem item = null;
	private int type;
	
	/**
	 * Constructor
	 */
	public CreateDialog(ListItem item, int type) {
		
		super();
		
		this.item = item;
		this.type = type;
		init();
	}
	
	/**
	 * Get new object type
	 * @return 
	 */
	public int getType() {
		return this.type;
	}
	
	/**
	 * Get list item
	 * @return 
	 */
	public ListItem getListItem() {
		return this.item;
	}
	
	public void parse() {
		
		GridPane grid = new GridPane();
		grid.setHgap(10);
		grid.setVgap(12);
		
		// Title
		Label titleLabel = new Label("Title");
		final TextField title = TextFieldBuilder.create().
				prefColumnCount(40).
				editable(true).
				build();
		
		// Add warning
		final Label warningLabel = new Label();
		warningLabel.setId("warning");
		warningLabel.setAlignment(Pos.CENTER);
		warningLabel.setVisible(false);
		getChildren().addAll(warningLabel);
				

		Label descriptionLabel = null;
		final TextArea description = TextAreaBuilder.create().build();
		
		// Description
		if(getListItem() instanceof RemoteListItem) {
			
			descriptionLabel = new Label("Description");
			
			String descriptionValue = " ";
			description.setText(descriptionValue);
			description.setEditable(true);
			description.setPrefColumnCount(40);
			description.setPrefRowCount(6);
		}

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
		
		Button submit = new Button("Save");
		submit.setOnMouseClicked(
				new EventHandler<MouseEvent>() {
					
					private boolean eventHandled = false;
					
					public void handle(MouseEvent me) {
						
						// Check for empty title
						if(title.getText().trim().length() <= 0) {
							warningLabel.setText("Please a enter a title.");
							warningLabel.setVisible(true);
							return;
						}

						if(eventHandled) {
							me.consume();
							return;
						}
						eventHandled = true;
						

						if(getListItem() instanceof LocalListItem) {

							// @todo create local folder
							/*
							File orig = new File(getListItem().getAbsolutePath());
							String parent = orig.getParent();

							File renamed = new File(parent + File.separator + title.getText());
							if(!orig.renameTo(renamed)) {
								warningLabel.setVisible(true);
								return;
							}
							*/
						}
						if(getListItem() instanceof RemoteListItem) {

							// @todo add remote object
							SoapClientObjects objs = new SoapClientObjects();
							SoapClientObject obj = new SoapClientObject();
							obj.setType(FileManagerUtils.createTypeToILIASType(getType()));
							obj.setTitle(title.getText());
							obj.setDescription(description.getText());
							objs.getObjects().add(obj);
							
							SoapClientConnector con = SoapClientConnector.getInstance(SoapClientConnector.FRAME_RIGHT);
							if(con.addObject(objs, getListItem().getRefId()) <= 0) {
								warningLabel.setText("Could not create object. Please check your input.");
								warningLabel.setVisible(true);
								return;
							}
						}
						MainController.getInstance().switchDirectory(getListItem());
						MainController.getInstance().hideModalDialog();
						me.consume();
					}
				}
			);
		
		HBox buttonBox = new HBox();
		buttonBox.setSpacing(10.0);
		buttonBox.setAlignment(Pos.BOTTOM_RIGHT);
		buttonBox.getChildren().addAll(cancel,submit);
		
		// Add all to grid
		grid.add(titleLabel, 0, 0);
		grid.add(title, 1, 0);
		
		if(getListItem() instanceof RemoteListItem) {
			grid.add(descriptionLabel, 0, 1);
			grid.add(description,1,1);
		}
		
		grid.add(buttonBox,0,2,2,1);
		
		this.getChildren().add(grid);
	}
	
	
	/**
	 * Init box
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
		

		Label title = new Label();
		switch(this.type) {
			case ListItemContextMenuEventHandler.ACTION_CREATE_CAT:
				title.setText("Create New Category");
				break;
			case ListItemContextMenuEventHandler.ACTION_CREATE_CRS:
				title.setText("Create New Course");
				break;
			case ListItemContextMenuEventHandler.ACTION_CREATE_GRP:
				title.setText("Create New Group");
				break;
			case ListItemContextMenuEventHandler.ACTION_CREATE_FOLD:
				title.setText("Create New Folder");
				break;
		}
        title.setId("title");
        title.setMinHeight(22);
        title.setPrefHeight(22);
        title.setMaxWidth(Double.MAX_VALUE);
        title.setAlignment(Pos.CENTER);
		
		getChildren().addAll(title);
	}
}

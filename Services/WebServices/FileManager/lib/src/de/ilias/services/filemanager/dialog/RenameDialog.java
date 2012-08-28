/*
 * Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE
 */
package de.ilias.services.filemanager.dialog;

import de.ilias.services.filemanager.content.ListItem;
import de.ilias.services.filemanager.content.LocalListItem;
import de.ilias.services.filemanager.content.RemoteListItem;
import de.ilias.services.filemanager.controller.MainController;
import de.ilias.services.filemanager.soap.SoapClientConnector;
import de.ilias.services.filemanager.soap.api.SoapClientObject;
import de.ilias.services.filemanager.soap.api.SoapClientObjects;
import java.io.File;
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
public class RenameDialog extends VBox {
	
	protected static final Logger logger = Logger.getLogger(RenameDialog.class.getName());
	
	private ListItem item = null;
	
	/**
	 * Constructor
	 */
	public RenameDialog(ListItem item) {
		
		super();
		
		this.item = item;
		init();
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
				text(getListItem().getTitle()).
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
			
			String descriptionValue = this.getListItem().getDescription();
			if(descriptionValue.length() <= 0) {
				descriptionValue = " ";
			}
			
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
					
					public synchronized void handle(MouseEvent me) {
						
						// Check for empty title
						if(title.getText().trim().length() <= 0) {
							warningLabel.setText("Please a enter a title.");
							warningLabel.setVisible(true);
							return;
						}
						
						if(getListItem() instanceof LocalListItem) {

							File orig = new File(getListItem().getAbsolutePath());
							String parent = orig.getParent();

							File renamed = new File(parent + File.separator + title.getText());
							if(!orig.renameTo(renamed)) {
								warningLabel.setVisible(true);
								return;
							}
						}
						if(getListItem() instanceof RemoteListItem) {

							SoapClientObjects objs = new SoapClientObjects();
							SoapClientObject obj = new SoapClientObject();
							obj.setType(getListItem().getType());
							obj.setObjId(getListItem().getObjId());
							obj.setTitle(title.getText());
							obj.setDescription(description.getText());
							objs.getObjects().add(obj);
							
							SoapClientConnector con = SoapClientConnector.getInstance(SoapClientConnector.FRAME_RIGHT);
							if(!con.updateObjects(objs)) {
								warningLabel.setText("The settings could not be saved. Please check your input.");
								warningLabel.setVisible(true);
								return;
							}
						
						}
						MainController.getInstance().switchDirectory(getListItem().getParent());
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
		
		Label title = new Label("Update Title");
        title.setId("title");
        title.setMinHeight(22);
        title.setPrefHeight(22);
        title.setMaxWidth(Double.MAX_VALUE);
        title.setAlignment(Pos.CENTER);
		
		getChildren().addAll(title);
	}
}

/*
 * Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE
 */
package de.ilias.services.filemanager.dialog;

import de.ilias.services.filemanager.FileManager;
import de.ilias.services.filemanager.action.ActionHandler;
import de.ilias.services.filemanager.content.ListItem;
import de.ilias.services.filemanager.controller.MainController;
import de.ilias.services.filemanager.utils.FileManagerUtils;
import java.io.File;
import java.util.ArrayList;
import java.util.Iterator;
import javafx.collections.ObservableList;
import javafx.event.EventHandler;
import javafx.geometry.Pos;
import javafx.scene.Node;
import javafx.scene.control.Button;
import javafx.scene.control.Label;
import javafx.scene.control.ListView;
import javafx.scene.image.Image;
import javafx.scene.image.ImageView;
import javafx.scene.input.MouseEvent;
import javafx.scene.layout.ColumnConstraints;
import javafx.scene.layout.GridPane;
import javafx.scene.layout.HBox;
import javafx.scene.layout.VBox;


/**
 * Class RenameDialog
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
public class UploadLimitConflictDialog extends VBox {

	
	private ArrayList<File> errorFiles = new ArrayList<File>();

	
	/**
	 * Constructor
	 */
	public UploadLimitConflictDialog(ArrayList<File> files) {
		
		super();
		this.errorFiles = files;
		init();
	}
	
	
	public void parse() {
		
		GridPane grid = new GridPane();
		grid.setHgap(10);
		grid.setVgap(12);
		
		
		// Add warning
		final Label warningLabel = new Label();
		warningLabel.setId("warning");
		warningLabel.setAlignment(Pos.CENTER);
		warningLabel.setVisible(true);
		warningLabel.setText("Could not copy the following files, since the upload limit of " + String.valueOf(FileManager.getInstance().getUploadFilesize()) + " Mib has been reached");
		getChildren().addAll(warningLabel);
				
		
		grid.add(populateFileList(),0,1,2,1);
		
		this.getChildren().add(grid);
	}
	
	/**
	 * Show conffirm items
	 * @return 
	 */
	private ListView populateFileList() {
		
		ListView listView = new ListView();
		listView.setPrefSize(600, 200);
		
		ColumnConstraints colImage;
		ColumnConstraints colTitle;
		
		// Column constraints
		colImage = new ColumnConstraints(16,16,16);
		colTitle = new ColumnConstraints(100,300,Double.MAX_VALUE);
		
		Iterator ite = errorFiles.iterator();
		Label description;
		
		while(ite.hasNext()) {
			
			File item = (File) ite.next();
						
			GridPane grid = new GridPane();
			
			grid.setVgap(2);
			grid.setHgap(20);

			grid.getColumnConstraints().addAll(
					colImage,
					colTitle
				);
			
			// Type image
			Image img = FileManagerUtils.getImageByType("file");
			ImageView view = new ImageView(img);
			view.setPreserveRatio(true);
			grid.add(view,0,0);

			// Title
			Label title = new Label(item.getName());
			title.setId("listText");
			grid.add(title,1,0);
			listView.getItems().add(grid);
		}
		return listView;
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
		
		Label title = new Label("Upload Limit Reached");
        title.setId("title");
        title.setMinHeight(22);
        title.setPrefHeight(22);
        title.setMaxWidth(Double.MAX_VALUE);
        title.setAlignment(Pos.CENTER);
		
		getChildren().addAll(title);
	}
}

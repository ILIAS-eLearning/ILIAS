/*
 * Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE
 */
package de.ilias.services.filemanager.dialog;

import de.ilias.services.filemanager.action.ActionHandler;
import de.ilias.services.filemanager.content.ListItem;
import de.ilias.services.filemanager.controller.MainController;
import de.ilias.services.filemanager.events.ListItemDragEventHandler;
import de.ilias.services.filemanager.events.ListItemKeyEventHandler;
import de.ilias.services.filemanager.events.ListItemMouseEventHandler;
import de.ilias.services.filemanager.utils.FileManagerUtils;
import java.text.DateFormat;
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
public class DeleteDialog extends VBox {
	

	private ListItem item = null;
	
	/**
	 * Constructor
	 */
	public DeleteDialog(ListItem item) {
		
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
		
		
		// Add warning
		final Label warningLabel = new Label();
		warningLabel.setId("warning");
		warningLabel.setAlignment(Pos.CENTER);
		warningLabel.setVisible(true);
		warningLabel.setText("Do you really want to delete the following files and folders?");
		getChildren().addAll(warningLabel);
				


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
		
		Button submit = new Button("Delete");
		submit.setOnMouseClicked(
				new EventHandler<MouseEvent>() {
					public void handle(MouseEvent me) {
						ObservableList selectedItems = MainController.getInstance().getList(getListItem()).
													   getSelectionModel().
													   getSelectedItems();
						ArrayList<ListItem> items = new ArrayList<ListItem>();
						Iterator selected = selectedItems.iterator();
						while(selected.hasNext()) {
							items.add((ListItem) ((Node) selected.next()).getUserData());
						}
						
						if(ActionHandler.deleteItems(items)) {
							// Do something
						}
						MainController.getInstance().hideModalDialog();
						MainController.getInstance().switchDirectory(getListItem().getParent());
					}
				}
			);
		
		HBox buttonBox = new HBox();
		buttonBox.setSpacing(10.0);
		buttonBox.setAlignment(Pos.BOTTOM_RIGHT);
		buttonBox.getChildren().addAll(cancel,submit);
		
		grid.add(populateItemList(),0,1,2,1);
		
		// Add all to grid
		grid.add(buttonBox,0,2,2,1);
		
		this.getChildren().add(grid);
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
		
		Iterator ite = MainController.getInstance().getList(getListItem()).
				getSelectionModel().
				getSelectedItems().
				iterator();
		
		Label description;
		
		while(ite.hasNext()) {
			
			ListItem item = (ListItem) ((Node) ite.next()).getUserData();
						
			GridPane grid = new GridPane();
			
			grid.setVgap(2);
			grid.setHgap(20);

			grid.getColumnConstraints().addAll(
					colImage,
					colTitle
				);
			
			// Type image
			Image img = FileManagerUtils.getImageByType(item.getType());
			ImageView view = new ImageView(img);
			view.setPreserveRatio(true);
			grid.add(view,0,0);

			// Title
			Label title = new Label(item.getTitle());
			title.setId("listText");
			grid.add(title,1,0);

			if(item.getDescription().length() > 0) {
				description = new Label(item.getDescription());
				description.setId("listTextDescription");
				description.setWrapText(false);
				grid.add(description,1,1,4,1);
			}

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
		
		Label title = new Label("Delete Files");
        title.setId("title");
        title.setMinHeight(22);
        title.setPrefHeight(22);
        title.setMaxWidth(Double.MAX_VALUE);
        title.setAlignment(Pos.CENTER);
		
		getChildren().addAll(title);
	}
}

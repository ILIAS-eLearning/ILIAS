/*
 * Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE
 */
package de.ilias.services.filemanager.content;

import de.ilias.services.filemanager.controller.MainController;
import de.ilias.services.filemanager.events.*;
import de.ilias.services.filemanager.utils.FileManagerUtils;
import java.text.DateFormat;
import java.util.ArrayList;
import java.util.Collections;
import java.util.Iterator;
import java.util.logging.Logger;
import javafx.collections.FXCollections;
import javafx.collections.ObservableList;
import javafx.geometry.HPos;
import javafx.scene.control.Hyperlink;
import javafx.scene.control.Label;
import javafx.scene.control.ListView;
import javafx.scene.image.Image;
import javafx.scene.image.ImageView;
import javafx.scene.layout.ColumnConstraints;
import javafx.scene.layout.GridPane;
import javafx.scene.layout.Priority;
import javafx.scene.text.Text;

/**
 * Class ListViewItemParser
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
public class ListViewItemParser {
	
	protected static final Logger logger = Logger.getLogger(ListViewItemParser.class.getName());
	
	private int currentFrame;
	private GridPane grid;
	private ArrayList<ListItem> items;

	/**
	 * Constructor
	 * @param frame 
	 */
	public ListViewItemParser(int frame) {
		
		currentFrame = frame;
	}
	
	/**
	 * set list items
	 * @param items 
	 */
	public void setListItems(ArrayList<ListItem> items) {
		this.items = items;
	}
	
	/**
	 * Sort the list items
	 * This is not done after searching since the relavence order should be kept.
	 */
	public void sort() {
		Collections.sort(getListItems());
	}
	
	/**
	 * Get list items
	 * @return 
	 */
	public ArrayList<ListItem> getListItems() {
		return items;
	}
	
	/**
	 * Parse items
	 * @return 
	 */
	public ObservableList parse() {
		
		if(currentFrame == ContentFrame.FRAME_LEFT) {
			return parseLocalList();
		}
		else {
			return parseRemoteList();
		}
	}
	
	/**
	 * Parse local list
	 * @return 
	 */
	private ObservableList parseLocalList() {

		ObservableList listItems = FXCollections.observableArrayList();
		
		Image img;
		ImageView view;
		Label title;
		Label type;
		Label size;
		Label modified;
		Label perm;
		Label description;
		
		ColumnConstraints colImage;
		ColumnConstraints colTitle;
		ColumnConstraints colType;
		ColumnConstraints colSize;
		
		// Column constraints
		colImage = new ColumnConstraints(16,16,16);
		colTitle = new ColumnConstraints(100,100,Double.MAX_VALUE);
		colTitle.setHgrow(Priority.ALWAYS);
		
		colType = new ColumnConstraints(50,50,100);
		colSize = new ColumnConstraints(75,75,100);
		colSize.setHalignment(HPos.RIGHT);
		
		Iterator ite = getListItems().iterator();
		
		int counter = 0;
		while(ite.hasNext()) {
			
			ListItem item = (ListItem) ite.next();
			
			if(counter++ == 0) {
				addListViewHandlers(item);
			}
			
			
			
			grid = new GridPane();
			grid.setUserData(item);
			
			ListItemMouseEventHandler mouseEventHandler = new ListItemMouseEventHandler(item, grid);
			ListItemDragEventHandler dragEventHandler = new ListItemDragEventHandler(item);
			
			// Mouse events
			grid.setOnMouseClicked(mouseEventHandler);
			grid.setOnMousePressed(mouseEventHandler);
			grid.setOnDragDetected(mouseEventHandler);
			grid.setOnMouseReleased(mouseEventHandler);

			
			// Drag events
			grid.setOnDragDropped(dragEventHandler);
			grid.setOnDragEntered(dragEventHandler);
			grid.setOnDragExited(dragEventHandler);
			grid.setOnDragOver(dragEventHandler);
			grid.setOnDragDone(dragEventHandler);
			
			grid.setVgap(2);
			grid.setHgap(20);

			grid.getColumnConstraints().addAll(
					colImage,
					colTitle,
					colType,
					colSize
				);
			
			// Type image
			img = FileManagerUtils.getImageByType(item.getType());
			view = new ImageView(img);
			view.setPreserveRatio(true);
			grid.add(view,0,0);

			// Title
			Hyperlink titleLink;
			if(item.isReadable() && false) {
				titleLink = new Hyperlink(item.getTitle());
				grid.add(titleLink,1,0);
			}
			else {
				title = new Label(item.getTitle());
				title.setId("listText");
				grid.add(title,1,0);
			}
			
			// File type
			type = new Label(item.getFileType());
			type.setId("listText");
			grid.add(type,2,0);
			

			// Size info
			size = new Label(item.getReadableFileSize());
			size.setId("listText");
			grid.add(size,3,0);

			modified = new Label();
			if(item.getLastUpdate() != null) {
				modified.setText(DateFormat.getDateTimeInstance(DateFormat.MEDIUM, DateFormat.SHORT).format(item.getLastUpdate()));
			}
			modified.setId("listText");
			grid.add(modified,4,0);

			if(item.getDescription().length() > 0) {
				description = new Label(item.getDescription());
				description.setId("listTextDescription");
				description.setWrapText(false);
				grid.add(description,1,1,4,1);
			}

			listItems.add(grid);
		}
		return listItems;
	}
	
	/**
	 * Parse local list
	 * @return 
	 */
	private ObservableList parseRemoteList() {

		ObservableList listItems = FXCollections.observableArrayList();
		
		Image img;
		ImageView view;
		Text title;
		Label size;
		Label modified;
		Label perm;
		Label description;
		
		ColumnConstraints colImage;
		ColumnConstraints colTitle;
		ColumnConstraints colType;
		ColumnConstraints colSize;
		
		// Column constraints
		colImage = new ColumnConstraints(16,16,16);
		colTitle = new ColumnConstraints(100,200,Double.MAX_VALUE);
		colTitle.setHgrow(Priority.ALWAYS);
		
		colType = new ColumnConstraints(30,30,100);
		colSize = new ColumnConstraints(30,60,100);
		colSize.setHalignment(HPos.RIGHT);

		Iterator ite = getListItems().iterator();
		
		int counter = 0;
		while(ite.hasNext()) {
			
			ListItem item = (ListItem) ite.next();
			
			if(counter++ == 0) {
				if(item.isUpperLink()) {
					addListViewHandlers(item);
				}
				else {
					addListViewHandlers(item.getParent());
				}
			}
			
			grid = new GridPane();
			grid.setId("listViewGrid");
			grid.setUserData(item);
			
			ListItemMouseEventHandler mouseEventHandler = new ListItemMouseEventHandler(item, grid);
			ListItemDragEventHandler dragEventHandler = new ListItemDragEventHandler(item);
			
			grid.setOnMouseClicked(mouseEventHandler);
			grid.setOnMousePressed(mouseEventHandler);
			grid.setOnDragDetected(mouseEventHandler);
			
			// Drag events
			grid.setOnDragDropped(dragEventHandler);
			grid.setOnDragEntered(dragEventHandler);
			grid.setOnDragExited(dragEventHandler);
			grid.setOnDragOver(dragEventHandler);
			grid.setOnDragDone(dragEventHandler);

			grid.setVgap(2);
			grid.setHgap(20);

			grid.getColumnConstraints().addAll(
					colImage,
					colTitle,
					colType,
					colSize
				);
			
			// Type image
			img = FileManagerUtils.getImageByType(item.getType());
			view = new ImageView(img);
			view.setPreserveRatio(true);
			grid.add(view,0,0);

			// Title
			Hyperlink titleLink;
			if(item.isReadable() && false) {
				titleLink = new Hyperlink(item.getTitle());
				grid.add(titleLink,1,0);
			}
			else {
				title = new Text(item.getTitle());
				title.setId("listText");
				grid.add(title,1,0);
			}

			// File type
			title = new Text(item.getFileType());
			title.setId("listText");
			grid.add(title,2,0);

			// Size info
			size = new Label(item.getReadableFileSize());
			size.setId("listText");
			grid.add(size,3,0);

			modified = new Label();
			if(item.getLastUpdate() != null) {
				modified.setText(DateFormat.getDateTimeInstance(DateFormat.MEDIUM, DateFormat.SHORT).format(item.getLastUpdate()));
			}
			modified.setId("listText");
			grid.add(modified,4,0);

			// Permissions
			perm = new Label(item.getPermissions());
			perm.setId("listText");
			grid.add(perm,5,0);


			if(item.getDescription().length() > 0) {
				description = new Label(item.getDescription());
				description.setId("listTextDescription");
				description.setWrapText(false);
				grid.add(description,1,1,3,1);
			}
			
			if(item.getProperties().size() > 0) {
				int row = 2;
				for(ListItemProperty prop : item.getProperties()) {
					
					if(prop.toString().length() > 0) {
						Label propT = new Label(prop.toString());
						propT.setId("listTextProperty");
						propT.setWrapText(false);
						grid.add(propT,1,row,3,1);
						row++;
					}
					
				}
			}
			
			listItems.add(grid);
		}
		return listItems;
	}
	
	/**
	 * add list view handler
	 * @param item 
	 */
	protected void addListViewHandlers(ListItem item) {

		// Mouse right
		ListView view = MainController.getInstance().getList(item);
		view.setUserData(item);
		view.setOnMousePressed(new ListItemMouseEventHandler(item, view));
		
		ListItemKeyEventHandler keyEventHandler = new ListItemKeyEventHandler(item);
			
		view.setOnKeyPressed(keyEventHandler);
		view.setOnKeyReleased(keyEventHandler);
		view.setOnKeyTyped(keyEventHandler);
		
		// Drag events
		ListItemDragEventHandler dragEventHandler = new ListItemDragEventHandler(item);
		view.setOnDragDropped(dragEventHandler);
		view.setOnDragEntered(dragEventHandler);
		view.setOnDragExited(dragEventHandler);
		view.setOnDragOver(dragEventHandler);
		view.setOnDragDone(dragEventHandler);
	}
}

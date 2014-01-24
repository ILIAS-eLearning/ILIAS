/*
 * Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE
 */
package de.ilias.services.filemanager.dialog;

import de.ilias.services.filemanager.content.ListItem;
import de.ilias.services.filemanager.content.LocalListItem;
import de.ilias.services.filemanager.content.RemoteListItem;
import de.ilias.services.filemanager.controller.MainController;
import de.ilias.services.filemanager.soap.SoapClientConnector;
import de.ilias.services.filemanager.soap.SoapClientConnectorException;
import de.ilias.services.filemanager.soap.api.*;
import de.ilias.services.filemanager.utils.FileManagerUtils;
import java.io.File;
import java.util.logging.Level;
import java.util.logging.Logger;
import javafx.beans.value.ChangeListener;
import javafx.beans.value.ObservableValue;
import javafx.event.EventHandler;
import javafx.geometry.Pos;
import javafx.scene.control.*;
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
public class RevisionStateDialog extends VBox {
	
	protected static final int PARSE_DAYS = 1;
	protected static final int PARSE_HOURS = 2;
	protected static final int PARSE_MINUTES = 3;
	
	protected static final Logger logger = Logger.getLogger(RevisionStateDialog.class.getName());
	
	private ListItem item = null;
	private SoapClientFile file = null;
	
	/**
	 * Constructor
	 */
	public RevisionStateDialog(ListItem item) {
		
		super();
		
		this.item = item;
		initFile();
		init();
	}
	
	/**
	 * Get list item
	 * @return 
	 */
	public ListItem getListItem() {
		return this.item;
	}
	
	/**
	 * Get file lock info
	 * @return 
	 */
	public SoapClientFileLock getFileLock() {
		return this.file.getFileLock();
	}
	
	/**
	 * Get file
	 * @return 
	 */
	public SoapClientFile getFile() {
		return this.file;
	}
	
	public void parse() {
		
		GridPane grid = new GridPane();
		grid.setHgap(10);
		grid.setVgap(12);
		
		/**
		 * Fixed columns
		 */
		grid.getColumnConstraints().add(new ColumnConstraints(20));
		grid.getColumnConstraints().add(new ColumnConstraints(20));
		grid.getColumnConstraints().add(new ColumnConstraints(500));
		
		// All elements for toggling
		final CheckBox rs = new CheckBox();
		final RadioButton unlimited = new RadioButton();
		final RadioButton limited = new RadioButton();		
		final CheckBox download = new CheckBox();
		// Toggle group 
		final ToggleGroup tg = new ToggleGroup();
		final HBox hbox = new HBox(6);
		

		boolean defaultEnabled = FileManagerUtils.textToInt(getFileLock().getUserId()) > 0;
		
		// Toggle revision state
		rs.setText("Set \"In Revision\"");
		rs.setIndeterminate(false);
		rs.setSelected(defaultEnabled);
		logger.info(getFileLock().getUserId());
		rs.selectedProperty().addListener(new ChangeListener<Boolean>() {
			
			public void changed(ObservableValue<? extends Boolean> ov, Boolean oldVal, Boolean newVal) {
				
				if(!newVal) {
					unlimited.setDisable(true);
					limited.setDisable(true);
					download.setDisable(true);
					hbox.setDisable(true);
				}
				else {
					unlimited.setDisable(false);
					limited.setDisable(false);
					download.setDisable(false);
					hbox.setDisable(false);
				}
				
				// changed from disabled to enabled
				if(newVal && !oldVal) {
					if(!unlimited.isSelected() && !limited.isSelected()) {
						unlimited.setSelected(true);
					}
				}
				
			}
		});
		grid.add(rs,0,0,3,1);
		
		// Add warning
		final Label warningLabel = new Label();
		warningLabel.setId("warning");
		warningLabel.setAlignment(Pos.CENTER);
		warningLabel.setVisible(false);
		getChildren().addAll(warningLabel);
		
		
		// Unlimited revision
		unlimited.setText("Unlimited");
		unlimited.setToggleGroup(tg);
		unlimited.setSelected(getFileLock().getUntil().equals("-1"));
		unlimited.setDisable(!defaultEnabled);
		grid.add(unlimited,1,1,2,1);
		
		// Limited revision
		limited.setText("Limited");
		limited.setToggleGroup(tg);
		limited.setSelected(FileManagerUtils.textToInt(getFileLock().getUntil()) > 0);
		limited.setDisable(!defaultEnabled);
		grid.add(limited,1,2,2,1);
		
		// Add duration 
		hbox.getChildren().addAll(new Label("Days:"));
		final TextField days = new TextField();
		days.setText(parseDuration(PARSE_DAYS, getFileLock().getRemainingSeconds()));
		days.setPromptText("0");
		days.setPrefColumnCount(2);
		hbox.getChildren().addAll(days);
		
		// Add duration 
		hbox.getChildren().addAll(new Label("Hours:"));
		final TextField hours = new TextField();
		hours.setText(parseDuration(PARSE_HOURS, getFileLock().getRemainingSeconds()));
		hours.setPromptText("0");
		hours.setPrefColumnCount(2);
		hbox.getChildren().addAll(hours);
		
		hbox.getChildren().addAll(new Label("Minutes:"));
		final TextField min = new TextField();
		min.setText(parseDuration(PARSE_MINUTES, getFileLock().getRemainingSeconds()));
		min.setPromptText("0");
		min.setPrefColumnCount(2);
		hbox.getChildren().addAll(min);
		hbox.setDisable(!defaultEnabled);
		grid.add(hbox,2,3,1,1);
		
		// Download 
		download.setText("Enable Download");
		download.setIndeterminate(false);
		download.setSelected(getFileLock().isDownloadEnabled());
		download.setDisable(!defaultEnabled);
		grid.add(download,1,4,2,1);
		
		
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
					
					public synchronized void handle(MouseEvent me) {
						
						long durationSeconds;
						
						durationSeconds  = Integer.valueOf(FileManagerUtils.textToInt(days.getText()))* 24 * 60 * 60;
						durationSeconds += Integer.valueOf(FileManagerUtils.textToInt(hours.getText())) * 60 * 60;
						durationSeconds += Integer.valueOf(FileManagerUtils.textToInt(min.getText())) * 60;

						if(rs.isSelected()) {
							logger.info("RS is checked!");
							if(limited.isSelected()) {
								logger.info("Limited is sleected");
								
								
								if(durationSeconds <= 0) {
									warningLabel.setText("Please enter a valid duration.");
									warningLabel.setVisible(true);
									return;
								}
							}
						}
						
						if(!rs.isSelected()) {
							// Set empty lock
							logger.info("Deactivated file locking");
							getFile().setFileLock(new SoapClientFileLock());
						}
						else {
							getFileLock().setUserId(SoapClientConnector.getInstance(SoapClientConnector.FRAME_RIGHT).getUserId());
							getFileLock().enableDownload(download.isSelected());
							if(unlimited.isSelected()) {
								getFileLock().setUntil(-1);
							}
							else {
								getFileLock().setUntil(System.currentTimeMillis() / 1000L + durationSeconds);
							}
							getFile().setFileLock(getFileLock());
						}

						getFile().setContent(new SoapClientFileContent());
						SoapClientConnector con = SoapClientConnector.getInstance(SoapClientConnector.FRAME_RIGHT);
						con.updateFileMD(getFile(),getListItem().getRefId());
						

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
		grid.add(buttonBox,0,5,3,1);
		
		this.getChildren().add(grid);
	}
	
	
	/**
	 * Init box
	 */
	private void init() {
		
		setId("ProxyDialog");
		setAlignment(Pos.CENTER);
		setSpacing(20);
		setMaxSize(600, USE_PREF_SIZE);
		
		// do not react on mouse clicks
		setOnMouseClicked(new EventHandler<MouseEvent>() {
            public void handle(MouseEvent t) {
                t.consume();
            }
        });		
		
		Label title = new Label("Edit Revision State");
        title.setId("title");
        title.setMinHeight(22);
        title.setPrefHeight(22);
        title.setMaxWidth(Double.MAX_VALUE);
        title.setAlignment(Pos.CENTER);
		
		getChildren().addAll(title);
	}
	
	/**
	 * Init file object
	 */
	private void initFile() {
		
		SoapClientConnector con = SoapClientConnector.getInstance(SoapClientConnector.FRAME_RIGHT);
		
		try {
			file = con.getFileXML(getListItem().getRefId());
		} 
		catch (SoapClientConnectorException ex) {
			logger.severe(ex.getMessage());
		}
	}
	
	/**
	 * Parse duration
	 * @param parseMode
	 * @param seconds
	 * @return 
	 */
	protected String parseDuration(int parseMode, long seconds) {
		
		long mod = 0;
		long rest = 0;
		
		if(seconds <= 0) {
			return "";
		}
		
		if(parseMode == RevisionStateDialog.PARSE_DAYS) {
			mod = seconds / (24 * 60 * 60);
		}
		if(parseMode == RevisionStateDialog.PARSE_HOURS) {
			rest = seconds % (24 * 60 * 60);
			mod = rest / (60 * 60);
		}
		if(parseMode == RevisionStateDialog.PARSE_MINUTES) {
			rest = seconds % (24 * 60 * 60);
			rest = rest % (60 * 60);
			mod = rest / 60;
		}
		if(mod > 0) {
			return String.valueOf(mod);
		}
		return "";
	}
}

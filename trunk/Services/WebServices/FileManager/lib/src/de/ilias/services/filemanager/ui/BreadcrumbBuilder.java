/*
 * Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE
 */
package de.ilias.services.filemanager.ui;

import de.ilias.services.filemanager.content.RemoteListItem;
import de.ilias.services.filemanager.events.BreadcrumbMouseEventHandler;
import de.ilias.services.filemanager.soap.api.SoapClientObjectReferencePath;
import de.ilias.services.filemanager.utils.FileManagerUtils;
import java.util.List;
import java.util.logging.Logger;
import javafx.scene.control.Hyperlink;
import javafx.scene.control.Label;
import javafx.scene.image.ImageView;
import javafx.scene.layout.HBox;

/**
 * Class BreadcrumbBuilder
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
public class BreadcrumbBuilder {
	
	private boolean useImages = false;
	private static final Logger logger = Logger.getLogger(BreadcrumbBuilder.class.getName());
	
	
	/**
	 * Build breadcrumb list from path elements
	 * @param pathElements
	 * @return 
	 */
	public HBox buildHBox(final List<SoapClientObjectReferencePath> pathElements) {
		
		HBox box = new HBox();
		box.getChildren().add(new Label("  "));
		BreadcrumbMouseEventHandler handler = new BreadcrumbMouseEventHandler();
		
		int counter = 0;
		for(SoapClientObjectReferencePath ele : pathElements) {
			
			if(counter > 0) {
				box.getChildren().add(new Label("»"));
			}
			
			Hyperlink link = new Hyperlink(ele.getTitle());
			
			if(useImages) {
				link.setGraphic(new ImageView(FileManagerUtils.getTinyImageByType(ele.getType())));
			}
			
			// Add a remote item as user data
			RemoteListItem remote = new RemoteListItem();
			remote.setRefId(ele.getRefId());
			link.setUserData(remote);
			link.setOnMouseReleased(handler);
			
			box.getChildren().add(link);
			++counter;
		}
		return box;
	}
	
}

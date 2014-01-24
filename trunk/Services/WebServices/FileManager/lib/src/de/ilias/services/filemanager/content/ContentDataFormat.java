/*
 * Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE
 */
package de.ilias.services.filemanager.content;

import java.awt.datatransfer.DataFlavor;
import java.awt.datatransfer.Transferable;
import java.awt.datatransfer.UnsupportedFlavorException;
import java.io.IOException;
import java.io.Serializable;
import java.util.logging.Level;
import java.util.logging.Logger;

/**
 * Class ContentDataFormat
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
public class ContentDataFormat implements Transferable, Serializable {

	
	protected static DataFlavor flavor = DataFlavor.stringFlavor;
	protected static DataFlavor[] flavors = {ContentDataFormat.flavor};
	
	public DataFlavor[] getTransferDataFlavors() {
		return flavors;
	}

	public boolean isDataFlavorSupported(DataFlavor df) {
		return true;
	}

	public String getTransferData(DataFlavor df) throws UnsupportedFlavorException, IOException {
		try {
			Thread.sleep(10000);
		} catch (InterruptedException ex) {
			
		}
		return "Naboooo";
	}
	
	
}

/*
 * Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE
 */
package de.ilias.services.filemanager.content;

import de.ilias.services.filemanager.soap.api.SoapClientObject;
import java.util.logging.Logger;

/**
 * Class ListItemProperty
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
public class ListItemProperty {
	
	private static final Logger logger = Logger.getLogger(ListItemProperty.class.getName());
	
	private String name;
	private String value;
	
	/**
	 * Get name
	 * @return 
	 */
	public String getName() {
		return this.name;
	}
	
	public void setName(String name) {
		this.name = name;
	}
	
	/**
	 * Get value
	 * @return 
	 */
	public String getValue() {
		return this.value;
	}
	
	public void setValue(String value) {
		this.value = value;
	}
	
	/**
	 * Get string presentation
	 * @return 
	 */
	public String toString() {
		
		if(getName().equalsIgnoreCase(SoapClientObject.PROP_FILE_VERSION)) {
		
			int version = Integer.parseInt(getValue());
			if(version > 1) {
				return "Version: " + getValue();
			}
			return "";
		}
		return getValue() + "   ";
	}
	
}

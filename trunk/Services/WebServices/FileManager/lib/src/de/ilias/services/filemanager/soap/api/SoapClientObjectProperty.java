/*
 * Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE
 */
package de.ilias.services.filemanager.soap.api;

import javax.xml.bind.annotation.*;

/**
 * Class SoapClientObjectProperty
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
@XmlAccessorType(XmlAccessType.FIELD)
public class SoapClientObjectProperty {
	
	@XmlAttribute( name = "name")
	private String name;
	
	@XmlValue
	private String value;
	
	
	/**
	 * Get name
	 * @return 
	 */
	public String getName() {
		return this.name;
	}
	
	/**
	 * Get value
	 * @return 
	 */
	public String getValue() {
		return this.value;
	}
}

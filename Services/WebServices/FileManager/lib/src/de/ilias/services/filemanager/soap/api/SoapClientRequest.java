/*
 * Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE
 */
package de.ilias.services.filemanager.soap.api;

import javax.xml.bind.annotation.XmlAccessType;
import javax.xml.bind.annotation.XmlAccessorType;
import javax.xml.bind.annotation.XmlElement;
import javax.xml.bind.annotation.XmlTransient;

/**
 * Class SoapClientRequest
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
@XmlTransient
public abstract class SoapClientRequest implements SoapClientInteraction {
	
	protected String sid = null;

	
	/**
	 * Default constructor
	 */
	public SoapClientRequest() {
	}
	
	
	/**
	 * Set sid
	 * @param sid 
	 */
	public void setSid(String sid) {
		this.sid = sid;
	}
	
	/**
	 * Get sid
	 * @return 
	 */
	public String getSid() {
		return sid;
	}
	
}

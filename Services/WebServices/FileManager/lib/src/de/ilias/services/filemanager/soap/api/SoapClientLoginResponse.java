/*
 * Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE
 */
package de.ilias.services.filemanager.soap.api;

import de.ilias.services.filemanager.soap.api.SoapClientResponse;
import javax.xml.bind.annotation.XmlRootElement;

/**
 * Class SoapClientLoginResponse
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
@XmlRootElement( name = "loginResponse", namespace = "urn:ilUserAdministration")
public class SoapClientLoginResponse extends SoapClientResponse {
	
	private String sid;
	
	
	/**
	 * Get sid
	 * @return String 
	 */
	public String getSid()
	{
		return sid;
	}
	
	/**
	 * Set sid
	 * @param sid 
	 */
	public void setSid(String sid)
	{
		this.sid = sid;
	}
}

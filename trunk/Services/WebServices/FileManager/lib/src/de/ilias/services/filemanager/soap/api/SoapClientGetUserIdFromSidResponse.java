/*
 * Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE
 */
package de.ilias.services.filemanager.soap.api;

import javax.xml.bind.annotation.XmlAccessType;
import javax.xml.bind.annotation.XmlAccessorType;
import javax.xml.bind.annotation.XmlRootElement;

/**
 * Class SoapClientGetUserIdFromSidResponse
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
@XmlAccessorType( XmlAccessType.FIELD)
@XmlRootElement( name = "getUserIdBySidResponse", namespace = "urn:ilUserAdministration")
public class SoapClientGetUserIdFromSidResponse extends SoapClientResponse {
	
	protected int usr_id;
	
	/**
	 * Get user id
	 * @return 
	 */
	public int getUserId() {
		return usr_id;
	}
	
	/**
	 * Set user id
	 * @param userId 
	 */
	public void setUserId(int userId) {
		this.usr_id = userId;
	}
	
}

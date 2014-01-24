/*
 * Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE
 */
package de.ilias.services.filemanager.soap.api;

import javax.xml.bind.annotation.XmlRootElement;

/**
 * Class SoapClientUpdateObjectsResponse
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
@XmlRootElement( name = "updateObjectsResponse", namespace = "urn:ilUserAdministration")
public class SoapClientUpdateObjectsResponse extends SoapClientResponse {
	
	private boolean success;
	
	/**
	 * Get success
	 * @return 
	 */
	public boolean getSuccess() {
		return success;
	}
	
	/**
	 * Set success
	 * @param success 
	 */
	public void setSuccess(boolean success) {
		this.success = success;
	}
	
}

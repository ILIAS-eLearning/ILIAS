/*
 * Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE
 */
package de.ilias.services.filemanager.soap.api;

import javax.xml.bind.annotation.XmlAccessType;
import javax.xml.bind.annotation.XmlAccessorType;
import javax.xml.bind.annotation.XmlRootElement;

/**
 * Class SoapClientUpdateObjectsResponse
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
@XmlAccessorType( XmlAccessType.FIELD)
@XmlRootElement( name = "updateFileResponse", namespace = "urn:ilUserAdministration")
public class SoapClientUpdateFileResponse extends SoapClientResponse {
	
	private boolean success;
	
	/**
	 * Get ref id
	 * @return 
	 */
	public boolean getSuccess() {
		return this.success;
	}
	
	
}

/*
 * Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE
 */
package de.ilias.services.filemanager.soap.api;

import javax.xml.bind.annotation.XmlAccessType;
import javax.xml.bind.annotation.XmlAccessorType;
import javax.xml.bind.annotation.XmlRootElement;

/**
 * Class SoapClientDeleteObjectResponse
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
@XmlAccessorType( XmlAccessType.FIELD)
@XmlRootElement( name = "deleteObjectResponse", namespace = "urn:ilUserAdministration")
public class SoapClientDeleteObjectResponse  extends SoapClientResponse {

	private boolean success;
	
	
	public boolean getSuccess() {
		return this.success;
	}
	
	public void setSuccess(boolean success) {
		this.success = success;
	}
	
}

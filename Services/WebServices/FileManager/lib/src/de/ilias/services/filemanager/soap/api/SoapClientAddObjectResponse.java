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
@XmlRootElement( name = "addObjectResponse", namespace = "urn:ilUserAdministration")
public class SoapClientAddObjectResponse extends SoapClientResponse {
	
	private int ref_id;
	
	/**
	 * Get ref id
	 * @return 
	 */
	public int getRefId() {
		return this.ref_id;
	}
	
	public void setRefId(int ref_id) {
		this.ref_id = ref_id;
	}
	
}

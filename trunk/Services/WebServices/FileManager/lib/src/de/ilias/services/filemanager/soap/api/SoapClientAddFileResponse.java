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
@XmlRootElement( name = "addFileResponse", namespace = "urn:ilUserAdministration")
public class SoapClientAddFileResponse extends SoapClientResponse {
	
	private int refid;
	
	/**
	 * Get ref id
	 * @return 
	 */
	public int getRefId() {
		return this.refid;
	}
	
	public void setRefId(int ref_id) {
		this.refid = ref_id;
	}
	
}

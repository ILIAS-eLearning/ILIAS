/*
 * Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE
 */
package de.ilias.services.filemanager.soap.api;

import javax.xml.bind.annotation.XmlAccessType;
import javax.xml.bind.annotation.XmlAccessorType;
import javax.xml.bind.annotation.XmlElement;
import javax.xml.bind.annotation.XmlRootElement;
import javax.xml.bind.annotation.XmlType;

/**
 * Class SoapClientGetObjectByReferenceRequest
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
@XmlAccessorType( XmlAccessType.FIELD)
@XmlRootElement( name = "getObjectByReference", namespace = "urn:ilUserAdministration")
@XmlType( propOrder = {"sid", "reference_id", "user_id"})
public class SoapClientGetObjectByReferenceRequest extends SoapClientRequest {
	
	protected int reference_id;
	protected int user_id;
	
	/**
	 * set user id
	 * @param userId 
	 */
	public void setUserId(int userId) {
		this.user_id = userId;
	}
	
	public void setRefId(int refId) {
		this.reference_id = refId;
	}
}

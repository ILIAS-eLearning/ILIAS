/*
 * Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE
 */
package de.ilias.services.filemanager.soap.api;

import javax.xml.bind.annotation.XmlAccessType;
import javax.xml.bind.annotation.XmlAccessorType;
import javax.xml.bind.annotation.XmlRootElement;
import javax.xml.bind.annotation.XmlType;

/**
 * Class SoapClientDeleteObjectRequest
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
@XmlAccessorType(XmlAccessType.FIELD)
@XmlRootElement( name = "deleteObject", namespace = "urn:ilUserAdministration")
@XmlType( propOrder = {"sid", "reference_id"})
public class SoapClientDeleteObjectRequest extends SoapClientRequest { 
	
	private int reference_id;
	
	/**
	 * Get ref id
	 * @return 
	 */
	public int getReferenceId() {
		return this.reference_id;
	}
	
	/**
	 * Set ref id
	 * @param refId 
	 */
	public void setReferenceId(int refId) {
		this.reference_id = refId;
	}
	
	
}

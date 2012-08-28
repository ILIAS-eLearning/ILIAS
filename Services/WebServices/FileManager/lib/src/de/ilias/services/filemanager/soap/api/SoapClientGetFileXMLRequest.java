/*
 * Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE
 */
package de.ilias.services.filemanager.soap.api;

import javax.xml.bind.annotation.XmlAccessType;
import javax.xml.bind.annotation.XmlAccessorType;
import javax.xml.bind.annotation.XmlRootElement;
import javax.xml.bind.annotation.XmlType;

/**
 * Class SoapClientGetUserXMLRequest
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
@XmlAccessorType( XmlAccessType.FIELD)
@XmlRootElement( name = "getFileXML", namespace = "urn:ilUserAdministration")
@XmlType( propOrder = {"sid", "ref_id", "attachment_mode"})
public class SoapClientGetFileXMLRequest extends SoapClientRequest {
	
	private int ref_id;
	// 5 is rest mode 
	private int attachment_mode = 5;
	
	

	/**
	 * Set ref id
	 * @param refId 
	 */
	public void setRefId(int refId) {
		this.ref_id = refId;
	}
	
	/**
	 * set attachment mode
	 * @return 
	 */
	public void setAttachmentMode(int mode) {
		this.attachment_mode = mode;
	}
	
}
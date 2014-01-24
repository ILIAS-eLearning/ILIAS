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
@XmlRootElement( name = "getUserXML", namespace = "urn:ilUserAdministration")
@XmlType( propOrder = {"sid", "user_ids", "attach_roles"})
public class SoapClientGetUserXMLRequest extends SoapClientRequest {
	
	private int[] user_ids;
	private int attach_roles = 0;

	public SoapClientGetUserXMLRequest() {
		
	}
	
	/**
	 * Get user ids
	 * @return 
	 */
	public int[] getUserIds() {
		return user_ids;
	}
	
	/**
	 * Set user ids
	 * @param userIds 
	 */
	public void setUserIds(int[] userIds) {
		this.user_ids = userIds;
	}
	
	/**
	 * Get atttach roles
	 * @return 
	 */
	public int getAttachRoles() {
		return this.attach_roles;
	}
	
	/**
	 * attach roles
	 * @param attach 
	 */
	public void setAttachRoles(boolean attach) {
		
		if(attach) {
			attach_roles = 1;
		}
		else {
			attach_roles = 0;
		}
	}
	
}
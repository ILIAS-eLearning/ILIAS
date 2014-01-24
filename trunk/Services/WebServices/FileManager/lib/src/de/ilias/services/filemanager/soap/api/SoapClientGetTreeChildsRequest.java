/*
 * Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE
 */
package de.ilias.services.filemanager.soap.api;

import javax.xml.bind.annotation.XmlAccessType;
import javax.xml.bind.annotation.XmlAccessorType;
import javax.xml.bind.annotation.XmlRootElement;
import javax.xml.bind.annotation.XmlTransient;
import javax.xml.bind.annotation.XmlType;

/**
 * Class SoapClientGetTreeChildsRequest
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
@XmlAccessorType( XmlAccessType.FIELD)
@XmlRootElement( name = "getTreeChilds", namespace = "urn:ilUserAdministration")
@XmlType( propOrder = {"sid", "ref_id", "types", "user_id"})
public class SoapClientGetTreeChildsRequest extends SoapClientRequest {
	
	
	protected int ref_id;
	protected String[] types;
	protected int user_id;
	
	@XmlTransient
	protected String[] defaultTypes = {"parent", "root", "cat", "crs", "grp", "fold", "file", "crsr"};
	
	/**
	 * Constructor
	 */
	public SoapClientGetTreeChildsRequest() {
		types = defaultTypes;
	}

	/**
	 * get ref id
	 * @return 
	 */
	public int getRefId() {
		return this.ref_id;
	}
	
	/**
	 * set ref id
	 * @param refId
	 * @return 
	 */
	public void setRefId(int refId) {
		this.ref_id = refId;
	}
	
	/**
	 * get types
	 * @return 
	 */
	public String[] getTypes() {
		return this.types;
	}
	
	/**
	 * Set types
	 * @param types 
	 */
	public void setTypes(String[] types) {
		this.types = types;
	}
	
	/**
	 * get user id
	 * @return 
	 */
	public int getUserId() {
		return this.user_id;
	}

	/**
	 * set user id
	 * @param userId 
	 */
	public void setUserId(int userId) {
		this.user_id = userId;
	}
}

/*
 * Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE
 */
package de.ilias.services.filemanager.soap.api;

import javax.xml.bind.annotation.XmlAccessType;
import javax.xml.bind.annotation.XmlAccessorType;
import javax.xml.bind.annotation.XmlRootElement;
import javax.xml.bind.annotation.XmlType;

/**
 * Class SoapClientGetTreeChildsRequest
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
@XmlAccessorType( XmlAccessType.FIELD)
@XmlRootElement( name = "searchObjects", namespace = "urn:ilUserAdministration")
@XmlType( propOrder = {"sid", "types", "key", "combination", "user_id"})
public class SoapClientSearchObjectsRequest extends SoapClientRequest {
	
	protected String[] types = {"cat", "crs", "grp", "fold", "file"};
	protected String key;
	protected String combination;
	protected int user_id;
	
	
	/**
	 * Constructor
	 */
	public SoapClientSearchObjectsRequest() {
	}
	

	/**
	 * Set types
	 * @param types 
	 */
	public void setTypes(String[] types) {
		this.types = types;
	}
	
	/**
	 * get types
	 * @return 
	 */
	public String[] getTypes() {
		return this.types;
	}
	
	/**
	 * Set query
	 * @param query 
	 */
	public void setQuery(String query) {
		this.key = query;
	}
	
	/**
	 * Get query
	 * @return 
	 */
	public String getQuery() {
		return this.key;
	}
	
	/**
	 * Set combination
	 * @param combination 
	 */
	public void setCombination(String combination) {
		this.combination = combination;
	}
	
	public String getCombination() {
		return this.combination;
	}
	
	/**
	 * set user id
	 * @param userId 
	 */
	public void setUserId(int userId) {
		this.user_id = userId;
	}
	
	/**
	 * get user id
	 * @return 
	 */
	public int getUserId() {
		return this.user_id;
	}

}

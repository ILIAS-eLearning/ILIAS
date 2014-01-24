/*
 * Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE
 */
package de.ilias.services.filemanager.soap.api;

import javax.xml.bind.annotation.XmlAttribute;
import javax.xml.bind.annotation.XmlElement;
import javax.xml.bind.annotation.XmlRootElement;
import javax.xml.bind.annotation.XmlTransient;

/**
 * Class SoapClientUser
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
public class SoapClientUser {
	
	private String userId;
	private String login;
	private String firstname;
	private String lastname;
	
	@XmlAttribute( name = "Id")
	public String getUserId() {
		return userId;
	}
	
	/**
	 * get user id
	 * @return 
	 */
	@XmlTransient
	public int getUserObjectId() {
		
		String[] splitted = getUserId().split("_");
		
		if(splitted[3] != null) {
			return Integer.parseInt(splitted[3]);
		}
		return 0;
		
	}
	
	/**
	 * Get login
	 * @return 
	 */
	@XmlElement( name = "Login")
	public String getLogin() {
		return this.login;
	}
	
	/**
	 * Set login
	 * @param login 
	 */
	public void setLogin(String login) {
		this.login = login;
	}
	
	/**
	 * Get firstname
	 * @return 
	 */
	@XmlElement( name = "Firstname")
	public String getFirstname() {
		return firstname;
	}
	
	/**
	 * Set firstname
	 * @param first 
	 */
	public void setFirstname(String first) {
		this.firstname = first;
	}
	
	/**
	 * Get lastname
	 * @return 
	 */
	@XmlElement( name = "Lastname")
	public String getLastname() {
		return lastname;
	}
	
	/**
	 * Set lastname
	 * @param last 
	 */
	public void setLastname(String last) {
		this.lastname = last;
	}
}

/*
 * Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE
 */
package de.ilias.services.filemanager.soap.api;

import javax.xml.bind.annotation.XmlAccessType;
import javax.xml.bind.annotation.XmlAccessorType;
import javax.xml.bind.annotation.XmlRootElement;
import javax.xml.bind.annotation.XmlType;

/**
 * Class SoapClientLoginRequest
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
@XmlAccessorType( XmlAccessType.FIELD)
@XmlRootElement( name = "login", namespace = "urn:ilUserAdministration")
@XmlType( propOrder = {"client", "username", "password"})
public class SoapClientLoginRequest implements SoapClientInteraction {
	
	private String client;
	private String username;
	private String password;
	

	/**
	 * Set client id
	 * @param client 
	 */
	public void setClient(String client)
	{
		this.client = client;
	}
	
	/**
	 * Get client id
	 * @return 
	 */
	public String getClient()
	{
		return client;
	}
	
	
	/**
	 * Set username
	 * @param username 
	 */
	public void setUsername(String username)
	{
		this.username = username;
	}
	
	/**
	 * Get username
	 * @return 
	 */
	public String getUsername()
	{
		return this.username;
	}
	
	/**
	 * Set password
	 * @param pass 
	 */
	public void setPassword(String pass)
	{
		this.password = pass;
	}

	/**
	 * Get password
	 * @return 
	 */
	public String getPassword()
	{
		return this.password;
	}
	
}

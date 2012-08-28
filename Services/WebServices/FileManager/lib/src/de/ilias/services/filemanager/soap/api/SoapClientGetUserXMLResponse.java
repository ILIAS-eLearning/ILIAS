/*
 * Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE
 */
package de.ilias.services.filemanager.soap.api;

import java.util.logging.Level;
import java.util.logging.Logger;
import javax.xml.bind.JAXBException;
import javax.xml.bind.annotation.XmlRootElement;
import javax.xml.bind.annotation.XmlTransient;

/**
 * Class SoapClientGetUserXMLResponse
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
@XmlRootElement( name = "getUserXMLResponse", namespace = "urn:ilUserAdministration")
public class SoapClientGetUserXMLResponse extends SoapClientResponse {
	
	protected static final Logger logger = Logger.getLogger(SoapClientGetUserXMLResponse.class.getName());
	
	private String xml;
	
	private SoapClientUsers users;
	
	/**
	 * Set xml
	 * @param xml 
	 */
	public void setXml(String xml) {
		this.xml = xml;
	}
	
	/**
	 * Get xml
	 * @return 
	 */
	public String getXml() {
		return xml;
	}
	
	/**
	 * Get users
	 * @return 
	 */
	@XmlTransient
	public SoapClientUsers getUsers() {
		
		return users;
	}
	
	public void unmarshall() throws JAXBException {
		
		//logger.info("Unmarshalling xml string: " + getXml());
		this.users = (SoapClientUsers) unmarshallResponse(getXml(),SoapClientUsers.class);
		logger.log(Level.INFO, getUsers().toString());
	}
}

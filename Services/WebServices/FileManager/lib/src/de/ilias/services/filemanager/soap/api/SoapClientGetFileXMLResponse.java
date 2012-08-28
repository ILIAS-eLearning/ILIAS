/*
 * Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE
 */
package de.ilias.services.filemanager.soap.api;

import java.io.File;
import java.util.logging.Level;
import java.util.logging.Logger;
import javax.xml.bind.JAXBException;
import javax.xml.bind.annotation.XmlAccessType;
import javax.xml.bind.annotation.XmlAccessorType;
import javax.xml.bind.annotation.XmlRootElement;
import javax.xml.bind.annotation.XmlTransient;

/**
 * Class SoapClientGetUserXMLResponse
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
@XmlAccessorType( XmlAccessType.FIELD)
@XmlRootElement( name = "getFileXMLResponse", namespace = "urn:ilUserAdministration")
public class SoapClientGetFileXMLResponse extends SoapClientResponse {
	
	@XmlTransient
	protected static final Logger logger = Logger.getLogger(SoapClientGetFileXMLResponse.class.getName());
	
	private String filexml;
	
	@XmlTransient
	SoapClientFile file;
	

	/**
	 * Set xml
	 * @param xml 
	 */
	public void setXml(String xml) {
		this.filexml = xml;
	}
	
	/**
	 * Get xml
	 * @return 
	 */
	public String getXml() {
		return filexml;
	}
	
	/**
	 * get file
	 * @return 
	 */
	public SoapClientFile getFile() {
		return this.file;
	}
	

	public void unmarshall() throws JAXBException {
		
		this.file = (SoapClientFile) unmarshallResponse(getXml(),SoapClientFile.class);
	}
}

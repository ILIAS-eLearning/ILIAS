/*
 * Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE
 */
package de.ilias.services.filemanager.soap.api;

import java.util.logging.Logger;
import javax.xml.bind.JAXBException;
import javax.xml.bind.annotation.XmlAccessType;
import javax.xml.bind.annotation.XmlAccessorType;
import javax.xml.bind.annotation.XmlRootElement;
import javax.xml.bind.annotation.XmlTransient;

/**
 * Class SoapClientGetObjectByReferenceResponse
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
@XmlAccessorType( XmlAccessType.FIELD)
@XmlRootElement( name = "getObjectByReferenceResponse", namespace = "urn:ilUserAdministration")
public class SoapClientGetObjectByReferenceResponse extends SoapClientResponse {
	
	@XmlTransient
	protected static final Logger logger = Logger.getLogger(SoapClientGetObjectByReferenceResponse.class.getName());
	
	private String object_xml;
	
	@XmlTransient
	private SoapClientObjects objects;
	
	/**
	 * Set xml
	 * @param xml 
	 */
	public void setXml(String xml) {
		this.object_xml = xml;
	}
	
	/**
	 * Get xml
	 * @return 
	 */
	public String getXml() {
		return object_xml;
	}

	/**
	 * Get objects
	 * @return 
	 */
	@XmlTransient
	public SoapClientObjects getObjects() {
		
		return objects;
	}
	
	
	/**
	 * Unmarshall object xml
	 * @throws JAXBException 
	 */
	public void unmarshall() throws JAXBException {
		
		//logger.fine("Unmarshalling xml string: " + getXml());
		this.objects = (SoapClientObjects) unmarshallResponse(getXml(),SoapClientObjects.class);
	}
	
	
}

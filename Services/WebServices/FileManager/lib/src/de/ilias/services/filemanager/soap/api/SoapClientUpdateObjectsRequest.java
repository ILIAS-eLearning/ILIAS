/*
 * Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE
 */
package de.ilias.services.filemanager.soap.api;

import de.ilias.services.filemanager.soap.SoapClientConnector;
import de.ilias.services.filemanager.soap.api.SoapClientRequest;
import java.io.ByteArrayInputStream;
import java.io.StringWriter;
import java.util.logging.Logger;
import javax.xml.bind.JAXBContext;
import javax.xml.bind.Marshaller;
import javax.xml.bind.annotation.XmlAccessType;
import javax.xml.bind.annotation.XmlAccessorType;
import javax.xml.bind.annotation.XmlRootElement;
import javax.xml.bind.annotation.XmlType;
import javax.xml.transform.stream.StreamSource;

/**
 * Class SoapClientUpdateObjectsRequest
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
@XmlAccessorType( XmlAccessType.FIELD)
@XmlRootElement( name = "updateObjects", namespace = "urn:ilUserAdministration")
@XmlType( propOrder = {"sid", "object_xml"})
public class SoapClientUpdateObjectsRequest extends SoapClientRequest {
	
	protected static final Logger logger = Logger.getLogger(SoapClientUpdateObjectsRequest.class.getName());
	
	private String object_xml;
	
	/**
	 * Set object xml
	 * @param xml 
	 */
	public void setObjectXml(String xml) {
		this.object_xml = xml;
	}
	
	/**
	 * Get object xml
	 * @return 
	 */
	public String getObjectXml() {
		return this.object_xml;
	}

	/**
	 * Set objects
	 * @param objects 
	 */
	public void setObjects(SoapClientObjects objects) {
		
		StringWriter writer = new StringWriter();
		StreamSource streamSource = null;
		
		try {
			JAXBContext context = JAXBContext.newInstance(objects.getClass());
			Marshaller marshaller = context.createMarshaller();
			marshaller.marshal(objects, writer);
			logger.info(writer.toString());
			setObjectXml(writer.toString());
			
		} catch (Exception ex) {
			logger.warning("Unable to marshall request...");
			logger.severe(ex.getMessage());
		}
		
	}	
}

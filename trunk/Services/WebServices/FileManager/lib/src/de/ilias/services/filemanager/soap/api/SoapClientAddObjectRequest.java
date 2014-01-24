/*
 * Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE
 */
package de.ilias.services.filemanager.soap.api;

import de.ilias.services.filemanager.soap.api.SoapClientRequest;
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
@XmlRootElement( name = "addObject", namespace = "urn:ilUserAdministration")
@XmlType( propOrder = {"sid", "target_id", "object_xml"})
public class SoapClientAddObjectRequest extends SoapClientRequest {
	
	protected static final Logger logger = Logger.getLogger(SoapClientUpdateObjectsRequest.class.getName());
	
	private String object_xml;
	private int target_id;
	
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
	 * set target id
	 * @param id 
	 */
	public void setTargetId(int id) {
		this.target_id = id;
	}
	
	/**
	 * Get target id
	 * @return 
	 */
	public int getTargetId() {
		return this.target_id;
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
			//logger.info(writer.toString());
			setObjectXml(writer.toString());
			
		} catch (Exception ex) {
			logger.warning("Unable to marshall request...");
			logger.severe(ex.getMessage());
		}
		
	}
	
}

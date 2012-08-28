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
@XmlRootElement( name = "addFile", namespace = "urn:ilUserAdministration")
@XmlType( propOrder = {"sid", "target_id", "xml"})
public class SoapClientAddFileRequest extends SoapClientRequest {
	
	protected static final Logger logger = Logger.getLogger(SoapClientUpdateObjectsRequest.class.getName());
	
	private String xml;
	private int target_id;
	
	/**
	 * Set object xml
	 * @param xml 
	 */
	public void setFileXml(String xml) {
		this.xml = xml;
	}
	
	/**
	 * Get object xml
	 * @return 
	 */
	public String getFileXml() {
		return this.xml;
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
	public void setFile(SoapClientFile file) {
		
		StringWriter writer = new StringWriter();
		StreamSource streamSource = null;
		
		try {
			JAXBContext context = JAXBContext.newInstance(file.getClass());
			Marshaller marshaller = context.createMarshaller();
			marshaller.marshal(file, writer);
			//logger.info(writer.toString());
			setFileXml(writer.toString());
			
		} catch (Exception ex) {
			logger.warning("Unable to marshall request...");
			logger.severe(ex.getMessage());
		}
		
	}
	
}

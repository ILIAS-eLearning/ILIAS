/*
 * To change this template, choose Tools | Templates and open the template in
 * the editor.
 */
package de.ilias.services.filemanager.soap.api;

import java.io.StringReader;
import javax.xml.bind.JAXBContext;
import javax.xml.bind.JAXBException;
import javax.xml.bind.Unmarshaller;

/**
 * Soap client repsonse
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
public class SoapClientResponse implements SoapClientInteraction {
	
	
	/**
	 * Unmarshall response
	 * @param sourceXml
	 * @param responseHandler
	 * @return
	 * @throws JAXBException 
	 */
	public Object unmarshallResponse(String sourceXml, Class responseHandler) throws JAXBException {
		
		JAXBContext context = JAXBContext.newInstance(responseHandler);
		Unmarshaller unmarshaller = context.createUnmarshaller();
		return unmarshaller.unmarshal(new StringReader(sourceXml));
	}
	
}

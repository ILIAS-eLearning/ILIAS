/*
 * Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE
 */
package de.ilias.services.filemanager.soap;

import de.ilias.services.filemanager.rest.RestClientConnector;
import de.ilias.services.filemanager.soap.api.*;
import de.ilias.services.filemanager.user.RemoteAccount;
import java.io.ByteArrayInputStream;
import java.io.StringWriter;
import java.net.MalformedURLException;
import java.net.URL;
import java.util.HashMap;
import java.util.Iterator;
import java.util.logging.Level;
import java.util.logging.Logger;
import javax.xml.bind.JAXBContext;
import javax.xml.bind.JAXBException;
import javax.xml.bind.Marshaller;
import javax.xml.bind.Unmarshaller;
import javax.xml.namespace.QName;
import javax.xml.transform.Source;
import javax.xml.transform.stream.StreamSource;
import javax.xml.ws.Dispatch;
import javax.xml.ws.Service;
import javax.xml.ws.soap.SOAPFaultException;

/**
 * Class SoapClientConnector
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
public class SoapClientConnector {
	
	protected static final Logger logger = Logger.getLogger(SoapClientConnector.class.getName());
	
	public static final int FRAME_RIGHT = 1;
	public static final int FRAME_LEFT = 2;
	
	protected static final String QNAME_SERVICE_URI = "urn:ilUserAdministration";
	protected static final String QNAME_SERVICE_LOCAL = "ILIASSoapWebservice";
	protected static final String QNAME_PORT_URI = "urn:ilUserAdministration";
	protected static final String QNAME_PORT_LOCAL = "ILIASSoapWebservicePort";

	private static HashMap<Integer, SoapClientConnector> instances = new HashMap<Integer,SoapClientConnector>();
	
	private String wsdl = "";
	private String client = "";
	private String user = "";
	private int userId = 0;
	private String pass = "";
	private String sid = "";
	
	private Service service;
	private Dispatch dispatch;
	
	/**
	 * Singelton contructor
	 */
	private SoapClientConnector() {
		
	}
	
	/**
	 * Default instance
	 * @return SoapClientConnector
	 */
	public static SoapClientConnector getInstance() {
		
		return SoapClientConnector.getInstance(FRAME_RIGHT);
	}
	
	/**
	 * Get instance
	 * @param frame
	 * @return 
	 */
	public static SoapClientConnector getInstance(int frame) {
		
		if(instances.containsKey(frame)) {
			return instances.get(frame);
		}
		instances.put(frame, new SoapClientConnector());
		return instances.get(frame);
	}
	
	/**
	 * Set wsdl uri
	 * @param uri 
	 */
	public void setWsdlUri(String uri) {
		this.wsdl = uri;
	}
	
	/**
	 * Set client
	 * @param client 
	 */
	public void setClient(String client)
	{
		this.client = client;
	}
	
	/**
	 * set soap user
	 * @param soapUser 
	 */
	public void setUser(String soapUser) {
		user = soapUser;
	}
	
	/**
	 * Set user id
	 * @param id 
	 */
	public void setUserId(int id) {
		this.userId = id;
	}
	
	/**
	 * Get user id
	 * @return 
	 */
	public int getUserId() {
		return this.userId;
	}

	/**
	 * Set soap pass
	 * @param soapPass 
	 */
	public void setPassword(String soapPass) {
		pass = soapPass;
	}

	/**
	 * Set session id
	 * @param soapSid 
	 */
	public void setSessionId(String soapSid) {
		sid = soapSid;
	}

	public void init() {

		QName port;

		try {
			service = Service.create(
					new URL(wsdl),
					new QName(QNAME_SERVICE_URI, QNAME_SERVICE_LOCAL)
			);
			dispatch = service.createDispatch(
					new QName(QNAME_PORT_URI,QNAME_PORT_LOCAL),
					Source.class,
					Service.Mode.PAYLOAD
			);
		} 
		catch (MalformedURLException ex) {
			logger.log(Level.SEVERE,"Cannot connect to ILIAS webservice " + wsdl + " " + ex.getMessage());
		}
		catch (Exception e) {
			logger.severe(e.getMessage());
		}
	}
	

	/**
	 * Login to ILIAS
	 */
	public void login() throws SoapClientConnectorException, SoapClientLoginException {
		
		SoapClientLoginRequest loginRequest = new SoapClientLoginRequest();
		loginRequest.setClient(this.client);
		loginRequest.setUsername(this.user);
		loginRequest.setPassword(this.pass);

		try {
			Source response = (Source) dispatch.invoke(marshall(loginRequest));
			SoapClientLoginResponse loginResponse = (SoapClientLoginResponse) unmarshall(SoapClientLoginResponse.class, response);

			this.setSessionId(loginResponse.getSid());
		}
		catch(SOAPFaultException e) {
			logger.severe("Unable to login");
			throw new SoapClientLoginException(e.getMessage());
		}
		catch(Exception e) {
			logger.severe("Unable to login");
			throw new SoapClientConnectorException(e.getMessage());
		}
	}
	
	/**
	 * Fetch user id from session id
	 */
	public void fetchUserId() throws SoapClientConnectorException {
		
		SoapClientGetUserIdFromSidRequest request;
		SoapClientGetUserIdFromSidResponse userResponse;
		
		
		try {
			request = new SoapClientGetUserIdFromSidRequest();
			logger.log(Level.INFO, "Current sesion id is {0}", sid);
			request.setSid(sid);

			Source response = (Source) dispatch.invoke(marshall(request));
			userResponse = (SoapClientGetUserIdFromSidResponse) unmarshall(SoapClientGetUserIdFromSidResponse.class,response);
		}
		catch(Exception e) {
			logger.severe("Unable to fetch user id");
			throw new SoapClientConnectorException(e.getMessage());
		}
		
		logger.fine("Received user id " + userResponse.getUserId());
		this.setUserId(userResponse.getUserId());
	}
	
	/**
	 * Init remote user
	 * @return 
	 */
	public RemoteAccount initUser() throws SoapClientConnectorException {
		
		if(sid.isEmpty()) {
			try {
				login();
				fetchUserId();
			}
			catch (Exception e) {
				e.printStackTrace();
				throw new SoapClientConnectorException(e.getMessage());
			}
		}

		SoapClientGetUserXMLRequest request = new SoapClientGetUserXMLRequest();
		request.setSid(sid);
		request.setUserIds(new int[] { getUserId() });
		request.setAttachRoles(false);
		
		Source response = (Source) dispatch.invoke(marshall(request));
		logger.info("Trying to unmarshall soap request...");
		SoapClientGetUserXMLResponse responseXml = (SoapClientGetUserXMLResponse) unmarshall(SoapClientGetUserXMLResponse.class, response);
		try {
			logger.info("Trying to unmarshall soap response xml...");
			responseXml.unmarshall();
		} 
		catch (JAXBException ex) {
			ex.printStackTrace();
			logger.info(ex.getMessage());
			throw new SoapClientConnectorException(ex.getMessage());
		}
		if(responseXml.getUsers() == null) {
			throw new SoapClientConnectorException("Did not receive valid xml from soap service");
		}
		if(responseXml.getUsers().getUsers() == null) {
			throw new SoapClientConnectorException("Did not receive user account information.");
		}
		
		logger.log(Level.FINEST, "Reading remote user.");
		Iterator ite = responseXml.getUsers().getUsers().iterator();
		while(ite.hasNext()) {
			RemoteAccount.newInstance((SoapClientUser) ite.next());
			logger.info("Received user account data from " + RemoteAccount.getInstance().getUser().getLogin());
		}
		return RemoteAccount.getInstance();
	}
	
	/**
	 * Get object by reference
	 * @param refId
	 * @return 
	 */
	public SoapClientObjects getObjectByReference(int refId) throws SoapClientConnectorException {
		
		SoapClientGetObjectByReferenceResponse responseXml;
		
		SoapClientGetObjectByReferenceRequest request = new SoapClientGetObjectByReferenceRequest();
		request.setSid(sid);
		request.setRefId(refId);
		request.setUserId(this.getUserId());
		
		logger.info("Session id is" + sid );
		Source response = (Source) dispatch.invoke(marshall(request));
		responseXml = (SoapClientGetObjectByReferenceResponse) unmarshall(SoapClientGetObjectByReferenceResponse.class,response);
		//logger.fine(responseXml.getXml());
		try {
			logger.fine("Trying to unmarshall soap response xml...");
			responseXml.unmarshall();
		} 
		catch (JAXBException ex) {
			ex.printStackTrace();
			throw new SoapClientConnectorException(ex.getMessage());
		}
		if(responseXml.getObjects() == null) {
			throw new SoapClientConnectorException("Did not receive valid xml from soap service");
		}
		if(responseXml.getObjects().getObjects() == null) {
			throw new SoapClientConnectorException("Did not receive user account information.");
		}
		return responseXml.getObjects();
	}
	
	/**
	 * Update objects
	 * @param objectXml 
	 */
	public boolean updateObjects(SoapClientObjects objects) {
		
		SoapClientUpdateObjectsResponse responseXml;
		
		SoapClientUpdateObjectsRequest request = new SoapClientUpdateObjectsRequest();
		request.setSid(sid);
		request.setObjects(objects);
		
		Source response = (Source) dispatch.invoke(marshall(request));
		responseXml = (SoapClientUpdateObjectsResponse) unmarshall(SoapClientUpdateObjectsResponse.class,response);
		
		return responseXml.getSuccess();
	}

	/**
	 * Add object (cat, fold)
	 * @param objects
	 * @return 
	 */
	public int addObject(SoapClientObjects objects, int targetId) {
		
		SoapClientAddObjectResponse responseXml;
		
		SoapClientAddObjectRequest request = new SoapClientAddObjectRequest();
		request.setSid(sid);
		request.setTargetId(targetId);
		request.setObjects(objects);
		
		Source response = (Source) dispatch.invoke(marshall(request));
		responseXml = (SoapClientAddObjectResponse) unmarshall(SoapClientAddObjectResponse.class,response);
		
		return responseXml.getRefId();
	}
	
	public boolean deleteObject(int refId) {
		
		SoapClientDeleteObjectResponse responseXml;
		
		SoapClientDeleteObjectRequest request = new SoapClientDeleteObjectRequest();
		request.setSid(sid);
		request.setReferenceId(refId);

		Source response = (Source) dispatch.invoke(marshall(request));
		responseXml = (SoapClientDeleteObjectResponse) unmarshall(SoapClientDeleteObjectResponse.class,response);
		
		return responseXml.getSuccess();
		
		
	}
	
	/**
	 * Add object (cat, fold)
	 * @param objects
	 * @return 
	 */
	public int addFile(SoapClientFile file, int targetId) {
		
		SoapClientAddFileResponse responseXml;
		
		file = RestClientConnector.getInstance().addFile(file);
		
		SoapClientAddFileRequest request = new SoapClientAddFileRequest();
		request.setSid(sid);
		request.setTargetId(targetId);
		request.setFile(file);
		
		Source response = (Source) dispatch.invoke(marshall(request));
		responseXml = (SoapClientAddFileResponse) unmarshall(SoapClientAddFileResponse.class,response);
		
		logger.info(String.valueOf(responseXml.getRefId()));
		
		return responseXml.getRefId();
	}
	
	/**
	 * Update file (add new file version)
	 * @param file
	 * @param refId
	 * @return 
	 */
	public boolean updateFile(SoapClientFile file, int refId) {

		SoapClientUpdateFileResponse responseXml;
		
		file = RestClientConnector.getInstance().addFile(file);
		
		SoapClientUpdateFileRequest request = new SoapClientUpdateFileRequest();
		request.setSid(sid);
		request.setTargetId(refId);
		request.setFile(file);
		
		Source response = (Source) dispatch.invoke(marshall(request));
		responseXml = (SoapClientUpdateFileResponse) unmarshall(SoapClientUpdateFileResponse.class,response);
		
		return responseXml.getSuccess();
	}
	
	/**
	 * Update file meta data
	 * @param file
	 * @param refId
	 * @return 
	 */
	public boolean updateFileMD(SoapClientFile file, int refId) {

		SoapClientUpdateFileResponse responseXml;
		
		file.setContent(null);
		
		SoapClientUpdateFileRequest request = new SoapClientUpdateFileRequest();
		request.setSid(sid);
		request.setTargetId(refId);
		request.setFile(file);
		
		Source response = (Source) dispatch.invoke(marshall(request));
		responseXml = (SoapClientUpdateFileResponse) unmarshall(SoapClientUpdateFileResponse.class,response);
		
		return responseXml.getSuccess();
	}
	
	/**
	 * Get tree childs
	 * @param refId 
	 */
	public SoapClientObjects getTreeChilds(int refId) throws SoapClientConnectorException {
		
		SoapClientGetTreeChildsResponse responseXml;
		
		SoapClientGetTreeChildsRequest request = new SoapClientGetTreeChildsRequest();
		request.setSid(sid);
		request.setRefId(refId);
		request.setUserId(this.getUserId());
		
		Source response = (Source) dispatch.invoke(marshall(request));
		responseXml = (SoapClientGetTreeChildsResponse) unmarshall(SoapClientGetTreeChildsResponse.class,response);
		//logger.fine(responseXml.getXml());
		
		try {
			logger.fine("Trying to unmarshall soap response xml...");
			responseXml.unmarshall();
		} 
		catch (JAXBException ex) {
			ex.printStackTrace();
			throw new SoapClientConnectorException(ex.getMessage());
		}
		if(responseXml.getObjects() == null) {
			throw new SoapClientConnectorException("Did not receive valid xml from soap service");
		}
		if(responseXml.getObjects().getObjects() == null) {
			throw new SoapClientConnectorException("Did not receive user account information.");
		}
		return responseXml.getObjects();
	}
	
	/**
	 * Search objects
	 * @param query
	 * @return 
	 */
	public SoapClientObjects search(String query) throws SoapClientConnectorException {
		
		SoapClientSearchObjectsResponse responseXml;
		SoapClientSearchObjectsRequest request = new SoapClientSearchObjectsRequest();
		
		request.setSid(this.sid);
		request.setQuery(query);
		request.setCombination("and");
		request.setUserId(this.getUserId());
		
		Source response = (Source) dispatch.invoke(marshall(request));
		responseXml = (SoapClientSearchObjectsResponse) unmarshall(SoapClientSearchObjectsResponse.class,response);
		
		try {
			responseXml.unmarshall();
		}
		catch(JAXBException e) {
			logger.warning(e.getMessage());
			//throw new SoapClientConnectorException(e.getMessage());
			return new SoapClientObjects();
		}
		if(responseXml.getObjects() == null) {
			throw new SoapClientConnectorException("Did not receive valid xml from soap service");
		}
		if(responseXml.getObjects().getObjects() == null) {
			throw new SoapClientConnectorException("Did not receive user account information.");
		}
		return responseXml.getObjects();
	}
	
		
	/**
	 * Get file 
	 * @param refId
	 * @return 
	 */
	public SoapClientFile getFileXML(int refId) throws SoapClientConnectorException {
		
		SoapClientFile file;
		SoapClientGetFileXMLResponse responseXml;
		
		SoapClientGetFileXMLRequest request = new SoapClientGetFileXMLRequest();
		request.setSid(sid);
		request.setRefId(refId);
		
		Source response = (Source) dispatch.invoke(marshall(request));
		responseXml = (SoapClientGetFileXMLResponse) unmarshall(SoapClientGetFileXMLResponse.class, response);
		
		try {
			logger.fine("Trying to unmarshall soap response xml...");
			responseXml.unmarshall();
			
			// Get by rest
			logger.info(responseXml.getFile().getContent().getValue());
			file = RestClientConnector.getInstance().getFile(responseXml.getFile());
			
			//file = responseXml.getFile();
			
		} 
		catch (JAXBException ex) {
			ex.printStackTrace();
			throw new SoapClientConnectorException(ex.getMessage());
		}
		return file;
	}
	
	/**
	 * Marshall soap request
	 * @param requestHandler
	 * @param loginRequest
	 * @return 
	 */
	private StreamSource marshall(Object loginRequest)
	{
		StringWriter writer = new StringWriter();
		StreamSource streamSource = null;
		
		try {
			JAXBContext context = JAXBContext.newInstance(loginRequest.getClass());
			Marshaller marshaller = context.createMarshaller();
			marshaller.marshal(loginRequest, writer);
			
			//logger.fine(writer.toString());
			
			return new StreamSource(new ByteArrayInputStream(writer.toString().getBytes("UTF-8")));
			
		} catch (Exception ex) {
			logger.warning("Unable to marshall request...");
			ex.printStackTrace();
			logger.severe(ex.getMessage());
		}
		return streamSource;
	}
	
	/**
	 * Unmarshall soap response
	 * @param responseHandler
	 * @return 
	 */
	private SoapClientResponse unmarshall(Class responseHandler, javax.xml.transform.Source response)
	{
		try {
			// Debug 
			JAXBContext context = JAXBContext.newInstance(responseHandler);
			Unmarshaller unmarshaller = context.createUnmarshaller();
			return (SoapClientResponse) unmarshaller.unmarshal(response);
		} 
		catch (Exception ex) {
			logger.log(Level.SEVERE,ex.getMessage());
			return null;
		}
	}

}

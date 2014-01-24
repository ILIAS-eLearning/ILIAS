/*
 * Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE
 */
package de.ilias.services.filemanager.rest;

import com.sun.jersey.api.client.Client;
import com.sun.jersey.api.client.WebResource;
import com.sun.jersey.api.client.config.ClientConfig;
import com.sun.jersey.api.client.config.DefaultClientConfig;
import com.sun.jersey.core.util.MultivaluedMapImpl;
import de.ilias.services.filemanager.rest.api.RestGetFileResponse;
import de.ilias.services.filemanager.rest.api.RestPostFileRequest;
import de.ilias.services.filemanager.soap.api.SoapClientFile;
import de.ilias.services.filemanager.utils.Base64;
import de.ilias.services.filemanager.utils.FileManagerUtils;
import java.io.*;
import java.util.HashMap;
import java.util.logging.Logger;
import javax.ws.rs.core.MediaType;

/**
 * Class RestClientConnector
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
public class RestClientConnector {
	
	protected static final Logger logger = Logger.getLogger(RestClientConnector.class.getName());
	
	public static final int FRAME_RIGHT = 1;
	public static final int FRAME_LEFT = 2;

	protected static final String FILE_STORAGE = "fileStorage";
	
	private String server = "";
	private Client client = null;
	
	private static HashMap<Integer, RestClientConnector> instances = new HashMap<Integer,RestClientConnector>();
	
	/**
	 * Singelton contructor
	 */
	private RestClientConnector() {
		
	}
	
	/**
	 * Default instance
	 * @return SoapClientConnector
	 */
	public static RestClientConnector getInstance() {
		
		return RestClientConnector.getInstance(FRAME_RIGHT);
	}
	
	/**
	 * Get instance
	 * @param frame
	 * @return 
	 */
	public static RestClientConnector getInstance(int frame) {
		
		if(instances.containsKey(frame)) {
			return instances.get(frame);
		}
		instances.put(frame, new RestClientConnector());
		return instances.get(frame);
	}
	
	/**
	 * Set server url
	 * @param server 
	 */
	public void setServer(String server) {
		this.server = server;
	}
	
	/**
	 * Get server
	 * @return 
	 */
	public String getServer() {
		return this.server;
	}
	
	/**
	 * Get rest client
	 * @return 
	 */
	public Client getClient() {
		return this.client;
	}
	
	
	public SoapClientFile getFile(SoapClientFile file) {
		
		File tmp;
		
		this.init("get");
		
		// File content contains uid of rest path
		WebResource wr = client.resource(getServer() + "/" + FILE_STORAGE + "/" + file.getContent().getValue());
		InputStream responseStream;
		
		responseStream = wr.accept(MediaType.APPLICATION_JSON).get(InputStream.class);
		try {
			tmp = this.createTempFileFromStream(responseStream);
			file.getContent().setContentFile(tmp);
		} 
		catch (IOException ex) {
			logger.severe(ex.getMessage());
		}

		return file;
	}
	
	/**
	 * Add file via rest
	 * @param file
	 * @return 
	 */
	public SoapClientFile addFile(SoapClientFile file)  {
		
		try {
			this.init("post");
			
			// encode file content
			File tmp = FileManagerUtils.createTempFile();
			Base64.encodeFileToFile(file.getContent().getContentFile().getAbsolutePath(), tmp.getAbsolutePath());

			WebResource wr = client.resource(getServer() + "/" + FILE_STORAGE);

			// Add file content
			//RestPostFileRequest request = new RestPostFileRequest();
			//request.setContent(file.getContent().getContentFile());
			
			MultivaluedMapImpl mvm = new MultivaluedMapImpl();
			mvm.add("content", FileManagerUtils.fileToString(tmp));

			String response;
			//response = wr.accept(MediaType.APPLICATION_JSON).post(String.class, request);
			response = wr.accept(MediaType.APPLICATION_JSON).post(String.class, mvm);
			logger.info("Response add rest file: " + response);
			file.getContent().setValue(response);

		}
		catch(IOException ex) {
			logger.severe(ex.getMessage());
		}
		return file;
	}
	
	
	public void init(String type) {
		
		ClientConfig config = new DefaultClientConfig();
		
		if(type.equals("get")) {
			config.getClasses().add(RestGetFileResponse.class);
		}
		else if(type.equals("post")) {
			config.getClasses().add(RestPostFileRequest.class);
		}
		
		this.client = Client.create(config);
	}
	
	public File createTempFileFromStream(InputStream is) throws IOException {
		
		File temp = FileManagerUtils.createTempFile();
		OutputStream os = null;
		try {
			os = new FileOutputStream(temp);
		} 
		catch (FileNotFoundException ex) {
			logger.severe(ex.getMessage());
		}
		
		byte buffer[] = new byte[1024];
		int len;
		while((len = is.read(buffer)) > 0) {
			os.write(buffer,0,len);
		}
		os.close();
		is.close();
		return temp;
	}
	
	public File decodeFile(File decoded) throws IOException {

		File temp = FileManagerUtils.createTempFile();
		Base64.decodeFileToFile(decoded.getAbsolutePath(),temp.getAbsolutePath());
		return temp;
	}
}

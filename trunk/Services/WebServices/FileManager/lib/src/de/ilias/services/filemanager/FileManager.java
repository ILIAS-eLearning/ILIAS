/*
 * To change this template, choose Tools | Templates and open the template in
 * the editor.
 */
package de.ilias.services.filemanager;

import de.ilias.services.filemanager.controller.MainController;
import de.ilias.services.filemanager.layout.LayoutFactory;
import de.ilias.services.filemanager.layout.LayoutMaster;
import de.ilias.services.filemanager.rest.RestClientConnector;
import de.ilias.services.filemanager.soap.SoapClientConnector;
import de.ilias.services.filemanager.soap.SoapClientConnectorException;
import java.io.IOException;
import java.util.Map;
import java.util.logging.Level;
import java.util.logging.Logger;
import javafx.application.Application;
import javafx.stage.Stage;
import netscape.javascript.JSObject;

/**
 *
 * @author stefan
 */
public class FileManager extends Application {
    
    private static final Logger logger = Logger.getLogger(FileManager.class.getName());
	
	public static final int FILE_MANAGER_MODE_DEFAULT = 0;
	public static final int FILE_MANAGER_MODE_EXPLORER = 1;
	
	private static FileManager instance = null;
	
	// Environment
	private JSObject browser = null;
	private boolean isApplet = false;

	private Stage stage = null;
	
	// Soap environment
	private int soapRid = 1;
	private String soapSid = "";
	private String soapClient = "filemanager";
	private String soapWsdl = "http://localhost/~stefan/ilias42_fm/webservice/soap/server.php?wsdl";
	private int soapUserId = 6;
	private String soapUser = "root";
	private String soapPass = "homerhomer";
	
	private String restServer = "http://localhost/~stefan/ilias42_fm/Services/WebServices/Rest/server.php";
	
	private String mainTitle = "ILIAS open source";
	private boolean fileLocks = false;
	private int uploadFileSize = 100;
	private int fmMode = FileManager.FILE_MANAGER_MODE_EXPLORER;
	
	/**
	 * Main
	 * @param args 
	 */
	public static void main(String[] args) {
        
		Application.launch(FileManager.class, args);
    }
	
	/**
	 * Get singleton instance
	 * @return 
	 */
	public static FileManager getInstance() {
		return instance;
	}
	
	/**
	 * Get stage
	 * @return 
	 */
	public Stage getStage() {
		return stage;
	}
	
	/**
	 * Check if running applet
	 * @return 
	 */
	public boolean isApplet() {
		return isApplet;
	}
	
	/**
	 * Get initial repository container id
	 */
	public int getInitialRepositoryContainerId() {
		return this.soapRid;
	}
	
	/**
	 * Check if file locks are enabled
	 * @return 
	 */
	public boolean enabledFileLocks() {
		return this.fileLocks;
	}
	
	/**
	 * Set file manager mode
	 * @param mode 
	 */
	public void setFmMode(int mode) {
		this.fmMode = mode;
	}
	
	/**
	 * get file manager mode (default mode or (windows explorer mode))
	 * @return int
	 */
	public int getFmMode() {
		return this.fmMode;
	}
	
	/**
	 * get upload file size
	 * @return 
	 */
	public int getUploadFilesize() {
		return this.uploadFileSize;
	}
	
	/**
	 * set upload file size
	 * @param size 
	 */
	public void setUploadFilesize(int size) {
		this.uploadFileSize = size;
	}
    
    /**
	 * Start application
	 * @param stage
	 * @throws Exception 
	 */
    public void start(Stage stage) throws Exception {
		
		long mem = Runtime.getRuntime().maxMemory();
		
		logger.info("Max memory: " + String.valueOf(mem));
				
		
		initEnvironment(stage);
		initSoapConnector();
		initRestConnector();
		
		LayoutMaster master = null;
		
		try {
			master = LayoutFactory.getInstance(stage);
			master.init();
			master.show();
			
			// @todo get rid
			MainController.getInstance().getMainTitle().setText(mainTitle);
			
		}
		catch(IOException e) {

			logger.log(Level.SEVERE,e.getMessage());
			e.printStackTrace();
			
		}
    }
	
	/**
	 * Init environment
	 */
	private void initEnvironment(Stage st) {
		
		instance = this;
		stage = st;
		
		Logger.getLogger("de.ilias.services.filemanager").setLevel(Level.ALL);
		
		stage.setTitle("File Manager - ILIAS");
		
		try {
			browser = getHostServices().getWebContext();
			if(browser != null)
				isApplet = true;
		}
		catch(Exception e) {
			isApplet = false;
			System.out.println(e.getMessage());
		}
		// Parameters passed to application
		Parameters params = this.getParameters();
		Map<String, String> map = params.getNamed();
		
		if(map.containsKey("sid")) {
			this.soapSid = map.get("sid");
		}
		if(map.containsKey("uid")) {
			this.soapUserId = Integer.parseInt(map.get("uid"));
		}
		if(map.containsKey("rid")) {
			this.soapRid = Integer.parseInt(map.get("rid"));
		}
		if(map.containsKey("wsd")) {
			this.soapWsdl = map.get("wsd");
		}
		if(map.containsKey("restServer")) {
			this.restServer = map.get("restServer");
		}
		if(map.containsKey("localFrame")) {
			LayoutFactory.getInstance(st).enableLocalFrame(map.get("localFrame").equalsIgnoreCase("1") ? true : false);
		}
		if(map.containsKey("headerTitle")) {
			this.mainTitle = map.get("headerTitle");
		}
		if(map.containsKey("fileLocks")) {
			this.fileLocks = map.get("fileLocks").equalsIgnoreCase("1") ? true : false;
		}
		if(map.containsKey("explorerMode")) {
			// @todo: support more modes
			this.fmMode = map.get("explorerMode").equalsIgnoreCase("1") ? 1 : 0;
		}
		if(map.containsKey("uploadFileSize")) {
			this.setUploadFilesize(Integer.parseInt(map.get("uploadFileSize")));
		}
	}
	
	/**
	 * Init soap connection
	 */
	private void initSoapConnector() {
		
		try {
			SoapClientConnector client;
			
			client = SoapClientConnector.getInstance();
			client.setWsdlUri(soapWsdl);
			client.setClient(soapClient);
			client.setUser(soapUser);
			client.setUserId(soapUserId);
			client.setPassword(soapPass);
			client.setSessionId(soapSid);
			
			client.init();
			client.initUser();
		} 
		catch(SoapClientConnectorException ex) {
			logger.severe(ex.getMessage());
			logger.log(Level.SEVERE,ex.getMessage());
			ex.printStackTrace();
		}
	}
	
	private void initRestConnector() {
		
		RestClientConnector client;
		
		client = RestClientConnector.getInstance();
		client.setServer(restServer);
	}
}

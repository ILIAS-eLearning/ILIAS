package com.yahoo.test.SelNG.YUI.tests;

import com.yahoo.test.SelNG.framework.core.SelNGBase;
import com.yahoo.test.SelNG.framework.util.BrowserUtil;
import com.yahoo.test.SelNG.framework.util.Customreport;
import com.yahoo.test.SelNG.framework.util.DumpMonitor;
import com.yahoo.test.SelNG.framework.util.ThreadSafeSeleniumSessionStorage;

import static com.yahoo.test.SelNG.framework.util.ThreadSafeSeleniumSessionStorage.session;
import org.apache.commons.configuration.Configuration;
import org.apache.commons.configuration.PropertiesConfiguration;
import org.testng.annotations.*;


/**
 * @author
 */

public class CommonTest extends SelNGBase {

	public static final int waitTimeInSeconds = 5;


	/*Non Grid section 
     * Following methods are for execution in normal mode and not in grid mode. Make sure to include abstract and non-grid groups and
     * exclude grid group in testng.xml file 
    */
	
	@Parameters({ "config-file" })
    @BeforeTest(groups = { "abstract", "non-grid" })
	public void initFramework(String configfile) throws Exception {
			Configuration conf = new PropertiesConfiguration(configfile);
			super.Preparedtdfilesfrom_YALA(conf);	
			super.initXMLFiles(conf);
		
	}
	
	@BeforeTest(groups = { "abstract", "non-grid" },dependsOnMethods = { "initFramework" })
	public void initTests() throws Exception{
		DumpMonitor.monitor(true);
		super.startSeleniumServer();

	}

	@BeforeMethod(groups = { "abstract", "non-grid"})
	public void setUpTests() {
		try {
			super.openBrowser();
			session().setSpeed("500");
			SelNGBase.screenshotfilename="";
		} catch (Exception e) {
			logger.info(e.getMessage());
		}
	}
	
	@AfterMethod(groups = { "abstract", "non-grid" })
	public void tearDownTests() throws Exception{
		try {

			//Capture screenshot if 'CAPTURESCREENSHOT' set to true
			if(SelNGBase.config.getString("CAPTURESCREENSHOT")!=null){

				if(SelNGBase.config.getString("CAPTURESCREENSHOT").equalsIgnoreCase("true")){
					SelNGBase.screenshotfilename=Customreport.capturescreenshot();
					logger.info("Screenshot file name is "+SelNGBase.screenshotfilename);
				}
				
			}
			try {
				BrowserUtil.deleteCookie("Y", "/");
				BrowserUtil.deleteCookie("T", "/");
			} catch (Exception f){ };
			super.closeBrowser();
		} catch (Exception e) {
			logger.info(e.getMessage());
		}
	}

	@AfterTest(groups = { "abstract", "non-grid" })
	public void cleanUp() {
		try {
			DumpMonitor.monitor(false);
			try {
				BrowserUtil.deleteCookie("Y", "/");
				BrowserUtil.deleteCookie("T", "/");
			} catch (Exception f){ }

			super.stopSeleniumServer();
		} catch (Exception e) {
			logger.info(e.getMessage());
		}
	}

	

	
	
	/* 
	 * Grid  section
     * Following methods are for execution in Grid mode . Make sure to include abstract and grid group and exclude non-grid group in testng.xml file 
	 */
	
	@Parameters({ "config-file" })
	@BeforeTest(groups = {"abstract", "grid"})
	public void initTestInGrid(String configfile) throws Exception {
		logger.warn("Config File :" + configfile);
		config = new PropertiesConfiguration(configfile);
		super.Preparedtdfilesfrom_YALA(config);
		super.initXMLFilesInGrid(config);
		try {
			super.openBrowserInGrid();
		} catch (Exception e) {
			logger.info(e.getMessage());
		} finally {
			ThreadSafeSeleniumSessionStorage.resetSession();
		}
	}
	
	@BeforeMethod(groups = {"abstract", "grid"})
	public void setupTestGrid() {
		try {	
			super.initXMLFilesInGrid(config);
			super.openBrowserInGrid();
		}catch (Exception e) {
			logger.info(e.getMessage());
		}
	}
	
	@AfterMethod(groups = { "abstract","grid"})
	public void tearDownTestGrid() throws Exception{
		try {
			super.resetExpectedValueHashMap();
			if(config.getString("BROWSER").equals("IE")){
				ThreadSafeSeleniumSessionStorage.resetSession();
			} else {
				super.closeBrowserInGrid();
				
			}
		}catch (Exception e) {
			logger.info(e.getMessage());
		}
	}

}


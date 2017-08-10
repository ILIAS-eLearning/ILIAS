package com.yahoo.test.SelNG.YUI.tests;

import static org.testng.Assert.fail;

import org.apache.log4j.Logger;
import org.testng.annotations.Parameters;
import org.testng.annotations.Test;

import com.yahoo.test.SelNG.YUI.library.TabView;
import com.yahoo.test.SelNG.framework.util.SelNGRetryAnalyzer;

import javax.xml.parsers.DocumentBuilder;
import javax.xml.parsers.DocumentBuilderFactory;
import org.w3c.dom.Document;


public class TabViewTest extends CommonTest{
	
	//private String uri = "http://presentbright.corp.yahoo.com/yui2/latest_build/examples/tabview/frommarkup_clean.html";
	
	public static Logger logger = Logger.getLogger(TabViewTest.class.getName());

	@Parameters({"uri", "tab-xml-file"})
	@Test(groups = {"demo"}, retryAnalyzer = SelNGRetryAnalyzer.class)
	public void TabView(String uri, String tabXmlFile) {
		logger.info("Invoked Tabview Method");
		try {
			Document doc = xmlFileToDocument(tabXmlFile);
			TabView.tabTest(uri, doc);
	    } catch (Throwable t) {
	      t.printStackTrace(System.out);
	      logger.error(t);
	      fail();
	    }
	}

	private Document xmlFileToDocument(String xmlFile) throws Exception {
		//File file = new File(xmlFile);
		DocumentBuilderFactory dbf = DocumentBuilderFactory.newInstance();
		DocumentBuilder db = dbf.newDocumentBuilder();
		//Document doc = db.parse(file);
		Document doc = db.parse(xmlFile);
		//doc.getDocumentElement().normalize();
		return doc;
	}
}
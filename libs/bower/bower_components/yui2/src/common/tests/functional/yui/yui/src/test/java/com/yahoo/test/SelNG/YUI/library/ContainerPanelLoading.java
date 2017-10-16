package com.yahoo.test.SelNG.YUI.library;

import com.yahoo.test.SelNG.framework.core.SelNGBase;
import static com.yahoo.test.SelNG.framework.util.ThreadSafeSeleniumSessionStorage.session;
// import static com.thoughtworks.selenium.*;
import static org.testng.Assert.*;


public class ContainerPanelLoading extends SelNGBase {

	final private static int XHR_WAIT = 5;

	public static void containerTest() throws Exception {
		
		// check that panel IS full modal
		// is mask visible and size of document -- if only can do viewport, then red flag

		session().open("http://presentbright.corp.yahoo.com/yui2/latest_build/examples/container/panel-loading_clean.html");
		//assertEquals(session().getTitle(), "");

		// Check initial state
		assertFalse(hasAttribute("content", "style", "visibility: visible;"));
		String response = session().getText("content");
		assertTrue(response.equals(""));
		
		// Show the dialog box
		session().click("panelbutton");
		Thread.sleep(500);  // "wait_c" is dynamically built
		assertTrue(hasAttribute("wait_c", "style", "visibility: visible;"));
		sendXhrContains("content", "Lorem ipsum dolor sit amet");
		assertTrue(hasAttribute("wait_c", "style", "visibility: hidden;"));
		//response = session().getText("content");
		//assertTrue(response.contains("Lorem ipsum dolor sit amet"));
		
	}
	
	public static boolean hasAttribute(String elXpath, String attributeName, String attributeValue) {
		
		String attribute = session().getAttribute(elXpath + "@" + attributeName);
		return ((attribute != null) && (attribute.contains(attributeValue)));

	}

    public static void sendXhrContains(String el, String expected) {

    	// wait for the XHR to complete
    	for (int second = 0;; second++) {
    		if (second >= XHR_WAIT) { 
    			fail("XHR timeout");
    		}
    		try {
    			if (expected.contains(session().getText(el))) {
    				break;
    			}
    		} catch (Exception e) {
    			e.printStackTrace();
    		}
    		try {
    			Thread.sleep(1000);
    		} catch (InterruptedException e) {
    			e.printStackTrace();
    		}
    	}
    }

}

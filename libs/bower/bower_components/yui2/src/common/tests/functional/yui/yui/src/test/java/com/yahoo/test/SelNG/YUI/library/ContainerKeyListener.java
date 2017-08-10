package com.yahoo.test.SelNG.YUI.library;

import com.yahoo.test.SelNG.framework.core.SelNGBase;
import static com.yahoo.test.SelNG.framework.util.ThreadSafeSeleniumSessionStorage.session;
// import static com.thoughtworks.selenium.*;
import static org.testng.Assert.*;


public class ContainerKeyListener extends SelNGBase {


	public static void containerTest() throws Exception {
		
		// This test does not work at this time as Selenium does not have a way to send keystrokes to a window

		session().open("http://presentbright.corp.yahoo.com/yui2/latest_build/examples/container/keylistener_clean.html");
		//assertEquals(session().getTitle(), "Basic Drag and Drop");

		// Check initial state
		assertEquals(getStyleAttribute("panel1_c", "visibility"), "hidden");

		// Show the panel
		session().click("show");
		assertEquals(getStyleAttribute("panel1_c", "visibility"), "visible");
		
		// Hide the panel
		session().click("hide");
		assertEquals(getStyleAttribute("panel1_c", "visibility"), "hidden");

		// Show the panel with cntrl Y
		session().type("//", "\25");
		Thread.sleep(5000);
		session().click("show");
		Thread.sleep(5000);
		session().type("//", "\27");
		Thread.sleep(5000);
		
	}
	
	public static String getStyleAttribute(String el, String attribute) {

		String ret = "";

		String style = session().getAttribute(el+"@style").toLowerCase();
		if( ! style.contains(attribute.toLowerCase())) {
		    return ret;
		}

		String[] styles = style.split(";");
		
		for(int i=0; i<styles.length; i++) {
			if(styles[i].contains(attribute)) {
				String[] tokens = styles[i].split(":");
				ret = tokens[1].trim();
				break;
			}
		}
		
		return ret;


	    } 
}

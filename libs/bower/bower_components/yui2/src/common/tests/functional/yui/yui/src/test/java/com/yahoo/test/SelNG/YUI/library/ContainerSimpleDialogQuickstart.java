package com.yahoo.test.SelNG.YUI.library;

import com.yahoo.test.SelNG.framework.core.SelNGBase;
import static com.yahoo.test.SelNG.framework.util.ThreadSafeSeleniumSessionStorage.session;
// import static com.thoughtworks.selenium.*;
import static org.testng.Assert.*;


public class ContainerSimpleDialogQuickstart extends SelNGBase {


	public static void containerTest() throws Exception {

		session().open("http://presentbright.corp.yahoo.com/yui2/latest_build/examples/container/simpledialog-quickstart_clean.html");
		//assertEquals(session().getTitle(), "Basic Drag and Drop");

		// Check initial state
		assertTrue(hasAttribute("simpledialog1_c", "style", "visibility: hidden;"));
		
		// Show the dialog box
		session().click("show");
		assertTrue(hasAttribute("simpledialog1_c", "style", "visibility: visible;"));

		// Hide the dialog box with the hide button
		session().click("hide");
		assertTrue(hasAttribute("simpledialog1_c", "style", "visibility: hidden;"));
		
		// Show the dialog box
		session().click("show");
		assertTrue(hasAttribute("simpledialog1_c", "style", "visibility: visible;"));

		// Hide the dialog with the close button
		session().click("//a[@class='container-close']");
		assertTrue(hasAttribute("simpledialog1_c", "style", "visibility: hidden;"));
		
		// Show the dialog box
		session().click("show");
		assertTrue(hasAttribute("simpledialog1_c", "style", "visibility: visible;"));
		
		// push the No button
		session().click("yui-gen1-button");
		assertTrue(hasAttribute("simpledialog1_c", "style", "visibility: hidden;"));
		
		// show the dialog
		session().click("show");
		
		// press the Yes button
		session().click("yui-gen0-button");
		assertEquals(session().getAlert(), "You clicked yes!");
		assertTrue(hasAttribute("simpledialog1_c", "style", "visibility: hidden;"));
		
	}
	
	public static boolean hasAttribute(String elXpath, String attributeName, String attributeValue) {
		
		String attribute = session().getAttribute(elXpath + "@" + attributeName);
		return ((attribute != null) && (attribute.contains(attributeValue)));
	}

}

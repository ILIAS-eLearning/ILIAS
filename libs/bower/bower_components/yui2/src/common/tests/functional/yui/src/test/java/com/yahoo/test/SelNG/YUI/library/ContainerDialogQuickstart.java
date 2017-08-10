package com.yahoo.test.SelNG.YUI.library;

import com.yahoo.test.SelNG.framework.core.SelNGBase;
import static com.yahoo.test.SelNG.framework.util.ThreadSafeSeleniumSessionStorage.session;
// import static com.thoughtworks.selenium.*;
import static org.testng.Assert.*;


public class ContainerDialogQuickstart extends SelNGBase {

	private static final int MOVE_X = 20;
	private static final int MOVE_Y = 10;
	
	private static final int XHR_WAIT = 5; // seconds

	public static void containerTest() throws Exception {

		session().open("http://presentbright.corp.yahoo.com/yui2/latest_build/examples/container/dialog-quickstart_clean.html");
		//assertEquals(session().getTitle(), "");

		// Check initial state
		assertTrue(Util.hasAttribute("dialog1_c", "style", "visibility: hidden;"));
		
		// Show the dialog box
		session().click("show");
		assertTrue(Util.hasAttribute("dialog1_c", "style", "visibility: visible;"));

		// Get the initial position of the Dialog
		Number X = session().getElementPositionLeft("dialog1_c");
		Number Y = session().getElementPositionTop("dialog1_c");
		
		// Move the Dialog
		session().dragAndDrop("dialog1_h", "+" + MOVE_X + ",+" + MOVE_Y);
		Number newX = session().getElementPositionLeft("dialog1_c");
		Number newY = session().getElementPositionTop("dialog1_c");
		int deltaX = X.intValue() + MOVE_X;
		int deltaY = Y.intValue() + MOVE_Y;
		assertEquals(newX, deltaX);
		assertEquals(newY, deltaY);
		
		// Close (hide) the Dialog
		session().click("//a[@class='container-close']");
		assertTrue(Util.hasAttribute("dialog1_c", "style", "visibility: hidden;"));
		
		// Click the show button
		session().click("show");
		assertTrue(Util.hasAttribute("dialog1_c", "style", "visibility: visible;"));
		
		// Fill in the fields
		session().type("firstname", "Ed");
		session().type("lastname", "Wood");
		session().type("email", "woody@plan9.org");
		session().addSelection("state[]", "label=New Jersey");
		session().click("//input[@name='radiobuttons[]' and @value='2']");
		session().click("check");
		session().type("textarea", "Here is some text for the textarea");
		session().click("cbarray[]");
		session().click("//input[@name='cbarray[]' and @value='2']");
		
		// Click on the Cancel button
		session().click("yui-gen1-button");
		assertTrue(Util.hasAttribute("dialog1_c", "style", "visibility: hidden;"));

		String response = session().getText("resp");
		String noResponse = "Server response will be displayed in this area";
		assertEquals(response, noResponse);

		// Show the dialog with the data still in it
		session().click("show");
		assertTrue(Util.hasAttribute("dialog1_c", "style", "visibility: visible;"));

		// click on submit button
		String expectedResponse = "Submitted Datafirstname: Edlastname: Woodemail: woody@plan9.orgstate: New Jerseyradiobuttons: 2check: 1textarea: Here is some text for the textareacbarray: 1, 2";				
		session().click("yui-gen0-button");
		sendXhrEquals("resp", expectedResponse);

	}

	/*
	 * Sends and XML Http Request and checks the response in the el against the expected
	 */
	private static void sendXhrEquals(String el, String expected) {

    	// wait for the XHR to complete
		for (int second = 0;; second++) {
    		if (second >= XHR_WAIT) { 
    			fail("XHR timeout");
    		}
    		try {
    			String response = session().getText(el).replaceAll("\n", "");
    			//if (expected.contains(session().getText(el))) {
    			if (response.contains(expected)) {
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

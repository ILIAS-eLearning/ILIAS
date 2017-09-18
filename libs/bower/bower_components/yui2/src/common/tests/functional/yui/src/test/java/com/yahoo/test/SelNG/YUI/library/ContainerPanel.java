package com.yahoo.test.SelNG.YUI.library;

import com.yahoo.test.SelNG.framework.core.SelNGBase;
import static com.yahoo.test.SelNG.framework.util.ThreadSafeSeleniumSessionStorage.session;
// import static com.thoughtworks.selenium.*;
import static org.testng.Assert.*;


public class ContainerPanel extends SelNGBase {

	private static final int MOVE_X = 100;
	private static final int MOVE_Y = 150;

	public static void containerTest() {
		
		session().open("http://presentbright.corp.yahoo.com/yui2/latest_build/examples/container/panel_clean.html");
		//assertEquals(session().getTitle(), "Basic Drag and Drop");

		// Check initial state
		assertTrue(Util.hasAttribute("panel1_c", "style", "visibility: hidden"));
		assertTrue(Util.hasAttribute("panel2_c", "style", "visibility: hidden"));
		
		// Click on Show panel 1
		session().click("show1");
		assertTrue(Util.hasAttribute("panel1_c", "style", "visibility: visible"));
		assertTrue(Util.hasAttribute("panel2_c", "style", "visibility: hidden"));
		Number X = session().getElementPositionLeft("panel1_c");
		Number Y = session().getElementPositionTop("panel1_c");
		session().dragAndDrop("panel1_h", "+" + MOVE_X + ",+" + MOVE_Y);
		Number newX = session().getElementPositionLeft("panel1_c");
		Number newY = session().getElementPositionTop("panel1_c");
		int deltaX = X.intValue() + MOVE_X;
		int deltaY = Y.intValue() + MOVE_Y;
		assertEquals(deltaX, newX.intValue());
		assertEquals(deltaY, newY.intValue());

		// Click on Hide Panel 1
		session().click("hide1");
		assertTrue(Util.hasAttribute("panel1_c", "style", "visibility: hidden"));
		assertTrue(Util.hasAttribute("panel2_c", "style", "visibility: hidden"));

		// Click on Show panel 1
		session().click("show1");
		assertTrue(Util.hasAttribute("panel1_c", "style", "visibility: visible"));
		assertTrue(Util.hasAttribute("panel2_c", "style", "visibility: hidden"));
		// Hide the panel with the 'close' icon
		session().click("//a[@class='container-close']");
		assertTrue(Util.hasAttribute("panel1_c", "style", "visibility: hidden"));
		assertTrue(Util.hasAttribute("panel2_c", "style", "visibility: hidden"));
		
		// Click on Show Panel 2
		session().click("show2");
		assertTrue(Util.hasAttribute("panel1_c", "style", "visibility: hidden"));
		assertTrue(Util.hasAttribute("panel2_c", "style", "visibility: visible"));

		// Try to drag Panel 2
		X = session().getElementPositionLeft("panel2_c");
		Y = session().getElementPositionTop("panel2_c");
		session().dragAndDrop("panel2_c", "+" + MOVE_X + ",+" + MOVE_Y);
		newX = session().getElementPositionLeft("panel2_c");
		newY = session().getElementPositionTop("panel2_c");
		assertEquals(X.intValue(), newX.intValue());
		assertEquals(Y.intValue(), newY.intValue());
		
		// Click on Hide Panel 2
		session().click("hide2");
		assertTrue(Util.hasAttribute("panel1_c", "style", "visibility: hidden"));
		assertTrue(Util.hasAttribute("panel2_c", "style", "visibility: hidden"));
		
		// Open both panels
		session().click("show1");
		session().click("show2");
		assertTrue(Util.hasAttribute("panel1_c", "style", "visibility: visible"));
		assertTrue(Util.hasAttribute("panel2_c", "style", "visibility: visible"));
		
		// Close both panels
		session().click("hide1");
		session().click("hide2");
		assertTrue(Util.hasAttribute("panel1_c", "style", "visibility: hidden"));
		assertTrue(Util.hasAttribute("panel2_c", "style", "visibility: hidden"));

	}
	

}

package com.yahoo.test.SelNG.YUI.library;

import com.yahoo.test.SelNG.framework.core.SelNGBase;
import static com.yahoo.test.SelNG.framework.util.ThreadSafeSeleniumSessionStorage.session;
// import static com.thoughtworks.selenium.*;
import static org.testng.Assert.*;


public class ContainerPanelResize extends SelNGBase {
	
	private static final int MOVE_X = 200;
	private static final int MOVE_Y = 100;
	private static final int RESIZE_X = 40;
	private static final int RESIZE_Y = 60;


	public static void containerTest() {

		session().open("http://presentbright.corp.yahoo.com/yui2/latest_build/examples/container/panel-resize_source.html");
		//assertEquals(session().getTitle(), "Basic Drag and Drop");

		// Check initial state
		assertTrue(hasAttribute("resizablepanel_c", "style", "visibility: visible;"));

		// Get the initial size and position of the Panel
		Number X = session().getElementPositionLeft("resizablepanel_c");
		Number Y = session().getElementPositionTop("resizablepanel_c");
		Number width =  session().getElementWidth("resizablepanel");
		Number height=  session().getElementHeight("resizablepanel");
		
		// Move the Panel
		session().dragAndDrop("resizablepanel_h", "+" + MOVE_X + ",+" + MOVE_Y);
		Number newX = session().getElementPositionLeft("resizablepanel_c");
		Number newY = session().getElementPositionTop("resizablepanel_c");
		int deltaX = X.intValue() + MOVE_X;
		int deltaY = Y.intValue() + MOVE_Y;
		assertEquals(newX+"", deltaX+"");
		assertEquals(newY+"", deltaY+"");
		
		// Resize the Panel
		session().dragAndDrop("yui-gen0", "+" + RESIZE_X + ",+" + RESIZE_Y);
		Number newWidth = session().getElementWidth("resizablepanel");
		Number newHeight = session().getElementHeight("resizablepanel");
		deltaX = width.intValue() + RESIZE_X -8;  // the -8 is a fudge factor due to the size of the resize element
		deltaY = height.intValue() + RESIZE_Y -8;
		assertEquals(newWidth+"", deltaX+"");
		assertEquals(newHeight+"", deltaY+"");
		
		// Close (hide) the panel
		session().click("//a[@class='container-close']");
		assertTrue(hasAttribute("resizablepanel_c", "style", "visibility: hidden;"));
		
		// Click the show button
		session().click("showbtn");
		assertTrue(hasAttribute("resizablepanel_c", "style", "visibility: visible;"));
		
	}
	
	public static boolean hasAttribute(String elXpath, String attributeName, String attributeValue) {
		
		String attribute = session().getAttribute(elXpath + "@" + attributeName);
		return ((attribute != null) && (attribute.contains(attributeValue)));
	}

}

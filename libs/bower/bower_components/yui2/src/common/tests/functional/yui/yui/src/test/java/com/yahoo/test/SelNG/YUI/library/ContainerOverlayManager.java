package com.yahoo.test.SelNG.YUI.library;

import com.yahoo.test.SelNG.framework.core.SelNGBase;
import static com.yahoo.test.SelNG.framework.util.ThreadSafeSeleniumSessionStorage.session;
// import static com.thoughtworks.selenium.*;
import static org.testng.Assert.*;


public class ContainerOverlayManager extends SelNGBase {

	private static final int MOVE_X = 600;
	private static final int MOVE_Y = 150;

	private static final String Z_INDEX = "z-index";
	
	public static void containerTest() {
		
		// check that for correct class to signify 'focus' 

		session().open("http://presentbright.corp.yahoo.com/yui2/latest_build/examples/container/overlaymanager_clean.html");
		//assertEquals(session().getTitle(), "");

		// Check initial state
		assertTrue(hasAttribute("panel1_c", "style", "visibility: hidden;"));
		assertTrue(hasAttribute("panel2_c", "style", "visibility: hidden;"));
		assertTrue(hasAttribute("panel3_c", "style", "visibility: hidden;"));
		// nothing should be in focus
		assertFalse(hasAttribute("panel1_c", "class", "focused"));
		assertFalse(hasAttribute("panel2_c", "class", "focused"));
		assertFalse(hasAttribute("panel3_c", "class", "focused"));
		
		// Click on Show panel 1
		session().click("show1");
		assertTrue(hasAttribute("panel1_c", "style", "visibility: visible;"));
		assertTrue(hasAttribute("panel2_c", "style", "visibility: hidden;"));
		assertTrue(hasAttribute("panel3_c", "style", "visibility: hidden;"));
		// nothing should be in focus
		assertFalse(hasAttribute("panel1_c", "class", "focused"));
		assertFalse(hasAttribute("panel2_c", "class", "focused"));
		assertFalse(hasAttribute("panel3_c", "class", "focused"));
		
		// Click on show panel 2
		session().click("show2");
		assertTrue(hasAttribute("panel1_c", "style", "visibility: visible;"));
		assertTrue(hasAttribute("panel2_c", "style", "visibility: visible;"));
		assertTrue(hasAttribute("panel3_c", "style", "visibility: hidden;"));
		// nothing should be in focus
		assertFalse(hasAttribute("panel1_c", "class", "focused"));
		assertFalse(hasAttribute("panel2_c", "class", "focused"));
		assertFalse(hasAttribute("panel3_c", "class", "focused"));

		// focus panel 1 and bring it to the top and check for focus
		session().mouseDown("panel1_c");
		assertTrue(hasAttribute("panel1_c", "class", "focused"));
		assertFalse(hasAttribute("panel2_c", "class", "focused"));
		assertFalse(hasAttribute("panel3_c", "class", "focused"));
		
		// Click on Show panel 3
		session().click("show3");
		assertTrue(hasAttribute("panel1_c", "style", "visibility: visible;"));
		assertTrue(hasAttribute("panel2_c", "style", "visibility: visible;"));
		assertTrue(hasAttribute("panel3_c", "style", "visibility: visible;"));
		assertTrue(hasAttribute("panel1_c", "class", "focused"));
		assertFalse(hasAttribute("panel2_c", "class", "focused"));
		assertFalse(hasAttribute("panel3_c", "class", "focused"));
		
		// Focus panel 2
		session().mouseDown("panel2_c");
		assertFalse(hasAttribute("panel1_c", "class", "focused"));
		assertTrue(hasAttribute("panel2_c", "class", "focused"));
		assertFalse(hasAttribute("panel3_c", "class", "focused"));
		
		// Focus panel 3
		session().mouseDown("panel3_c");
		assertFalse(hasAttribute("panel1_c", "class", "focused"));
		assertFalse(hasAttribute("panel2_c", "class", "focused"));
		assertTrue(hasAttribute("panel3_c", "class", "focused"));
		
		// Move the panels
		checkMove("panel1_h");
		checkMove("panel2_h");
		checkMove("panel3_h");

		// At this point, the panels are stacked in reverse order.....panel 3 is on top
		// check that it has the highest z-index
		int p1Z = getZindex("panel1_c");
		int p2Z = getZindex("panel2_c");
		int p3Z = getZindex("panel3_c");
		assertTrue((p3Z > p2Z) && (p2Z > p1Z));
		
		// Hide all the panels
		session().click("hideAll");
		assertTrue(hasAttribute("panel1_c", "style", "visibility: hidden;"));
		assertTrue(hasAttribute("panel2_c", "style", "visibility: hidden;"));
		assertTrue(hasAttribute("panel3_c", "style", "visibility: hidden;"));
		
		// Show all the panels
		session().click("showAll");
		assertTrue(hasAttribute("panel1_c", "style", "visibility: visible;"));
		assertTrue(hasAttribute("panel2_c", "style", "visibility: visible;"));
		assertTrue(hasAttribute("panel3_c", "style", "visibility: visible;"));

		// Bring panel1 to the top
		session().click("focus1");
		assertTrue(hasAttribute("panel1_c", "style", "visibility: visible;"));
		assertTrue(hasAttribute("panel2_c", "style", "visibility: visible;"));
		assertTrue(hasAttribute("panel3_c", "style", "visibility: visible;"));

		// At this point, panel1 should be on the top
		p1Z = getZindex("panel1_c");
		p2Z = getZindex("panel2_c");
		p3Z = getZindex("panel3_c");
		assertTrue((p1Z > p3Z) && (p3Z > p2Z));
		
		// Bring panel2 to the top
		session().click("focus2");
		assertTrue(hasAttribute("panel1_c", "style", "visibility: visible;"));
		assertTrue(hasAttribute("panel2_c", "style", "visibility: visible;"));
		assertTrue(hasAttribute("panel3_c", "style", "visibility: visible;"));

		// At this point, panel2 should be on the top
		p1Z = getZindex("panel1_c");
		p2Z = getZindex("panel2_c");
		p3Z = getZindex("panel3_c");
		assertTrue((p2Z > p1Z) && (p1Z > p3Z));
		
		// Bring panel3 to the top
		session().click("focus3");
		assertTrue(hasAttribute("panel1_c", "style", "visibility: visible;"));
		assertTrue(hasAttribute("panel2_c", "style", "visibility: visible;"));
		assertTrue(hasAttribute("panel3_c", "style", "visibility: visible;"));

		// At this point, panel3 should be on the top
		p1Z = getZindex("panel1_c");
		p2Z = getZindex("panel2_c");
		p3Z = getZindex("panel3_c");
		assertTrue((p3Z > p2Z) && (p2Z > p1Z));
		
		// Use the close icon to hide the panels starting with 3
		session().click("//div[@id='panel3']/a");
		assertTrue(hasAttribute("panel1_c", "style", "visibility: visible;"));
		assertTrue(hasAttribute("panel2_c", "style", "visibility: visible;"));
		assertTrue(hasAttribute("panel3_c", "style", "visibility: hidden;"));

		// Hide panel2
		session().click("//div[@id='panel2']/a");
		assertTrue(hasAttribute("panel1_c", "style", "visibility: visible;"));
		assertTrue(hasAttribute("panel2_c", "style", "visibility: hidden;"));
		assertTrue(hasAttribute("panel3_c", "style", "visibility: hidden;"));

		// Hide panel1
		session().click("//div[@id='panel1']/a");
		assertTrue(hasAttribute("panel1_c", "style", "visibility: hidden;"));
		assertTrue(hasAttribute("panel2_c", "style", "visibility: hidden;"));
		assertTrue(hasAttribute("panel3_c", "style", "visibility: hidden;"));

	}

    public static void checkMove(String el) {

        Number X = session().getElementPositionLeft(el);
		Number Y = session().getElementPositionTop(el);
		session().dragAndDrop(el,"+" + MOVE_X + ",+" + MOVE_Y);
		Number newX = session().getElementPositionLeft(el);
		Number newY = session().getElementPositionTop(el);
		int expectedX = X.intValue() + MOVE_X;
		int expectedY = Y.intValue() + MOVE_Y;
		assertEquals(newX.intValue()+"",expectedX+"");
		assertEquals(newY.intValue()+"",expectedY+"");

    }
	
	public static int getZindex(String el) {
	
		String style = session().getAttribute(el+"@style");
		assertTrue(style.contains(Z_INDEX));
		String[] styles = style.split(";");

		int ret = 0;
		for(int i=0; i<styles.length; i++) {
			if(styles[i].contains(Z_INDEX)) {
				String[] tokens = styles[i].split(":");
				ret = Integer.parseInt(tokens[1].trim());
				break;
			}
		}
		
		return ret;
	
	}
	
	public static boolean hasAttribute(String elXpath, String attributeName, String attributeValue) {
		
		String attribute = session().getAttribute(elXpath + "@" + attributeName);
		return ((attribute != null) && (attribute.contains(attributeValue)));

	}

}

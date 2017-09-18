package com.yahoo.test.SelNG.YUI.library;

import com.yahoo.test.SelNG.framework.core.SelNGBase;
import static com.yahoo.test.SelNG.framework.util.ThreadSafeSeleniumSessionStorage.session;
// import static com.thoughtworks.selenium.*;
import static org.testng.Assert.*;


public class ContainerOverlay extends SelNGBase {
	
	// from the markup for the panel
	private static final int OVERLAY_WIDTH = 300;
	private static final int OVERLAY_HEIGHT = 110;
	private static final int OVERLAY2_X = 600;
	private static final int OVERLAY2_Y = 200;
	private static final int OVERLAY4_OFFSET_X = 20;
	private static final int OVERLAY4_OFFSET_Y = 30;


	public static void containerTest() {
		
		//selenium.open("http://presentbright.corp.yahoo.com/yui2/latest_build/examples/container/overlay_clean.html");
		session().open("http://10.72.112.142/dev/gitroot/yui2/src/container/tests/functional/html/ContainerPositionOverlay.html");
		//assertEquals(selenium.getTitle(), "");

		checkInitialState();
		checkViewportCenter();
		checkOverlayOneAttributes();
		checkOverlayTwo();
		checkOverlayThree();
		checkOverlayFour();
		checkAllOverlays();

	}
	
	private static void checkInitialState() {

		assertTrue(Util.hasAttribute("overlay1", "style", "visibility: hidden"));
		assertTrue(Util.hasAttribute("overlay2", "style", "visibility: hidden"));
		assertTrue(Util.hasAttribute("overlay3", "style", "visibility: hidden"));
		assertTrue(Util.hasAttribute("overlay4", "style", "visibility: hidden"));

	}

	private static void checkViewportCenter() {

		// compute the screen location for a viewport centered overlay
		int innerWidth = Integer.parseInt(session().getEval("this.browserbot.getCurrentWindow().innerWidth"));
		int innerHeight= Integer.parseInt(session().getEval("this.browserbot.getCurrentWindow().innerHeight"));
		int scrollX = Integer.parseInt(session().getEval("this.browserbot.getCurrentWindow().scrollX"));
		int scrollY = Integer.parseInt(session().getEval("this.browserbot.getCurrentWindow().scrollY"));
		int expectedX = ((int)(0.5 * (innerWidth - OVERLAY_WIDTH))) + scrollX - 14;  // for chrome
		int expectedY = ((int)(0.5 * (innerHeight - OVERLAY_HEIGHT))) + scrollY - 6;
		int actualX = (session().getElementPositionLeft("overlay1")).intValue();
		int actualY = (session().getElementPositionTop("overlay1")).intValue();
		assertEquals(expectedX, actualX);
		assertEquals(expectedY, actualY);
		
		// scroll the window to change the position of the viewport centered overlay
		// overlay will not reposition unless it is visible
		session().click("show1");
		session().getEval("this.browserbot.getCurrentWindow().scroll(0,500);");

		scrollX = Integer.parseInt(session().getEval("this.browserbot.getCurrentWindow().scrollX"));
		scrollY = Integer.parseInt(session().getEval("this.browserbot.getCurrentWindow().scrollY"));
		expectedX = ((int)(0.5 * (innerWidth - OVERLAY_WIDTH))) + scrollX - 14;  // for chrome
		expectedY = ((int)(0.5 * (innerHeight - OVERLAY_HEIGHT))) + scrollY - 6;
		actualX = (session().getElementPositionLeft("overlay1")).intValue();
		actualY = (session().getElementPositionTop("overlay1")).intValue();
		assertEquals(expectedX, actualX);
		assertEquals(expectedY, actualY);
		
		session().click("hide1");
		session().getEval("this.browserbot.getCurrentWindow().scroll(0,0);");

	}

	private static void checkOverlayOneAttributes() {

		session().click("show1");
		assertTrue(Util.hasAttribute("overlay1", "style", "visibility: visible"));
		assertTrue(Util.hasAttribute("overlay2", "style", "visibility: hidden"));
		assertTrue(Util.hasAttribute("overlay3", "style", "visibility: hidden"));
		assertTrue(Util.hasAttribute("overlay4", "style", "visibility: hidden"));
		session().click("hide1");
		
	}

	private static void checkOverlayTwo() {

		// Click on Show Overlay 2
		session().click("show2");
		assertTrue(Util.hasAttribute("overlay1", "style", "visibility: hidden"));
		assertTrue(Util.hasAttribute("overlay2", "style", "visibility: visible"));
		assertTrue(Util.hasAttribute("overlay3", "style", "visibility: hidden"));
		assertTrue(Util.hasAttribute("overlay4", "style", "visibility: hidden"));

		// Check Overlay 2 placement
		int currentX = (session().getElementPositionLeft("overlay2")).intValue();
		int currentY = (session().getElementPositionTop("overlay2")).intValue();
		assertEquals(currentX, OVERLAY2_X);
		assertEquals(currentY, OVERLAY2_Y);

		// Hide Overlay 2, resize the viewport and check that overlay is constrained to the viewport.
		session().click("hide2");
		session().getEval("this.browserbot.getCurrentWindow().innerWidth = 600");
		session().refresh(); // the page needs to be reloaded so that the contraint will work
		session().click("show2");
		currentX = (session().getElementPositionLeft("overlay2")).intValue();
		currentY = (session().getElementPositionTop("overlay2")).intValue();

		assertFalse( currentX == OVERLAY2_X );
		assertEquals(currentY, OVERLAY2_Y);
		
		// Click on Hide Overlay 2
		session().click("hide2");
		assertTrue(Util.hasAttribute("overlay1", "style", "visibility: hidden"));
		assertTrue(Util.hasAttribute("overlay2", "style", "visibility: hidden"));
		assertTrue(Util.hasAttribute("overlay3", "style", "visibility: hidden"));
		assertTrue(Util.hasAttribute("overlay4", "style", "visibility: hidden"));

		session().windowMaximize();
		session().refresh();
		
	}	

	private static void checkOverlayThree() {

		// Click on Show Overlay 3
		session().click("show3");
		assertTrue(Util.hasAttribute("overlay1", "style", "visibility: hidden"));
		assertTrue(Util.hasAttribute("overlay2", "style", "visibility: hidden"));
		assertTrue(Util.hasAttribute("overlay3", "style", "visibility: visible"));
		assertTrue(Util.hasAttribute("overlay4", "style", "visibility: hidden"));

		// get the expected position for the overlay
		int expectedX = (session().getElementPositionLeft("ctx")).intValue();
		int expectedY = (session().getElementPositionTop("ctx")).intValue() + (session().getElementHeight("ctx")).intValue();
		int actualX = (session().getElementPositionLeft("overlay3")).intValue();
		int actualY = (session().getElementPositionTop("overlay3")).intValue() + 1; // overlap
		assertEquals(expectedX, actualX);
		assertEquals(expectedY, actualY);

		// Click on Hide Overlay 3
		session().click("hide3");
		assertTrue(Util.hasAttribute("overlay1", "style", "visibility: hidden"));
		assertTrue(Util.hasAttribute("overlay2", "style", "visibility: hidden"));
		assertTrue(Util.hasAttribute("overlay3", "style", "visibility: hidden"));
		assertTrue(Util.hasAttribute("overlay4", "style", "visibility: hidden"));

	}

	private static void checkOverlayFour() {

		// Click on Show Overlay 4
		session().click("show4");
		assertTrue(Util.hasAttribute("overlay1", "style", "visibility: hidden"));
		assertTrue(Util.hasAttribute("overlay2", "style", "visibility: hidden"));
		assertTrue(Util.hasAttribute("overlay3", "style", "visibility: hidden"));
		assertTrue(Util.hasAttribute("overlay4", "style", "visibility: visible"));

		// get the expected position for the overlay
		int expectedX = (session().getElementPositionLeft("ctx")).intValue() + OVERLAY4_OFFSET_X;
		int expectedY = (session().getElementPositionTop("ctx")).intValue() + (session().getElementHeight("ctx")).intValue() + OVERLAY4_OFFSET_Y;
		int actualX = (session().getElementPositionLeft("overlay4")).intValue();
		int actualY = (session().getElementPositionTop("overlay4")).intValue() + 1; // overlap
		assertEquals(expectedX, actualX);
		assertEquals(expectedY, actualY);

		// Change the height of the viewport to be sure that overlay does not overlap its context element
		session().getEval("this.browserbot.getCurrentWindow().innerHeight = 300");
		actualX = (session().getElementPositionLeft("overlay4")).intValue();
		expectedY = (session().getElementPositionTop("ctx")).intValue() - (session().getElementHeight("overlay4")).intValue();
		actualY = (session().getElementPositionTop("overlay4")).intValue() + 1; // overlap
		assertEquals(actualX, expectedX);
		assertEquals(actualY, expectedY);

		session().windowMaximize();

		// Click on Hide Overlay 4
		session().click("hide4");
		assertTrue(Util.hasAttribute("overlay1", "style", "visibility: hidden"));
		assertTrue(Util.hasAttribute("overlay2", "style", "visibility: hidden"));
		assertTrue(Util.hasAttribute("overlay3", "style", "visibility: hidden"));
		assertTrue(Util.hasAttribute("overlay4", "style", "visibility: hidden"));

	}

	private static void checkAllOverlays() {

		// Open all Overlays
		session().click("show1");
		session().click("show2");
		session().click("show3");
		session().click("show4");
		assertTrue(Util.hasAttribute("overlay1", "style", "visibility: visible"));
		assertTrue(Util.hasAttribute("overlay2", "style", "visibility: visible"));
		assertTrue(Util.hasAttribute("overlay3", "style", "visibility: visible"));
		assertTrue(Util.hasAttribute("overlay4", "style", "visibility: visible"));
		
		// Close all Overlays
		session().click("hide1");
		session().click("hide2");
		session().click("hide3");
		session().click("hide4");
		assertTrue(Util.hasAttribute("overlay1", "style", "visibility: hidden"));
		assertTrue(Util.hasAttribute("overlay2", "style", "visibility: hidden"));
		assertTrue(Util.hasAttribute("overlay3", "style", "visibility: hidden"));
		assertTrue(Util.hasAttribute("overlay4", "style", "visibility: hidden"));

	}

}

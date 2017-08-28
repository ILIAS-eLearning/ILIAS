package com.yahoo.test.SelNG.YUI.library;

import com.yahoo.test.SelNG.framework.core.SelNGBase;
import static com.yahoo.test.SelNG.framework.util.ThreadSafeSeleniumSessionStorage.session;
import com.yahoo.test.SelNG.framework.util.BrowserUtil;
// import static com.thoughtworks.selenium.*;
import static org.testng.Assert.assertEquals;


public class DragAndDropShim extends SelNGBase {


	public static void ddTest() throws Exception {

		//  This does not run in IE as the drag is not performed
		
		session().open("http://presentbright.corp.yahoo.com/yui2/latest_build/examples/dragdrop/dd-shim_clean.html");

		session().click("shim");
		int elWidth = getElementWidth("demo");
		//int elHeight = getElementHeight("demo");
//		int innerWidth =  getViewportWidth();
		//int innerHeight = getViewportHeight();
//		int deltaX = -(innerWidth - elWidth - 170);
		int deltaY = 100;
//		moveElement("demo", deltaX, deltaY);
	}

	private static void moveElement(String el, int deltaX, int deltaY) {
	
		int X = (session().getElementPositionLeft(el)).intValue();
		int Y = (session().getElementPositionTop(el)).intValue();
		String deltaStr = deltaX + "," + deltaY;
		session().dragAndDrop(el, deltaStr);

		int newX = (session().getElementPositionLeft(el)).intValue();
		int newY = (session().getElementPositionTop(el)).intValue();

		assertEquals(deltaX + X, newX);
		assertEquals(deltaY + Y, newY);

	}
	
	/**
	 * 
	 * @param el
	 * @return
	 */
	private static int getElementHeight(String el) {
		return Integer.parseInt(session().getEval("this.browserbot.getCurrentWindow().document.getElementById('" + el + "').clientHeight;"));
	}
	
	/**
	 * 
	 * @param el
	 * @return
	 */
	private static int getElementWidth(String el) {
		return Integer.parseInt(session().getEval("this.browserbot.getCurrentWindow().document.getElementById('" + el + "').clientWidth;"));
	}
	
}

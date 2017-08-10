package com.yahoo.test.SelNG.YUI.library;

import com.yahoo.test.SelNG.framework.core.SelNGBase;
import static com.yahoo.test.SelNG.framework.util.ThreadSafeSeleniumSessionStorage.session;
import com.yahoo.test.SelNG.framework.util.BrowserUtil;
// import static com.thoughtworks.selenium.*;
import static org.testng.Assert.assertEquals;


public class DragAndDropCircle extends SelNGBase {


	public static void ddTest() throws Exception {

		session().open("http://presentbright.corp.yahoo.com/yui2/latest_build/examples/dragdrop/dd-circle_clean.html");

		moveElement("dd-demo-1", "+180", "+115");

	}

	private static void moveElement(String el, String deltaX, String deltaY) throws Exception {
		
		// This test can not be performed at this time as the D&D contains animation and I can not
		// find a way to get Selenium to do the D&D
	
		Number X = session().getElementPositionLeft(el);
		Number Y = session().getElementPositionTop(el);

		String deltaStr = deltaX + "," + deltaY;

		//BrowserUtil.DragAndDrop(el, "dd-demo-2");
		Thread.sleep(4000);
		session().dragAndDrop(el, deltaStr);
		Thread.sleep(4000);
		
		/**********
		Thread.sleep(4000);
		session().mouseDown(el);
		Thread.sleep(4000);
		session().mouseMoveAt(el, deltaStr);
		Thread.sleep(4000);
		session().mouseOver("dd-demo-2");
		Thread.sleep(4000);
		session().mouseUp("dd-demo-2");
		Thread.sleep(4000);
		*******/

		Number newX = session().getElementPositionLeft(el);
		System.out.println("newX----->"+newX);
		Number newY = session().getElementPositionTop(el);
		System.out.println("newY----->"+newY);
		
		//String xExpected = X.intValue() + Integer.parseInt(deltaX) + "";
		//String yExpected = Y.intValue() + Integer.parseInt(deltaY) + "";
		
		assertEquals(session().getExpression(session().getEval("parseInt('" + X + "')+parseInt('" + deltaX + "')")), newX.toString());
		assertEquals(session().getExpression(session().getEval("parseInt('" + Y + "')+parseInt('" + deltaY + "')")), newY.toString());
	
	}
}

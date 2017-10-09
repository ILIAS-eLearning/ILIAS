package com.yahoo.test.SelNG.YUI.library;

import com.yahoo.test.SelNG.framework.core.SelNGBase;
import com.yahoo.test.SelNG.framework.util.BrowserUtil;

import static com.yahoo.test.SelNG.framework.util.ThreadSafeSeleniumSessionStorage.session;
// import static com.thoughtworks.selenium.*;
import static org.testng.Assert.assertEquals;


public class DragAndDropOnTop extends SelNGBase  {


	public static void ddTest()  throws Exception {
		
		// This test can not be performed at this time as there is no way to measure attributes during
		// the drag portion before the drop.

		session().open("http://presentbright.corp.yahoo.com/yui2/latest_build/examples/dragdrop/dd-ontop_clean.html");
		//assertEquals(session().getTitle(), "Basic Drag and Drop");

		moveElement("dd-demo-1", "+30", "-15");
		
	}

	private static void moveElement(String el, String deltaX, String deltaY)  throws Exception {
	
		Number X = session().getElementPositionLeft(el);
		System.out.println("X-->"+X);
		Number Y = session().getElementPositionTop(el);
		System.out.println("Y-->"+Y);
		// TODO: put this in Java
		String deltaStr = session().getEval("'" + deltaX + "'+','+'" + deltaY + "'");
		//assertEquals(session().getExpression(deltaStr), "123456");
		//session().dragAndDrop(el, deltaStr);
		BrowserUtil.DragAndDrop(el, "dd-demo-3");
		/********
		session().mouseDown(el);
		Thread.sleep(2000);
		session().mouseMoveAt(el, "+230,+20");
		Thread.sleep(2000);
		session().mouseOver("dd-demo-3");
		Thread.sleep(2000);
		session().mouseUp("dd-demo-3");
		session().mouseUp(el);
		**********/
		//session().dragAndDrop(el, deltaStr);
		//Number newX = session().getElementPositionLeft("//div[@id='dd-demo-1']");
		Number newX = session().getElementPositionLeft(el);
		System.out.println("newX-->"+newX);
		//Number newY = session().getElementPositionTop("//div[@id='dd-demo-1']");
		Number newY = session().getElementPositionTop(el);
		System.out.println("newY-->"+newY);
	  //verifyEquals(session().getExpression(session().getEval("parseInt('" + X + "')+parseInt('" + deltaX + "')+''")), newX);
		assertEquals(session().getExpression(session().getEval("parseInt('" + X + "')+parseInt('" + deltaX + "')")), newX.toString());
		assertEquals(session().getExpression(session().getEval("parseInt('" + Y + "')+parseInt('" + deltaY + "')")), newY.toString());
	
	}
}

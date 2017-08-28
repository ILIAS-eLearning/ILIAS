package com.yahoo.test.SelNG.YUI.library;

import com.yahoo.test.SelNG.framework.core.SelNGBase;
import static com.yahoo.test.SelNG.framework.util.ThreadSafeSeleniumSessionStorage.session;
// import static com.thoughtworks.selenium.*;
import static org.testng.Assert.assertEquals;


public class DragAndDropRegion extends SelNGBase {


	public static void ddTest() {

		session().open("http://presentbright.corp.yahoo.com/yui2/latest_build/examples/dragdrop/dd-region_clean.html");
		//assertEquals(session().getTitle(), "Basic Drag and Drop");

		testElement("dd-demo-1", "dd-demo-canvas3");
		testElement("dd-demo-2", "dd-demo-canvas2");
		testElement("dd-demo-3", "dd-demo-canvas1");

	}
	
	private static void testElement(String el, String container) {

		Number elX = session().getElementPositionLeft(el);
		Number elY = session().getElementPositionTop(el);
		Number containerX = session().getElementPositionLeft(container);
		Number containerY = session().getElementPositionTop(container);

		// try to move outside of the container
		int deltaX = elX.intValue() - containerX.intValue() + 15;
		int deltaY = elY.intValue() - containerY.intValue() + 15;
		String deltaStr = "-" + deltaX + ",-" + deltaY;
		session().dragAndDrop(el, deltaStr);
		Number newX = session().getElementPositionLeft(el);
		Number newY = session().getElementPositionTop(el);
		int newDeltaX = containerX.intValue() - newX.intValue();	
		int newDeltaY = containerY.intValue() - newY.intValue();
		String newDeltaXstr = newDeltaX + "";
		String newDeltaYstr = newDeltaY + "";
		
		assertEquals(session().getExpression(newDeltaXstr), "0");
		assertEquals(session().getExpression(newDeltaYstr), "0");


	}
}

package com.yahoo.test.SelNG.YUI.library;

import com.yahoo.test.SelNG.framework.core.SelNGBase;
import com.yahoo.test.SelNG.framework.util.BrowserUtil;

import static com.yahoo.test.SelNG.framework.util.ThreadSafeSeleniumSessionStorage.session;
// import static com.thoughtworks.selenium.*;
import static org.testng.Assert.assertEquals;


public class DragAndDropReorder extends SelNGBase {


	public static void ddTest() throws Exception {
		
		// This test can not be performed at this time as Selenium can not d&d elements with animation

		session().open("http://presentbright.corp.yahoo.com/yui2/latest_build/examples/dragdrop/dd-reorder_clean.html");
		//assertEquals(session().getTitle(), "Reordering a List");
		
		// Get the height and width of one of the list elements
		String elHeightStr = session().getEval("session().browserbot.getCurrentWindow().document.getElementById('li1_1').clientHeight;");
		String elWidthStr = session().getEval("session().browserbot.getCurrentWindow().document.getElementById('li1_1').clientWidth;");
		int elHeight = Integer.parseInt(elHeightStr);
		int elWidth = Integer.parseInt(elWidthStr);

		// Check initial list order
		session().click("showButton");
		// Alert box really has a newline but the getAlert() wont find it
		assertEquals(session().getAlert(), "List 1: li1_1 li1_2 li1_3  List 2: li2_1 li2_2 li2_3 ");

		// Move 'li1_1' below 'li1_3'
		// BrowserUtil.DragAndDrop(objectToBeDragged, objectToBeDroppedInto);
		Thread.sleep(1000);
		// BrowserUtil.DragAndDrop("li1_1", "ul2");
		session().mouseDown("li1_1");
		Thread.sleep(2000);
		session().mouseMoveAt("li1_1", "+100,+100");
		Thread.sleep(2000);
		session().mouseOver("ul2");
		Thread.sleep(2000);
		session().mouseUp("ul2");
		int deltaY = 2 * elHeight;
		session().dragAndDrop("li1_1", "+0,+" + deltaY);
		Thread.sleep(1000);

		// Check new list order
		session().click("showButton");
		assertEquals(session().getAlert(), "List 1: li1_2 li1_3 li1_1  List 2: li2_1 li2_2 li2_3 ");

		
		
		/********
		for(int i=0; i<deltas.length; i++) {
			// li1_1  li1_2  li1_3  li2_1  li2_2  li2_3 
            // move li_1 below li1_3
			//moveElement("li1_1", "li1_3", 0, 2);
			
		}
        ********/
		
		//moveElement("//div[@id='dd-demo-1']", "+30", "-15");
	}

	private static void moveElement(String listItem1, String listItem2, int deltaX, int deltaY) {
	
		/*****
		//String deltaX = "+50";
		//String deltaY = "-30";
		//Number X = session().getElementPositionLeft("//div[@id='dd-demo-1']");
		Number X = session().getElementPositionLeft(el);
		//Number Y = session().getElementPositionTop("//div[@id='dd-demo-1']");
		Number Y = session().getElementPositionTop(el);
		String deltaStr = session().getEval("'" + deltaX + "'+','+'" + deltaY + "'");
		//assertEquals(session().getExpression(deltaStr), "123456");
		//session().dragAndDrop("//div[@id='dd-demo-1']", deltaStr);
		session().dragAndDrop(el, deltaStr);
		//Number newX = session().getElementPositionLeft("//div[@id='dd-demo-1']");
		Number newX = session().getElementPositionLeft(el);
		//Number newY = session().getElementPositionTop("//div[@id='dd-demo-1']");
		Number newY = session().getElementPositionTop(el);
	  //verifyEquals(session().getExpression(session().getEval("parseInt('" + X + "')+parseInt('" + deltaX + "')+''")), newX);
		assertEquals(session().getExpression(session().getEval("parseInt('" + X + "')+parseInt('" + deltaX + "')")), newX.toString());
		assertEquals(session().getExpression(session().getEval("parseInt('" + Y + "')+parseInt('" + deltaY + "')")), newY.toString());
	********/
	}
}

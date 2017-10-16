package com.yahoo.test.SelNG.YUI.library;

import com.yahoo.test.SelNG.framework.core.SelNGBase;
import static com.yahoo.test.SelNG.framework.util.ThreadSafeSeleniumSessionStorage.session;
import com.yahoo.test.SelNG.framework.util.BrowserUtil;
// import static com.thoughtworks.selenium.*;
import static org.testng.Assert.assertEquals;


public class DragAndDropDdRows extends SelNGBase {


	public static void ddTest() throws Exception {
		
		// This test can not be done at this time due to Selenium not being able to do d&d on this test

		//selenium.open("http://presentbright.corp.yahoo.com/yui2/latest_build/examples/dragdrop/dd-ddrows_clean.html");
		selenium.open("http://presentbright.corp.yahoo.com/yui2/latest_build/examples/datatable/dt_ddrows_clean.html");

		// this is how to run JS in the app window
		//String what = selenium.getEval("var appWindow = selenium.browserbot.getCurrentWindow(); appWindow.YAHOO.util.DDProxy.dragElId; x = 119966; 1==1");
		//System.out.println(what);
		
		//assertEquals(selenium.getExpression(selenium.getEval("selenium.browserbot.getCurrentWindow().YAHOO.util.DDProxy.dragElId")), "12345");
		
		String[][] deltas = {
				{ "+10", "-30" },
				{ "+20", "+40" },
				{ "-50", "-10" },
				{ "-50", "-10" }
		};
		/**********
		for(int i=0; i<deltas.length; i++) {
			for(int k=1; k<4; k++) {
				moveElement("dd-demo-" + k, deltas[i][0], deltas[i][1] );
			}
		}
		*****************/
		
		
		//selenium.click("shim");
		//moveElement("demo", "-500", "+100");
		moveElement("yui-rec0", "+0", "+200");
	}

	private static void moveElement(String el, String deltaX, String deltaY) throws Exception {
	
		//String deltaX = "+50";
		//String deltaY = "-30";
		//Number X = selenium.getElementPositionLeft("//div[@id='dd-demo-1']");
		Number X = selenium.getElementPositionLeft(el);
		//Number Y = selenium.getElementPositionTop("//div[@id='dd-demo-1']");
		Number Y = selenium.getElementPositionTop(el);
		// TODO: put this in Java
		String deltaStr = selenium.getEval("'" + deltaX + "'+','+'" + deltaY + "'");
		//assertEquals(selenium.getExpression(deltaStr), "123456");
		//selenium.dragAndDrop("//div[@id='dd-demo-1']", deltaStr);
		//BrowserUtil.DragAndDrop(el, "dd-demo-2");
		selenium.dragAndDrop(el, deltaStr);
		//Number newX = selenium.getElementPositionLeft("//div[@id='dd-demo-1']");
		Number newX = selenium.getElementPositionLeft(el);
		//Number newY = selenium.getElementPositionTop("//div[@id='dd-demo-1']");
		Number newY = selenium.getElementPositionTop(el);
	  //verifyEquals(selenium.getExpression(selenium.getEval("parseInt('" + X + "')+parseInt('" + deltaX + "')+''")), newX);
		assertEquals(selenium.getExpression(selenium.getEval("parseInt('" + X + "')+parseInt('" + deltaX + "')")), newX.toString());
		assertEquals(selenium.getExpression(selenium.getEval("parseInt('" + Y + "')+parseInt('" + deltaY + "')")), newY.toString());
	
	}
}

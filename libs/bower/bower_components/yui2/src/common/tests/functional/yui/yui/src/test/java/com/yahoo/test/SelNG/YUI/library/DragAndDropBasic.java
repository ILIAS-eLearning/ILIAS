package com.yahoo.test.SelNG.YUI.library;

import com.yahoo.test.SelNG.framework.core.SelNGBase;
import static com.yahoo.test.SelNG.framework.util.ThreadSafeSeleniumSessionStorage.session;
// import static com.thoughtworks.selenium.*;
import static org.testng.Assert.assertEquals;


public class DragAndDropBasic extends SelNGBase {


	public static void ddTest() {

		session().open("http://presentbright.corp.yahoo.com/yui2/latest_build/examples/dragdrop/dd-basic_clean.html");
		//assertEquals(session().getTitle(), "Basic Drag and Drop");

		// this is how to run JS in the app window
		//String what = session().getEval("var appWindow = session().browserbot.getCurrentWindow(); appWindow.YAHOO.util.DDProxy.dragElId; x = 119966; 1==1");
		//System.out.println(what);
		
		//assertEquals(session().getExpression(session().getEval("session().browserbot.getCurrentWindow().YAHOO.util.DDProxy.dragElId")), "12345");
		
		String[][] deltas = {
				{ "+10", "-30" },
				{ "+20", "+40" },
				{ "-50", "-10" },
				{ "-50", "-10" }
		};
		
		for(int i=0; i<deltas.length; i++) {
			for(int k=1; k<4; k++) {
				moveElement("dd-demo-" + k, deltas[i][0], deltas[i][1] );
			}
		}

	}

	private static void moveElement(String el, String deltaX, String deltaY) {
	
		int X = (session().getElementPositionLeft(el)).intValue();
		int Y = (session().getElementPositionTop(el)).intValue();
		String deltaStr = deltaX + "," + deltaY;
		session().dragAndDrop(el, deltaStr);

		int newX = (session().getElementPositionLeft(el)).intValue();
		int newY = (session().getElementPositionTop(el)).intValue();

		assertEquals((parseInt(deltaX) + X), newX);
		assertEquals((parseInt(deltaY) + Y), newY);
	}
	
	private static int parseInt(String s) {
		
		if ( s.charAt(0) == '+') { 
			s = s.substring(1);
		}
		return Integer.parseInt(s);
	}
	
}

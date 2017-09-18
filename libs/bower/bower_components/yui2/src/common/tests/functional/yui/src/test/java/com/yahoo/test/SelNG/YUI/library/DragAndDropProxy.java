package com.yahoo.test.SelNG.YUI.library;

import com.yahoo.test.SelNG.framework.core.SelNGBase;
import static com.yahoo.test.SelNG.framework.util.ThreadSafeSeleniumSessionStorage.session;
// import static com.thoughtworks.selenium.*;
import static org.testng.Assert.assertEquals;


public class DragAndDropProxy extends SelNGBase {


	public static void ddTest() {
		
		// This test can not be performed at this time as there is no way to measure the proxy element's
		// attributes during the drag.

		selenium.open("http://presentbright.corp.yahoo.com/yui2/latest_build/examples/dragdrop/dd-basic_proxy.html");
		//assertEquals(selenium.getTitle(), "Basic Drag and Drop");

		String[][] deltas = {
				{ "+10", "-30" },
				{ "+100", "+50" },
				{ "-50", "-10" },
				{ "-50", "-10" }
		};
		
		//Map <String, String>els = new HashMap<String, String>();
		
		for(int i=0; i<deltas.length; i++) {

			moveElement("dd-handle-1a", "dd-demo-1", deltas[i][0], deltas[i][1] );
			// dd-handle-1b, dd-demo-1
			moveElement("dd-handle-1b", "dd-demo-1", deltas[i][0], deltas[i][1] );
			// dd-handle-2, dd-demo-2
			moveElement("dd-handle-2", "dd-demo-2", deltas[i][0], deltas[i][1] );
			// dd-handle-3b, dd-demo-3
			moveElement("dd-handle-3b", "dd-demo-3", deltas[i][0], deltas[i][1] );
		}

	}

	private static void moveElement(String elHandle, String elBody, String deltaX, String deltaY) {
	
		Number X = selenium.getElementPositionLeft(elBody);
		Number Y = selenium.getElementPositionTop(elBody);
		String deltaStr = selenium.getEval("'" + deltaX + "'+','+'" + deltaY + "'");
		//assertEquals(selenium.getExpression(deltaStr), "123456");
		selenium.dragAndDrop(elHandle, deltaStr);
		Number newX = selenium.getElementPositionLeft(elBody);
		Number newY = selenium.getElementPositionTop(elBody);
		assertEquals(selenium.getExpression(selenium.getEval("parseInt('" + X + "')+parseInt('" + deltaX + "')")), newX.toString());
		assertEquals(selenium.getExpression(selenium.getEval("parseInt('" + Y + "')+parseInt('" + deltaY + "')")), newY.toString());
	
	}
}

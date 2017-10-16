package com.yahoo.test.SelNG.YUI.library;

import com.yahoo.test.SelNG.framework.core.SelNGBase;
import static com.yahoo.test.SelNG.framework.util.ThreadSafeSeleniumSessionStorage.session;
// import static com.thoughtworks.selenium.*;
import static org.testng.Assert.assertEquals;
import java.util.*;

public class DragAndDropHandles extends SelNGBase {


	public static void ddTest() {

		session().open("http://presentbright.corp.yahoo.com/yui2/latest_build/examples/dragdrop/dd-handles_clean.html");
		//assertEquals(session().getTitle(), "Basic Drag and Drop");

		String[][] deltas = {
				{ "+10", "-30" },
				{ "+100", "+50" },
				{ "-50", "-10" },
				{ "-50", "-10" }
		};
		
		for(int i=0; i<deltas.length; i++) {
			moveElement("dd-handle-1a", "dd-demo-1", deltas[i][0], deltas[i][1] );
			moveElement("dd-handle-1b", "dd-demo-1", deltas[i][0], deltas[i][1] );
			moveElement("dd-handle-2", "dd-demo-2", deltas[i][0], deltas[i][1] );
			moveElement("dd-handle-3b", "dd-demo-3", deltas[i][0], deltas[i][1] );
		}
		// TODO: try to move the element using other than the handle
	}


	private static void moveElement(String elHandle, String elBody, String deltaX, String deltaY) {
	
		int X = (session().getElementPositionLeft(elBody)).intValue();
		int Y = (session().getElementPositionTop(elBody)).intValue();
		String deltaStr = deltaX + "," + deltaY;
		session().dragAndDrop(elHandle, deltaStr);

		int newX = (session().getElementPositionLeft(elBody)).intValue();
		int newY = (session().getElementPositionTop(elBody)).intValue();

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

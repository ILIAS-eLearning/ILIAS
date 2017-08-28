package com.yahoo.test.SelNG.YUI.library;

import com.yahoo.test.SelNG.framework.core.SelNGBase;
import static com.yahoo.test.SelNG.framework.util.ThreadSafeSeleniumSessionStorage.session;
// import static com.thoughtworks.selenium.*;
import static org.testng.Assert.*;


public class ContainerTooltipMulti extends SelNGBase {


	public static void containerTest() throws Exception {

		session().open("http://presentbright.corp.yahoo.com/yui2/latest_build/examples/container/tooltip-multi_clean.html");
		//assertEquals(session().getTitle(), "Basic Drag and Drop");
		
		// check for hide delay and show delay and auto dismiss delay
		
		//Thread.sleep(3000);

		// When the page is first loaded, the tt element has no value for visibility rather than "visibility: hidden;"
		assertFalse(hasAttribute("ttA", "style", "visibility: visible;"));
		session().mouseOver("A1");
		assertTrue(hasAttribute("ttA", "style", "visibility: visible;"));
		String ttText = session().getText("ttA");
		assertEquals(ttText, "Tooltip for link A1, set through title");

		// Check for auto dismiss default timeout of 5 seconds
		Thread.sleep(5500);
		assertFalse(hasAttribute("ttA", "style", "visibility: visible;"));
		session().mouseOut("A1");
		
		// Now that the tt element has a value for visibility, we can check for it
		checkTT("ttA", "A2", "Tooltip for link A2, set through title");
		checkTT("ttA", "A3", "Tooltip for link A3, set through title");
		checkTT("ttA", "A4", "Tooltip for link A4, set through title");
		checkTT("ttA", "A5", "Tooltip for link A5, set through title");
		checkTT("ttB", "B1", "Tooltip for B1, set using contextTriggerEvent");
		checkTT("ttB", "B2", "Tooltip for B2, set using contextTriggerEvent");
		
		// anchor B3 has no tt
		assertFalse(hasAttribute("ttA", "style", "visibility: visible;"));
		session().mouseOver("B3");
		assertFalse(hasAttribute("ttA", "style", "visibility: visible;"));
		session().mouseOut("B3");
		
		checkTT("ttB", "B4", "Tooltip for B4, set using contextTriggerEvent");
		checkTT("ttB", "B5", "Tooltip for B5, set using contextTriggerEvent");
		
	}
	
	public static void checkTT(String elTT, String elA, String expectedTTtext) throws Exception {

		assertTrue(hasAttribute(elTT, "style", "visibility: hidden;"));
		session().mouseOver(elA);
		//Number X = session().getElementPositionLeft(elTT);
		//Number Y = session().getElementPositionTop(elTT);
		assertTrue(hasAttribute(elTT, "style", "visibility: visible;"));
		String ttText = session().getText(elTT);
		assertEquals(ttText, expectedTTtext);
		Thread.sleep(5500);
		assertTrue(hasAttribute(elTT, "style", "visibility: hidden;"));
		session().mouseOut(elA);

	}
	
	public static boolean hasAttribute(String elXpath, String attributeName, String attributeValue) {
		
		String attribute = session().getAttribute(elXpath + "@" + attributeName);
		return ((attribute != null) && (attribute.contains(attributeValue)));
	}

}

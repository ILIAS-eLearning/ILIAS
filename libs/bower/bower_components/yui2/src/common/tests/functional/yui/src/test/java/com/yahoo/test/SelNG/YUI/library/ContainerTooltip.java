package com.yahoo.test.SelNG.YUI.library;

import com.yahoo.test.SelNG.framework.core.SelNGBase;
import static com.yahoo.test.SelNG.framework.util.ThreadSafeSeleniumSessionStorage.session;
// import static com.thoughtworks.selenium.*;
import static org.testng.Assert.*;


public class ContainerTooltip extends SelNGBase {


	public static void containerTest() throws Exception {

		// can i vector over the element without stopping??

		session().open("http://presentbright.corp.yahoo.com/yui2/latest_build/examples/container/tooltip_clean.html");
		//assertEquals(session().getTitle(), "");

		// Check tt1
		assertTrue(Util.hasAttribute("tt1", "style", "visibility: hidden;"));
		session().mouseOver("ctx");
		assertTrue(Util.hasAttribute("tt1", "style", "visibility: visible;"));
		// check the autodismissdelay default of 5 seconds
		Thread.sleep(6000);
		assertTrue(Util.hasAttribute("tt1", "style", "visibility: hidden;"));
		
		// Check tt2
		assertTrue(Util.hasAttribute("tt2", "style", "visibility: hidden;"));
		session().mouseOver("link");
		assertTrue(Util.hasAttribute("tt2", "style", "visibility: visible;"));
		// check the autodismissdelay default of 5 seconds
		Thread.sleep(6000);
		assertTrue(Util.hasAttribute("tt2", "style", "visibility: hidden;"));

	}
	

}

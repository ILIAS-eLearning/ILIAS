package com.yahoo.test.SelNG.YUI.library;

import com.yahoo.test.SelNG.framework.core.SelNGBase;
import static com.yahoo.test.SelNG.framework.util.ThreadSafeSeleniumSessionStorage.session;
// import static com.thoughtworks.selenium.*;
import static org.testng.Assert.*;


public class ContainerModule extends SelNGBase {


	public static void containerTest() {
		
		// set up separate test page for hd, body, ft....thunk it over

		session().open("http://presentbright.corp.yahoo.com/yui2/latest_build/examples/container/module_clean.html");
		//assertEquals(session().getTitle(), "Basic Drag and Drop");

		// Check initial state
		assertTrue(Util.hasAttribute("module1", "style", "display: none"));
		assertTrue(Util.hasAttribute("module2", "style", "display: none"));
		
		// Click on Show module 1
		session().click("show1");
		assertTrue(Util.hasAttribute("module1", "style", "display: block"));
		assertTrue(Util.hasAttribute("module2", "style", "display: none"));

		// Click on Hide Module 1
		session().click("hide1");
		assertTrue(Util.hasAttribute("module1", "style", "display: none"));
		assertTrue(Util.hasAttribute("module2", "style", "display: none"));
		
		// Click on Show Module 2
		session().click("show2");
		assertTrue(Util.hasAttribute("module1", "style", "display: none"));
		assertTrue(Util.hasAttribute("module2", "style", "display: block"));
		
		// Click on Hide Module 2
		session().click("hide2");
		assertTrue(Util.hasAttribute("module1", "style", "display: none"));
		assertTrue(Util.hasAttribute("module2", "style", "display: none"));
		
		// Open both modules
		session().click("show1");
		session().click("show2");
		assertTrue(Util.hasAttribute("module1", "style", "display: block"));
		assertTrue(Util.hasAttribute("module2", "style", "display: block"));
		
		// Close both modules
		session().click("hide1");
		session().click("hide2");
		assertTrue(Util.hasAttribute("module1", "style", "display: none"));
		assertTrue(Util.hasAttribute("module2", "style", "display: none"));
		
	}
	
}

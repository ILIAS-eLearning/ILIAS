package com.yahoo.test.SelNG.YUI.library;

import com.yahoo.test.SelNG.framework.core.SelNGBase;
import static com.yahoo.test.SelNG.framework.util.ThreadSafeSeleniumSessionStorage.session;
// import static com.thoughtworks.selenium.*;
import static org.testng.Assert.assertEquals;
import static org.testng.Assert.assertFalse;


public class TabViewRemovingTabs extends SelNGBase {

	public static void tabTest() {

	    session().open("http://presentbright.corp.yahoo.com/yui2/latest_build/examples/tabview/removetab_clean.html");
		//assertEquals(session().getTitle(), "Removing Tabs");

	    // Check initial state
		assertEquals(session().getAttribute("//div[@id='demo']/ul/li[1]@class"), null);
		assertEquals(session().getAttribute("//div[@id='demo']/ul/li[2]@class"), "selected");
		assertEquals(session().getAttribute("//div[@id='demo']/ul/li[3]@class"), null);
		assertEquals(session().getAttribute("//div[@id='tab1']@class"), "yui-hidden");
		assertEquals(session().getAttribute("//div[@id='tab2']@class"), null);
		assertEquals(session().getAttribute("//div[@id='tab3']@class"), "yui-hidden");

		// Click on first tab
		session().click("//div[@id='demo']/ul/li[1]/a");
		assertEquals(session().getAttribute("//div[@id='demo']/ul/li[1]@class"), "selected");
		assertEquals(session().getAttribute("//div[@id='demo']/ul/li[2]@class"), null);
		assertEquals(session().getAttribute("//div[@id='demo']/ul/li[3]@class"), null);
		assertEquals(session().getAttribute("//div[@id='tab1']@class"), null);
		assertEquals(session().getAttribute("//div[@id='tab2']@class"), "yui-hidden");
		assertEquals(session().getAttribute("//div[@id='tab3']@class"), "yui-hidden");

		// click on third tab
		session().click("//div[@id='demo']/ul/li[3]/a");
		assertEquals(session().getAttribute("//div[@id='demo']/ul/li[1]@class"), null);
		assertEquals(session().getAttribute("//div[@id='demo']/ul/li[2]@class"), null);
		assertEquals(session().getAttribute("//div[@id='demo']/ul/li[3]@class"), "selected");
		assertEquals(session().getAttribute("//div[@id='tab1']@class"), "yui-hidden");
		assertEquals(session().getAttribute("//div[@id='tab2']@class"), "yui-hidden");
		assertEquals(session().getAttribute("//div[@id='tab3']@class"), null);

		// Click on second tab
		session().click("//div[@id='demo']/ul/li[2]/a");
		assertEquals(session().getAttribute("//div[@id='demo']/ul/li[1]@class"), null);
		assertEquals(session().getAttribute("//div[@id='demo']/ul/li[2]@class"), "selected");
		assertEquals(session().getAttribute("//div[@id='demo']/ul/li[3]@class"), null);
		assertEquals(session().getAttribute("//div[@id='tab1']@class"), "yui-hidden");
		assertEquals(session().getAttribute("//div[@id='tab2']@class"), null);
		assertEquals(session().getAttribute("//div[@id='tab3']@class"), "yui-hidden");
	
		// Click on first tab
		session().click("//div[@id='demo']/ul/li[1]/a");
		assertEquals(session().getAttribute("//div[@id='demo']/ul/li[1]@class"), "selected");
		assertEquals(session().getAttribute("//div[@id='demo']/ul/li[2]@class"), null);
		assertEquals(session().getAttribute("//div[@id='demo']/ul/li[3]@class"), null);
		assertEquals(session().getAttribute("//div[@id='tab1']@class"), null);
		assertEquals(session().getAttribute("//div[@id='tab2']@class"), "yui-hidden");
		assertEquals(session().getAttribute("//div[@id='tab3']@class"), "yui-hidden");

		// Click on second tab
		session().click("//div[@id='demo']/ul/li[2]/a");
		assertEquals(session().getAttribute("//div[@id='demo']/ul/li[1]@class"), null);
		assertEquals(session().getAttribute("//div[@id='demo']/ul/li[2]@class"), "selected");
		assertEquals(session().getAttribute("//div[@id='demo']/ul/li[3]@class"), null);
		assertEquals(session().getAttribute("//div[@id='tab1']@class"), "yui-hidden");
		assertEquals(session().getAttribute("//div[@id='tab2']@class"), null);
		assertEquals(session().getAttribute("//div[@id='tab3']@class"), "yui-hidden");

		// TODO: make label/content text a variable
		// Click the delete button and remove Tab 2
		session().click("//div[@id='demo']/button");
		assertEquals(session().getText("//a[@href='#tab1']/em"), "Tab One Label");
		assertFalse(session().isTextPresent("Tab Two Label"));
		assertEquals(session().getText("//a[@href='#tab3']/em"), "Tab Three Label");
		assertEquals(session().getAttribute("//div[@id='demo']/ul/li[1]@class"), null);
		assertEquals(session().getAttribute("//div[@id='demo']/ul/li[2]@class"), "selected");
		assertEquals(session().getAttribute("//div[@id='tab1']/@class"), "yui-hidden");
		assertFalse(session().isTextPresent("Tab Two Content"));
		assertEquals(session().getAttribute("//div[@id='tab3']/@class"), null);

		// remove old Tab 3 (new Tab 2)
		session().click("//div[@id='demo']/button");
		assertEquals(session().getAttribute("//div[@id='demo']/ul/li[1]@class"), "selected");
		assertFalse(session().isTextPresent("Tab Two Label"));
		assertFalse(session().isTextPresent("Tab Three Label"));
		assertEquals(session().getAttribute("//div[@id='tab1']/@class"), null);
		assertFalse(session().isTextPresent("Tab Two Content"));
		assertFalse(session().isTextPresent("Tab Three Content"));
		
		// remove last Tab
		session().click("//div[@id='demo']/button");
		assertFalse(session().isTextPresent("Tab One Label"));
		assertFalse(session().isTextPresent("Tab Two Label"));
		assertFalse(session().isTextPresent("Tab Three Label"));
		assertFalse(session().isTextPresent("Tab One Content"));
		assertFalse(session().isTextPresent("Tab Two Content"));
		assertFalse(session().isTextPresent("Tab Three Content"));
		
	}

}

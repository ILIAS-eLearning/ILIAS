package com.yahoo.test.SelNG.YUI.library;

import com.yahoo.test.SelNG.framework.core.SelNGBase;
import static com.yahoo.test.SelNG.framework.util.ThreadSafeSeleniumSessionStorage.session;
// import static com.thoughtworks.selenium.*;
import static org.testng.Assert.assertEquals;


public class TabViewBuildFromScript extends SelNGBase {

	public static void tabTest() throws Exception {

	    session().open("http://presentbright.corp.yahoo.com/yui2/latest_build/examples/tabview/fromscript_clean.html");
		//assertEquals(session().getTitle(), "Build from Script");
		
		// initial state -- Tab 1 selected
	    // TODO: do the same way as 'Adding Tabs'
	    
	    assertEquals(session().getAttribute("//ul[@class='yui-nav']/li[1]@class"), "selected");
	    //assertEquals(session().getAttribute("//div[@id='container']/div/ul/li[1]@class"), "selected");
		assertEquals(session().getAttribute("//div[@id='container']/div/ul/li[2]@class"), null);
		assertEquals(session().getAttribute("//div[@id='container']/div/ul/li[3]@class"), null);
		assertEquals(session().getAttribute("//div[@id='container']/div/div/div[1]@class"), null);
		assertEquals(session().getAttribute("//div[@id='container']/div/div/div[2]@class"), "yui-hidden");
		assertEquals(session().getAttribute("//div[@id='container']/div/div/div[3]@class"), "yui-hidden");

		// Click on Tab 2
		session().click("//div[@id='container']/div/ul/li[2]/a/em");
		assertEquals(session().getAttribute("//div[@id='container']/div/ul/li[1]@class"), null);
		assertEquals(session().getAttribute("//div[@id='container']/div/ul/li[2]@class"), "selected");
		assertEquals(session().getAttribute("//div[@id='container']/div/ul/li[3]@class"), null);
		assertEquals(session().getAttribute("//div[@id='container']/div/div/div[1]@class"), "yui-hidden");
		assertEquals(session().getAttribute("//div[@id='container']/div/div/div[2]@class"), null);
		assertEquals(session().getAttribute("//div[@id='container']/div/div/div[3]@class"), "yui-hidden");

		// Click on Tab 3
		session().click("//div[@id='container']/div/ul/li[3]/a");
		assertEquals(session().getAttribute("//div[@id='container']/div/ul/li[1]@class"), null);
		assertEquals(session().getAttribute("//div[@id='container']/div/ul/li[2]@class"), null);
		assertEquals(session().getAttribute("//div[@id='container']/div/ul/li[3]@class"), "selected");
		assertEquals(session().getAttribute("//div[@id='container']/div/div/div[1]@class"), "yui-hidden");
		assertEquals(session().getAttribute("//div[@id='container']/div/div/div[2]@class"), "yui-hidden");
		assertEquals(session().getAttribute("//div[@id='container']/div/div/div[3]@class"), null);

		// Type some text in the textbox and submit
		// take this out
		//session().type("//input[@id='foo']", "foobar");
		//assertEquals(session().getValue("//input[@id='foo']"), "foobar");
		//session().click("//input[@value='submit']");
		
		// Now should be back at Tab 1
		session().click("//div[@id='container']/div/ul/li[1]/a");
		assertEquals(session().getAttribute("//div[@id='container']/div/ul/li[1]@class"), "selected");
		assertEquals(session().getAttribute("//div[@id='container']/div/ul/li[2]@class"), null);
		assertEquals(session().getAttribute("//div[@id='container']/div/ul/li[3]@class"), null);
		assertEquals(session().getAttribute("//div[@id='container']/div/div/div[1]@class"), null);
		assertEquals(session().getAttribute("//div[@id='container']/div/div/div[2]@class"), "yui-hidden");
		assertEquals(session().getAttribute("//div[@id='container']/div/div/div[3]@class"), "yui-hidden");
	

	}

}

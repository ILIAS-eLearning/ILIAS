package com.yahoo.test.SelNG.YUI.library;

import com.yahoo.test.SelNG.framework.core.SelNGBase;
import static com.yahoo.test.SelNG.framework.util.ThreadSafeSeleniumSessionStorage.session;
// import static com.thoughtworks.selenium.*;
import static org.testng.Assert.*;


public class TabViewExternalSource extends SelNGBase {

	public static void tabTest() {
		
		// TODO:  need way to test more than one of these widgets on a page --  hrefs ??

		session().open("http://10.72.112.142/dev/gitroot/yui2/src/tabview/tests/functional/html/TabViewExternalSource.html");
		//assertEquals(session().getTitle(), "Getting Content from an External Source");
		
		// wait 5 seconds for the XHR to complete
		for (int second = 0;; second++) {
			if (second >= 5) fail("XHR timeout");
			try { if ("This is Opera content".equals(session().getText("//div[@class='yui-content']/div[1]"))) break;
			} catch (Exception e) {
					e.printStackTrace();
				}
			try {
				Thread.sleep(1000);
			} catch (InterruptedException e) {
				e.printStackTrace();
			}
		}

		// Start state
		assertEquals(session().getAttribute("//div[@id='container']/div/ul/li[1]@class"), "selected");
		assertEquals(session().getAttribute("//div[@id='container']/div/ul/li[2]@class"), null);
		assertEquals(session().getAttribute("//div[@id='container']/div/ul/li[3]@class"), null);
		assertEquals(session().getAttribute("//div[@id='container']/div/ul/li[4]@class"), null);
		assertEquals(session().getAttribute("//div[@id='container']/div/div/div[1]@class"), null);
		assertEquals(session().getAttribute("//div[@id='container']/div/div/div[2]@class"), "yui-hidden");
		assertEquals(session().getAttribute("//div[@id='container']/div/div/div[3]@class"), "yui-hidden");
		assertEquals(session().getAttribute("//div[@id='container']/div/div/div[4]@class"), "yui-hidden");
		assertEquals(session().getText("//div[@id='container']/div/div/div[1]"), "This is Opera content");
	    
		// Push Explorer Tab
		session().click("//div[@id='container']/div/ul/li[3]/a/em");
		assertEquals(session().getAttribute("//div[@id='container']/div/ul/li[1]@class"), null);
		assertEquals(session().getAttribute("//div[@id='container']/div/ul/li[2]@class"), null);
		assertEquals(session().getAttribute("//div[@id='container']/div/ul/li[3]@class"), "selected");
		assertEquals(session().getAttribute("//div[@id='container']/div/ul/li[4]@class"), null);
		assertEquals(session().getAttribute("//div[@id='container']/div/div/div[1]@class"), "yui-hidden");
		assertEquals(session().getAttribute("//div[@id='container']/div/div/div[2]@class"), "yui-hidden");
		assertEquals(session().getAttribute("//div[@id='container']/div/div/div[3]@class"), null);
		assertEquals(session().getAttribute("//div[@id='container']/div/div/div[4]@class"), "yui-hidden");
		assertEquals(session().getText("//div[@id='container']/div/div/div[3]"), "This is Explorer content");

		// Push Firefox Tab
		session().click("//div[@id='container']/div/ul/li[2]/a/em");
		assertEquals(session().getAttribute("//div[@id='container']/div/ul/li[1]@class"), null);
		assertEquals(session().getAttribute("//div[@id='container']/div/ul/li[2]@class"), "selected");
		assertEquals(session().getAttribute("//div[@id='container']/div/ul/li[3]@class"), null);
		assertEquals(session().getAttribute("//div[@id='container']/div/ul/li[4]@class"), null);
		assertEquals(session().getAttribute("//div[@id='container']/div/div/div[1]@class"), "yui-hidden");
		assertEquals(session().getAttribute("//div[@id='container']/div/div/div[2]@class"), null);
		assertEquals(session().getAttribute("//div[@id='container']/div/div/div[3]@class"), "yui-hidden");
		assertEquals(session().getAttribute("//div[@id='container']/div/div/div[4]@class"), "yui-hidden");
		assertEquals(session().getText("//div[@id='container']/div/div/div[2]"), "This is Firefox content");

		// Push Opera Tab
		session().click("//div[@id='container']/div/ul/li[1]/a/em");
		assertEquals(session().getAttribute("//div[@id='container']/div/ul/li[1]@class"), "selected");
		assertEquals(session().getAttribute("//div[@id='container']/div/ul/li[2]@class"), null);
		assertEquals(session().getAttribute("//div[@id='container']/div/ul/li[3]@class"), null);
		assertEquals(session().getAttribute("//div[@id='container']/div/ul/li[4]@class"), null);
		assertEquals(session().getAttribute("//div[@id='container']/div/div/div[1]@class"), null);
		assertEquals(session().getAttribute("//div[@id='container']/div/div/div[2]@class"), "yui-hidden");
		assertEquals(session().getAttribute("//div[@id='container']/div/div/div[3]@class"), "yui-hidden");
		assertEquals(session().getAttribute("//div[@id='container']/div/div/div[4]@class"), "yui-hidden");
		assertEquals(session().getText("//div[@id='container']/div/div/div[1]"), "This is Opera content");

		// Push Firefox Tab
		session().click("//div[@id='container']/div/ul/li[2]/a/em");
		assertEquals(session().getAttribute("//div[@id='container']/div/ul/li[1]@class"), null);
		assertEquals(session().getAttribute("//div[@id='container']/div/ul/li[2]@class"), "selected");
		assertEquals(session().getAttribute("//div[@id='container']/div/ul/li[3]@class"), null);
		assertEquals(session().getAttribute("//div[@id='container']/div/ul/li[4]@class"), null);
		assertEquals(session().getAttribute("//div[@id='container']/div/div/div[1]@class"), "yui-hidden");
		assertEquals(session().getAttribute("//div[@id='container']/div/div/div[2]@class"), null);
		assertEquals(session().getAttribute("//div[@id='container']/div/div/div[3]@class"), "yui-hidden");
		assertEquals(session().getAttribute("//div[@id='container']/div/div/div[4]@class"), "yui-hidden");
		assertEquals(session().getText("//div[@id='container']/div/div/div[2]"), "This is Firefox content");

		// Push Explorer Tab
		session().click("//div[@id='container']/div/ul/li[3]/a/em");
		assertEquals(session().getAttribute("//div[@id='container']/div/ul/li[1]@class"), null);
		assertEquals(session().getAttribute("//div[@id='container']/div/ul/li[2]@class"), null);
		assertEquals(session().getAttribute("//div[@id='container']/div/ul/li[3]@class"), "selected");
		assertEquals(session().getAttribute("//div[@id='container']/div/ul/li[4]@class"), null);
		assertEquals(session().getAttribute("//div[@id='container']/div/div/div[1]@class"), "yui-hidden");
		assertEquals(session().getAttribute("//div[@id='container']/div/div/div[2]@class"), "yui-hidden");
		assertEquals(session().getAttribute("//div[@id='container']/div/div/div[3]@class"), null);
		assertEquals(session().getAttribute("//div[@id='container']/div/div/div[4]@class"), "yui-hidden");
		assertEquals(session().getText("//div[@id='container']/div/div/div[3]"), "This is Explorer content");

		// Push Safari Tab
		session().click("//div[@id='container']/div/ul/li[4]/a/em");
		assertEquals(session().getAttribute("//div[@id='container']/div/ul/li[1]@class"), null);
		assertEquals(session().getAttribute("//div[@id='container']/div/ul/li[2]@class"), null);
		assertEquals(session().getAttribute("//div[@id='container']/div/ul/li[3]@class"), null);
		assertEquals(session().getAttribute("//div[@id='container']/div/ul/li[4]@class"), "selected");
		assertEquals(session().getAttribute("//div[@id='container']/div/div/div[1]@class"), "yui-hidden");
		assertEquals(session().getAttribute("//div[@id='container']/div/div/div[2]@class"), "yui-hidden");
		assertEquals(session().getAttribute("//div[@id='container']/div/div/div[3]@class"), "yui-hidden");
		assertEquals(session().getAttribute("//div[@id='container']/div/div/div[4]@class"), null);
		assertEquals(session().getText("//div[@id='container']/div/div/div[4]"), "This is Safari content");
	
	
	}

}

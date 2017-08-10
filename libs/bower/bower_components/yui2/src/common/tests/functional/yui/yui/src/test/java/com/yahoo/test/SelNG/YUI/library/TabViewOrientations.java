package com.yahoo.test.SelNG.YUI.library;

import com.yahoo.test.SelNG.framework.core.SelNGBase;
import static com.yahoo.test.SelNG.framework.util.ThreadSafeSeleniumSessionStorage.session;
// import static com.thoughtworks.selenium.*;
import static org.testng.Assert.assertEquals;


public class TabViewOrientations extends SelNGBase {

	public static void tabTest() {

	    session().open("http://10.72.112.142/dev/gitroot/yui2/src/tabview/tests/functional/html/VerticalTabs.html");
	    //assertEquals(session().getTitle(), "Build Different Orientations");
	    
	    // Top orientation
	    // TODO: check that tabs are oriented correctly vs content -- look for class
	    // TabViewsOrientations
	    session().click("//a[@href='#tab1-top']");
	    assertEquals(session().getAttribute("//div[@id='demo-top']/ul/li[1]@class"), "selected");
	    assertEquals(session().getAttribute("//div[@id='demo-top']/ul/li[2]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo-top']/ul/li[3]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo-top']/div/div[1]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo-top']/div/div[2]@class"), "yui-hidden");
	    assertEquals(session().getAttribute("//div[@id='demo-top']/div/div[3]@class"), "yui-hidden");
	    
	    session().click("//a[@href='#tab2-top']");
	    assertEquals(session().getAttribute("//div[@id='demo-top']/ul/li[2]@class"), "selected");
	    assertEquals(session().getAttribute("//div[@id='demo-top']/ul/li[1]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo-top']/ul/li[3]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo-top']/div/div[2]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo-top']/div/div[1]@class"), "yui-hidden");
	    assertEquals(session().getAttribute("//div[@id='demo-top']/div/div[3]@class"), "yui-hidden");
	    
	    session().click("//a[@href='#tab3-top']");
	    assertEquals(session().getAttribute("//div[@id='demo-top']/ul/li[3]@class"), "selected");
	    assertEquals(session().getAttribute("//div[@id='demo-top']/ul/li[1]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo-top']/ul/li[2]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo-top']/div/div[3]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo-top']/div/div[1]@class"), "yui-hidden");
	    assertEquals(session().getAttribute("//div[@id='demo-top']/div/div[2]@class"), "yui-hidden");
	    
	    session().click("//a[@href='#tab2-top']");
	    assertEquals(session().getAttribute("//div[@id='demo-top']/ul/li[2]@class"), "selected");
	    assertEquals(session().getAttribute("//div[@id='demo-top']/ul/li[1]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo-top']/ul/li[3]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo-top']/div/div[2]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo-top']/div/div[1]@class"), "yui-hidden");
	    assertEquals(session().getAttribute("//div[@id='demo-top']/div/div[3]@class"), "yui-hidden");
	    
	    session().click("//a[@href='#tab1-top']");
	    assertEquals(session().getAttribute("//div[@id='demo-top']/ul/li[1]@class"), "selected");
	    assertEquals(session().getAttribute("//div[@id='demo-top']/ul/li[2]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo-top']/ul/li[3]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo-top']/div/div[1]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo-top']/div/div[2]@class"), "yui-hidden");
	    assertEquals(session().getAttribute("//div[@id='demo-top']/div/div[3]@class"), "yui-hidden");
	
	    // Left orientation
	    session().click("//a[@href='#tab1-left']");
	    assertEquals(session().getAttribute("//div[@id='demo-left']/ul/li[1]@class"), "selected");
	    assertEquals(session().getAttribute("//div[@id='demo-left']/ul/li[2]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo-left']/ul/li[3]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo-left']/div/div[1]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo-left']/div/div[2]@class"), "yui-hidden");
	    assertEquals(session().getAttribute("//div[@id='demo-left']/div/div[3]@class"), "yui-hidden");
	    
	    session().click("//a[@href='#tab2-left']");
	    assertEquals(session().getAttribute("//div[@id='demo-left']/ul/li[2]@class"), "selected");
	    assertEquals(session().getAttribute("//div[@id='demo-left']/ul/li[1]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo-left']/ul/li[3]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo-left']/div/div[2]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo-left']/div/div[1]@class"), "yui-hidden");
	    assertEquals(session().getAttribute("//div[@id='demo-left']/div/div[3]@class"), "yui-hidden");
	    
	    session().click("//a[@href='#tab3-left']");
	    assertEquals(session().getAttribute("//div[@id='demo-left']/ul/li[3]@class"), "selected");
	    assertEquals(session().getAttribute("//div[@id='demo-left']/ul/li[1]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo-left']/ul/li[2]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo-left']/div/div[3]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo-left']/div/div[1]@class"), "yui-hidden");
	    assertEquals(session().getAttribute("//div[@id='demo-left']/div/div[2]@class"), "yui-hidden");
	    
	    session().click("//a[@href='#tab2-left']");
	    assertEquals(session().getAttribute("//div[@id='demo-left']/ul/li[2]@class"), "selected");
	    assertEquals(session().getAttribute("//div[@id='demo-left']/ul/li[1]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo-left']/ul/li[3]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo-left']/div/div[2]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo-left']/div/div[1]@class"), "yui-hidden");
	    assertEquals(session().getAttribute("//div[@id='demo-left']/div/div[3]@class"), "yui-hidden");
	    
	    session().click("//a[@href='#tab1-left']");
	    assertEquals(session().getAttribute("//div[@id='demo-left']/ul/li[1]@class"), "selected");
	    assertEquals(session().getAttribute("//div[@id='demo-left']/ul/li[2]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo-left']/ul/li[3]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo-left']/div/div[1]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo-left']/div/div[2]@class"), "yui-hidden");
	    assertEquals(session().getAttribute("//div[@id='demo-left']/div/div[3]@class"), "yui-hidden");
	
	    // Right orientation
	    session().click("//a[@href='#tab1-right']");
	    assertEquals(session().getAttribute("//div[@id='demo-right']/ul/li[1]@class"), "selected");
	    assertEquals(session().getAttribute("//div[@id='demo-right']/ul/li[2]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo-right']/ul/li[3]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo-right']/div/div[1]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo-right']/div/div[2]@class"), "yui-hidden");
	    assertEquals(session().getAttribute("//div[@id='demo-right']/div/div[3]@class"), "yui-hidden");
	    
	    session().click("//a[@href='#tab2-right']");
	    assertEquals(session().getAttribute("//div[@id='demo-right']/ul/li[2]@class"), "selected");
	    assertEquals(session().getAttribute("//div[@id='demo-right']/ul/li[1]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo-right']/ul/li[3]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo-right']/div/div[2]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo-right']/div/div[1]@class"), "yui-hidden");
	    assertEquals(session().getAttribute("//div[@id='demo-right']/div/div[3]@class"), "yui-hidden");
	    
	    session().click("//a[@href='#tab3-right']");
	    assertEquals(session().getAttribute("//div[@id='demo-right']/ul/li[3]@class"), "selected");
	    assertEquals(session().getAttribute("//div[@id='demo-right']/ul/li[1]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo-right']/ul/li[2]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo-right']/div/div[3]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo-right']/div/div[1]@class"), "yui-hidden");
	    assertEquals(session().getAttribute("//div[@id='demo-right']/div/div[2]@class"), "yui-hidden");
	    
	    session().click("//a[@href='#tab2-right']");
	    assertEquals(session().getAttribute("//div[@id='demo-right']/ul/li[2]@class"), "selected");
	    assertEquals(session().getAttribute("//div[@id='demo-right']/ul/li[1]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo-right']/ul/li[3]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo-right']/div/div[2]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo-right']/div/div[1]@class"), "yui-hidden");
	    assertEquals(session().getAttribute("//div[@id='demo-right']/div/div[3]@class"), "yui-hidden");
	    
	    session().click("//a[@href='#tab1-right']");
	    assertEquals(session().getAttribute("//div[@id='demo-right']/ul/li[1]@class"), "selected");
	    assertEquals(session().getAttribute("//div[@id='demo-right']/ul/li[2]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo-right']/ul/li[3]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo-right']/div/div[1]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo-right']/div/div[2]@class"), "yui-hidden");
	    assertEquals(session().getAttribute("//div[@id='demo-right']/div/div[3]@class"), "yui-hidden");
	
	    // Bottom orientation
	    session().click("//a[@href='#tab1-bottom']");
	    assertEquals(session().getAttribute("//div[@id='demo-bottom']/ul/li[1]@class"), "selected");
	    assertEquals(session().getAttribute("//div[@id='demo-bottom']/ul/li[2]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo-bottom']/ul/li[3]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo-bottom']/div/div[1]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo-bottom']/div/div[2]@class"), "yui-hidden");
	    assertEquals(session().getAttribute("//div[@id='demo-bottom']/div/div[3]@class"), "yui-hidden");
	    
	    session().click("//a[@href='#tab2-bottom']");
	    assertEquals(session().getAttribute("//div[@id='demo-bottom']/ul/li[2]@class"), "selected");
	    assertEquals(session().getAttribute("//div[@id='demo-bottom']/ul/li[1]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo-bottom']/ul/li[3]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo-bottom']/div/div[2]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo-bottom']/div/div[1]@class"), "yui-hidden");
	    assertEquals(session().getAttribute("//div[@id='demo-bottom']/div/div[3]@class"), "yui-hidden");
	    
	    session().click("//a[@href='#tab3-bottom']");
	    assertEquals(session().getAttribute("//div[@id='demo-bottom']/ul/li[3]@class"), "selected");
	    assertEquals(session().getAttribute("//div[@id='demo-bottom']/ul/li[1]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo-bottom']/ul/li[2]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo-bottom']/div/div[3]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo-bottom']/div/div[1]@class"), "yui-hidden");
	    assertEquals(session().getAttribute("//div[@id='demo-bottom']/div/div[2]@class"), "yui-hidden");
	    
	    session().click("//a[@href='#tab2-bottom']");
	    assertEquals(session().getAttribute("//div[@id='demo-bottom']/ul/li[2]@class"), "selected");
	    assertEquals(session().getAttribute("//div[@id='demo-bottom']/ul/li[1]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo-bottom']/ul/li[3]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo-bottom']/div/div[2]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo-bottom']/div/div[1]@class"), "yui-hidden");
	    assertEquals(session().getAttribute("//div[@id='demo-bottom']/div/div[3]@class"), "yui-hidden");
	    
	    session().click("//a[@href='#tab1-bottom']");
	    assertEquals(session().getAttribute("//div[@id='demo-bottom']/ul/li[1]@class"), "selected");
	    assertEquals(session().getAttribute("//div[@id='demo-bottom']/ul/li[2]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo-bottom']/ul/li[3]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo-bottom']/div/div[1]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo-bottom']/div/div[2]@class"), "yui-hidden");
	    assertEquals(session().getAttribute("//div[@id='demo-bottom']/div/div[3]@class"), "yui-hidden");
	
	}

}

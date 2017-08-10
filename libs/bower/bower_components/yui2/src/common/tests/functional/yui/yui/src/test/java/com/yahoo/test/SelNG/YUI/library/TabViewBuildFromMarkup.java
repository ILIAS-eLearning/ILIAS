package com.yahoo.test.SelNG.YUI.library;

import com.yahoo.test.SelNG.framework.core.SelNGBase;
import static com.yahoo.test.SelNG.framework.util.ThreadSafeSeleniumSessionStorage.session;
// import static com.thoughtworks.selenium.*;
import static org.testng.Assert.assertEquals;
import java.util.Properties;
import java.io.FileInputStream;
import java.io.IOException;

import java.awt.Robot;
import java.awt.event.KeyEvent;

public class TabViewBuildFromMarkup extends SelNGBase {

	
	public static void tabTest() throws Exception {
		
	    session().open("http://presentbright.corp.yahoo.com/yui2/latest_build/examples/tabview/frommarkup_clean.html");
	    //assertEquals(session().getTitle(), "Build from Markup");
	    
	    // TODO:
	    // check for selected will multiple class attributes cause this to fail? yes
	    // abstract this block
	    // look for content class and ids
	    // focus on element by class, then <ENTER>
	    // put selenium IDE html in sandbox
	    // do like 'Adding Tabs'
	    
	    // TODO: redo code as the page markup changed
	    
	    // Click on first tab
	    session().click("//a[@href='#tab1']");
	    assertEquals(session().getAttribute("//div[@id='demo']/ul/li[1]@class"), "selected");
	    //TODO: assertEquals(session().getAttribute(classVariable), "selected");
	    assertEquals(session().getAttribute("//div[@id='demo']/ul/li[2]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo']/ul/li[3]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo']/div/div[1]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo']/div/div[2]@class"), "yui-hidden");
	    assertEquals(session().getAttribute("//div[@id='demo']/div/div[3]@class"), "yui-hidden");
	    
	    // Click on second tab
	    session().click("//a[@href='#tab2']");
	    assertEquals(session().getAttribute("//div[@id='demo']/ul/li[2]@class"), "selected");
	    assertEquals(session().getAttribute("//div[@id='demo']/ul/li[1]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo']/ul/li[3]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo']/div/div[2]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo']/div/div[1]@class"), "yui-hidden");
	    assertEquals(session().getAttribute("//div[@id='demo']/div/div[3]@class"), "yui-hidden");
	    
	    // Click on third tab
	    session().click("//a[@href='#tab3']");
	    assertEquals(session().getAttribute("//div[@id='demo']/ul/li[3]@class"), "selected");
	    assertEquals(session().getAttribute("//div[@id='demo']/ul/li[1]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo']/ul/li[2]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo']/div/div[3]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo']/div/div[1]@class"), "yui-hidden");
	    assertEquals(session().getAttribute("//div[@id='demo']/div/div[2]@class"), "yui-hidden");
	    
	    // Click on second tab
	    session().click("//a[@href='#tab2']");
	    assertEquals(session().getAttribute("//div[@id='demo']/ul/li[2]@class"), "selected");
	    assertEquals(session().getAttribute("//div[@id='demo']/ul/li[1]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo']/ul/li[3]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo']/div/div[2]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo']/div/div[1]@class"), "yui-hidden");
	    assertEquals(session().getAttribute("//div[@id='demo']/div/div[3]@class"), "yui-hidden");
	    
	    // Click on first tab
	    session().click("//a[@href='#tab1']");
	    assertEquals(session().getAttribute("//div[@id='demo']/ul/li[1]@class"), "selected");
	    assertEquals(session().getAttribute("//div[@id='demo']/ul/li[2]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo']/ul/li[3]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo']/div/div[1]@class"), null);
	    assertEquals(session().getAttribute("//div[@id='demo']/div/div[2]@class"), "yui-hidden");
	    assertEquals(session().getAttribute("//div[@id='demo']/div/div[3]@class"), "yui-hidden");
	
    }

}

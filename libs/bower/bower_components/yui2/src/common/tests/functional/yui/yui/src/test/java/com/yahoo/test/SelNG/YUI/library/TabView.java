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

public class TabView extends SelNGBase {

/******	
	public static void tabTest() {
		session().open("http://developer.yahoo.com/yui/examples/tabview/frommarkup_clean.html");
		session().click("//div[@id='demo']/ul/li[1]/a/em");
		session().click("//div[@id='demo']/ul/li[2]/a/em");
		session().click("//div[@id='demo']/ul/li[3]/a/em");
		session().click("//div[@id='demo']/ul/li[2]/a/em");
		session().click("//div[@id='demo']/ul/li[1]/a/em");
	}
*****/

	public static void tabTest() {

		/*******		
		Properties hostProperties = new Properties();
	    String hostName = "";
	    try {
	        hostProperties.load(new FileInputStream("host.properties"));
            hostName = hostProperties.getProperty("DOMAIN");
	    
	    } catch (IOException e) {
	    	//e.printStackTrace();
	    }
	    ******/

		
		
//		session().open(hostName + "/yui2/latest_build/examples/tabview/frommarkup_clean.html"); 	    
	    session().open("http://presentbright.corp.yahoo.com/yui2/latest_build/examples/tabview/frommarkup_clean.html");
	    assertEquals(session().getTitle(), "Build from Markup");

	    
	    session().click("//a[@href='#tab1']/em");

	    try {
	    	Robot robot = new Robot();
	    	robot.keyPress(KeyEvent.VK_TAB);
	    } catch(Exception e) {
	    	e.printStackTrace();
	    }
	    
	    /*************
	    try {
	 
	    	Thread.sleep(1000);
	    	Robot robot = new Robot();
	    	System.out.println("something");
	    	robot.keyPress(KeyEvent.VK_TAB);
	    	robot.keyPress(KeyEvent.VK_ENTER);
	    	System.out.println("after");
	    	Thread.sleep(1000);
	    } catch(Exception e) {
	    	e.printStackTrace();
	    }
	    ****************/
	    
	    
	    /*******
	    //session().windowMaximize();
        //session().waitForPageToLoad("30000");
        //session().windowFocus();
	     *****/

	
	    /********
	    
	    assertEquals(session().getAttribute("//div[@id='demo']/ul/li[1]@class"), "selected");
	assertEquals(session().getAttribute("//div[@id='demo']/ul/li[2]@class"), null);
	assertEquals(session().getAttribute("//div[@id='demo']/ul/li[3]@class"), null);
	//assertEquals(session().getExpression("1"), "1");
	assertEquals(session().getAttribute("//div[@id='demo']/div/div[1]@class"), null);
	assertEquals(session().getAttribute("//div[@id='demo']/div/div[2]@class"), "yui-hidden");
	assertEquals(session().getAttribute("//div[@id='demo']/div/div[3]@class"), "yui-hidden");
	session().click("//a[@href='#tab2']/em");
	assertEquals(session().getAttribute("//div[@id='demo']/ul/li[2]@class"), "selected");
	assertEquals(session().getAttribute("//div[@id='demo']/ul/li[1]@class"), null);
	assertEquals(session().getAttribute("//div[@id='demo']/ul/li[3]@class"), null);
	assertEquals(session().getAttribute("//div[@id='demo']/div/div[2]@class"), null);
	assertEquals(session().getAttribute("//div[@id='demo']/div/div[1]@class"), "yui-hidden");
	assertEquals(session().getAttribute("//div[@id='demo']/div/div[3]@class"), "yui-hidden");
	session().click("//a[@href='#tab3']/em");
	assertEquals(session().getAttribute("//div[@id='demo']/ul/li[3]@class"), "selected");
	assertEquals(session().getAttribute("//div[@id='demo']/ul/li[1]@class"), null);
	assertEquals(session().getAttribute("//div[@id='demo']/ul/li[2]@class"), null);
	assertEquals(session().getAttribute("//div[@id='demo']/div/div[3]@class"), null);
	assertEquals(session().getAttribute("//div[@id='demo']/div/div[1]@class"), "yui-hidden");
	assertEquals(session().getAttribute("//div[@id='demo']/div/div[2]@class"), "yui-hidden");
	session().click("//a[@href='#tab2']/em");
	assertEquals(session().getAttribute("//div[@id='demo']/ul/li[2]@class"), "selected");
	assertEquals(session().getAttribute("//div[@id='demo']/ul/li[1]@class"), null);
	assertEquals(session().getAttribute("//div[@id='demo']/ul/li[3]@class"), null);
	assertEquals(session().getAttribute("//div[@id='demo']/div/div[2]@class"), null);
	assertEquals(session().getAttribute("//div[@id='demo']/div/div[1]@class"), "yui-hidden");
	assertEquals(session().getAttribute("//div[@id='demo']/div/div[3]@class"), "yui-hidden");
	session().click("//a[@href='#tab1']/em");
	assertEquals(session().getAttribute("//div[@id='demo']/ul/li[1]@class"), "selected");
	assertEquals(session().getAttribute("//div[@id='demo']/ul/li[2]@class"), null);
	assertEquals(session().getAttribute("//div[@id='demo']/ul/li[3]@class"), null);
	assertEquals(session().getAttribute("//div[@id='demo']/div/div[1]@class"), null);
	assertEquals(session().getAttribute("//div[@id='demo']/div/div[2]@class"), "yui-hidden");
	assertEquals(session().getAttribute("//div[@id='demo']/div/div[3]@class"), "yui-hidden");
	// How to check for tab key??

	************/
	
	
	}

}

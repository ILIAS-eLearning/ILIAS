package com.yahoo.test.SelNG.YUI.tests;

import static org.testng.Assert.fail;

import org.apache.log4j.Logger;
import org.testng.annotations.Test;

import com.yahoo.test.SelNG.YUI.library.TabViewRemovingTabs;
import com.yahoo.test.SelNG.framework.util.SelNGRetryAnalyzer;

public class TabViewTestRemovingTabs extends CommonTest{

	  public static Logger logger = Logger.getLogger(TabViewTestRemovingTabs.class.getName());

	  @Test(groups = {"demo"}, retryAnalyzer = SelNGRetryAnalyzer.class)
	  public void TabViewRemovingTabs() {
	    logger.info("Invoked Tabview Method");
	    try {
	    	TabViewRemovingTabs.tabTest();
	    } catch (Throwable t) {
	      t.printStackTrace(System.out);
	      logger.error(t);
	      fail();
	    }
	  }
	}
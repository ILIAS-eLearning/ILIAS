package com.yahoo.test.SelNG.YUI.tests;

import static org.testng.Assert.fail;

import org.apache.log4j.Logger;
import org.testng.annotations.Test;

import com.yahoo.test.SelNG.YUI.library.TabViewExternalSource;
import com.yahoo.test.SelNG.framework.util.SelNGRetryAnalyzer;

public class TabViewTestExternalSource extends CommonTest{

	  public static Logger logger = Logger.getLogger(TabViewTest.class.getName());

	  @Test(groups = {"demo"}, retryAnalyzer = SelNGRetryAnalyzer.class)
	  public void TabViewExternalSource() {
	    logger.info("Invoked Tabview Method");
	    try {
	    	TabViewExternalSource.tabTest();
	    } catch (Throwable t) {
	      t.printStackTrace(System.out);
	      logger.error(t);
	      fail();
	    }
	  }
	}
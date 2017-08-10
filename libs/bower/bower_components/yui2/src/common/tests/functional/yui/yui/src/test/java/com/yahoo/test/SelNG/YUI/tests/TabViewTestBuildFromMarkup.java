package com.yahoo.test.SelNG.YUI.tests;

import static org.testng.Assert.fail;

import org.apache.log4j.Logger;
import org.testng.annotations.Test;

import com.yahoo.test.SelNG.YUI.library.TabViewBuildFromMarkup;
import com.yahoo.test.SelNG.framework.util.SelNGRetryAnalyzer;

public class TabViewTestBuildFromMarkup extends CommonTest{

	  public static Logger logger = Logger.getLogger(TabViewTestBuildFromMarkup.class.getName());

	  @Test(groups = {"demo"}, retryAnalyzer = SelNGRetryAnalyzer.class)
	  public void TabViewBuildFromMarkup() {
	    logger.info("Invoked TabViewBuildFromMarkup Method");
	    try {
	    	TabViewBuildFromMarkup.tabTest();
	    } catch (Throwable t) {
	      t.printStackTrace(System.out);
	      logger.error(t);
	      fail();
	    }
	  }
	}
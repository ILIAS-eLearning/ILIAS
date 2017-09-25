package com.yahoo.test.SelNG.YUI.tests;

import static org.testng.Assert.fail;

import org.apache.log4j.Logger;
import org.testng.annotations.Test;

import com.yahoo.test.SelNG.YUI.library.TabViewOrientations;
import com.yahoo.test.SelNG.framework.util.SelNGRetryAnalyzer;

public class TabViewTestOrientations extends CommonTest{

	  public static Logger logger = Logger.getLogger(TabViewTestOrientations.class.getName());

	  @Test(groups = {"demo"}, retryAnalyzer = SelNGRetryAnalyzer.class)
	  public void TabViewDifferentOrientations() {
	    logger.info("Invoked Tabview Method");
	    try {
	    	TabViewOrientations.tabTest();
	    } catch (Throwable t) {
	      t.printStackTrace(System.out);
	      logger.error(t);
	      fail();
	    }
	  }
	}
package com.yahoo.test.SelNG.YUI.tests;

import static org.testng.Assert.fail;

import org.apache.log4j.Logger;
import org.testng.annotations.Test;

import com.yahoo.test.SelNG.YUI.library.DragAndDropCircle;
import com.yahoo.test.SelNG.framework.util.SelNGRetryAnalyzer;

public class DragAndDropTestCircle extends CommonTest{

	  public static Logger logger = Logger.getLogger(DragAndDropTestCircle.class.getName());

	  @Test(groups = {"demo"}, retryAnalyzer = SelNGRetryAnalyzer.class)
	  public void DragAndDropCircle() {
	    logger.info("Invoked Tabview Method");
	    try {
	    	DragAndDropCircle.ddTest();
	    } catch (Throwable t) {
	      t.printStackTrace(System.out);
	      logger.error(t);
	      fail();
	    }
	  }
	}
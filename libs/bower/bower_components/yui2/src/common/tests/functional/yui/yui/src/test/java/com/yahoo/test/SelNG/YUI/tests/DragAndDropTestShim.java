package com.yahoo.test.SelNG.YUI.tests;

import static org.testng.Assert.fail;

import org.apache.log4j.Logger;
import org.testng.annotations.Test;

import com.yahoo.test.SelNG.YUI.library.DragAndDropShim;
import com.yahoo.test.SelNG.framework.util.SelNGRetryAnalyzer;

public class DragAndDropTestShim extends CommonTest{

	  public static Logger logger = Logger.getLogger(DragAndDropTestShim.class.getName());

	  @Test(groups = {"demo"}, retryAnalyzer = SelNGRetryAnalyzer.class)
	  public void DragAndDropShim() {
	    logger.info("Invoked Tabview Method");
	    try {
	    	DragAndDropShim.ddTest();
	    } catch (Throwable t) {
	      t.printStackTrace(System.out);
	      logger.error(t);
	      fail();
	    }
	  }
	}
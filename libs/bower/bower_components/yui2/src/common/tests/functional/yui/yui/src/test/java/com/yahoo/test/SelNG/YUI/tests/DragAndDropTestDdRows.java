package com.yahoo.test.SelNG.YUI.tests;

import static org.testng.Assert.fail;

import org.apache.log4j.Logger;
import org.testng.annotations.Test;

import com.yahoo.test.SelNG.YUI.library.DragAndDropDdRows;
import com.yahoo.test.SelNG.framework.util.SelNGRetryAnalyzer;

public class DragAndDropTestDdRows extends CommonTest{

	  public static Logger logger = Logger.getLogger(DragAndDropTestDdRows.class.getName());

	  @Test(groups = {"demo"}, retryAnalyzer = SelNGRetryAnalyzer.class)
	  public void DragAndDropDdRows() {
	    logger.info("Invoked Tabview Method");
	    try {
	    	DragAndDropDdRows.ddTest();
	    } catch (Throwable t) {
	      t.printStackTrace(System.out);
	      logger.error(t);
	      fail();
	    }
	  }
	}
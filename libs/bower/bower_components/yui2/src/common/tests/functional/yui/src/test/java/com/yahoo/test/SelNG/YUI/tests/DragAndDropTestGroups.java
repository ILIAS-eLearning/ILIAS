package com.yahoo.test.SelNG.YUI.tests;

import static org.testng.Assert.fail;

import org.apache.log4j.Logger;
import org.testng.annotations.Test;

import com.yahoo.test.SelNG.YUI.library.DragAndDropGroups;
import com.yahoo.test.SelNG.framework.util.SelNGRetryAnalyzer;

public class DragAndDropTestGroups extends CommonTest{

	  public static Logger logger = Logger.getLogger(DragAndDropTestBasic.class.getName());

	  @Test(groups = {"demo"}, retryAnalyzer = SelNGRetryAnalyzer.class)
	  public void DragAndDropGroups() {
	    logger.info("Invoked Tabview Method");
	    try {
	    	DragAndDropGroups.ddTest();
	    } catch (Throwable t) {
	      t.printStackTrace(System.out);
	      logger.error(t);
	      fail();
	    }
	  }
	}
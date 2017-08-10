package com.yahoo.test.SelNG.YUI.tests;

import static org.testng.Assert.fail;

import org.apache.log4j.Logger;
import org.testng.annotations.Test;

import com.yahoo.test.SelNG.YUI.library.DragAndDropProxy;
import com.yahoo.test.SelNG.framework.util.SelNGRetryAnalyzer;

public class DragAndDropTestProxy extends CommonTest{

	  public static Logger logger = Logger.getLogger(DragAndDropTestBasic.class.getName());

	  @Test(groups = {"demo"}, retryAnalyzer = SelNGRetryAnalyzer.class)
	  public void DragAndDropBasic() {
	    logger.info("Invoked Tabview Method");
	    try {
	    	DragAndDropProxy.ddTest();
	    } catch (Throwable t) {
	      t.printStackTrace(System.out);
	      logger.error(t);
	      fail();
	    }
	  }
	}
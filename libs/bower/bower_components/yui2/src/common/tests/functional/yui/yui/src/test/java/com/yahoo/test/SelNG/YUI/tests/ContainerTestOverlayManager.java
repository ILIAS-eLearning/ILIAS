package com.yahoo.test.SelNG.YUI.tests;

import static org.testng.Assert.fail;

import org.apache.log4j.Logger;
import org.testng.annotations.Test;

import com.yahoo.test.SelNG.YUI.library.ContainerOverlayManager;
import com.yahoo.test.SelNG.framework.util.SelNGRetryAnalyzer;

public class ContainerTestOverlayManager extends CommonTest{

	  public static Logger logger = Logger.getLogger(ContainerTestOverlayManager.class.getName());

	  @Test(groups = {"demo"}, retryAnalyzer = SelNGRetryAnalyzer.class)
	  public void TabView() {
	    logger.info("Invoked Tabview Method");
	    try {
	    	ContainerOverlayManager.containerTest();
	    } catch (Throwable t) {
	      t.printStackTrace(System.out);
	      logger.error(t);
	      fail();
	    }
	  }
	}
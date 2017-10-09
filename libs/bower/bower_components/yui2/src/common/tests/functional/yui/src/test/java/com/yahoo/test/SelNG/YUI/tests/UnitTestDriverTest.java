package com.yahoo.test.SelNG.YUI.tests;

import static org.testng.Assert.fail;

import org.apache.log4j.Logger;
import org.testng.annotations.DataProvider;
import org.testng.annotations.Test;

import com.yahoo.test.SelNG.YUI.library.UnitTestDriver;
import com.yahoo.test.SelNG.framework.util.SelNGRetryAnalyzer;

public class UnitTestDriverTest extends CommonTest{

    public static Logger logger = Logger.getLogger(UnitTestDriverTest.class.getName());
    
    @Test(groups = {"demo"}, retryAnalyzer = SelNGRetryAnalyzer.class, dataProvider = "YUIUnitTest")
	public void UnitTestDriver(String item) {
    	logger.info("Invoked UnitTestDriver Method");
    	try {
    		UnitTestDriver.unitTest(item);
    	} catch (Throwable t) {
    		t.printStackTrace(System.out);
    		logger.error(t);
    		fail();
    	}
    }
    
    @DataProvider(name="YUIUnitTest")
    public String[][] YUIUnitTest() { 
    	return new String[][] {
    		{ "animation" },
    		{ "autocomplete" }
/*******
    		{ "base" },
    		// { "button" },
    		{ "calendar" },
    		// { "carousel" },
    		// { "charts" },
    		// { "colorpicker" },
    		// { "connection" },
    		{ "container" },
    		{ "cookie" },
    		{ "datasource" },
    		{ "datatable" },
    		{ "datemath" },
    		{ "dom" },
    		{ "dragdrop" },
    		{ "editor" },
    		{ "element" },
    		//"element-delegate",
    		//"event",
    		//"event-delegate",
    		//"event-mouseenter",
    		//"event-simulate",
    		//"fonts",
    		//"get",
    		//"grids",
    		//"history",
    		//"imagecropper",
    		{ "imageloader" },
    		{ "json" },
    		//"layout",
    		{ "logger" },
    		//"menu",
    		//"paginator",
    		{ "profiler" },
    		//"profilerviewer",
    		//"progressbar",
    		//"reset",
    		//"resize",
    		//"selector",
    		//"slider",
    		//"storage",
    		//"stylesheet",
    		//"swf",
    		//"swfdetect",
    		//"swfstore",
    		{ "tabview" },
    		//"treeview",
    		//"uploader",
    		{ "yahoo" },
    		{ "yuiloader" },
    		{ "yuiloader-config" },
    		{ "yuiloader-rollup" },
    		{ "yuitest" }
  		*********/ 
    	};
    }
    
}

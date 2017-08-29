package com.yahoo.test.SelNG.YUI.library;

import com.yahoo.test.SelNG.framework.core.SelNGBase;
import static com.yahoo.test.SelNG.framework.util.ThreadSafeSeleniumSessionStorage.session;
import com.yahoo.test.SelNG.framework.util.YUIReport;
//import static org.testng.Assert.assertEquals;
//import static org.testng.Assert.assertFalse;
import static org.testng.Assert.*;

public class UnitTestDriver extends SelNGBase {

 	public static void unitTest(String item) throws Exception {

		//session().open("http://192.168.1.5/dev/gitroot/yui2/src/common/tests/functional/UnitTestDriver.html?item=" + item);
		//session().open("http://10.72.112.142/dev/gitroot/yui2/src/common/tests/functional/UnitTestDriver.html?item=" + item);
		//session().open("http://172.21.155.101/dev/gitroot/yui2/src/common/tests/functional/UnitTestDriver.html?item=" + item);

 		
 		String uri = "";
 		
 		if(SelNGBase.config.getString("YUIUNITTESTPAGE") != null) {

 			uri = SelNGBase.config.getString("YUIUNITTESTPAGE");

 			session().open(uri + item);
 		
 			Thread.sleep(2000);
 			String s = session().getEval("this.browserbot.getCurrentWindow().YAHOO.Functional.TestManager.results");

 			YUIReport.jsonresponse = s;
 			
 		} else {
 			fail("No URI specified for unit test page in config file");
 		}

	}

}

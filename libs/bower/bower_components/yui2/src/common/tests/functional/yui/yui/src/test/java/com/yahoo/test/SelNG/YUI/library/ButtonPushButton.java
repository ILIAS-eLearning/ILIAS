package com.yahoo.test.SelNG.YUI.library;

import com.yahoo.test.SelNG.framework.core.SelNGBase;
import static com.yahoo.test.SelNG.framework.util.ThreadSafeSeleniumSessionStorage.session;
// import static com.thoughtworks.selenium.*;
import static org.testng.Assert.assertEquals;


public class ButtonPushButton extends SelNGBase {

	public static void buttonTest() throws Exception {

	    session().open("http://10.72.112.142/dev/gitroot/yui2/src/button/tests/functional/html/PushButtons.html");
		//assertEquals(session().getTitle(), "");
		
		for(int i=1; i<10; i++) {
			pushButton(i);
		}

	}
	
	private static void pushButton(int i) {
		
		session().click("pushbutton" + i);
		assertEquals(session().getText("which"), "pushbutton" + i);
		
	}

}

package com.yahoo.test.SelNG.YUI.library;

import com.yahoo.test.SelNG.framework.core.SelNGBase;
import static com.yahoo.test.SelNG.framework.util.ThreadSafeSeleniumSessionStorage.session;
// import static com.thoughtworks.selenium.*;
import static org.testng.Assert.*;


public class AutoCompleteBasicLocal extends SelNGBase {


	public static void autocompleteTest() throws Exception {

		session().open("http://presentbright.corp.yahoo.com/yui2/latest_build/examples/autocomplete/ac_basic_array_clean.html");
		//assertEquals(session().getTitle(), "");

		// the single character that is inserted in the autocomplete
		char[] alpha = {'a','c','d','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w'};
		// the expected number of states for each alpha character that should appear in the ac list
		int[] numStates = {4, 3, 1, 1, 1, 1, 4, 0, 2, 1, 8, 8, 3, 1, 0, 1, 2, 2, 1, 2, 4};

		for(int i=0; i<alpha.length; i++) {
		   checkState(alpha[i], numStates[i]);
		}

		// the two characters that are inserted in the autocomplete
		String[] alphaTwo = {"ar", "co", "in", "mi", "ne", "so"};
		// the xPath li element number that will be clicked in the autocomplete list  e.g. l1[2]
		int[] path = {2, 1, 1, 4, 4, 2};
		// the expected state that will fill the autocomplete after the list li element is clicked
		String[] results = {"Arkansas", "Colorado", "Indiana", "Missouri", "New Jersey", "South Dakota"};
		
		for(int j=0; j<alphaTwo.length; j++) {
			checkStateTwo(alphaTwo[j], path[j], results[j]);
		}
		
		
		/*******
		//session().click("myInput");
		session().keyPress("myInput", "\\40");
		session().keyUp("myInput", "\\40");
		Thread.sleep(1000);
		session().keyPress("myInput", "\\40");
		session().keyUp("myInput", "\\40");
		Thread.sleep(1000);
		session().keyPress("myInput","\\09" );
		session().keyUp("myInput","\\09" );		

		Thread.sleep(2000);
		//session().doubleClick("myInput");
		session().keyPress("myInput","\\08" );
		session().keyUp("myInput","\\08" );		
		Thread.sleep(2000);
		session().refresh();
        ****/

	}
	
	private static void checkState(char alpha, int num) throws Exception {
		
		session().click("myInput");
		session().keyPress("myInput", Character.toString(alpha));
		session().keyUp("myInput", Character.toString(alpha));
		Thread.sleep(1000);
		if(num > 0) {
			for(int i=1; i<=10; i++) {
				if(i <= num) {
					assertFalse(hasAttribute("//ul/li["+i+"]", "style", "display: none"));
				} else {
					assertTrue(hasAttribute("//ul/li["+i+"]", "style", "display: none"));
				}
			}
		}
		
		// backspace
		session().keyPress("myInput","\\08" );
		session().keyUp("myInput","\\08" );		
		Thread.sleep(1000);

	}
	
	private static void checkStateTwo(String s, int e, String results) throws Exception {
		
		for(int i=0; i<s.length(); i++) {
			session().click("myInput");
			String c = s.substring(i, i+1);
			session().keyPress("myInput", c);
			session().keyUp("myInput", c );		
			Thread.sleep(1000);
		}

		session().click("//ul/li["+e+"]");
		Thread.sleep(1000);
		assertEquals(session().getValue("myInput"), results);
		session().refresh();  // make sure the field is cleared
		
	}
	
	public static boolean hasAttribute(String elXpath, String attributeName, String attributeValue) {
		
		String attribute = session().getAttribute(elXpath + "@" + attributeName);
		return ((attribute != null) && (attribute.contains(attributeValue)));

	}

}

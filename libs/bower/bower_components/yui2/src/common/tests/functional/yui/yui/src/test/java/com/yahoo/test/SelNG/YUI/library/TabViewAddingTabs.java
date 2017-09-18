package com.yahoo.test.SelNG.YUI.library;

import com.yahoo.test.SelNG.framework.core.SelNGBase;
import static com.yahoo.test.SelNG.framework.util.ThreadSafeSeleniumSessionStorage.session;
//import com.thoughtworks.selenium.*;
import static org.testng.Assert.*;


public class TabViewAddingTabs extends SelNGBase {
	

	// TODO: put documentation in test, add functions
	
	/** 
	 *
	 *	1. Go to: http://presentbright.corp.yahoo.com/<path to be determined>/AddingTabs.html
	 *	2. Click on each tab separately.
	 *	3. Check that selected tab is highlighted by class name.
	 *	4. Check that non-selected tabs are non-highlighted by class name.
	 *	5. Check that each content associated with the selected tab is visible by class name.
	 *	6. Check the each content not associated with the highlighted tab is invisible by class name. 
	 *	7. Click on add tab button.
	 *	8. Enter tab information and a new tab and new content will be created by appending 'label' and 'content'.
	 *	9. Check that new tab label and new tab content are correct.
	 *	9. Repeat Steps 3 through 6 for new tab.
	 *	10. Use the Selenium focus command (not TAB key) to focus on each tab and then press ENTER key.
	 *	11. Repeat Steps 3 through 6 for selected tab.
	 *
	 * @throws
     * @author 
	 * 
	 * @param
	 * @return
	 * 
	 **/
	
	// TODO: after directory structure is determined and this is checked in, serve from presentbright
	// TODO: put base url in property
	private static final String HOST = "http://10.72.112.142";
	private static final String URI = "/dev/gitroot/yui2/src/tabview/tests/functional/html/TabViewAddTabs.html";

	// private static final String
	private static final String LOCATOR_TAB1_ANCHOR = "//a[@href='#tab1']";
	private static final String LOCATOR_TAB2_ANCHOR = "//a[@href='#tab2']";
	private static final String LOCATOR_TAB3_ANCHOR = "//a[@href='#tab3']";
	private static final String LOCATOR_TAB4_ANCHOR = "//ul[@class='yui-nav']/li[4]/a";
	private static final String LOCATOR_TAB5_ANCHOR = "//ul[@class='yui-nav']/li[5]/a";
	
	private static final String LOCATOR_TAB1 = "//ul[@class='yui-nav']/li[1]";
	private static final String LOCATOR_TAB2 = "//ul[@class='yui-nav']/li[2]";
	private static final String LOCATOR_TAB3 = "//ul[@class='yui-nav']/li[3]";
	private static final String LOCATOR_TAB4 = "//ul[@class='yui-nav']/li[4]";
	private static final String LOCATOR_TAB5 = "//ul[@class='yui-nav']/li[5]";
	
	private static final String LOCATOR_TAB4_LABEL = "//ul[@class='yui-nav']/li[4]/a/em";
	private static final String LOCATOR_TAB5_LABEL = "//ul[@class='yui-nav']/li[5]/a/em";
	
	private static final String LOCATOR_TAB1_CONTENT = "//div[@class='yui-content']/div[1]";
	private static final String LOCATOR_TAB2_CONTENT = "//div[@class='yui-content']/div[2]";
	private static final String LOCATOR_TAB3_CONTENT = "//div[@class='yui-content']/div[3]";
	private static final String LOCATOR_TAB4_CONTENT = "//div[@class='yui-content']/div[4]";
	private static final String LOCATOR_TAB5_CONTENT = "//div[@class='yui-content']/div[5]";
	
	private static final String SELECTED_CLASS = "selected";
	private static final String HIDDEN_CLASS = "yui-hidden";

    private static final String LOCATOR_ADD_TAB_BUTTON = "//div[@id='demo']/button";
	
	
	public static void tabTest() {

		// Open page
		session().open(HOST + URI);
		//session().open("http://172.21.152.81/dev/gitroot/yui2/src/tabview/tests/functional/html/TabViewAddTabs.html");
		//assertEquals(session().getTitle(), "Adding Tabs");

	    // TODO: focus on element by class, then <ENTER>
		
		// Click on the first tab 
		session().click(LOCATOR_TAB1_ANCHOR);

		// Check for correct classes
		assertTrue(hasClass(LOCATOR_TAB1, SELECTED_CLASS));
		assertFalse(hasClass(LOCATOR_TAB2, SELECTED_CLASS));
		assertFalse(hasClass(LOCATOR_TAB3, SELECTED_CLASS));
		assertFalse(hasClass(LOCATOR_TAB1_CONTENT, HIDDEN_CLASS));
		assertTrue(hasClass(LOCATOR_TAB2_CONTENT, HIDDEN_CLASS));
		assertTrue(hasClass(LOCATOR_TAB3_CONTENT, HIDDEN_CLASS));
		
		// Click on the second tab
		session().click(LOCATOR_TAB2_ANCHOR);

		// Check for correct classes
		assertFalse(hasClass(LOCATOR_TAB1, SELECTED_CLASS));
		assertTrue(hasClass(LOCATOR_TAB2, SELECTED_CLASS));
		assertFalse(hasClass(LOCATOR_TAB3, SELECTED_CLASS));
		assertTrue(hasClass(LOCATOR_TAB1_CONTENT, HIDDEN_CLASS));
		assertFalse(hasClass(LOCATOR_TAB2_CONTENT, HIDDEN_CLASS));
		assertTrue(hasClass(LOCATOR_TAB3_CONTENT, HIDDEN_CLASS));

		// Click on the third tab
		session().click(LOCATOR_TAB3_ANCHOR);

		// Check for correct classes
		assertFalse(hasClass(LOCATOR_TAB1, SELECTED_CLASS));
		assertFalse(hasClass(LOCATOR_TAB2, SELECTED_CLASS));
		assertTrue(hasClass(LOCATOR_TAB3, SELECTED_CLASS));
		assertTrue(hasClass(LOCATOR_TAB1_CONTENT, HIDDEN_CLASS));
		assertTrue(hasClass(LOCATOR_TAB2_CONTENT, HIDDEN_CLASS));
		assertFalse(hasClass(LOCATOR_TAB3_CONTENT, HIDDEN_CLASS));

		// Click on the second tab
		session().click(LOCATOR_TAB2_ANCHOR);
		
		// Check for correct classes
		assertFalse(hasClass(LOCATOR_TAB1, SELECTED_CLASS));
		assertTrue(hasClass(LOCATOR_TAB2, SELECTED_CLASS));
		assertFalse(hasClass(LOCATOR_TAB3, SELECTED_CLASS));
		assertTrue(hasClass(LOCATOR_TAB1_CONTENT, HIDDEN_CLASS));
		assertFalse(hasClass(LOCATOR_TAB2_CONTENT, HIDDEN_CLASS));
		assertTrue(hasClass(LOCATOR_TAB3_CONTENT, HIDDEN_CLASS));

		// TODO:  Check that info in prompt is the same as in the label and content
		// Add two new tabs
		session().answerOnNextPrompt("Tab 4");
		session().click(LOCATOR_ADD_TAB_BUTTON);
		assertEquals(session().getPrompt(), "Enter the new tab name");
		session().answerOnNextPrompt("Tab 5");
		session().click(LOCATOR_ADD_TAB_BUTTON);
		assertEquals(session().getPrompt(), "Enter the new tab name");

		// Click on new fourth tab
		session().click(LOCATOR_TAB4_ANCHOR);
		
		// Check for correct label and content
		assertEquals(session().getText(LOCATOR_TAB4_LABEL), "Tab 4 Label");
		assertEquals(session().getText(LOCATOR_TAB4_CONTENT), "Tab 4 Content");
		
		// Check for correct classes
		assertFalse(hasClass(LOCATOR_TAB1, SELECTED_CLASS));
		assertFalse(hasClass(LOCATOR_TAB2, SELECTED_CLASS));
		assertFalse(hasClass(LOCATOR_TAB3, SELECTED_CLASS));
		assertTrue(hasClass(LOCATOR_TAB4, SELECTED_CLASS));
		assertFalse(hasClass(LOCATOR_TAB5, SELECTED_CLASS));
		assertTrue(hasClass(LOCATOR_TAB1_CONTENT, HIDDEN_CLASS));
		assertTrue(hasClass(LOCATOR_TAB2_CONTENT, HIDDEN_CLASS));
		assertTrue(hasClass(LOCATOR_TAB3_CONTENT, HIDDEN_CLASS));
		assertFalse(hasClass(LOCATOR_TAB4_CONTENT, HIDDEN_CLASS));
		assertTrue(hasClass(LOCATOR_TAB5_CONTENT, HIDDEN_CLASS));
		
		// Click on new fifth tab
		session().click(LOCATOR_TAB5_ANCHOR);
		
		// Check for correct classes
		assertFalse(hasClass(LOCATOR_TAB1, SELECTED_CLASS));
		assertFalse(hasClass(LOCATOR_TAB2, SELECTED_CLASS));
		assertFalse(hasClass(LOCATOR_TAB3, SELECTED_CLASS));
		assertFalse(hasClass(LOCATOR_TAB4, SELECTED_CLASS));
		assertTrue(hasClass(LOCATOR_TAB5, SELECTED_CLASS));
		assertTrue(hasClass(LOCATOR_TAB1_CONTENT, HIDDEN_CLASS));
		assertTrue(hasClass(LOCATOR_TAB2_CONTENT, HIDDEN_CLASS));
		assertTrue(hasClass(LOCATOR_TAB3_CONTENT, HIDDEN_CLASS));
		assertTrue(hasClass(LOCATOR_TAB4_CONTENT, HIDDEN_CLASS));
		assertFalse(hasClass(LOCATOR_TAB5_CONTENT, HIDDEN_CLASS));

	}

    // TODO: put this in its own package as it will be used in other tests
	public static boolean hasClass(String elXpath, String className) {
		
		String classAttribute = session().getAttribute(elXpath + "@class");
		return ((classAttribute != null) && (classAttribute.contains(className)));
	}

}

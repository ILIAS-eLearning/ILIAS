package com.yahoo.test.SelNG.YUI.library;

import com.yahoo.test.SelNG.framework.core.SelNGBase;
import static com.yahoo.test.SelNG.framework.util.ThreadSafeSeleniumSessionStorage.session;
// import static com.thoughtworks.selenium.*;
import static org.testng.Assert.*;


public class ButtonCheckboxButton extends SelNGBase {

	public static void buttonTest() throws Exception {

	    session().open("http://presentbright.corp.yahoo.com/yui2/latest_build/examples/button/btn_example03_clean.html");
		//assertEquals(session().getTitle(), "");
		
	    //checkInitialSetup();
	    checkFirstRow();
	    
	}
	
	private static void checkInitialSetup() {
	
		// checkbox1 is checked and the others in this row are not
		assertTrue(Util.hasAttribute("checkbutton1", "class", "yui-button-checked"));
		assertFalse(Util.hasAttribute("checkbutton2", "class", "yui-button-checked"));
		assertFalse(Util.hasAttribute("checkbutton3", "class", "yui-button-checked"));
		assertFalse(Util.hasAttribute("checkbutton4", "class", "yui-button-checked"));
		
		// checkbox5 is checked and the others in this row are not
		assertTrue(Util.hasAttribute("checkbutton5", "class", "yui-button-checked"));
		assertFalse(Util.hasAttribute("checkbutton6", "class", "yui-button-checked"));
		assertFalse(Util.hasAttribute("checkbutton7", "class", "yui-button-checked"));
		assertFalse(Util.hasAttribute("checkbutton8", "class", "yui-button-checked"));
		
		// checkbox9 is checked and the others in this row are not
		assertTrue(Util.hasAttribute("checkbutton9", "class", "yui-button-checked"));
		assertFalse(Util.hasAttribute("checkbutton10", "class", "yui-button-checked"));
		assertFalse(Util.hasAttribute("checkbutton11", "class", "yui-button-checked"));
		assertFalse(Util.hasAttribute("checkbutton12", "class", "yui-button-checked"));
		
	}

	private static void checkFirstRow() {

		// check hovering for the non-highlighted buttons
		checkHover("checkbutton2");
		checkHover("checkbutton3");
		checkHover("checkbutton4");

		// click does not work
		session().mouseUp("checkbutton2-button");
		// The first two butttons in this row are highlighted
		assertTrue(Util.hasAttribute("checkbutton1", "class", "yui-button-checked"));
		assertTrue(Util.hasAttribute("checkbutton2", "class", "yui-button-checked"));
		assertFalse(Util.hasAttribute("checkbutton3", "class", "yui-button-checked"));
		assertFalse(Util.hasAttribute("checkbutton4", "class", "yui-button-checked"));
		
		// check hovering for the non-highlighted buttons
		checkHover("checkbutton3");
		checkHover("checkbutton4");

		session().mouseUp("checkbutton3-button");
		// The first three buttons in this row are highlighted
		assertTrue(Util.hasAttribute("checkbutton1", "class", "yui-button-checked"));
		assertTrue(Util.hasAttribute("checkbutton2", "class", "yui-button-checked"));
		assertTrue(Util.hasAttribute("checkbutton3", "class", "yui-button-checked"));
		assertFalse(Util.hasAttribute("checkbutton4", "class", "yui-button-checked"));
		
		// check hovering for the non-highlighted buttons
		checkHover("checkbutton4");

		session().mouseUp("checkbutton4-button");
		// All buttons in this row are highlighted
		assertTrue(Util.hasAttribute("checkbutton1", "class", "yui-button-checked"));
		assertTrue(Util.hasAttribute("checkbutton2", "class", "yui-button-checked"));
		assertTrue(Util.hasAttribute("checkbutton3", "class", "yui-button-checked"));
		assertTrue(Util.hasAttribute("checkbutton4", "class", "yui-button-checked"));
		
		// a mouse up on a selected button will un-highlight it but we need to leave it focused like a real "click" would do
		session().mouseDown("checkbutton4-button");
		session().mouseUp("checkbutton4-button");
		//session().focus("checkbutton4-button");
		// First three buttons are highlighted, last button is focused
		assertTrue(Util.hasAttribute("checkbutton1", "class", "yui-button-checked"));
		assertTrue(Util.hasAttribute("checkbutton2", "class", "yui-button-checked"));
		assertTrue(Util.hasAttribute("checkbutton3", "class", "yui-button-checked"));
		assertTrue(Util.hasAttribute("checkbutton4", "class", "yui-button-focus"));

		// "click" button3
		//session().mouseUp("checkbutton4-button");
		//session().mouseUp("checkbutton4-button");
		session().mouseDown("checkbutton3-button");
		session().mouseUp("checkbutton3-button");
		session().focus("checkbutton3-button");
		// First two buttons are highlighted, button3 is focused
		assertTrue(Util.hasAttribute("checkbutton1", "class", "yui-button-checked"));
		assertTrue(Util.hasAttribute("checkbutton2", "class", "yui-button-checked"));
		assertFalse(Util.hasAttribute("checkbutton3", "class", "yui-button-focus"));
		assertTrue(Util.hasAttribute("checkbutton4", "class", "yui-button-focus"));
		
		/*****
		session().mouseUp("checkbutton4-button");
		session().focus("checkbutton4-button");
		// First three buttons are highlighted, last button is focused
		assertTrue(Util.hasAttribute("checkbutton1", "class", "yui-button-checked"));
		assertTrue(Util.hasAttribute("checkbutton2", "class", "yui-button-checked"));
		assertTrue(Util.hasAttribute("checkbutton3", "class", "yui-button-checked"));
		assertTrue(Util.hasAttribute("checkbutton4", "class", "yui-button-focus"));
		
		session().mouseUp("checkbutton4-button");
		session().focus("checkbutton4-button");
		// First three buttons are highlighted, last button is focused
		assertTrue(Util.hasAttribute("checkbutton1", "class", "yui-button-checked"));
		assertTrue(Util.hasAttribute("checkbutton2", "class", "yui-button-checked"));
		assertTrue(Util.hasAttribute("checkbutton3", "class", "yui-button-checked"));
		assertTrue(Util.hasAttribute("checkbutton4", "class", "yui-button-focus"));
		****/
		
	}
	
	private static void checkHover(String id) {

		// When hovering over this element, does it have the "hover" class ?
		session().mouseOver(id);
		assertTrue(Util.hasAttribute(id, "class", "yui-button-hover"));
		session().mouseOut(id);
		
	}


}

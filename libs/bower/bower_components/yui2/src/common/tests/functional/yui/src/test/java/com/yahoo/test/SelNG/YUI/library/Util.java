package com.yahoo.test.SelNG.YUI.library;

import static com.yahoo.test.SelNG.framework.util.ThreadSafeSeleniumSessionStorage.session;

public class Util {
	
	/**
	 * Checks if an 'elXpath' has an 'attributeValue' of attributeName'
	 * 
	 * IE returns attribute names in upper case, thus the toLowerCase()
	 * 
	 */
	public static boolean hasAttribute(String elXpath, String attributeName, String attributeValue) {
		
		String attribute = session().getAttribute(elXpath + "@" + attributeName).toLowerCase();
		return ((attribute != null) && (attribute.contains(attributeValue.toLowerCase())));
		
	}
	
	public static boolean hasClass(String elXpath, String className) {
		
		String classAttribute = session().getAttribute(elXpath + "@class");
		return ((classAttribute != null) && (classAttribute.contains(className)));
	}


	/**
	 * The raw Java parseInt() will not handle a leading plus sign.
	 * 
	 * @param s String
	 * @return the int that the String
	 */
	public static int parseInt(String s) {
		
		if ( s.charAt(0) == '+') { 
			s = s.substring(1);
		}
		return Integer.parseInt(s);
	}

	/**
	 * 
	 * @return
	 */
	public static int getViewportHeight() {
		
		String js = 
			" var w = this.browserbot.getCurrentWindow(); " +
			" var myHeight = 0; " + 
		        " if( typeof( w.innerHeight ) == 'number' ) { " +
			/*   //Non-IE    */
			"    myHeight = w.innerHeight; " + 
			" } else if( w.document.documentElement && w.document.documentElement.clientHeight ) { " + 
			/*   //IE 6+ in 'standards compliant mode'  */
			"    myHeight = w.document.documentElement.clientHeight; " +
			" } else if( w.document.body && w.document.body.clientHeight ) { " +
			/*    //IE 4 compatible  */
			"    myHeight = w.document.body.clientHeight; " +
			" } " +
			" myHeight; ";
		
			//String xx = session().getEval(js);
			return Integer.parseInt(session().getEval(js));
			
			//return Integer.parseInt(session().getEval("this.browserbot.getCurrentWindow().document.documentElement.clientHeight"));
	}

	/**
	 * 
	 * @return
	 */
	public static int getViewportWidth() {

		String js = 
			" var w = this.browserbot.getCurrentWindow(); " +
			" var myWidth = 0; " + 
		        " if( typeof( w.innerWidth ) == 'number' ) { " +
			/*   //Non-IE    */
			"    myWidth = w.innerWidth; " + 
			" } else if( w.document.documentElement && w.document.documentElement.clientWidth ) { " + 
			/*   //IE 6+ in 'standards compliant mode'  */
			"    myWidth = w.document.documentElement.clientWidth; " +
			" } else if( w.document.body && w.document.body.clientWidth ) { " +
			/*    //IE 4 compatible  */
			"    myWidth = w.document.body.clientWidth; " +
			" } " +
			" myWidth; ";
		
		return Integer.parseInt(session().getEval(js));		
		
		//return Integer.parseInt(session().getEval("this.browserbot.getCurrentWindow().document.documentElement.clientWidth"));
	}

	/**
	 * 
	 * 
	 */
	public static void setViewportHeight(int h) {
	
		String js = 
			" var w = this.browserbot.getCurrentWindow(); " +
			" var myHeight = 0; " + 
		    " if( typeof( w.innerHeight ) == 'number' ) { " +
			"   w.innerWidth = " + h + ";" +
			" } else if( w.document.documentElement && w.document.documentElement.clientHeight ) { " + 
			"   w.document.documentElement.clientHeight = " + h + ";" +
			" } else if( w.document.body && w.document.body.clientHeight ) { " +
			"   w.document.body.clientHeight = " + h + ";" +
			" } "; 

		session().getEval(js);
		
	}
	
	/**
	 * 
	 * 
	 */
	public static void setViewportWidth(int w) {
	
		String js = 
			" var w = this.browserbot.getCurrentWindow(); " +
			" var myWidth = 0; " + 
		    " if( typeof( w.innerWidth ) == 'number' ) { " +
			"   w.innerWidth = " + w + ";" +
			" } else if( w.document.documentElement && w.document.documentElement.clientWidth ) { " + 
			"   w.document.documentElement.clientWidth = " + w + ";" +
			" } else if( w.document.body && w.document.body.clientWidth ) { " +
			"   w.document.body.clientWidth = " + w + ";" +
			" } "; 

		session().getEval(js);
		
	}
	
	/**
	 * 
	 * 
	 */
	public static int getScrollX() {
		
		String js =
			" var w = this.browserbot.getCurrentWindow(); " +
			" var scrOfX = 0; " +
			" if( typeof( w.pageXOffset ) == 'number' ) { " +
			"   scrOfX = w.pageXOffset; " +
			" } else if( w.document.body && w.document.body.scrollLeft ) { " +
			"   scrOfX = w.document.body.scrollLeft; " +
			" } else if( w.document.documentElement && w.document.documentElement.scrollLeft ) { " +
			"   scrOfX = w.document.documentElement.scrollLeft; " +
			" } " +
			" scrOfX; ";
		
		return Integer.parseInt(session().getEval(js));
		
	}
	
	/**
	 * 
	 * 
	 */
	public static int getScrollY() {

		String js =
			" var w = this.browserbot.getCurrentWindow(); " +
			" var scrOfY = 0; " +
			" if( typeof( w.pageYOffset ) == 'number' ) { " +
			"   scrOfY = w.pageYOffset; " +
			" } else if( w.document.body && w.document.body.scrollTop ) { " +
			"   scrOfY = w.document.body.scrollTop; " +
			" } else if( w.document.documentElement && w.document.documentElement.scrollTop ) { " +
			"   scrOfY = w.document.documentElement.scrollTop; " +
			" } " +
			" scrOfY; ";
			
		return Integer.parseInt(session().getEval(js));

	}
	
	/**
	 * 
	 * 
	 * 
	 */
	public static void scrollWindow(int x, int y) {

		String js = 
			" var w = this.browserbot.getCurrentWindow(); " +
			" if( typeof( w.pageYOffset ) == 'number' ) { " +
			"   w.pageYOffset = " + y + ";" +
			"   w.pageXOffset = " + x + ";" +
			" } else if( w.document.body && ( w.document.body.scrollLeft || w.document.body.scrollTop ) ) { " +
			"   w.document.body.scrollTop = " + y + ";" +
			"   w.document.body.scrollLeft = " + x + ";" +
			" } else if( w.document.documentElement && ( w.document.documentElement.scrollLeft || w.document.documentElement.scrollTop ) ) { " +
			"   w.document.documentElement.scrollTop = " + y + ";" +
			"   w.document.documentElement.scrollLeft = " + x + ";" +
			" } ";

		session().getEval(js);
		
	}
	
	/**
	 * 
	 * 
	 * 
	 */
	public static boolean isIE6() {
		
		return false;
		
	}
	
	/******
	 * function getScrollXY() {
  var scrOfX = 0, scrOfY = 0;
  if( typeof( window.pageYOffset ) == 'number' ) {
    //Netscape compliant
    scrOfY = window.pageYOffset;
    scrOfX = window.pageXOffset;
  } else if( document.body && ( document.body.scrollLeft || document.body.scrollTop ) ) {
    //DOM compliant
    scrOfY = document.body.scrollTop;
    scrOfX = document.body.scrollLeft;
  } else if( document.documentElement && ( document.documentElement.scrollLeft || document.documentElement.scrollTop ) ) {
    //IE6 standards compliant mode
    scrOfY = document.documentElement.scrollTop;
    scrOfX = document.documentElement.scrollLeft;
  }
  return [ scrOfX, scrOfY ];
}
	 */
	

}

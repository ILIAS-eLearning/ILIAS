package com.yahoo.test.SelNG.YUI.library;

import com.yahoo.test.SelNG.framework.core.SelNGBase;
import static com.yahoo.test.SelNG.framework.util.ThreadSafeSeleniumSessionStorage.session;
import static org.testng.Assert.assertFalse;
import static org.testng.Assert.assertTrue;
import static com.yahoo.test.SelNG.YUI.library.Util.*;

import java.util.*;

import org.w3c.dom.*;

import javax.xml.xpath.*;


public class TabView extends SelNGBase {
	
	private static final String SELECTED_CLASS = "selected";
	private static final String HIDDEN_CLASS = "yui-hidden";


	@SuppressWarnings("unchecked")
	public static void tabTest(String uri, Document doc) throws Exception {
		
		ArrayList theTabClickLocators = new ArrayList();
		ArrayList theTabLiLocators = new ArrayList();
		ArrayList theContentLocators = new ArrayList();
		ArrayList theSelecteds = new ArrayList();
		
		session().open(uri);

		XPathFactory factory = XPathFactory.newInstance();
	    XPath xpath = factory.newXPath();
	    XPathExpression containerExpr = xpath.compile("//container");
	    Object containerResult = containerExpr.evaluate(doc, XPathConstants.NODESET);
	    NodeList containerNodes = (NodeList) containerResult;
	    //int numberOfContainers = containerNodes.getLength();
	    String containerLocator = "";
	    for (int i=0; i<containerNodes.getLength(); i++) {
	    	NamedNodeMap containerAttributes = containerNodes.item(i).getAttributes();
	    	//int numberOfContainerAttributes = containerAttributes.getLength();
	    	containerLocator = containerAttributes.getNamedItem("locator").getNodeValue();
	    	int ii = i+1;
	    	XPathExpression tabExpr = xpath.compile("//container["+ii+"]/tab");
	    	Object tabResult = tabExpr.evaluate(doc, XPathConstants.NODESET);
	    	NodeList tabNodes = (NodeList) tabResult;
	    	//int numberOfTabs = tabNodes.getLength();
	    	for (int k=0; k<tabNodes.getLength(); k++) {
		    	NamedNodeMap tabAttributes = tabNodes.item(k).getAttributes();
		    	//int numberOfTabAttributes = tabAttributes.getLength();
		    	String tabClickLocator = tabAttributes.getNamedItem("tabClickLocator").getNodeValue();
		    	theTabClickLocators.add(k, tabClickLocator);
		    	String tabLiLocator = tabAttributes.getNamedItem("tabLiLocator").getNodeValue();
		    	theTabLiLocators.add(k, tabLiLocator);
		    	String contentLocator = tabAttributes.getNamedItem("contentLocator").getNodeValue();
		    	theContentLocators.add(k, contentLocator);
		    	String selected = tabAttributes.getNamedItem("selected").getNodeValue();
		    	theSelecteds.add(k, selected);
	    	}

		    testContainer(containerLocator, theTabClickLocators, theTabLiLocators, theContentLocators, theSelecteds);
		    theTabClickLocators.clear();
		    theTabLiLocators.clear();
		    theContentLocators.clear();
		    theSelecteds.clear();
		    
	    }

	}

	private static void testContainer(String containerLocator, ArrayList theTabClickLocators, ArrayList theTabLiLocators, ArrayList theContentLocators, ArrayList theSelecteds) {

		int numberOfTabs = theTabClickLocators.size();
		
		for (int i=0; i<numberOfTabs; i++) {

			// Click on tab 
			String s = (String)theTabClickLocators.get(i);
			session().click(s);
			//session().click((String)theTabClickLocators.get(i));

			// Check for correct classes
			for (int k=0; k<numberOfTabs; k++) {
				if (i == k) {
					assertTrue(hasClass((String)theTabLiLocators.get(k), SELECTED_CLASS));
					assertFalse(hasClass((String)theContentLocators.get(k), HIDDEN_CLASS));
				} else {
					assertFalse(hasClass((String)theTabLiLocators.get(k), SELECTED_CLASS));
					assertTrue(hasClass((String)theContentLocators.get(k), HIDDEN_CLASS));
					
				}
				
			}
		}

	}


}

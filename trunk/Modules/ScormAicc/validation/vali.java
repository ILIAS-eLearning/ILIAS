import javax.xml.parsers.*;
import org.xml.sax.*;
import org.w3c.dom.*;
import java.io.*;

// Author: Romeo Kienzler, contact@kienzler.biz , 21 LearnLine AG

public class vali {

	public static void main(String args[]) {
		try {
			DocumentBuilderFactory factory = DocumentBuilderFactory.newInstance();
			factory.setNamespaceAware(true);
			factory.setValidating(true);
			DocumentBuilder builder = factory.newDocumentBuilder();
			Document doc = builder.parse(args[0]);
		}
		catch (SAXException e) {
			//e.printStackTrace();
			System.out.println(e.getMessage());
		}
		catch (Exception e) {
			//e.printStackTrace();
		}
	}
}

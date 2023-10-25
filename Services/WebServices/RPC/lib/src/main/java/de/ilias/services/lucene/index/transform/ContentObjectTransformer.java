
package de.ilias.services.lucene.index.transform;

import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;
import org.xml.sax.InputSource;
import org.xml.sax.SAXException;
import org.xml.sax.XMLReader;

import javax.xml.parsers.ParserConfigurationException;
import javax.xml.parsers.SAXParserFactory;
import java.io.IOException;
import java.io.StringReader;

/**
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
public class ContentObjectTransformer implements ContentTransformer {

	protected Logger logger = LogManager.getLogger(ContentObjectTransformer.class);

	/**
	 * Extract text from page_objects
	 * @see de.ilias.services.lucene.index.transform.ContentTransformer#transform(java.lang.String)
	 */
	public String transform(String content) {

		XMLReader reader;
		PageObjectHandler handler;
		StringReader stringReader = new StringReader(content);
		
		try {
			reader = SAXParserFactory.newInstance().newSAXParser().getXMLReader();
			handler = new PageObjectHandler();
			
			reader.setContentHandler(handler);
			reader.parse(new InputSource(stringReader));
			
			return handler.getContent();
			
		} 
		catch (SAXException e) {
			logger.warn("Cannot parse page_object content." + e);
		} catch (IOException e) {
			logger.warn("Found invalid content." + e);
		} catch (ParserConfigurationException e) {
			logger.error("Creating XMLReader failed: " + e);
        }
        return "";
	}


}

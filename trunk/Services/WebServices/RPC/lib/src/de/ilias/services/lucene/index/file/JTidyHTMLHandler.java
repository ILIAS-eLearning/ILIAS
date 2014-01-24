/*
        +-----------------------------------------------------------------------------+
        | ILIAS open source                                                           |
        +-----------------------------------------------------------------------------+
        | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
        |                                                                             |
        | This program is free software; you can redistribute it and/or               |
        | modify it under the terms of the GNU General Public License                 |
        | as published by the Free Software Foundation; either version 2              |
        | of the License, or (at your option) any later version.                      |
        |                                                                             |
        | This program is distributed in the hope that it will be useful,             |
        | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
        | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
        | GNU General Public License for more details.                                |
        |                                                                             |
        | You should have received a copy of the GNU General Public License           |
        | along with this program; if not, write to the Free Software                 |
        | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
        +-----------------------------------------------------------------------------+
 */

package de.ilias.services.lucene.index.file;

import java.io.ByteArrayOutputStream;
import java.io.IOException;
import java.io.InputStream;
import java.io.PrintWriter;
import java.io.StringWriter;

import org.apache.log4j.Logger;
import org.w3c.dom.Element;
import org.w3c.dom.Node;
import org.w3c.dom.NodeList;
import org.w3c.dom.Text;
import org.w3c.tidy.Tidy;

/**
 * 
 * 
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
public class JTidyHTMLHandler implements FileHandler {

	protected Logger logger = Logger.getLogger(JTidyHTMLHandler.class);

	private Tidy tidy;

	/**
	 * @see de.ilias.services.lucene.index.file.FileHandler#getContent(java.io.InputStream)
	 */
	public String getContent(InputStream is) throws FileHandlerException,
			IOException {

		StringBuilder builder = new StringBuilder();
		ByteArrayOutputStream bout = new ByteArrayOutputStream();

		tidy = new Tidy();
		tidy.setErrout(new PrintWriter(new ByteArrayOutputStream()));
		tidy.setQuiet(true);
		tidy.setShowWarnings(false);

		org.w3c.dom.Document root = tidy.parseDOM(is, bout);
		Element rawDoc = root.getDocumentElement();

		String title = getTitle(rawDoc);
		String body = getBody(rawDoc);

		if (title != null && !title.equals("")) {
			builder.append(title);
		}
		if (body != null && !body.equals("")) {
			builder.append(body);
		}
		
		if(bout != null) {
			try {
				bout.close();
			}
			catch(IOException e) {
				// Nothing
			}
		}
		
		
		return builder.toString();

	}

	/**
	 * @see de.ilias.services.lucene.index.file.FileHandler#transformStream(java.io.InputStream)
	 */
	public InputStream transformStream(InputStream is) {

		return null;
	}

	private String getTitle(Element rawDoc) {
		if (rawDoc == null) {
			return null;
		}

		String title = "";
		NodeList children = rawDoc.getElementsByTagName("title");
		if (children.getLength() > 0) {
			Element titleElement = (Element) children.item(0);
			Text text = (Text) titleElement.getFirstChild();
			if (text != null) {
				title = text.getData();
			}
		}
		return title;
	}

	private String getBody(Element rawDoc) {
		if (rawDoc == null) {
			return null;
		}
		String body = "";
		NodeList children = rawDoc.getElementsByTagName("body");
		if (children.getLength() > 0) {
			body = getText(children.item(0));
		}
		return body;
	}

	private String getText(Node node) {
		NodeList children = node.getChildNodes();
		StringBuilder sb = new StringBuilder();

		for (int i = 0; i < children.getLength(); i++) {
			Node child = children.item(i);
			switch (child.getNodeType()) {
			case Node.ELEMENT_NODE:
				sb.append(getText(child));
				sb.append(" ");
				break;
			case Node.TEXT_NODE:
				sb.append(((Text) child).getData());
				break;
			}
		}
		return sb.toString();
	}
}

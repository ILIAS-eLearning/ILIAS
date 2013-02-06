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

package de.ilias.services.lucene.index.transform;

import java.util.HashMap;

import javax.xml.transform.Transformer;

import org.apache.log4j.Logger;

/**
 * A caching transformer factory
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
public class TransformerFactory {

	protected static Logger logger = Logger.getLogger(Transformer.class);
	
	private static HashMap<String, ContentTransformer> map = new HashMap<String, ContentTransformer>();
	
	public static ContentTransformer factory(String name) {
		
		if(map.containsKey(name))
			return map.get(name);
		
		if(name.equalsIgnoreCase("QuotingSanitizer")) {
			map.put(name,new QuotingSanitizer());
			return map.get(name);
		}
		if(name.equalsIgnoreCase("ContentObjectTransformer")) {
			map.put(name, new ContentObjectTransformer());
			return map.get(name);
		}
		if(name.equalsIgnoreCase("LinefeedSanitizer")) {
			map.put(name, new LinefeedSanitizer());
			return map.get(name);
		}
		if(name.equalsIgnoreCase("WhitespaceSanitizer")) {
			map.put(name, new WhitespaceSanitizer());
			return map.get(name);
		}
		if(name.equalsIgnoreCase("FilenameExtractor")) {
			map.put(name, new FilnameExtractor());
			return map.get(name);
		}
		if(name.equalsIgnoreCase("TagSanitizer")) {
			map.put(name, new TagSanitizer());
			return map.get(name);
		}
		if(name.equalsIgnoreCase("MimeTypeExtractor")) {
			map.put(name, new MimeTypeExtractor());
			return map.get(name);
		}
		
		logger.error("Cannot find transformer with name: " + name);
		return null;
	}
	
}

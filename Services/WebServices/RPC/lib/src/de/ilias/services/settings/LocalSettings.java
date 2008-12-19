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

package de.ilias.services.settings;

import org.apache.log4j.Logger;

/**
 * Class that stores thread local settings
 * E.g the clientKey
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
public class LocalSettings {

	protected static Logger logger = Logger.getLogger(LocalSettings.class);

	private static ThreadLocal<String> clientKey = new ThreadLocal<String>() {
		public String initialValue() {
			return "";
		}
	};
	

	/**
	 * 
	 */
	public LocalSettings() {
	}

	/**
	 * 
	 * @param ck
	 */
	public static void setClientKey(String ck) {
		clientKey.set(ck);
	}
	
	/**
	 * 
	 * @return
	 */
	public static String getClientKey() {
		return (String) clientKey.get();
	}
}
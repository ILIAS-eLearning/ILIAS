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

package de.ilias.services.lucene.index.file.path;

import java.util.Vector;

import org.apache.log4j.Logger;

/**
 * 
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
public class PathUtils {
	
	protected static final int FACTOR = 100;
	protected static final int MAX_EXPONENT = 3;
	
	protected static Logger logger = Logger.getLogger(PathUtils.class);
	
	
	/**
	 * 
	 * @param objId
	 * @return
	 */
	public static String buildSplittedPathFromId(int objId) {
		
		boolean found	= false;
		int num		= objId;
		Vector<String>	path	= new Vector<String>();
		StringBuilder pathString = new StringBuilder();
		
		for(int i = PathUtils.MAX_EXPONENT; i > 0; i--) {
			
			int tmp = 0;
			int factor = (int) Math.pow(PathUtils.FACTOR, i);
			
			if(((tmp = (num / factor)) != 0) || found) {
				
				path.add(String.valueOf(tmp));
				num = num % factor;
				found = true;
			}
		}
		
		for(int i = 0; i < path.size(); i++) {
			
			pathString.append(path.get(i));
			pathString.append(System.getProperty("file.separator"));
		}
		
		return pathString.toString();
	}
	
	/**
	 * 
	 * @param objId
	 * @param name
	 * @return
	 */
	public static String buildSplittedPathFromId(int objId, String name) {
		
		StringBuilder fullPath = new StringBuilder();
		
		fullPath.append(PathUtils.buildSplittedPathFromId(objId));
		if(fullPath.length() == 0) {
			return "";
		}
		fullPath.append(name);
		fullPath.append('_');
		fullPath.append(String.valueOf(objId));
		fullPath.append(System.getProperty("file.separator"));
		
		return fullPath.toString();
	}

	/**
	 * Build directory name of version dir according for ILIAS file versions
	 * @param version
	 * @return
	 */
	public static String buildVersionDirectory(int version) {
		
		StringBuilder directoryName = new StringBuilder();
		
		if(version < 10) {
			directoryName.append("00");
			directoryName.append(String.valueOf(version));
			return directoryName.toString();
		}
		else if(version < 100) {
			directoryName.append("0");
			directoryName.append(String.valueOf(version));
			return directoryName.toString();
			
		}
		else
		{
			directoryName.append(String.valueOf(version));
			return directoryName.toString();
		}
	}

}

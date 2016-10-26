<?php
// BEGIN WebDAV
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2005 ILIAS open source, University of Cologne            |
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

require_once "class.ilObjectDAV.php";
require_once "Modules/Course/classes/class.ilObjCourse.php";

/**
* Class ilObjCourseDAV
*
* Handles DAV requests on a course object.
*
* @author Werner Randelshofer, Hochschule Luzern, werner.randelshofer@hslu.ch
* @version $Id$
*
* @package webdav
*/
class ilObjCourseDAV extends ilObjectDAV
{
	/** 
	* Constructor
	*
	* @param refid A refid to the object.
	*/
	function __construct($refid) 
	{
		parent::__construct($refid);
	}
	
	/**
	 * Returns the DAV resource type of this object.
	 * 
         * @return String "collection" or "".
	 */
	function getResourceType()
	{
		return "collection";
	}

	/**
	 * Returns the mime type of the content of this object.
         * @return String.
	 */
	function getContentType()
	{
		return 'httpd/unix-directory';
	}
	/**
	 * Returns the number of bytes of the content.
         * @return int.
	 */
	function getContentLength()
	{
		return 0;
	}
	
	/**
	 * Returns true if the object is online.
	 */
	function isOnline()
	{
		return ! $this->obj->getOfflineStatus();
	}
	
	/**
	 * Reads the object data.
         * @return void.
	 */
	function read()
	{
		if (is_null($this->obj))
		{
			$this->obj = new ilObjCourse($this->getRefId(),true);
			$this->obj->read();
		}
	}
}
// END WebDAV
?>

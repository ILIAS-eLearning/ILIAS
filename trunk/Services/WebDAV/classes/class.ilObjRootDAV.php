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
require_once "Modules/Category/classes/class.ilObjCategory.php";

/**
* Class ilObjRootDAV
*
* Represents the root node of the ILIAS Repository.
*
* @author Werner Randelshofer, Hochschule Luzern, werner.randelshofer@hslu.ch
* @version $Id: class.ilDAVServer.php,v 1.0 2005/07/08 12:00:00 wrandelshofer Exp $
*
* @package webdav
*/
class ilObjRootDAV extends ilObjectDAV
{
	var $data;
	/** 
	* Constructor
	*
	* @param refid A refid to the object.
	*/
	function ilObjRootDAV($refid) 
	{
		$this->ilObjectDAV($refid);
	}
	
	/**
	 * Returns the object id of this object.
	 * Precondition: Object must have been read.
         * @return int.
	 */
	function getObjectId()
	{
		return $this->data['obj_id'];
	}
	/**
	 * Returns the file name of this object.
	 * Precondition: Object must have been read.
         * @return String.
	 */
	function getResourceName()
	{
		//return '';
		return $this->data['title'];
	}
	/**
	 * Returns the file name of this object.
	 * Precondition: Object must have been read.
         * @return String.
	 */
	function getDisplayName()
	{
		//return 'ILIAS';
		return $this->data['title'];
	}
	/**
	 * Returns the creation timestamp of this object.
	 * Precondition: Object must have been read.
         * @return int Unix timestamp.
	 */
	function getCreationTimestamp()
	{
		return strtotime($this->data['create_date']);
	}
	
	/**
	 * Returns the modification timestamp of this object.
	 * Precondition: Object must have been read.
         * @return int Unix timestamp.
	 */
	function getModificationTimestamp()
	{
		return strtotime($this->data['last_update']);
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
	 * Returns 'cat' as the ilias object type for collections that can be
     * created as children of this object.
	 */
	function getILIASCollectionType()
	{
		return 'cat';
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
	 * Reads the object data.
         * @return void.
	 */
	function read()
	{
		global $tree;
		$this->data = $tree->getNodeData($this->getRefId());
	}
}
// END WebDAV
?>

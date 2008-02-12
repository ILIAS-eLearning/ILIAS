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
* Class ilObjMountPointDAV
*
* Represents the top level mount point of webfolders.
*
* This object, represents the parent of the root node of the ILIAS repository.
*
* @author Werner Randelshofer, Hochschule Luzern, werner.randelshofer@hslu.ch
* @version $Id: class.ilDAVServer.php,v 1.0 2007/10/27 12:00:00 wrandelshofer Exp $
*
* @package webdav
*/
class ilObjMountPointDAV extends ilObjectDAV
{
	var $data;
	/** 
	* Constructor
	*
	* @param refid A refid to the object.
	*/
	function ilObjMountPointDAV() 
	{
		$this->ilObjectDAV(-1);
	}
	
	/**
	 * Returns the object id of this object.
     * This method returns -1, because there is actually no parent of the root
     * node.
	 */
	function getObjectId()
	{
		return -1;
	}
	/**
	 * Reads the object data.
     * This method does nothing, because there is actually no parent of the root
     * node.
     * @return void.
	 */
	function read()
	{
	}
	/**
	 * Writes the object data.
     * This method does nothing, because there is actually no parent of the root
     * node.
     * @return void.
	 */
	function write()
	{
	}
	/**
	 * Returns the file name of this object.
	 * Precondition: Object must have been read.
     * @return String.
	 */
	function getResourceName()
	{
		return '';
	}
	/**
	 * Returns the file name of this object.
	 * Precondition: Object must have been read.
     * @return String.
	 */
	function getDisplayName()
	{
		return '';
	}
	/**
	 * Returns the creation timestamp of this object.
	 * Precondition: Object must have been read.
     * @return int Unix timestamp.
	 */
	function getCreationTimestamp()
	{
		return strtotime('2000-01-01');
	}
	
	/**
	 * Returns the modification timestamp of this object.
	 * Precondition: Object must have been read.
         * @return int Unix timestamp.
	 */
	function getModificationTimestamp()
	{
		return strtotime('2000-01-01');
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
    function isPermitted($actions,$type='')
    {
		// All users have "visible" and "read" access to the mount point
		$a = explode(',',$actions);
		foreach ($a as $action)
		{
			switch ($action)
			{
				case 'read' :
				case 'visible' :
					break;
				default :
					return false;
			}
		}
		return true;
    }
	/**
	 * Returns the children of this object.
	 *
     * @return Array<ilObjectDAV>. Returns an empty array, if this object is not
	 * a collection..
	 */
	function children()
	{
		global $tree;
		
		$childrenDAV = array();
		$data =& $tree->getNodeData($tree->getRootId());
		$childDAV =& $this->createObject($data['ref_id'],'root');
		if (! is_null($childDAV))
		{
			// Note: We must not assign with =& here, because this will cause trouble
			//       when other functions attempt to work with the $childrenDAV array.
			$childrenDAV[] = $childDAV;
		}
		return $childrenDAV;
	}
}
// END WebDAV
?>

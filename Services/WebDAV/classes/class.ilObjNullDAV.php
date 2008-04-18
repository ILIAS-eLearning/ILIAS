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
require_once "class.ilObjNull.php";

/**
* Class ilObjNullDAV
*
* Null Resources are used by WebDAV clients, to lock a resource name in the repository.
*
* Cited from RFC 2518, chapter 3 "Terminology":
* "Null Resource - A resource which responds with 404 (Not Found) to any HTTP/1.1 or
* DAV method except for PUT, MKCOL, OPTIONS and LOCK. A Null Resource MUST NOT appear
* as a member of its parent collection."
*
* Cited from RFC 2518, chapter 7.4 "Write Locks and Null Resources":
* "It is possible to assert a write lock on a null resource in order to lock the name.
* A write locked null resource, referred to as a lock-null resource, MUST respond with
* a 404 (Not Found) or 405 (Method Not Allowed) to any HTTP71.1 or DAV methods except
* for PUT, MKCOL, OPTIONS, PROPFIND, LOCK and UNLOCK. A lock-null resource MUST appear
* as a member of its parent collection. Additionally the lock-null resource MUST have
* defined on it all mandatory DAV properties. Most of these properties such as all
* the get properties, will have no value as a lock-null resource does not support the
* GET method. Lock-Null resources MUST have defined values for lockdiscovery and
* supportedlock properties.
* Until a method such as PUT or MKCOL is successfully executed on the lock-null resource
* the resource MUST stay in the lock-null state. However, once a PUT or MKCOL is
* successfully executed on a lock-null resouce the resource ceases to be in the lock-null
* state.
* If the resource is unlocked, for any reason, without a PUT, MKCOL, or similar method
* having been successfully executed upon it then the resource MUST return to the null state."
*
* Null-resources are used by class.ilDAVSerrev.php for the following use cases:
* 
* Use case 1: Creating a new file resource
* 1. Client sends a PROPFIND request on the name of the resource
* 2. Server returns 404 (Not Found)
* 3. Client sends a LOCK request on the name of the resource.
* 4. Server creates a Null Resource and a Lock on the name.
* 5. Client sends a PUT request on the name and passes the resource content along
* 6. Server converts the Null Resource into a File resource and stores the resource content
* 7. Client sends an UNLOCK request on the name of the resource
* 8. Server deletes the Lock.
*
* Use case 2: Creating a new collection resource
* 1. Client sends a PROPFIND request on the name of the resource
* 2. Server returns 404 (Not Found)
* 3. Client sends a LOCK request on the name of the resource.
* 4. Server creates a Null Resource and a Lock on the name.
* 5. Client sends a MKCOL request on the name
* 6. Server converts the Null Resource into a Collection resource and stores the resource content
* 7. Client sends an UNLOCK request on the name of the resource
* 8. Server deletes the Lock.
*
* Use case 3: Locking and Unlocking a null resource
* 1. Client sends a LOCK request on the name of the resource for some reason.
* 2. Server creates a Null Resource and a Lock on the name.
* 3. (Some time later) Client sends a UNLOCK request on the name of the resource
* 4. Server deletes the Null Resource
* 5. Server deletes the Lock.
*
* Use case 4: Locking and timeout on lock on a null resource
* 1. Client sends a LOCK request on the name of the resource for some reason.
* 2. Server creates a Null Resource and a Lock on the name.
* 3. (Some time later) the lock times out.
* 4. Server deletes the Null Resource
* 5. Server deletes the Lock.
*
* @author Werner Randelshofer, Hochschule Luzern, werner.randelshofer@hslu.ch
* @version $Id: class.ilDAVServer.php,v 1.0 2005/07/08 12:00:00 wrandelshofer Exp $
*
* @package webdav
*/
class ilObjNullDAV extends ilObjectDAV
{
	/** 
	* Constructor
	*
	* @param refid A refid to the object.
	*/
	function ilObjNullDAV($refid, $obj = null) 
	{
		$this->ilObjectDAV($refid, $obj);
	}
	
	/**
	 * Returns the DAV resource type of this object.
	 * 
         * @return String "collection", "" (file) or "null".
	 */
	function getResourceType()
	{
		return 'null';
	}

	/**
	 * Returns the mime type of the content of this object.
         * @return String.
	 */
	function getContentType()
	{
		return 'application/x-non-readable';
	}
	/**
	 * Reads the object data.
         * @return void.
	 */
	function read()
	{
		if (is_null($this->obj))
		{
			$this->obj = &new ilObjNull($this->getRefId(),true);
			$this->obj->read();
		}
	}
	
	/**
	 * Converts this object to the specified ILIAS type.
	 *
	 * @param refid of the parent object
	 * @param ILIAS type
	 */
	function convertToILIASType($refId, $type)
	{
		$this->obj->setType($type);
		$this->write();
		$this->obj->setPermissions($refId);
		$this->writelog('convertToILIASType '.$type.' obj='.$this->getObjectId());
		$converted =& $this->createObject($this->getRefId(), $type);
		$converted->obj->createProperties();
		return $converted;
	}
}
// END WebDAV
?>

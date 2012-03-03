<?php
// BEGIN WebDAV: Null Object.
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


/**
* Class ilObjNull
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
* @version $Id: class.ilObjNull.php,v 1.0 2005/01/31 10:01:10 wrandels Exp $
*
* @extends ilObject
* @package ilias-core
*/

require_once "./Services/Object/classes/class.ilObject.php";

class ilObjNull extends ilObject
{
	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function ilObjNull($a_id = 0,$a_call_by_reference = true)
	{
		$this->type = "null";
		$this->ilObject($a_id,$a_call_by_reference);
	}
	/**
	* update object in db
	* 
	* Note: This is mostly the same as method update in class.ilObject.php.
	*       In addition to updating descriptional properties, we also update the
	*       "type" property. This is needed, because a Null Resource can be converted
	*       into another resource type using a PUT or a MKCOL request by a WebDAV client.
	*
	* @access	public
	* @return	boolean	true on success
	*/
	function update()
	{
		parent::update();

		$q = "UPDATE object_data"
			." SET"
			." type = '".ilUtil::prepareDBString($this->getType())."'"
			." WHERE obj_id = '".$this->getId()."'";
		$this->ilias->db->query($q);

		return true;
	}

} // END class.ilObjNull
// END WebDAV: Null Object.
?>

<?php
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
* Class ilObjRootFolder
*
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$Id: class.ilObjRootFolder.php,v 1.7 2003/07/11 13:23:05 shofmann Exp $
* 
* @extends ilObject
* @package ilias-core
*/

require_once "class.ilObject.php";

class ilObjRootFolder extends ilObject
{
	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function ilObjRootFolder($a_id,$a_call_by_reference = true)
	{
		$this->type = "root";
		$this->ilObject($a_id,$a_call_by_reference);
	}


	/**
	* copy all properties and subobjects of a rootfolder.
	* DISABLED
	* @access	public
	* @return	integer	new ref id
	*/
	function clone($a_parent_ref)
	{		
		// DISABLED
		return false;

		global $rbacadmin;

		// always call parent clone function first!!
		$new_ref_id = parent::clone($a_parent_ref);
		
		// put here rootfolder specific stuff

		// ... and finally always return new reference ID!!
		return $new_ref_id;
	}

	/**
	* delete rootfolder and all related data	
	*
	* @access	public
	* @return	boolean	true if all object data were removed; false if only a references were removed
	*/
	function delete()
	{		
		// DISABLED
		global $log;
		$message = get_class($this)."::delete(): Can't delete root folder!";
		$log->writeWarning($message);
		$this->ilias->raiseError($message,$this->ilias->error_obj->WARNING);
		return false;
		
		// always call parent delete function first!!
		if (!parent::delete())
		{
			return false;
		}
		
		// put here rootfolder specific stuff

		return true;;
	}

	/**
	* notifys an object about an event occured
	* Based on the event happend, each object may decide how it reacts.
	* 
	* @access	public
	* @param	string	event
	* @param	integer	reference id of object where the event occured
	* @param	array	passes optional parameters if required
	* @return	boolean
	*/
	function notify($a_event,$a_ref_id,$a_params = 0)
	{
		return true;
	}
} // END class.ObjRootFolder
?>

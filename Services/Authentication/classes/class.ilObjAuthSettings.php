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
* Class ilObjAuthSettings
* 
* @author Sascha Hofmann <saschahofmann@gmx.de> 
* @version $Id$
*
* @extends ilObject
*/

include_once "./classes/class.ilObject.php";

class ilObjAuthSettings extends ilObject
{
	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function ilObjAuthSettings($a_id = 0,$a_call_by_reference = true)
	{
		$this->type = "auth";
		$this->ilObject($a_id,$a_call_by_reference);
	}
	
	function checkAuthLDAP()
	{
		$settings = $this->ilias->getAllSettings();
		
		if (!$settings["ldap_server"] or !$settings["ldap_basedn"] or !$settings["ldap_port"])
		{
			return false;
		}
		
		$this->ilias->setSetting('ldap_active',true);
		
		return true;
	}
	
	function checkAuthSHIB()
	{
		$settings = $this->ilias->getAllSettings();
		
		if (!$settings["hos_type"] or !$settings["shib_user_default_role"] or !$settings["shib_login"]
			 or !$settings["shib_firstname"]  or !$settings["shib_lastname"])
		{
			return false;
		}

		$this->ilias->setSetting('shibboleth_active',true);

		return true;
	}
	
	function checkAuthRADIUS()
	{
		$settings = $this->ilias->getAllSettings();
		
		if (!$settings["radius_server"] or !$settings["radius_shared_secret"] or !$settings["radius_port"])
		{
			return false;
		}
		
		$this->ilias->setSetting('radius_active',true);
		
		return true;
	}

	function checkAuthScript()
	{
		$settings = $this->ilias->getAllSettings();
		
		if (!$settings["auth_script_name"])
		{
			return false;
		}
		
		$this->ilias->setSetting('script_active',true);

		return true;
	}

	/**
	* update object data
	*
	* @access	public
	* @return	boolean
	*/
	function update()
	{
		if (!parent::update())
		{			
			return false;
		}

		// put here object specific stuff
		
		return true;
	}
	

	/**
	* delete object and all related data	
	*
	* @access	public
	* @return	boolean	true if all object data were removed; false if only a references were removed
	*/
	function delete()
	{		
		// always call parent delete function first!!
		if (!parent::delete())
		{
			return false;
		}
		
		//put here your module specific stuff
		
		return true;
	}

	/**
	* init default roles settings
	* 
	* If your module does not require any default roles, delete this method 
	* (For an example how this method is used, look at ilObjForum)
	* 
	* @access	public
	* @return	array	object IDs of created local roles.
	*/
	function initDefaultRoles()
	{
		global $rbacadmin;
		
		// create a local role folder
		//$rfoldObj = $this->createRoleFolder("Local roles","Role Folder of forum obj_no.".$this->getId());

		// create moderator role and assign role to rolefolder...
		//$roleObj = $rfoldObj->createRole("Moderator","Moderator of forum obj_no.".$this->getId());
		//$roles[] = $roleObj->getId();

		//unset($rfoldObj);
		//unset($roleObj);

		return $roles ? $roles : array();
	}

	/**
	* notifys an object about an event occured
	* Based on the event happend, each object may decide how it reacts.
	* 
	* If you are not required to handle any events related to your module, just delete this method.
	* (For an example how this method is used, look at ilObjGroup)
	* 
	* @access	public
	* @param	string	event
	* @param	integer	reference id of object where the event occured
	* @param	array	passes optional parameters if required
	* @return	boolean
	*/
	function notify($a_event,$a_ref_id,$a_parent_non_rbac_id,$a_node_id,$a_params = 0)
	{
		global $tree;
		
		switch ($a_event)
		{
			case "link":
				
				//var_dump("<pre>",$a_params,"</pre>");
				//echo "Module name ".$this->getRefId()." triggered by link event. Objects linked into target object ref_id: ".$a_ref_id;
				//exit;
				break;
			
			case "cut":
				
				//echo "Module name ".$this->getRefId()." triggered by cut event. Objects are removed from target object ref_id: ".$a_ref_id;
				//exit;
				break;
				
			case "copy":
			
				//var_dump("<pre>",$a_params,"</pre>");
				//echo "Module name ".$this->getRefId()." triggered by copy event. Objects are copied into target object ref_id: ".$a_ref_id;
				//exit;
				break;

			case "paste":
				
				//echo "Module name ".$this->getRefId()." triggered by paste (cut) event. Objects are pasted into target object ref_id: ".$a_ref_id;
				//exit;
				break;
			
			case "new":
				
				//echo "Module name ".$this->getRefId()." triggered by paste (new) event. Objects are applied to target object ref_id: ".$a_ref_id;
				//exit;
				break;
		}
		
		// At the beginning of the recursive process it avoids second call of the notify function with the same parameter
		if ($a_node_id==$_GET["ref_id"])
		{	
			$parent_obj =& $this->ilias->obj_factory->getInstanceByRefId($a_node_id);
			$parent_type = $parent_obj->getType();
			if($parent_type == $this->getType())
			{
				$a_node_id = (int) $tree->getParentId($a_node_id);
			}
		}
		
		parent::notify($a_event,$a_ref_id,$a_parent_non_rbac_id,$a_node_id,$a_params);
	}
} // END class.ilObjAuthSettings
?>

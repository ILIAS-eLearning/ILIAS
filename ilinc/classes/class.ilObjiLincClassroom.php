<?php
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

/**
* Class ilObjiLincClassroom
* 
* @author Sascha Hofmann <saschahofmann@gmx.de> 
* @version $Id$
*
* @extends ilObject
* @package ilias-core
*/

require_once "./classes/class.ilObject.php";

class ilObjiLincClassroom extends ilObject
{
	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function ilObjiLincClassroom($a_id = 0,$a_call_by_reference = true)
	{
		$this->type = "icla";
		$this->ilObject($a_id,$a_call_by_reference);
	}
	
	function _lookupiCourseId($a_ref_id)
	{
		global $ilDB;

		$q = "SELECT course_id FROM ilinc_data ".
			 "LEFT JOIN object_reference ON object_reference.obj_id=ilinc_data.obj_id ".
			 "WHERE object_reference.ref_id = '".$a_ref_id."'";
		$obj_set = $ilDB->query($q);
		$obj_rec = $obj_set->fetchRow(DB_FETCHMODE_ASSOC);

		return $obj_rec["course_id"];
	}
	
	/**
	* 
	* @access private
	*/
	function read()
	{
		parent::read();
		
		// TODO: fetching default role should be done in rbacadmin
		$q = "SELECT * FROM ilinc_data ".
			 "WHERE obj_id='".$this->id."'";
		$r = $this->ilias->db->query($q);

		if ($r->numRows() > 0)
		{
			$data = $r->fetchRow(DB_FETCHMODE_OBJECT);

			// fill member vars in one shot
			$this->ilinc_id = $data->class_id;
			$this->ilinc_course_id = $data->course_id;
		}
		else
		{
			 $this->ilias->raiseError("<b>Error: There is no dataset with id ".$this->id."!</b><br />class: ".get_class($this)."<br />Script: ".__FILE__."<br />Line: ".__LINE__, $this->ilias->FATAL);
		}
	}
	
	function saveID($a_icla_id,$a_icrs_id)
	{
		$q = "INSERT INTO ilinc_data (obj_id,type,course_id,class_id) VALUES (".$this->id.",'icla','".$a_icrs_id."','".$a_icla_id."')";
		$this->ilias->db->query($q);
	}
	
	function isMember($a_user_id,$a_course_id)
	{
		$q = "SELECT * FROM ilinc_data ".
			 "WHERE user_id='".$a_user_id."' AND course_id='".$a_course_id."'";
		$r = $this->ilias->db->query($q);
		
		if ($r->numRows() > 0)
		{
			return true;
		}
		
		return false;
	}
	
	function isRegisteredAtiLincServer(&$a_user_obj)
	{
		if (empty($a_user_obj->ilinc_id))
		{
			return false;
		}
		
		return true;
	}
	
	function addUser(&$a_user_obj)
	{
		include_once "class.ilnetucateXMLAPI.php";
		$ilinc = new ilnetucateXMLAPI();
		$ilinc->addUser($a_user_obj);
		$response = $ilinc->sendRequest();

		if ($response->isError())
		{
			$this->ilias->raiseError($response->getErrorMsg(),$this->ilias->MESSAGE);
		}
		
		$ilinc_user_id = $response->getFirstID();
		$a_user_obj->setiLincID($ilinc_user_id);
		$a_user_obj->update();
		
		return $a_user_obj->getiLincID();
	}

	function registerUser(&$a_user_obj,$a_ilinc_course_id,$a_instructor = "False")
	{
		include_once "class.ilnetucateXMLAPI.php";
		$ilinc = new ilnetucateXMLAPI();
		$ilinc->registerUser($a_user_obj->getiLincID(),$a_ilinc_course_id,$a_instructor);
		$response = $ilinc->sendRequest("registerUser");

		if ($response->isError())
		{
			$this->ilias->raiseError($response->getErrorMsg(),$this->ilias->MESSAGE);
		}
		
		//$ilinc_user_id = $response->getFirstID();
		
		$q = "INSERT INTO ilinc_data (obj_id,type,course_id,class_id,user_id) VALUES (".$a_user_obj->getId().",'user','".$a_ilinc_course_id."',null,'".$a_user_obj->getiLincID()."')";
		$this->ilias->db->query($q);

		return true;
	}
	
	function joinClass(&$a_user_obj,$a_ilinc_class_id)
	{
		include_once "class.ilnetucateXMLAPI.php";
		$ilinc = new ilnetucateXMLAPI();
		$ilinc->joinClass($a_user_obj,$a_ilinc_class_id);
		$response = $ilinc->sendRequest("joinClass");
		
		if ($response->isError())
		{
			$this->ilias->raiseError($response->getErrorMsg(),$this->ilias->MESSAGE);
		}
		
		//var_dump($response->data);exit;
		
		// return URL to join class room
		return $response->data['url']['cdata'];
	}
	
	function findUser(&$a_user_obj)
	{
		include_once "class.ilnetucateXMLAPI.php";
		$ilinc = new ilnetucateXMLAPI();
		$ilinc->findUser($a_user_obj);
		$response = $ilinc->sendRequest();
		
		var_dump($response->data);
		exit;
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
	* copy all entries of your object.
	* 
	* @access	public
	* @param	integer	ref_id of parent object
	* @return	integer	new ref id
	*/
	function ilClone($a_parent_ref)
	{		
		global $rbacadmin;

		// always call parent clone function first!!
		$new_ref_id = parent::ilClone($a_parent_ref);
		
		// get object instance of cloned object
		//$newObj =& $this->ilias->obj_factory->getInstanceByRefId($new_ref_id);

		// create a local role folder & default roles
		//$roles = $newObj->initDefaultRoles();

		// ...finally assign role to creator of object
		//$rbacadmin->assignUser($roles[0], $newObj->getOwner(), "n");		

		// always destroy objects in clone method because clone() is recursive and creates instances for each object in subtree!
		//unset($newObj);

		// ... and finally always return new reference ID!!
		return $new_ref_id;
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
		$q = "DELETE FROM ilinc_data WHERE class_id='".$this->ilinc_id."'";
		$this->ilias->db->query($q);
		
		include_once "class.ilnetucateXMLAPI.php";
		$ilinc = new ilnetucateXMLAPI();
		$ilinc->removeClass($this->ilinc_id);
		$response = $ilinc->sendRequest();
		
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
		return true;
	}

} // END class.ilObjiLincClassroom
?>

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
* Class ilObjiLincCourse
* 
* @author Sascha Hofmann <saschahofmann@gmx.de> 
*
* @version $Id$
*
* @extends ilObject
*/

require_once 'Services/Container/classes/class.ilContainer.php';
require_once 'Modules/ILinc/classes/class.ilnetucateXMLAPI.php';

class ilObjiLincCourse extends ilContainer
{
	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	public function ilObjiLincCourse($a_id = 0,$a_call_by_reference = true)
	{
		$this->type = 'icrs';
		$this->ilObject($a_id,$a_call_by_reference);
		$this->setRegisterMode(false);
		$this->ilincAPI = new ilnetucateXMLAPI();
		
		$this->docent_ids = array();
		$this->student_ids = array();
	}
	
	public function getViewMode()
	{
		return ilContainer::VIEW_ILINC;
	}
	
	/**
	* 
	* @access private
	*/
	function read()
	{
		global $ilDB, $ilErr;

		parent::read();
		
		// TODO: fetching default role should be done in rbacadmin
		$statement = $ilDB->prepare('
			SELECT * FROM ilinc_data
			WHERE obj_id = ?',
			array('integer')
		);
		
		$sql_data = array($this->id);
		$r = $ilDB->execute($statement, $sql_data);
		
		if($r->numRows() > 0)
		{
			$data = $r->fetchRow(DB_FETCHMODE_OBJECT);

			$this->ilinc_id = $data->course_id;
			$this->activated = ilUtil::yn2tf($data->activation_offline);
			$this->akclassvalue1 = $data->akclassvalue1;
			$this->akclassvalue2 = $data->akclassvalue2;
		}
		else
		{
			$ilErr->raiseError("<b>Error: There is no dataset with id ".$this->id."!</b><br />class: ".get_class($this)."<br />Script: ".__FILE__."<br />Line: ".__LINE__, $ilErr->FATAL);
		}
	}
	
	function getiLincId()
	{
		return $this->ilinc_id;
	}
	
	function getErrorMsg()
	{
		$err_msg = $this->error_msg;
		$this->error_msg = "";

		return $err_msg;
	}
	
	function getAKClassValue1()
	{
		return $this->akclassvalue1;
	}
	
	function getAKClassValue2()
	{
		return $this->akclassvalue2;
	}
	
	function setAKClassValue1($a_str)
	{
		$this->akclassvalue1 = $a_str;
	}
	
	function setAKClassValue2($a_str)
	{
		$this->akclassvalue2 = $a_str;
	}

	/**
	* update object data
	*
	* @access	public
	* @return	boolean
	*/
	function update()
	{
		global $ilDB;

		$this->ilincAPI->editCourse($this->getiLincId(),$_POST["Fobject"]);
		$response = $this->ilincAPI->sendRequest();
		
		if ($response->isError())
		{
			$this->error_msg = $response->getErrorMsg();
			return false;
		}
		
		// TODO: alter akclassvalues of classes here

		if (!parent::update())
		{			
			$this->error_msg = "database_error";
			return false;
		}

		$statement = $ilDB->prepareManip('
			UPDATE ilinc_data 
			SET activation_offline = ?,
				akclassvalue1 = ?,
				akclassvalue2 = ?
			WHERE obj_id = ?',
			array('text', 'text', 'text', 'integer')
		);

		$data = array($this->activated, $this->getAKClassValue1(), $this->getAKClassValue2(), $this->getId());
		
		$r = $ilDB->execute($statement, $data);
		
		return true;
	}
	
	/**
	* create course on iLinc server
	*
	* @access	public
	* @return	boolean
	*/
	function addCourse()
	{
		$this->ilincAPI->addCourse($_POST["Fobject"]);
		$response = $this->ilincAPI->sendRequest();
		
		if ($response->isError())
		{
			$this->error_msg = $response->getErrorMsg();
			return false;
		}
		
		$this->ilinc_id = $response->getFirstID();
		
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
		global $ilDB;

		// always call parent delete function first!!
		if (!parent::delete())
		{
			return false;
		}
		
		//put here your module specific stuff
		$statement = $ilDB->prepareManip('
			DELETE FROM ilinc_data WHERE course_id = ?',
			array('integer')
		);

		$data = array($this->getiLincId());
		$ilDB->execute($statement, $data);
	
		// TODO: delete data in ilinc_registration table
		/*
		 * not tested yet
		 */		
/*		$statement = $ilDB->prepareManip('
			DELETE FROM ilinc_registration 
			WHERE  obj_id = ?',
			array('integer')
		);
		$data = array($this->getId());
		$ilDB->execute($statement, $data);
*/		
		
		// remove course from ilinc server
		$this->ilincAPI->removeCourse($this->getiLincId());
		$response = $this->ilincAPI->sendRequest();

		return true;
	}
	
	// store iLinc Id in ILIAS and set variable
	function storeiLincId($a_icrs_id)
	{
		global $ilDB;

		$statement = $ilDB->prepareManip('
			INSERT INTO ilinc_data (
				obj_id, type, course_id, activation_offline) 
			VALUES (?, ?, ?, ?)',
			array('integer', 'text', 'integer', 'text')
		);
			
		$data = array($this->id,'icrs',$a_icrs_id,$this->activated);
		$ilDB->execute($statement, $data);
				
		$this->ilinc_id = $a_icrs_id;
	}
	
	// saveActivationStatus()
	function saveActivationStatus($a_activated)
	{
		global $ilDB;

		$statement = $ilDB->prepareManip('
			UPDATE ilinc_data 
			SET activation_offline = ?
			WHERE obj_id = ?',
			array('text', 'integer')
		);
		$data = array($a_activated, $this->getId());
		$res = $ilDB->execute($statement, $data);
		
	}
	
	// saveAKClassValues
	function saveAKClassValues($a_akclassvalue1,$a_akclassvalue2)
	{
		global $ilDB;

		$statement = $ilDB->prepareManip('
			UPDATE ilinc_data 
			SET akclassvalue1 = ?,
				akclassvalue2 = ?
			WHERE obj_id = ?',
			array('text', 'text', 'integer')
		);
		
		$data = array($a_akclassvalue1, $a_akclassvalue2, $this->getId());
		$res = $ilDB->execute($statement, $data);

	}
	
	/**
	* init default roles settings
	* 
	* @access	public
	* @return	array	object IDs of created local roles.
	*/
	function initDefaultRoles()
	{
		global $rbacadmin, $rbacreview;

		// create a local role folder
		$rfoldObj =& $this->createRoleFolder();

		// ADMIN ROLE
		// create role and assign role to rolefolder...
		$roleObj = $rfoldObj->createRole("il_icrs_admin_".$this->getRefId(),"LearnLinc admin of seminar obj_no.".$this->getId());
		$this->m_roleAdminId = $roleObj->getId();

		//set permission template of new local role
		$statement = $this->ilias->db->prepare('
			SELECT obj_id FROM object_data WHERE type=? AND title=?',
			array('text', 'text')
		);
		
		$data = array('rolt', 'il_icrs_admin');
		$res = $this->ilias->db->execute($statement, $data);
		$r = $res->fetchRow(DB_FETCHMODE_OBJECT);
		
		$rbacadmin->copyRoleTemplatePermissions($r->obj_id,ROLE_FOLDER_ID,$rfoldObj->getRefId(),$roleObj->getId());

		// set object permissions of icrs object
		$ops = $rbacreview->getOperationsOfRole($roleObj->getId(),"icrs",$rfoldObj->getRefId());
		$rbacadmin->grantPermission($roleObj->getId(),$ops,$this->getRefId());

		// set object permissions of role folder object
		//$ops = $rbacreview->getOperationsOfRole($roleObj->getId(),"rolf",$rfoldObj->getRefId());
		//$rbacadmin->grantPermission($roleObj->getId(),$ops,$rfoldObj->getRefId());

		// MEMBER ROLE
		// create role and assign role to rolefolder...
		$roleObj = $rfoldObj->createRole("il_icrs_member_".$this->getRefId(),"LearnLinc admin of seminar obj_no.".$this->getId());
		$this->m_roleMemberId = $roleObj->getId();

		//set permission template of new local role
		$statement = $this->ilias->db->prepare('
			SELECT obj_id FROM object_data WHERE type=? AND title=?',
			array('text', 'text')
		);
		
		$data = array('rolt', 'il_icrs_member');
		$res = $this->ilias->db->execute($statement, $data);
		$r = $res->fetchRow(DB_FETCHMODE_OBJECT);
						
		$rbacadmin->copyRoleTemplatePermissions($r->obj_id,ROLE_FOLDER_ID,$rfoldObj->getRefId(),$roleObj->getId());
		
		// set object permissions of icrs object
		$ops = $rbacreview->getOperationsOfRole($roleObj->getId(),"icrs",$rfoldObj->getRefId());
		$rbacadmin->grantPermission($roleObj->getId(),$ops,$this->getRefId());

		// set object permissions of role folder object
		//$ops = $rbacreview->getOperationsOfRole($roleObj->getId(),"rolf",$rfoldObj->getRefId());
		//$rbacadmin->grantPermission($roleObj->getId(),$ops,$rfoldObj->getRefId());

		unset($rfoldObj);
		unset($roleObj);

		$roles[] = $this->m_roleAdminId;
		$roles[] = $this->m_roleMemberId;
		
		// Break inheritance and initialize permission settings using intersection method with a non_member_template 
		// not implemented for ilinc. maybe never will...
		$this->__setCourseStatus();
		
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
	
	/**
	* add Member to iLic course
	* @access	public
	* @param	integer	user_id
	* @param	integer	member role_id of local group_role
	* @param	boolean	register member on iLinc server as student(false) or docent(true)
	*/
	function addMember(&$a_user_obj, $a_mem_role, $a_instructor = false)
	{
		global $rbacadmin;
//echo "0";
		if (!isset($a_user_obj) && !isset($a_mem_role))
		{
			$this->error_msg = get_class($this)."::addMember(): Missing parameters !";
			return false;
		}
//echo "1";
		// check if user is registered at iLinc server
		if (!$this->userExists($a_user_obj))
		{
			// if not, add user on iLinc server
			if ($this->addUser($a_user_obj) == false)
			{
				// error_msg already set
				return false;
			}
		}
//echo "2";
		// assign membership to icourse on iLinc server
		if (!$this->registerUser($a_user_obj,$a_instructor))
		{
			// error_msg already set
			return false;
		}
//echo "3";
		// finally assign user to member role in ILIAS
		$this->join($a_user_obj->getId(),$a_mem_role);
//echo "4";
		return true;
	}

	/**
	* join icrs, assigns user to role
	* @access	private
	* @param	integer	member status = obj_id of local_group_role
	*/
	function join($a_user_id, $a_mem_role="")
	{
		global $rbacadmin;

		if (is_array($a_mem_role))
		{
			foreach ($a_mem_role as $role)
			{
				$rbacadmin->assignUser($role,$a_user_id, false);
			}
		}
		else
		{
			$rbacadmin->assignUser($a_mem_role,$a_user_id, false);
		}

		return true;
	}
	
	/**
	* deassign member from group role
	* @access	private
	*/
	function leave($a_user_id)
	{
		global $rbacadmin;

		$arr_groupRoles = $this->getMemberRoles($a_user_id);

		if (is_array($arr_groupRoles))
		{
			foreach ($arr_groupRoles as $groupRole)
			{
				$rbacadmin->deassignUser($groupRole, $a_user_id);
			}
		}
		else
		{
			$rbacadmin->deassignUser($arr_groupRoles, $a_user_id);
		}

		return true;
	}
	
	/**
	* get group member status
	* @access	public
	* @param	integer	user_id
	* @return	returns array of obj_ids of assigned local roles
	*/
	function getMemberRoles($a_user_id)
	{
		global $rbacadmin, $rbacreview;

		$arr_assignedRoles = array();

		$arr_assignedRoles = array_intersect($rbacreview->assignedRoles($a_user_id),$this->getLocalRoles());

		return $arr_assignedRoles;
	}
	
	/**
	* get all group Member ids regardless of role
	* @access	public
	* @return	return array of users (obj_ids) that are assigned to
	* the groupspecific roles (grp_member,grp_admin)
	*/
	function getMemberIds()
	{
		global $rbacadmin, $rbacreview;

		$usr_arr= array();

		$rol  = $this->getLocalRoles();

		foreach ($rol as $value)
		{
			foreach ($rbacreview->assignedUsers($value) as $member_id)
			{
				array_push($usr_arr,$member_id);
			}
		}

		$mem_arr = array_unique($usr_arr);
		
		return $mem_arr ? $mem_arr : array();
	}
	
	/**
	* get all group Members regardless of group role.
	* fetch all users data in one shot to improve performance
	* @access	public
	* @param	array	of user ids
	* @return	return array of userdata
	*/
	function getMemberData($a_mem_ids, $active = 1)
	{
		global $rbacadmin, $rbacreview, $ilBench, $ilDB;

		$usr_arr= array();
	
		$data_types = array();
		$data_values = array();
		$cnt_mem_ids = count($a_mem_ids);
		
		$query = 'SELECT login,firstname,lastname,title,usr_id,ilinc_id
		 			FROM usr_data WHERE usr_id IN ';
		
		if (is_array($a_mem_ids) &&
			$cnt_mem_ids > 0)
		{
			$in = '(';
			$counter = 0;			
			foreach($a_mem_ids as $mem_id)
			{
				array_push($data_values, $mem_id);
				array_push($data_types, 'integer');
				
				if($counter > 0) $in .= ',';
				$in .= '?';								
				++$counter;				
			}
			$in .= ')';
			$query .= $in;
		}

		if (is_numeric($active) && $active > -1)
		{
			$query .= ' AND active = ?';
			array_push($data_values,$active);
			array_push($data_types, 'integer');
		}
  		
		$statement= $ilDB->prepare($query, $data_types);
		$r = $ilDB->execute($statement, $data_values);
		
		while($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$mem_arr[] = array("id" => $row->usr_id,
								"login" => $row->login,
								"firstname" => $row->firstname,
								"lastname" => $row->lastname,
								"ilinc_id" => $row->ilinc_id
								);
		}

		return $mem_arr ? $mem_arr : array();
	}
	
	/**
	* get ALL local roles of group, also those created and defined afterwards
	* only fetch data once from database. info is stored in object variable
	* @access	public
	* @return	return array [title|id] of roles...
	*/
	function getLocalRoles($a_translate = false)
	{
		global $rbacadmin,$rbacreview;
		
		if (empty($this->local_roles))
		{
			$this->local_roles = array();
			$rolf 	   = $rbacreview->getRoleFolderOfObject($this->getRefId());
			$role_arr  = $rbacreview->getRolesOfRoleFolder($rolf["ref_id"]);

			foreach ($role_arr as $role_id)
			{
				if ($rbacreview->isAssignable($role_id,$rolf["ref_id"]) == true)
				{
					$role_Obj =& $this->ilias->obj_factory->getInstanceByObjId($role_id);
					
					if ($a_translate)
					{
						$role_name = ilObjRole::_getTranslation($role_Obj->getTitle());
					}
					else
					{
						$role_name = $role_Obj->getTitle();
					}
					
					$this->local_roles[$role_name] = $role_Obj->getId();
				}
			}
		}
		
		return $this->local_roles;
	}
	
	/**
	* get group member status
	* @access	public
	* @param	integer	user_id
	* @return	returns string of role titles
	*/
	function getMemberRolesTitle($a_user_id)
	{		
		global $ilDB,$ilBench;
		
		include_once ('./Services/AccessControl/classes/class.ilObjRole.php');

		$str_member_roles ="";

		$data_types = array();
		$data_values = array();
		
		$query = 'SELECT title FROM object_data
					LEFT JOIN rbac_ua ON object_data.obj_id = rbac_ua.rol_id
					WHERE object_data.type = ?
					AND rbac_ua.usr_id = ?
					AND rbac_ua.rol_id IN';
		
		array_push($data_types, 'text', 'integer');
		array_push($data_values,'role', $a_user_id);
		
		$local_roles = $this->getLocalRoles();
		$cnt_local_roles = count($local_roles);
		
		if (is_array($local_roles) &&
			$cnt_local_roles > 0)
		{
			$in = '(';
			$counter = 0;			
			foreach($local_roles as $local_role)
			{
				array_push($data_values, $local_role);
				array_push($data_types, 'integer');
				
				if($counter > 0) $in .= ',';
				$in .= '?';								
				++$counter;				
			}
			$in .= ')';
			$query .= $in;
		}
		$cnt_data_values = count($data_values);
		$cnt_data_types = count($data_types);
		
		
		
		$statement = $ilDB->prepare($query, $data_types);
		$r = $ilDB->execute($statement, $data_values);	

		while($row = $r->fetchRow(DB_FETCHMODE_ASSOC))
		{
			// display human readable role names for autogenerated roles
			$str_member_roles .= ilObjRole::_getTranslation($row["title"]).", ";
		}

		return substr($str_member_roles,0,-2);
	}
	
	/**
	* returns object id of created default member role
	* @access	public
	*/
	function getDefaultMemberRole()
	{
		$local_icrs_Roles = $this->getLocalRoles();

		return $local_icrs_Roles["il_icrs_member_".$this->getRefId()];
	}

	/**
	* returns object id of created default adminstrator role
	* @access	public
	*/
	function getDefaultAdminRole()
	{
		$local_icrs_Roles = $this->getLocalRoles();

		return $local_icrs_Roles["il_icrs_admin_".$this->getRefId()];
	}
	
	function getClassrooms()
	{
		global $ilErr;
		
		if (!$this->ilias->getSetting("ilinc_active"))
		{
			$this->error_msg = "ilinc_server_not_active";
			return false;
		}

		$this->ilincAPI->findCourseClasses($this->getiLincId());
		$response = $this->ilincAPI->sendRequest();

		if ($response->isError())
		{
			if (!$response->getErrorMsg())
			{
				$this->error_msg = "err_get_classrooms";
			}
			else
			{
				$this->error_msg = $response->getErrorMsg();
			}
			
			return false;
		}

		if (!$response->data['classes'])
		{

			$this->error_msg = $response->data['result']['cdata'];
			return false;
		}

		foreach ($response->data['classes'] as $class_id => $data)
		{
			$this->ilincAPI->findClass($class_id);
			$response = $this->ilincAPI->sendRequest("findClass");

			if ($response->data['classes'])
			{
				$full_class_data[$class_id] = $response->data['classes'][$class_id];
			}
		}
		
		return $full_class_data;
	}
	
	function updateClassrooms()
	{
		global $ilErr;

		$this->ilincAPI->findCourseClasses($this->getiLincId());
		$response = $this->ilincAPI->sendRequest();

		if ($response->isError())
		{
			if (!$response->getErrorMsg())
			{
				$this->error_msg = "err_get_classrooms";
			}
			else
			{
				$this->error_msg = $response->getErrorMsg();
			}
			
			return false;
		}

		if (!$response->data['classes'])
		{

			$this->error_msg = $response->data['result']['cdata'];
			return false;
		}
		
		if (array_key_exists('akclassvalue1',$_POST["Fobject"]))
		{
			$data["akclassvalue1"] = $_POST["Fobject"]["akclassvalue1"];
		}
		
		if (array_key_exists('akclassvalue2',$_POST["Fobject"]))
		{
			$data["akclassvalue2"] = $_POST["Fobject"]["akclassvalue2"];
		}
		
		foreach ($response->data['classes'] as $class_id => $data2)
		{
			include_once("./Modules/ILinc/classes/class.ilObjiLincClassroom.php");
			$icla_obj = new ilObjiLincClassroom($class_id,$this->ref_id);
			
			if (!$icla_obj->update($data))
			{
				$this->error_msg = $icla_obj->getErrorMsg();
				
				return false;
			}
			
			unset($icla_obj);
		}
		
		return true;
	}
	
	// checks if user account already exists at iLinc server
	// TODO: check is only local in ILIAS not on iLinc server
	function userExists(&$a_user_obj)
	{
		//$data = $a_user_obj->getiLincData();

		include_once ('./Modules/ILinc/classes/class.ilObjiLincUser.php');
		$ilinc_user = new ilObjiLincUser($a_user_obj);
		
		if (!$ilinc_user->id and !$ilinc_user->login)
		{
			return false;
		}
		
		return true;
	}
	
	// create user account on iLinc server
	function addUser(&$a_user_obj)
	{
		include_once ('./Modules/ILinc/classes/class.ilObjiLincUser.php');
		$ilinc_user = new ilObjiLincUser($a_user_obj);
		
		return $ilinc_user->add();
	}

	function isMember($a_user_id = "")
	{
		if (strlen($a_user_id) == 0)
		{
			$a_user_id = $this->ilias->account->getId();
		}

		$arr_members = $this->getMemberIds();

		if (in_array($a_user_id, $arr_members))
		{
			return true;
		}
		
		return false;
	}
	
	function isDocent($a_user_obj = "")
	{
		if (!$a_user_obj)
		{
			$a_user_obj =& $this->ilias->account;
		}
		
		$docents = $this->getiLincMemberIds(true);
		
		include_once ('./Modules/ILinc/classes/class.ilObjiLincUser.php');
		$ilinc_user = new ilObjiLincUser($a_user_obj);
		
		if (in_array($ilinc_user->id,$docents))
		{
			return true;
		}
		
		return false;
	}
	
	function registerUser(&$a_user_obj,$a_instructor = false)
	{
		if ($a_instructor === true)
		{
			$a_instructor = "True";
		}
		else
		{
			$a_instructor = "False";
		}
		
		include_once ('./Modules/ILinc/classes/class.ilObjiLincUser.php');
		$ilinc_user = new ilObjiLincUser($a_user_obj);
		
		$user[] = array('id' => $ilinc_user->id, 'instructor' => $a_instructor);
		$this->ilincAPI->registerUser($this->getiLincId(),$user);
		$response = $this->ilincAPI->sendRequest("registerUser");
		
		if ($response->isError())
		{
			if (!$response->getErrorMsg())
			{
				$this->error_msg = "err_register_user";
			}
			else
			{
				$this->error_msg = $response->getErrorMsg();
			}
			
			return false;
		}
		
		return true;
	}
	
	function registerUsers($a_user_arr)
	{
		foreach ($a_user_arr as $user_id => $instructorflag)
		{
			$flag = "False";
			
			if ($instructorflag == ILINC_MEMBER_DOCENT)
			{
				$flag = "True";
			}
			
			$ilinc_users[] = array('id' => $user_id,'instructor' => $flag);
		}
		
		$this->ilincAPI->registerUser($this->getiLincId(),$ilinc_users);
		$response = $this->ilincAPI->sendRequest("registerUser");

		if ($response->isError())
		{
			if (!$response->getErrorMsg())
			{
				$this->error_msg = "err_register_users";
			}
			else
			{
				$this->error_msg = $response->getErrorMsg();
			}
			
			return false;
		}
		
		return true;
	}
	
	// unregister user from course on iLinc server
	function unregisterUser($a_user_obj)
	{
		include_once ('./Modules/ILinc/classes/class.ilObjiLincUser.php');
		$ilinc_user = new ilObjiLincUser($a_user_obj);
		
		// do not send request if user is not registered at iLinc server at all
		if ($ilinc_user->id == '0')
		{
			return true;
		}
		
		$this->ilincAPI->unregisterUser($this->getiLincId(),array($ilinc_user->id));
		$response = $this->ilincAPI->sendRequest();

		if ($response->isError())
		{
			if (!$response->getErrorMsg())
			{
				$this->error_msg = "err_unregister_user";
			}
			else
			{
				$this->error_msg = $response->getErrorMsg();
			}
			
			return false;
		}
		
		return true;
	}
	
	function unregisterUsers($a_ilinc_user_ids)
	{
		$this->ilincAPI->unregisterUser($this->getiLincId(),$a_ilinc_user_ids);
		$response = $this->ilincAPI->sendRequest();
		
		if ($response->isError())
		{
			if (!$response->getErrorMsg())
			{
				$this->error_msg = "err_unregister_users";
			}
			else
			{
				$this->error_msg = $response->getErrorMsg();
			}
			
			return false;
		}
		
		return true;
	}
	
	function userLogin(&$a_user_obj)
	{
		include_once ('./Modules/ILinc/classes/class.ilObjiLincUser.php');
		$ilinc_user = new ilObjiLincUser($a_user_obj);
		
		$this->ilincAPI->userLogin($ilinc_user);
		$response = $this->ilincAPI->sendRequest("userLogin");

		if ($response->isError())
		{
			if (!$response->getErrorMsg())
			{
				$this->error_msg = "err_user_login";
			}
			else
			{
				$this->error_msg = $response->getErrorMsg();
			}
			
			return false;
		}
		
		// return URL to join class room
		return $response->data['url']['cdata'];
	}
	
	// not used here
	function uploadPicture(&$a_user_obj,$a_lang)
	{
		$this->ilincAPI->uploadPicture($a_user_obj,$a_lang);
		$response = $this->ilincAPI->sendRequest("uploadPicture");

		if ($response->isError())
		{
			if (!$response->getErrorMsg())
			{
				$this->error_msg = "err_upload_picture";
			}
			else
			{
				$this->error_msg = $response->getErrorMsg();
			}
			
			return false;
		}
		
		// return URL to user's personal page
		return $response->data['url']['cdata'];
	}
	
	/**
	* get Group Admin Id
	* @access	public
	* @param	integer	group id
	* @param	returns userids that are assigned to a group administrator! role
	*/
	function getAdminIds($a_grpId="")
	{
		global $rbacreview;

		if (!empty($a_grpId))
		{
			$grp_id = $a_grpId;
		}
		else
		{
			$grp_id = $this->getRefId();
		}

		$usr_arr = array();
		$roles = $this->getDefaultRoles($this->getRefId());

		foreach ($rbacreview->assignedUsers($this->getDefaultAdminRole()) as $member_id)
		{
			array_push($usr_arr,$member_id);
		}

		return $usr_arr;
	}
	
	/**
	* removes Member from group
	* @access	public
	*/
	function removeMember(&$a_user_obj)
	{
		if (!isset($a_user_obj))
		{
			$this->error_msg = get_class($this)."::removeMember(): Missing parameters !";
			return false;
		}
		
		if (!$this->isMember($a_user_obj->getId()))
		{
			return true;
		}

		if (count($this->getMemberIds()) > 1)
		{
			if ($this->isAdmin($a_user_obj->getId()) && count($this->getAdminIds()) < 2)
			{
				$this->error_msg = "ilinc_err_administrator_required";
				return false;
			}
		}
		
		// unregister from course on iLinc server
		if (!$this->unregisterUser($a_user_obj))
		{
			// error_msg already set
			return false;
		}

		$this->leave($a_user_obj->getId());

		return true;
	}

	/**
	* is Admin
	* @access	public
	* @param	integer	user_id
	* @param	boolean, true if user is group administrator
	*/
	function isAdmin($a_user_id)
	{
		global $rbacreview;

		$icrs_roles = $this->getDefaultRoles();

		if (in_array($a_user_id,$rbacreview->assignedUsers($icrs_roles["icrs_admin_role"])))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	/**
	* get default group roles, returns the defaultlike create roles il_grp_member, il_grp_admin
	* @access	public
	* @param 	returns the obj_ids of group specific roles(il_grp_member,il_grp_admin)
	*/
	function getDefaultRoles($a_grp_id="")
	{
		global $rbacadmin, $rbacreview;

		if (strlen($a_grp_id) > 0)
		{
			$grp_id = $a_grp_id;
		}
		else
		{
			$grp_id = $this->getRefId();
		}

		$rolf 	   = $rbacreview->getRoleFolderOfObject($grp_id);
		$role_arr  = $rbacreview->getRolesOfRoleFolder($rolf["ref_id"]);

		foreach ($role_arr as $role_id)
		{
			$role_Obj =& $this->ilias->obj_factory->getInstanceByObjId($role_id);

			$grp_Member ="il_icrs_member_".$grp_id;
			$grp_Admin  ="il_icrs_admin_".$grp_id;

			if (strcmp($role_Obj->getTitle(), $grp_Member) == 0 )
			{
				$arr_grpDefaultRoles["icrs_member_role"] = $role_Obj->getId();
			}

			if (strcmp($role_Obj->getTitle(), $grp_Admin) == 0)
			{
				$arr_grpDefaultRoles["icrs_admin_role"] = $role_Obj->getId();
			}
		}

		return $arr_grpDefaultRoles;
	}
	
	// returns ilinc_user_ids of course (students=false,docents=true)
	function getiLincMemberIds($a_instructorflag = false)
	{
		if ($a_instructorflag == true)
		{
			if (!empty($this->docent_ids))
			{
				return $this->docent_ids;
			}
		}
		else
		{
			if (!empty($this->student_ids))
			{
				return $this->student_ids;
			}
		}
		
		$this->ilincAPI->findRegisteredUsersByRole($this->getiLincId(),$a_instructorflag);
		$response = $this->ilincAPI->sendRequest();
			
		if (is_array($response->data['users']))
		{
			if ($a_instructorflag == true)
			{
				$this->docent_ids = array_keys($response->data['users']);
			}
			else
			{
				$this->student_ids = array_keys($response->data['users']);
			}

			return array_keys($response->data['users']);
		}
		
		return array();
	}
	
	function checkiLincMemberStatus($a_ilinc_user_id,$a_docent_ids,$a_student_ids)
	{
		if (in_array($a_ilinc_user_id,$a_docent_ids))
		{
			return ILINC_MEMBER_DOCENT;
		}
		
		if (in_array($a_ilinc_user_id,$a_student_ids))
		{
			return ILINC_MEMBER_STUDENT;
		}
			
		return ILINC_MEMBER_NOTSET;
	}
	
	function _isActivated($a_course_obj_id)
	{
		global $ilDB,$ilias;

		if (!$ilias->getSetting("ilinc_active"))
		{
			return false;
		}

		$statement = $ilDB->prepare('
			SELECT activation_offline FROM ilinc_data WHERE obj_id = ?',
			array('integer')
		);

		$data = array($a_course_obj_id);
		$r = $ilDB->execute($statement, $data);
		
		$row = $r->fetchRow(DB_FETCHMODE_OBJECT);

		return ilUtil::yn2tf($row->activation_offline);
	}
	
	function _getAKClassValues($a_course_obj_id)
	{
		global $ilDB,$ilias;

		$statement = $ilDB->prepare('
			SELECT akclassvalue1, akclassvalue2 FROM ilinc_data WHERE obj_id = ?',
			array('integer')
		);
		$data = array($a_course_obj_id);
		$r = $ilDB->execute($statement, $data);
			
		$row = $r->fetchRow(DB_FETCHMODE_OBJECT);

		return $akclassvalues = array($row->akclassvalue1,$row->akclassvalue2);
	}
	
	function _isMember($a_user_id,$a_ref_id)
	{
		global $rbacreview;
		
		$rolf = $rbacreview->getRoleFolderOfObject($a_ref_id);
		$local_roles = $rbacreview->getRolesOfRoleFolder($rolf["ref_id"],false);
		$user_roles = $rbacreview->assignedRoles($a_user_id);
		
		if (!array_intersect($local_roles,$user_roles))
		{
			return false;
		}
		
		return true;
	}
	
	function __setCourseStatus()
	{
		// empty
	}
	
	/**
	* get all subitems of the container
	* overwrites method in ilContainerGUI
	*/
	function getSubItems()
	{
		$objects = array();

		if(!($objects = $this->getClassrooms()))
		{
			ilUtil::sendInfo($this->lng->txt($this->getErrorMsg()));	
			return array();
		}

		foreach((array)$objects as $key => $object)
		{
			$this->items['icla'][$key] = $object;
		}

		return is_array($this->items) ? $this->items : array();
	}
	
	/**
	 * STATIC METHOD
	 * create a link to the object
	 * @param	int uniq id
	 * @return array array('link','target')
	 * @static
	 * @access	public
	 */
	function _getLinkToObject($a_id)
	{
		return array("repository.php?ref_id=".$a_id."&set_mode=flat&cmdClass=ilobjilinccoursegui","");
	}
} // END class.ilObjiLincCourse
?>

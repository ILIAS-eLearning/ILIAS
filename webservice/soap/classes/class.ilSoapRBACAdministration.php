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
   * Soap rbac administration methods
   *
   * @author Stefan Meyer <smeyer@databay.de>
   * @version $Id$
   *
   * @package ilias
   */
include_once './webservice/soap/classes/class.ilSoapAdministration.php';

class ilSoapRBACAdministration extends ilSoapAdministration
{
	function ilSoapRBACAdministration()
	{
		parent::ilSoapAdministration();
	}
	

	function addUserRoleEntry($sid,$user_id,$role_id)
	{

		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->sauth->getMessage(),$this->sauth->getMessageCode());
		}			

		// Include main header
		include_once './include/inc.header.php';

		if($tmp_user =& ilObjectFactory::getInstanceByObjId($user_id) and $tmp_user->getType() != 'usr')
		{
			return $this->__raiseError('No valid user id given. Please choose an existing id of an ILIAS user',
									   'Client');
		}
		if($tmp_role =& ilObjectFactory::getInstanceByObjId($role_id) and $tmp_role->getType() != 'role')
		{
			return $this->__raiseError('No valid role id given. Please choose an existing id of an ILIAS role',
									   'Client');
		}

		if(!$rbacadmin->assignUser($role_id,$user_id))
		{
			return $this->__raiseError('Error rbacadmin->assignUser()',
									   'Server');
		}
		return true;
	}
	function deleteUserRoleEntry($sid,$user_id,$role_id)
	{
		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->sauth->getMessage(),$this->sauth->getMessageCode());
		}			

		// Include main header
		include_once './include/inc.header.php';

		if($tmp_user =& ilObjectFactory::getInstanceByObjId($user_id,false) and $tmp_user->getType() != 'usr')
		{
			return $this->__raiseError('No valid user id given. Please choose an existing id of an ILIAS user',
									   'Client');
		}
		if($tmp_role =& ilObjectFactory::getInstanceByObjId($role_id,false) and $tmp_role->getType() != 'role')
		{
			return $this->__raiseError('No valid role id given. Please choose an existing id of an ILIAS role',
									   'Client');
		}

		if(!$rbacadmin->deassignUser($role_id,$user_id))
		{
			return $this->__raiseError('Error rbacadmin->deassignUser()',
									   'Server');
		}
		return true;
	}

	function getOperations($sid)
	{
		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->sauth->getMessage(),$this->sauth->getMessageCode());
		}			

		// Include main header
		include_once './include/inc.header.php';

		if(is_array($ops = $rbacreview->getOperations()))
		{
			return $ops;
		}
		else
		{
			return $this->__raiseError('Unknown error','Server');
		}
	}

	function revokePermissions($sid,$ref_id,$role_id)
	{
		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->sauth->getMessage(),$this->sauth->getMessageCode());
		}			
		
		// Include main header
		include_once './include/inc.header.php';

		if(!$tmp_obj =& ilObjectFactory::getInstanceByRefId($ref_id,false))
		{
			return $this->__raiseError('No valid ref id given. Please choose an existing reference id of an ILIAS object',
									   'Client');
		}
		if($tmp_role =& ilObjectFactory::getInstanceByObjId($role_id,false) and $tmp_role->getType() != 'role')
		{
			return $this->__raiseError('No valid role id given. Please choose an existing id of an ILIAS role',
									   'Client');
		}
		if ($role_id == SYSTEM_ROLE_ID)
		{
			return $this->__raiseError('Cannot revoke permissions of system role',
									   'Client');
		}

		$rbacadmin->revokePermission($ref_id,$role_id);

		return true;
	}
	function grantPermissions($sid,$ref_id,$role_id,$permissions)
	{
		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->sauth->getMessage(),$this->sauth->getMessageCode());
		}			
		
		// Include main header
		include_once './include/inc.header.php';

		if(!$tmp_obj =& ilObjectFactory::getInstanceByRefId($ref_id,false))
		{
			return $this->__raiseError('No valid ref id given. Please choose an existing reference id of an ILIAS object',
									   'Client');
		}
		if($tmp_role =& ilObjectFactory::getInstanceByObjId($role_id,false) and $tmp_role->getType() != 'role')
		{
			return $this->__raiseError('No valid role id given. Please choose an existing id of an ILIAS role',
									   'Client');
		}

		if(!is_array($permissions))
		{
			return $this->__raiseError('No valid permissions given.'.print_r($permissions),
									   'Client');
		}

		$rbacadmin->revokePermission($ref_id,$role_id);
		$rbacadmin->grantPermission($role_id,$permissions,$ref_id);

		return true;
	}

	function getLocalRoles($sid,$ref_id)
	{
		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->sauth->getMessage(),$this->sauth->getMessageCode());
		}			
		
		// Include main header
		include_once './include/inc.header.php';

		if(!$tmp_obj =& ilObjectFactory::getInstanceByRefId($ref_id,false))
		{
			return $this->__raiseError('No valid ref id given. Please choose an existing reference id of an ILIAS object',
									   'Client');
		}

		$role_folder = $rbacreview->getRoleFolderOfObject($ref_id);
		
		if(count($role_folder))
		{
			foreach($rbacreview->getRolesOfRoleFolder($role_folder['ref_id'],false) as $role_id)
			{
				if($tmp_obj = ilObjectFactory::getInstanceByObjId($role_id,false))
				{
					$objs[] =& $tmp_obj;
				}
			}
		}
		if(count($objs))
		{
			include_once './webservice/soap/classes/class.ilObjectXMLWriter.php';

			$xml_writer = new ilObjectXMLWriter();
			$xml_writer->setObjects($objs);
			if($xml_writer->start())
			{
				return $xml_writer->getXML();
			}
		}
		return '';
	}		

	function getUserRoles($sid,$user_id)
	{
		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->sauth->getMessage(),$this->sauth->getMessageCode());
		}			
		
		// Include main header
		include_once './include/inc.header.php';

		if(!$tmp_user =& ilObjectFactory::getInstanceByObjId($user_id,false))
		{
			return $this->__raiseError('No valid user id given. Please choose an existing id of an ILIAS user',
									   'Client');
		}

		foreach($rbacreview->assignedRoles($user_id) as $role_id)
		{
			if($tmp_obj = ilObjectFactory::getInstanceByObjId($role_id,false))
			{
				$objs[] =& $tmp_obj;
			}
		}
		if(count($objs))
		{
			include_once './webservice/soap/classes/class.ilObjectXMLWriter.php';

			$xml_writer = new ilObjectXMLWriter();
			$xml_writer->setObjects($objs);
			if($xml_writer->start())
			{
				return $xml_writer->getXML();
			}
		}
		return '';
	}

	function addRole($sid,$target_id,$role_xml)
	{
		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->sauth->getMessage(),$this->sauth->getMessageCode());
		}			
		
		// Include main header
		include_once './include/inc.header.php';

		if(!$tmp_obj =& ilObjectFactory::getInstanceByRefId($target_id,false))
		{
			return $this->__raiseError('No valid ref id given. Please choose an existing reference id of an ILIAS object',
									   'Client');
		}
		include_once 'webservice/soap/classes/class.ilObjectXMLParser.php';
		
		$xml_parser =& new ilObjectXMLParser($role_xml);
		$xml_parser->startParsing();

		foreach($xml_parser->getObjectData() as $object_data)
		{

			if($rbacreview->roleExists($object_data['title']))
			{
				return $this->__raiseError('The rolename must be unique. A role with name '.$object_data['title'].' already exists',
										   'Client');
			}
			// check if role title has il_ prefix
			if(substr($object_data['title'],0,3) == "il_")
			{
				return $this->__raiseError('Rolenames are not allowed to start with "il_" ',
										   'Client');
			}

			$rolf_data = $rbacreview->getRoleFolderOfObject($target_id);
			if (!$rolf_id = $rolf_data["child"])
			{
				// can the current object contain a rolefolder?
				$subobjects = $objDefinition->getSubObjects($tmp_obj->getType());
				if(!isset($subobjects["rolf"]))
				{
					return $this->__raiseError('Cannot create role at this position',
											   'Client');
				}

				// CHECK ACCESS 'create' rolefolder
				if (!$rbacsystem->checkAccess('create',$target_id,'rolf'))
				{
					return $this->__raiseError('No permission to create role folders',
											   'Client');
				}

				// create a rolefolder
				$rolf_obj = $tmp_obj->createRoleFolder();
				$rolf_id = $rolf_obj->getRefId();
			}
			$rolf_obj =& ilObjectFactory::getInstanceByRefId($rolf_id);
			$role_obj = $rolf_obj->createRole($object_data['title'],$object_data['description']);

			$new_roles[] = $role_obj->getId();
		}

		return $new_roles ? $new_roles : array();
	}

	function getObjectTreeOperations($sid,$ref_id,$user_id)
	{
		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->sauth->getMessage(),$this->sauth->getMessageCode());
		}			
		
		// Include main header
		include_once './include/inc.header.php';

		if(!$tmp_obj =& ilObjectFactory::getInstanceByRefId($ref_id,false))
		{
			return $this->__raiseError('No valid ref id given. Please choose an existing reference id of an ILIAS object',
									   'Client');
		}
		if(!$tmp_user =& ilObjectFactory::getInstanceByObjId($user_id,false))
		{
			return $this->__raiseError('No valid user id given.',
									   'Client');
		}

		$ops = $rbacreview->getOperationsOnTypeString($tmp_obj->getType());
		$ops_data = array();
		foreach(ilObject::_getAllReferences($tmp_obj->getId()) as $all_ref_id)
		{
			foreach($ops as $ops_id)
			{
				$op_data = $rbacreview->getOperation($ops_id);
				if($rbacsystem->checkAccessOfUser($user_id,$op_data['operation'],$all_ref_id))
				{
					$ops_data[$ops_id] = $op_data;
				}
			}
		}
		
		foreach($ops_data as $data)
		{
			$ret_data[] = $data;
		}
		return $ret_data ? $ret_data : array();
	}
}
?>
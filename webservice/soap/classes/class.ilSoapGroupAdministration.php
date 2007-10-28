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
   * Soap grp administration methods
   *
   * @author Stefan Meyer <smeyer@databay.de
   * @version $Id$
   *
   * @package ilias
   */
include_once './webservice/soap/classes/class.ilSoapAdministration.php';

class ilSoapGroupAdministration extends ilSoapAdministration
{
	function ilSoapGroupAdministration()
	{
		parent::ilSoapAdministration();
	}


	// Service methods
	function addGroup($sid,$target_id,$grp_xml)
	{

		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->sauth->getMessage(),$this->sauth->getMessageCode());
		}

		if(!is_numeric($target_id))
		{
			return $this->__raiseError('No valid target id given. Please choose an existing reference id of an ILIAS category or course',
									   'Client');
		}

		// Include main header
		include_once './include/inc.header.php';
		global $rbacsystem;

		if(!$rbacsystem->checkAccess('create',$target_id,'grp'))
		{
			return $this->__raiseError('Check access failed. No permission to create groups','Server');
		}

		if(ilObject::_isInTrash($target_id))
		{
			return $this->__raiseError("Parent with ID $target_id has been deleted.", 'CLIENT_TARGET_DELETED');
		}


		// Start import
		include_once("classes/class.ilObjGroup.php");
		include_once 'classes/class.ilGroupImportParser.php';
		$xml_parser = new ilGroupImportParser($grp_xml,$target_id);
		$new_ref_id = $xml_parser->startParsing();

		return $new_ref_id ? $new_ref_id : "0";
	}

	// Service methods
	function updateGroup($sid,$ref_id,$grp_xml)
	{

		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->sauth->getMessage(),$this->sauth->getMessageCode());
		}



		if(!is_numeric($ref_id))
		{
			return $this->__raiseError('No valid target id given. Please choose an existing reference id of an ILIAS category or course',
									   'Client');
		}

		// Include main header
		include_once './include/inc.header.php';
		global $rbacsystem;

		if(!$rbacsystem->checkAccess('write',$ref_id,'grp'))
		{
			return $this->__raiseError('Check access failed. No permission to edit groups','Server');
		}

		// Start import
		include_once("classes/class.ilObjGroup.php");

		if(!$grp = ilObjectFactory::getInstanceByRefId($ref_id, false))
		{
			return $this->__raiseError('Cannot create group instance!','CLIENT_OBJECT_NOT_FOUND');
		}

		if(ilObject::_isInTrash($ref_id))
		{
			return $this->__raiseError("Object with ID $ref_id has been deleted.", 'CLIENT_OBJECT_DELETED');
		}

		
		if (ilObjectFactory::getTypeByRefId($ref_id, false) !="grp") 
		{
			return $this->__raiseError('Reference id does not point to a group!','CLIENT_WRONG_TYPE');				
		}


		include_once 'classes/class.ilGroupImportParser.php';
		$xml_parser = new ilGroupImportParser($grp_xml, -1);
		$xml_parser->setMode(ilGroupImportParser::$UPDATE);
		$xml_parser->setGroup($grp);
		$new_ref_id = $xml_parser->startParsing();

		return $new_ref_id ? $new_ref_id : "0";
	}


	function groupExists($sid,$title)
	{
		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->sauth->getMessage(),$this->sauth->getMessageCode());
		}

		if(!$title)
		{
			return $this->__raiseError('No title given. Please choose an title for the group in question.',
									   'Client');
		}

		// Include main header
		include_once './include/inc.header.php';

		return ilUtil::groupNameExists($title);
	}

	function getGroup($sid,$ref_id)
	{
		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->sauth->getMessage(),$this->sauth->getMessageCode());
		}

		// Include main header
		include_once './include/inc.header.php';

		if(ilObject::_isInTrash($ref_id))
		{
			return $this->__raiseError("Parent with ID $ref_id has been deleted.", 'CLIENT_OBJECT_DELETED');
		}


		if(!$grp_obj =& ilObjectFactory::getInstanceByRefId($ref_id,false))
		{
			return $this->__raiseError('No valid reference id given.',
									   'Client');
		}


		include_once 'classes/class.ilGroupXMLWriter.php';

		$xml_writer = new ilGroupXMLWriter($grp_obj);
		$xml_writer->start();

		$xml = $xml_writer->getXML();

		return strlen($xml) ? $xml : '';
	}


	function assignGroupMember($sid,$group_id,$user_id,$type)
	{
		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->sauth->getMessage(),$this->sauth->getMessageCode());
		}

		if(!is_numeric($group_id))
		{
			return $this->__raiseError('No valid group id given. Please choose an existing reference id of an ILIAS group',
									   'Client');
		}

		// Include main header
		include_once './include/inc.header.php';
		global $rbacsystem;

		if(($obj_type = ilObject::_lookupType(ilObject::_lookupObjId($group_id))) != 'grp')
		{
			$group_id = end($ref_ids = ilObject::_getAllReferences($group_id));
			if(ilObject::_lookupType(ilObject::_lookupObjId($group_id)) != 'grp')
			{
				return $this->__raiseError('Invalid group id. Object with id "'. $group_id.'" is not of type "group"','Client');
			}
		}

		if(!$rbacsystem->checkAccess('write',$group_id))
		{
			return $this->__raiseError('Check access failed. No permission to write to group','Server');
		}


		if(ilObject::_lookupType($user_id) != 'usr')
		{
			return $this->__raiseError('Invalid user id. User with id "'. $user_id.' does not exist','Client');
		}
		if($type != 'Admin' and
		   $type != 'Member')
		{
			return $this->__raiseError('Invalid type '.$type.' given. Parameter "type" must be "Admin","Member"','Client');
		}

		if(!$tmp_group = ilObjectFactory::getInstanceByRefId($group_id,false))
		{
			return $this->__raiseError('Cannot create group instance!','Server');
		}

		if(!$tmp_user = ilObjectFactory::getInstanceByObjId($user_id,false))
		{
			return $this->__raiseError('Cannot create user instance!','Server');
		}


		switch($type)
		{
			case 'Admin':
				return $tmp_group->addMember($user_id,$tmp_group->getDefaultAdminRole());

			case 'Member':
				return $tmp_group->addMember($user_id,$tmp_group->getDefaultMemberRole());
				break;
		}

		return true;
	}

	function excludeGroupMember($sid,$group_id,$user_id)
	{
		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->sauth->getMessage(),$this->sauth->getMessageCode());
		}
		if(!is_numeric($group_id))
		{
			return $this->__raiseError('No valid group id given. Please choose an existing reference id of an ILIAS group',
									   'Client');
		}

		// Include main header
		include_once './include/inc.header.php';
		global $rbacsystem;

		if(($type = ilObject::_lookupType(ilObject::_lookupObjId($group_id))) != 'grp')
		{
			$group_id = end($ref_ids = ilObject::_getAllReferences($group_id));
			if(ilObject::_lookupType(ilObject::_lookupObjId($group_id)) != 'grp')
			{
				return $this->__raiseError('Invalid group id. Object with id "'. $group_id.'" is not of type "group"','Client');
			}
		}

		if(ilObject::_lookupType($user_id) != 'usr')
		{
			return $this->__raiseError('Invalid user id. User with id "'. $user_id.' does not exist','Client');
		}

		if(!$tmp_group = ilObjectFactory::getInstanceByRefId($group_id,false))
		{
			return $this->__raiseError('Cannot create group instance!','Server');
		}

		if(!$rbacsystem->checkAccess('write',$group_id))
		{
			return $this->__raiseError('Check access failed. No permission to write to group','Server');
		}

		$tmp_group->leave($user_id);
		return true;
	}


	function isAssignedToGroup($sid,$group_id,$user_id)
	{
		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->sauth->getMessage(),$this->sauth->getMessageCode());
		}
		if(!is_numeric($group_id))
		{
			return $this->__raiseError('No valid group id given. Please choose an existing id of an ILIAS group',
									   'Client');
		}
		// Include main header
		include_once './include/inc.header.php';
		global $rbacsystem;

		if(($type = ilObject::_lookupType(ilObject::_lookupObjId($group_id))) != 'grp')
		{
			$group_id = end($ref_ids = ilObject::_getAllReferences($group_id));
			if(ilObject::_lookupType(ilObject::_lookupObjId($group_id)) != 'grp')
			{
				return $this->__raiseError('Invalid group id. Object with id "'. $group_id.'" is not of type "group"','Client');
			}
		}

		if(ilObject::_lookupType($user_id) != 'usr')
		{
			return $this->__raiseError('Invalid user id. User with id "'. $user_id.' does not exist','Client');
		}

		if(!$tmp_group = ilObjectFactory::getInstanceByRefId($group_id,false))
		{
			return $this->__raiseError('Cannot create group instance!','Server');
		}

		if(!$rbacsystem->checkAccess('read',$group_id))
		{
			return $this->__raiseError('Check access failed. No permission to read group data','Server');
		}


		if($tmp_group->isAdmin($user_id))
		{
			return 1;
		}
		if($tmp_group->isMember($user_id))
		{
			return 2;
		}
		return "0";
	}

	// PRIVATE

}
?>
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
   * Soap course administration methods
   *
   * @author Stefan Meyer <smeyer@databay.de>
   * @version $Id$
   *
   * @package ilias
   */
include_once './webservice/soap/classes/class.ilSoapAdministration.php';

class ilSoapCourseAdministration extends ilSoapAdministration
{
	function ilSoapCourseAdministration()
	{
		parent::ilSoapAdministration();
	}
		

	// Service methods
	function addCourse($sid,$target_id,$crs_xml)
	{
		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->sauth->getMessage(),$this->sauth->getMessageCode());
		}			

		if(!is_numeric($target_id))
		{
			return $this->__raiseError('No valid target id given. Please choose an existing reference id of an ILIAS category',
									   'Client');
		}

		// Include main header
		include_once './include/inc.header.php';
		global $rbacsystem;

		if(!$target_obj =& ilObjectFactory::getInstanceByRefId($target_id, false))
		{
			return $this->__raiseError('No valid target given.', 'Client');
		}

		if(ilObject::_isInTrash($target_id))
		{
			return $this->__raiseError("Parent with ID $target_id has been deleted.", 'CLIENT_OBJECT_DELETED');
		}

		if(!$rbacsystem->checkAccess('create',$target_id,'crs'))
		{
			return $this->__raiseError('Check access failed. No permission to create courses','Server');
		}

		// Start import
		include_once("Modules/Course/classes/class.ilObjCourse.php");

		$newObj = new ilObjCourse();
		$newObj->setType('crs');
		$newObj->setTitle('dummy');
		$newObj->setDescription("");
		$newObj->create(true); // true for upload
		$newObj->createReference();
		$newObj->putInTree($target_id);
		$newObj->setPermissions($target_id);
		$newObj->initDefaultRoles();

		include_once 'Modules/Course/classes/class.ilCourseXMLParser.php';

		$xml_parser = new ilCourseXMLParser($newObj);
		$xml_parser->setXMLContent($crs_xml);

		$xml_parser->startParsing();

		$newObj->MDUpdateListener('General');

		return $newObj->getRefId() ? $newObj->getRefId() : "0";
		
	}

	function deleteCourse($sid,$course_id)
	{
		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->sauth->getMessage(),$this->sauth->getMessageCode());
		}			

		if(!is_numeric($course_id))
		{
			return $this->__raiseError('No valid course id given. Please choose an existing reference id of an ILIAS course',
									   'Client');
		}

		// Include main header
		include_once './include/inc.header.php';
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		global $rbacsystem;

		if(($obj_type = ilObject::_lookupType(ilObject::_lookupObjId($course_id))) != 'crs')
		{
			$course_id = end($ref_ids = ilObject::_getAllReferences($course_id));
			if(ilObject::_lookupType(ilObject::_lookupObjId($course_id)) != 'crs')
			{
				return $this->__raiseError('Invalid course id. Object with id "'. $course_id.'" is not of type "course"','Client');
			}
		}

		if(!$rbacsystem->checkAccess('delete',$course_id))
		{
			return $this->__raiseError('Check access failed. No permission to delete course','Server');
		}


		global $tree,$rbacadmin,$log;

		if($tree->isDeleted($course_id))
		{
			return $this->__raiseError('Node already deleted','Server');
		}

		$subnodes = $tree->getSubtree($tree->getNodeData($course_id));
		foreach ($subnodes as $subnode)
		{
			$rbacadmin->revokePermission($subnode["child"]);

			// remove item from all user desktops
			$affected_users = ilUtil::removeItemFromDesktops($subnode["child"]);
				
		}
		if(!$tree->saveSubTree($course_id))
		{
			return $this->__raiseError('Node already deleted','Client');
		}
		
		// write log entry
		$log->write("SOAP ilObjectGUI::confirmedDeleteObject(), moved ref_id ".$course_id." to trash");
		
		// remove item from all user desktops
		$affected_users = ilUtil::removeItemFromDesktops($course_id);
		
		return true;
	}

	function assignCourseMember($sid,$course_id,$user_id,$type)
	{
		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->sauth->getMessage(),$this->sauth->getMessageCode());
		}

		if(!is_numeric($course_id))
		{
			return $this->__raiseError('No valid course id given. Please choose an existing reference id of an ILIAS course',
									   'Client');
		}

		// Include main header
		include_once './include/inc.header.php';

		global $rbacsystem;

		if(($obj_type = ilObject::_lookupType(ilObject::_lookupObjId($course_id))) != 'crs')
		{
			$course_id = end($ref_ids = ilObject::_getAllReferences($course_id));
			if(ilObject::_lookupType(ilObject::_lookupObjId($course_id)) != 'crs')
			{
				return $this->__raiseError('Invalid course id. Object with id "'. $course_id.'" is not of type "course"','Client');
			}
		}

		if(!$rbacsystem->checkAccess('write',$course_id))
		{
			return $this->__raiseError('Check access failed. No permission to write to course','Server');
		}

		
		if(ilObject::_lookupType($user_id) != 'usr')
		{
			return $this->__raiseError('Invalid user id. User with id "'. $user_id.' does not exist','Client');
		}
		if($type != 'Admin' and
		   $type != 'Tutor' and
		   $type != 'Member')
		{
			return $this->__raiseError('Invalid type given. Parameter "type" must be "Admin", "Tutor" or "Member"','Client');
		}

		if(!$tmp_course = ilObjectFactory::getInstanceByRefId($course_id,false))
		{
			return $this->__raiseError('Cannot create course instance!','Server');
		}

		if(!$tmp_user = ilObjectFactory::getInstanceByObjId($user_id,false))
		{
			return $this->__raiseError('Cannot create user instance!','Server');
		}

		include_once 'Modules/Course/classes/class.ilCourseParticipants.php';
		
		$course_members = ilCourseParticipants::_getInstanceByObjId($tmp_course->getId());

		switch($type)
		{
			case 'Admin':
				$course_members->add($tmp_user->getId(),IL_CRS_ADMIN);
				$course_members->updateNotification($tmp_user->getId(),1);
				break;

			case 'Tutor':
				$course_members->add($tmp_user->getId(),IL_CRS_TUTOR);
				break;

			case 'Member':
				$course_members->add($tmp_user->getId(),IL_CRS_MEMBER);
				break;
		}

		return true;
	}

	function excludeCourseMember($sid,$course_id,$user_id)
	{
		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->sauth->getMessage(),$this->sauth->getMessageCode());
		}			
		if(!is_numeric($course_id))
		{
			return $this->__raiseError('No valid course id given. Please choose an existing reference id of an ILIAS course',
									   'Client');
		}

		// Include main header
		include_once './include/inc.header.php';
		global $rbacsystem;

		if(($obj_type = ilObject::_lookupType(ilObject::_lookupObjId($course_id))) != 'crs')
		{
			$course_id = end($ref_ids = ilObject::_getAllReferences($course_id));
			if(ilObject::_lookupType(ilObject::_lookupObjId($course_id)) != 'crs')
			{
				return $this->__raiseError('Invalid course id. Object with id "'. $course_id.'" is not of type "course"','Client');
			}
		}

		if(ilObject::_lookupType($user_id) != 'usr')
		{
			return $this->__raiseError('Invalid user id. User with id "'. $user_id.' does not exist','Client');
		}

		if(!$tmp_course = ilObjectFactory::getInstanceByRefId($course_id,false))
		{
			return $this->__raiseError('Cannot create course instance!','Server');
		}

		if(!$rbacsystem->checkAccess('write',$course_id))
		{
			return $this->__raiseError('Check access failed. No permission to write to course','Server');
		}

		include_once 'Modules/Course/classes/class.ilCourseParticipants.php';
		
		$course_members = ilCourseParticipants::_getInstanceByObjId($tmp_course->getId());
		if(!$course_members->checkLastAdmin(array($user_id)))
		{
			return $this->__raiseError('Cannot deassign last administrator from course','Server');
		}

		$course_members->delete($user_id);

		return true;
	}

	
	function isAssignedToCourse($sid,$course_id,$user_id)
	{
		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->sauth->getMessage(),$this->sauth->getMessageCode());
		}			
		if(!is_numeric($course_id))
		{
			return $this->__raiseError('No valid course id given. Please choose an existing reference id of an ILIAS course',
									   'Client');
		}
		// Include main header
		include_once './include/inc.header.php';
		global $rbacsystem;

		if(($obj_type = ilObject::_lookupType(ilObject::_lookupObjId($course_id))) != 'crs')
		{
			$course_id = end($ref_ids = ilObject::_getAllReferences($course_id));
			if(ilObject::_lookupType(ilObject::_lookupObjId($course_id)) != 'crs')
			{
				return $this->__raiseError('Invalid course id. Object with id "'. $course_id.'" is not of type "course"','Client');
			}
		}

		if(ilObject::_lookupType($user_id) != 'usr')
		{
			return $this->__raiseError('Invalid user id. User with id "'. $user_id.' does not exist','Client');
		}

		if(!$tmp_course = ilObjectFactory::getInstanceByRefId($course_id,false))
		{
			return $this->__raiseError('Cannot create course instance!','Server');
		}

		if(!$rbacsystem->checkAccess('write',$course_id))
		{
			return $this->__raiseError('Check access failed. No permission to write to course','Server');
		}

		include_once './Modules/Course/classes/class.ilCourseParticipants.php';
		$crs_members = ilCourseParticipants::_getInstanceByObjId($tmp_course->getId());
		
		if($crs_members->isAdmin($user_id))
		{
			return IL_CRS_ADMIN;
		}
		if($crs_members->isTutor($user_id))
		{
			return IL_CRS_TUTOR;
		}
		if($crs_members->isMember($user_id))
		{
			return IL_CRS_MEMBER;
		}

		return "0";
	}


	function getCourseXML($sid,$course_id)
	{
		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->sauth->getMessage(),$this->sauth->getMessageCode());
		}			
		if(!is_numeric($course_id))
		{
			return $this->__raiseError('No valid course id given. Please choose an existing reference id of an ILIAS course',
									   'Client');
		}

		// Include main header
		include_once './include/inc.header.php';

		global $rbacsystem;

		if(($obj_type = ilObject::_lookupType(ilObject::_lookupObjId($course_id))) != 'crs')
		{
			$course_id = end($ref_ids = ilObject::_getAllReferences($course_id));
			if(ilObject::_lookupType(ilObject::_lookupObjId($course_id)) != 'crs')
			{
				return $this->__raiseError('Invalid course id. Object with id "'. $course_id.'" is not of type "course"','Client');
			}
		}

		if(!$tmp_course = ilObjectFactory::getInstanceByRefId($course_id,false))
		{
			return $this->__raiseError('Cannot create course instance!','Server');
		}

		if(!$rbacsystem->checkAccess('read',$course_id))
		{
			return $this->__raiseError('Check access failed. No permission to read course','Server');
		}

		include_once 'Modules/Course/classes/class.ilCourseXMLWriter.php';

		$xml_writer = new ilCourseXMLWriter($tmp_course);
		$xml_writer->start();
		
		return $xml_writer->getXML();
	}

	function updateCourse($sid,$course_id,$xml)
	{
		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->sauth->getMessage(),$this->sauth->getMessageCode());
		}			
		
		if(!is_numeric($course_id))
		{
			return $this->__raiseError('No valid course id given. Please choose an existing reference id of an ILIAS course',
									   'Client');
		}

		// Include main header
		include_once './include/inc.header.php';

		global $rbacsystem;

		if(($obj_type = ilObject::_lookupType(ilObject::_lookupObjId($course_id))) != 'crs')
		{
			$course_id = end($ref_ids = ilObject::_getAllReferences($course_id));
			if(ilObject::_lookupType(ilObject::_lookupObjId($course_id)) != 'crs')
			{
				return $this->__raiseError('Invalid course id. Object with id "'. $course_id.'" is not of type "course"','Client');
			}
		}

		if(!$tmp_course = ilObjectFactory::getInstanceByRefId($course_id,false))
		{
			return $this->__raiseError('Cannot create course instance!','Server');
		}

		if(!$rbacsystem->checkAccess('write',$course_id))
		{
			return $this->__raiseError('Check access failed. No permission to write course','Server');
		}


		// First delete old meta data
		include_once 'Services/MetaData/classes/class.ilMD.php';

		$md = new ilMD($tmp_course->getId(),0,'crs');
		$md->deleteAll();

		include_once 'Modules/Course/classes/class.ilCourseParticipants.php';
		ilCourseParticipants::_deleteAllEntries($tmp_course->getId());


		include_once 'Modules/Course/classes/class.ilCourseWaitingList.php';
		ilCourseWaitingList::_deleteAll($tmp_course->getId());

		include_once 'Modules/Course/classes/class.ilCourseXMLParser.php';

		$xml_parser = new ilCourseXMLParser($tmp_course);
		$xml_parser->setXMLContent($xml);

		$xml_parser->startParsing();

		$tmp_course->MDUpdateListener('General');

		return true;
	}

	// PRIVATE

}
?>
<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/WebServices/ECS/interfaces/interface.ilECSCommandQueueHandler.php';

/**
 * Description of class
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilECSCmsCourseMemberCommandQueueHandler implements ilECSCommandQueueHandler
{
	private $server = null;
	private $mid = 0;
	
	private $mapping = null;
	
	
	/**
	 * Constructor
	 */
	public function __construct(ilECSSetting $server)
	{
		$this->server = $server;
		$this->init();
	}
	
	/**
	 * Get server
	 * @return ilECSServerSetting
	 */
	public function getServer()
	{
		return $this->server;
	}
	
	
	/**
	 * Get mapping settings
	 * @return ilECSnodeMappingSettings
	 */
	public function getMappingSettings()
	{
		return $this->mapping;
	}
	
	/**
	 * Check if course allocation is activated for one recipient of the 
	 * @param ilECSSetting $server
	 * @param type $a_content_id
	 */
	public function checkAllocationActivation(ilECSSetting $server, $a_content_id)
	{
		include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSNodeMappingSettings.php';
		$gl_settings = ilECSNodeMappingSettings::getInstance();
		return $gl_settings->isCourseAllocationEnabled();
	}


	/**
	 * Handle create
	 * @param ilECSSetting $server
	 * @param type $a_content_id
	 */
	public function handleCreate(ilECSSetting $server, $a_content_id)
	{
		include_once './Services/WebServices/ECS/classes/Tree/class.ilECSCmsData.php';
		include_once './Services/WebServices/ECS/classes/Tree/class.ilECSCmsTree.php';
		include_once './Services/WebServices/ECS/classes/Course/class.ilECSCourseConnector.php';

		if(!$this->checkAllocationActivation($server, $a_content_id))
		{
			return true;
		}
		try 
		{
			$course_member = $this->readCourseMember($server,$a_content_id);
			$this->doUpdate($a_content_id, $course_member);
			return true;
		}
		catch(ilECSConnectorException $e) 
		{
			$GLOBALS['ilLog']->write(__METHOD__.': Course member creation failed  with mesage ' . $e->getMessage());
			return false;
		}
		return true;
	}

	/**
	 * Handle delete
	 * @param ilECSSetting $server
	 * @param type $a_content_id
	 */
	public function handleDelete(ilECSSetting $server, $a_content_id)
	{
		// nothing todo
		return true;
	}

	/**
	 * Handle update
	 * @param ilECSSetting $server
	 * @param type $a_content_id
	 */
	public function handleUpdate(ilECSSetting $server, $a_content_id)
	{
		if(!$this->checkAllocationActivation($server, $a_content_id))
		{
			return true;
		}
		
		try 
		{
			$course_member = $this->readCourseMember($server,$a_content_id);
			$this->doUpdate($a_content_id, $course_member);
			return true;
		}
		catch(ilECSConnectorException $e) 
		{
			$GLOBALS['ilLog']->write(__METHOD__.': Course member update failed  with mesage ' . $e->getMessage());
			return false;
		}
		return true;
	}
	
	/**
	 * init handler
	 */
	private function init()
	{
		include_once './Services/WebServices/ECS/classes/class.ilECSParticipantSettings.php';
		$this->mid = ilECSParticipantSettings::loookupCmsMid($this->getServer()->getServerId());
		
		include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSNodeMappingSettings.php';
		$this->mapping = ilECSNodeMappingSettings::getInstance();
		
	}
	
	/**
	 * Perform update
	 * @param type $a_content_id
	 * @param type $course
	 */
	protected function doUpdate($a_content_id, $course_member)
	{
		$GLOBALS['ilLog']->write(__METHOD__.': Starting course member update');
		
		$course_id = (int) $course_member->courseID;
		if(!$course_id)
		{
			$GLOBALS['ilLog']->write(__METHOD__.': Missing course id');
			return false;
		}
		include_once './Services/WebServices/ECS/classes/class.ilECSImport.php';
		$crs_obj_id = ilECSImport::_lookupObjId($this->getServer()->getServerId(), $course_id, $this->mid);
		
		if(!$crs_obj_id)
		{
			$GLOBALS['ilLog']->write(__METHOD__.': Missing assigned course with id '. $course_id);
			return false;
		}
		
		// Lookup already imported users and update their status
		$this->refreshAssignmentStatus($course_member,$crs_obj_id);
		return true;
	}
	
	
	/**
	 * Refresh status of course member assignments
	 * @param type $course_member
	 * @param type $obj_id
	 */
	protected function refreshAssignmentStatus($course_member, $obj_id)
	{
		include_once './Services/WebServices/ECS/classes/Course/class.ilECSCourseMemberAssignment.php';
		
		include_once './Modules/Course/classes/class.ilCourseParticipants.php';
		$part = ilCourseParticipants::_getInstanceByObjId($obj_id);
		
		$person_ids = array();
		foreach ((array) $course_member->members as $person)
		{
			$person_ids[] = $person->personID;
		}

		$course_id = (int) $course_member->courseID;
		$usr_ids = ilECSCourseMemberAssignment::lookupUserIds(
				$course_id,
				$obj_id);
		
		// Delete remote deleted
		foreach((array) $usr_ids as $usr_id)
		{
			if(!in_array($usr_id, $person_ids))
			{
				$ass = ilECSCourseMemberAssignment::lookupAssignment($course_id, $obj_id, $usr_id);
				if($ass instanceof ilECSCourseMemberAssignment)
				{
					$acc = ilObjUser::_checkExternalAuthAccount(
							ilECSSetting::lookupAuthMode(),
							(string) $usr_id);
					if($il_usr_id = ilObjUser::_lookupId($acc))
					{
						// this removes also admin, tutor roles
						$part->delete($il_usr_id);
						$GLOBALS['ilLog']->write(__METHOD__.': Deassigning user ' . $usr_id. ' '. 'from course '. ilObject::_lookupTitle($obj_id));
					}
					else
					{
						$GLOBALS['ilLog']->write(__METHOD__.': Deassigning unknown ILIAS user ' . $usr_id. ' '. 'from course '. ilObject::_lookupTitle($obj_id));
					}

					$ass->delete();
				}
			}
		}
		
		// Assign new participants
		foreach((array) $course_member->members as $person)
		{
			$role = $this->lookupCourseRole($person->courseRole);
			$acc = ilObjUser::_checkExternalAuthAccount(
					ilECSSetting::lookupAuthMode(),
					(string) $person->personID);
			$GLOBALS['ilLog']->write(__METHOD__.': Handling user '. (string) $person->personID);
			
			if(in_array($person->personID, $usr_ids))
			{
				if($il_usr_id = ilObjUser::_lookupId($acc))
				{
					$GLOBALS['ilLog']->write(__METHOD__.': '. print_r($role,true));
					$part->updateRoleAssignments($il_usr_id, array($role));
					// Nothing to do, user is member or is locally deleted
				}
			}
			else
			{
				if($il_usr_id = ilObjUser::_lookupId($acc))
				{
					
					if($role)
					{
					// Add user
						$GLOBALS['ilLog']->write(__METHOD__.': Assigning new user ' . $person->personID. ' '. 'to course '. ilObject::_lookupTitle($obj_id));
						$part->add($il_usr_id,$role);
					}
					
				}
				else
				{
					// @todo 
					$GLOBALS['ilLog']->write(__METHOD__.': Unknown ILIAS User ' . $person->personID. ' '. ' marked as member for course '. ilObject::_lookupTitle($obj_id));
				}
				
				$assignment = new ilECSCourseMemberAssignment();
				$assignment->setServer($this->getServer()->getServerId());
				$assignment->setMid($this->mid);
				$assignment->setCmsId($course_id);
				$assignment->setObjId($obj_id);
				$assignment->setUid($person->personID);
				$assignment->save();
			}
		}
		return true;
	}
	
	protected function lookupCourseRole($role_value)
	{
		$role_mappings = $this->getMappingSettings()->getRoleMappings();
		
		if(!$role_value)
		{
			$GLOBALS['ilLog']->write(__METHOD__.': no role assignment missing attribute courseRole');
			return 0;
		}
		foreach($role_mappings as $name => $map)
		{
			if($role_value == $map)
			{
				return $name;
			}
		}
		$GLOBALS['ilLog']->write(__METHOD__.': No role assignment mapping for role ' . $role_value);
		return 0;
	}
	

	/**
	 * Read course from ecs
	 * @return boolean
	 */
	private function readCourseMember(ilECSSetting $server, $a_content_id)
	{
		try 
		{
			include_once './Services/WebServices/ECS/classes/Course/class.ilECSCourseMemberConnector.php';
			$crs_member_reader = new ilECSCourseMemberConnector($server);
			return $crs_member_reader->getCourseMember($a_content_id);
		}
		catch(ilECSConnectorException $e) 
		{
			throw $e;
		}
	}
}
?>

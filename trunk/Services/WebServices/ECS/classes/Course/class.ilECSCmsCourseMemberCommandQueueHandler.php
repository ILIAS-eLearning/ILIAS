<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSNodeMappingSettings.php';
include_once './Services/WebServices/ECS/interfaces/interface.ilECSCommandQueueHandler.php';
include_once './Services/WebServices/ECS/classes/class.ilECSParticipantSettings.php';
include_once './Services/WebServices/ECS/classes/class.ilECSParticipantSetting.php';

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
	 * get current mid
	 * @return int
	 */
	public function getMid()
	{
		return $this->mid;
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
		try 
		{
			include_once './Services/WebServices/ECS/classes/Course/class.ilECSCourseMemberConnector.php';
			$crsm_reader = new ilECSCourseMemberConnector($server);
			$details = $crsm_reader->getCourseMember($a_content_id,true);
			$this->mid = $details->getMySender();
			
			// Check if import is enabled
			include_once './Services/WebServices/ECS/classes/class.ilECSParticipantSetting.php';
			$part = ilECSParticipantSetting::getInstance($this->getServer()->getServerId(), $this->getMid());
			if(!$part->isImportEnabled())
			{
				$GLOBALS['ilLog']->write(__METHOD__.': Import disabled for mid '.$this->getMid());
				return false;
			}
			// Check course allocation setting
			include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSNodeMappingSettings.php';
			$this->mapping = ilECSNodeMappingSettings::getInstanceByServerMid(
					$this->getServer()->getServerId(),
					$this->getMid()
				);
			return $this->getMappingSettings()->isCourseAllocationEnabled();
		}
		catch(ilECSConnectorException $e) 
		{
			$GLOBALS['ilLog']->write(__METHOD__.': Reading course member details failed with message '. $e->getMessage());
			return false;
		}
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
			//$course = $this->readCourse($server, $a_content_id);
			$course_member = $this->readCourseMember($server,$a_content_id);
			$this->doUpdate($a_content_id,$course_member);
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
	 * Perform update
	 * @param type $a_content_id
	 */
	protected function doUpdate($a_content_id, $course_member)
	{
		$GLOBALS['ilLog']->write(__METHOD__.': Starting ecs  member update');
		
		$course_id = (int) $course_member->lectureID;
		if(!$course_id)
		{
			$GLOBALS['ilLog']->write(__METHOD__.': Missing course id');
			return false;
		}
		include_once './Services/WebServices/ECS/classes/class.ilECSImport.php';
		$GLOBALS['ilLog']->write(__METHOD__.': sid: '.$this->getServer()->getServerId().' course_id: '.$course_id.' mid: '.$this->mid);
		//$crs_obj_id = ilECSImport::_lookupObjId($this->getServer()->getServerId(), $course_id, $this->mid);
		$crs_obj_id = ilECSImport::lookupObjIdByContentId($this->getServer()->getServerId(), $this->mid, $course_id);
		
		if(!$crs_obj_id)
		{
			// check for parallel scenario iv and create courses
			$GLOBALS['ilLog']->write(__METHOD__.': Missing assigned course with id '. $course_id);
			return false;
		}
		
		$course = $this->readCourse($course_member);
		// Lookup already imported users and update their status
		$assignments = $this->readAssignments($course,$course_member);
		
		// iterate through all parallel groups
		foreach((array) $assignments as $cms_id => $assigned)
		{
			$sub_id = ($cms_id == $course_id) ? 0 : $cms_id;
			
			include_once './Services/WebServices/ECS/classes/class.ilECSImport.php';
			$obj_id = ilECSImport::lookupObjIdByContentId(
					$this->getServer()->getServerId(),
					$this->getMid(),
					$course_id,
					$sub_id);
					
			$this->refreshAssignmentStatus($course_member, $obj_id, $sub_id, $assigned);
		}
		return true;
	}
	
	/**
	 * Read assignments for all parallel groups
	 * @param type $course
	 * @param type $course_member
	 */
	protected function readAssignments($course,$course_member)
	{
		$put_in_course = true;
		
		include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSMappingUtils.php';
		switch((int) $course->groupScenario)
		{
			case ilECSMappingUtils::PARALLEL_UNDEFINED:
				$GLOBALS['ilLog']->write(__METHOD__.': No parallel group scenario defined.');
				$put_in_course = true;
				break;
				
			case ilECSMappingUtils::PARALLEL_ONE_COURSE:
				$GLOBALS['ilLog']->write(__METHOD__.': Parallel group scenario one course.');
				$put_in_course = true;
				break;
				
			case ilECSMappingUtils::PARALLEL_GROUPS_IN_COURSE:
				$GLOBALS['ilLog']->write(__METHOD__.': Parallel group scenario groups in courses.');
				$put_in_course = false;
				break;
				
			case ilECSMappingUtils::PARALLEL_ALL_COURSES:
				$GLOBALS['ilLog']->write(__METHOD__.': Parallel group scenario only courses.');
				$put_in_course = false;
				break;
			
			default:
				$GLOBALS['ilLog']->write(__METHOD__.': Parallel group scenario undefined.');
				$put_in_course = true;
				break;
		}
		
		$course_id = $course_member->lectureID;
		$assigned = array();
		foreach((array) $course_member->members as $member)
		{
			$assigned[$course_id][$member->personID] = array(
				'id' => $member->personID,
				'role' => $member->role
			);
			
			foreach((array) $member->groups as $pgroup)
			{
				if(!$put_in_course)
				{
					// @todo check hierarchy of roles
					$assigned[$pgroup->num][$member->personID] = array(
						'id' => $member->personID,
						'role' => $pgroup->role
					);
				}
			}
		}
		$GLOBALS['ilLog']->write(__METHOD__.': ECS member assignments '.print_r($assigned,true));
		return $assigned;
	}
	
	
	
	/**
	 * Refresh status of course member assignments
	 * @param object $course_member
	 * @param int $obj_id
	 */
	protected function refreshAssignmentStatus($course_member, $obj_id, $sub_id, $assigned)
	{
		include_once './Services/WebServices/ECS/classes/Course/class.ilECSCourseMemberAssignment.php';
		
		$type = ilObject::_lookupType($obj_id);
		if($type == 'crs')
		{
			include_once './Modules/Course/classes/class.ilCourseParticipants.php';
			$part = ilCourseParticipants::_getInstanceByObjId($obj_id);
		}
		else
		{
			include_once './Modules/Group/classes/class.ilGroupParticipants.php';
			$part = ilGroupParticipants::_getInstanceByObjId($obj_id);
		}
		
		

		$course_id = (int) $course_member->lectureID;
		$usr_ids = ilECSCourseMemberAssignment::lookupUserIds(
				$course_id,
				$sub_id,
				$obj_id);
		
		// Delete remote deleted
		foreach((array) $usr_ids as $usr_id)
		{
			if(!isset($assigned[$usr_id]))
			{
				$ass = ilECSCourseMemberAssignment::lookupAssignment($course_id, $sub_id,$obj_id, $usr_id);
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
		foreach((array) $assigned as $person_id => $person)
		{
			$role = $this->lookupRole($person['role']);
			$role_info = ilECSMappingUtils::getRoleMappingInfo($role);
			
			$acc = ilObjUser::_checkExternalAuthAccount(
					ilECSSetting::lookupAuthMode(),
					(string) $person_id);
			$GLOBALS['ilLog']->write(__METHOD__.': Handling user '. (string) $person_id);
			
			if(in_array($person_id, $usr_ids))
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
						$GLOBALS['ilLog']->write(__METHOD__.': Assigning new user ' . $person_id. ' '. 'to '. ilObject::_lookupTitle($obj_id));
						$part->add($il_usr_id,$role);
					}
					
				}
				else
				{
					if($role_info['create'])
					{
						$this->createMember($person_id);
						$GLOBALS['ilLog']->write(__METHOD__.': Added new user '. $person_id);
					}
					// Assign to role
					if($role)
					{
						$acc = ilObjUser::_checkExternalAuthAccount(
								ilECSSetting::lookupAuthMode(),
								(string) $person_id);

						if($il_usr_id = ilObjUser::_lookupId($acc))
						{
							$part->add($il_usr_id,$role);
						}
					}
				}
				
				$assignment = new ilECSCourseMemberAssignment();
				$assignment->setServer($this->getServer()->getServerId());
				$assignment->setMid($this->mid);
				$assignment->setCmsId($course_id);
				$assignment->setCmsSubId($sub_id);
				$assignment->setObjId($obj_id);
				$assignment->setUid($person_id);
				$assignment->save();
			}
		}
		return true;
	}
	
	protected function lookupRole($role_value)
	{
		$role_mappings = $this->getMappingSettings()->getRoleMappings();
		
		/* Zero is an allowed value */
		if(!$role_value)
		{
 			//$GLOBALS['ilLog']->write(__METHOD__.': No role assignment missing attribute: role');
			//return 0;
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
	 * Create user account
	 * @param type $a_person_id
	 */
	private function createMember($a_person_id)
	{
		try
		{
			include_once './Services/LDAP/classes/class.ilLDAPServer.php';
			$server = ilLDAPServer::getInstanceByServerId(ilLDAPServer::_getFirstActiveServer());
			$server->doConnectionCheck();

			include_once './Services/LDAP/classes/class.ilLDAPQuery.php';
			$query = new ilLDAPQuery($server);
			$query->bind(IL_LDAP_BIND_DEFAULT);
			
			$users = $query->fetchUser($a_person_id);
			if($users)
			{
				include_once './Services/LDAP/classes/class.ilLDAPAttributeToUser.php';
				$xml = new ilLDAPAttributeToUser($server);
				$xml->setNewUserAuthMode($server->getAuthenticationMappingKey());
				$xml->setUserData($users);
				$xml->refresh();
			}

		}
		catch (ilLDAPQueryException $exc)
		{
			$this->log->write($exc->getMessage());
		}
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
			
			$member = $crs_member_reader->getCourseMember($a_content_id);
			//$GLOBALS['ilLog']->write(__METHOD__.': ??????????? crs member:' . print_r($member,true));
			//$GLOBALS['ilLog']->write(__METHOD__.': ??????????? content id :' . $a_content_id);
			return $member;
		}
		catch(ilECSConnectorException $e) 
		{
			throw $e;
		}
	}
	
	/**
	 * Read course from ecs
	 * @return boolean
	 */
	private function readCourse($course_member)
	{
		try 
		{
			include_once './Services/WebServices/ECS/classes/class.ilECSImport.php';
			$ecs_id = ilECSImport::lookupEContentIdByContentId(
					$this->getServer()->getServerId(),
					$this->getMid(),
					$course_member->lectureID
			);
			
			include_once './Services/WebServices/ECS/classes/Course/class.ilECSCourseConnector.php';
			$crs_reader = new ilECSCourseConnector($this->getServer());
			return $crs_reader->getCourse($ecs_id);
		}
		catch(ilECSConnectorException $e) 
		{
			throw $e;
		}
	}
	
}
?>

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
		include_once './Modules/Course/classes/class.ilCourseParticipants.php';
		$part = ilCourseParticipants::_getInstanceByObjId($crs_obj_id);
		
		foreach((array) $course_member->members as $person)
		{
			$acc = ilObjUser::_checkExternalAuthAccount('ldap', (string) $person->personID);
			
			if(!$acc)
			{
				$GLOBALS['ilLog']->write(__METHOD__.': User '. $person->personID . ' has no ILIAS account.');
			}
			$usr_id = ilObjUser::_lookupId($acc);
			
			if(!$usr_id)
			{
				$GLOBALS['ilLog']->write(__METHOD__.': User '. $person->personID . ' not found.');
			}
			
			if(!$part->isAssigned($usr_id))
			{
				$GLOBALS['ilLog']->write(__METHOD__.': Assigning user '. $acc);
				$part->add($usr_id,IL_CRS_MEMBER);
			}
			else
			{
				$GLOBALS['ilLog']->write(__METHOD__.': User '. $person->personID .' is already assigned');
			}
		}
		return true;
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

<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/WebServices/ECS/interfaces/interface.ilECSCommandQueueHandler.php';

/**
 * Description of class
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilECSCmsCourseCommandQueueHandler implements ilECSCommandQueueHandler
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
			$course = $this->readCourse($server,$a_content_id);
			$GLOBALS['ilLog']->write(__METHOD__.': '. print_r($course,true));
			$this->doUpdate($a_content_id, $course);
			return true;
		}
		catch(ilECSConnectorException $e) 
		{
			$GLOBALS['ilLog']->write(__METHOD__.': Course creation failed  with mesage ' . $e->getMessage());
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
			$course = $this->readCourse($server,$a_content_id);
			$this->doUpdate($a_content_id, $course);
			return true;
		}
		catch(ilECSConnectorException $e) 
		{
			$GLOBALS['ilLog']->write(__METHOD__.': Course creation failed  with mesage ' . $e->getMessage());
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
	protected function doUpdate($a_content_id, $course)
	{
		$GLOBALS['ilLog']->write(__METHOD__.': Starting course creation/update');
		
		include_once './Services/WebServices/ECS/classes/Course/class.ilECSCourseCreationHandler.php';
		$creation_handler = new ilECSCourseCreationHandler($this->getServer(),$this->mid);
		$creation_handler->handle($a_content_id, $course);
	}
	

	/**
	 * Read course from ecs
	 * @return boolean
	 */
	private function readCourse(ilECSSetting $server, $a_content_id)
	{
		try 
		{
			include_once './Services/WebServices/ECS/classes/Course/class.ilECSCourseConnector.php';
			$crs_reader = new ilECSCourseConnector($server);
			return $crs_reader->getCourse($a_content_id);
		}
		catch(ilECSConnectorException $e) 
		{
			throw $e;
		}
		
	}
}
?>

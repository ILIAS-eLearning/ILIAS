<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/WebServices/ECS/interfaces/interface.ilECSCommandQueueHandler.php';
include_once './Services/WebServices/ECS/classes/class.ilECSSetting.php';
include_once './Services/WebServices/ECS/classes/class.ilECSParticipantSetting.php';


/**
 * Description of class
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilECSEnrolmentStatusCommandQueueHandler implements ilECSCommandQueueHandler
{
	private $server = null;
	private $mid = 0;
	
	
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
	 * Get mid
	 * @return type
	 */
	public function getMid()
	{
		return $this->mid;
	}
	
	/**
	 * Check if course allocation is activated for one recipient of the 
	 * @param ilECSSetting $server
	 * @param type $a_content_id
	 */
	public function checkAllocationActivation(ilECSSetting $server, $a_content_id)
	{
		
	}


	/**
	 * Handle create
	 * @param ilECSSetting $server
	 * @param type $a_content_id
	 */
	public function handleCreate(ilECSSetting $server, $a_content_id)
	{

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
		// Shouldn't happen
		return true;
	}
	
	
	/**
	 * Perform update
	 * @param type $a_content_id
	 * @param type $course
	 */
	protected function doUpdate($a_content_id, $course)
	{
	}
	

	/**
	 * Read course from ecs
	 * @return boolean
	 */
	private function readCourse(ilECSSetting $server, $a_content_id, $a_details = false)
	{
	}
}
?>

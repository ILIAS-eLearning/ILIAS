<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Abstract class for component tasks
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
abstract class ilSCComponentTaskGUI
{
	protected $ctrl;
	protected $lng;
	
	
	/**
	 * 
	 */
	public function __construct()
	{
		$this->ctrl = $GLOBALS['ilCtrl'];
		$this->lng = $GLOBALS['lng'];
	}
	
	/**
	 * Start validation of one task by identifier
	 * @return bool
	 */
	abstract protected function startTask($a_task_identifier);
	
	/**
	 * Defines whether a task supports repairing
	 * @return bool
	 */
	abstract protected function supportsRepairing($a_task_identifier);
	
	
	/**
	 * Start repair task
	 */
	abstract protected function repairTask($a_task_identifier);
	
	
	/**
	 * Get language
	 * @return ilLanguage
	 */
	protected function getLang()
	{
		return $this->lng;
	}
	
	/**
	 * Get ctrl
	 * @return ilCtrl
	 */
	protected function getCtrl()
	{
		return $this->ctrl;
	}

	/**
	 * Execute command
	 */
	public function executeCommand()
	{
		$next_class = $this->getCtrl()->getNextClass($this);
		$cmd        = $this->getCtrl()->getCmd();

		switch($next_class)
		{
			default:
				$this->$cmd();
				break;
		}
		
	}
	
}

?>
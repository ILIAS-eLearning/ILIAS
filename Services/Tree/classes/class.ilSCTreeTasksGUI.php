<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/SystemCheck/classes/class.ilSCComponentTaskGUI.php';

/**
 * Handles tree tasks
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @ilCtrl_isCalledBy ilSCTreeTasksGUI: ilObjSystemCheckGUI
 * 
 */
class ilSCTreeTasksGUI extends ilSCComponentTaskGUI
{
	const TYPE_DUPLICATES = 'duplicates';
	const TYPE_DUMP = 'dump';
	const TYPE_MISSING = 'missing';
	
	
	/**
	 * Get title of task
	 */
	public function getTitle()
	{
		switch($this->getTask()->getIdentifier())
		{
			case self::TYPE_DUMP:
				return $this->getLang()->txt('sysc_task_tree_dump');
				
			case self::TYPE_DUPLICATES:
				return $this->getLang()->txt('sysc_task_tree_duplicates');
				
			case self::TYPE_MISSING:
				return $this->getLang()->txt('sysc_task_tree_missing');
				
		}
	}
	
	/**
	 * Get title of task
	 */
	public function getDescription()
	{
		switch($this->getTask()->getIdentifier())
		{
			case self::TYPE_DUMP:
				return $this->getLang()->txt('sysc_task_tree_dump_desc');
				
			case self::TYPE_DUPLICATES:
				return $this->getLang()->txt('sysc_task_tree_duplicates_desc');

			case self::TYPE_MISSING:
				return $this->getLang()->txt('sysc_task_tree_missing_desc');
		}
	}

	/**
	 * get actions for table gui
	 */
	public function getActions()
	{
		$repair = FALSE;
		if($this->getTask()->getStatus() == ilSCTask::STATUS_FAILED)
		{
			$repair = TRUE;
		}
		
		$actions = array();
		switch($this->getTask()->getIdentifier())
		{
			case self::TYPE_DUPLICATES:
				
				$actions[] = array(
					'txt' => $this->getLang()->txt('sysc_action_validate'),
					'command' => 'validateDuplicates'
				);
				
				if($repair)
				{
					$actions[] = array(
						'txt' => $this->getLang()->txt('sysc_action_repair'),
						'command' => 'repairDuplicates'
					);
				}
				break;
				
			case self::TYPE_DUMP:
				
				include_once './Services/Repository/classes/class.ilValidator.php';
				$validator = new ilValidator();
				if($validator->hasScanLog())
				{
					$actions[] = array(
						'txt' => $this->getLang()->txt('sysc_action_show_tree'),
						'command' => 'showTree'
					);
				}
				
				$actions[] = array(
					'txt' => $this->getLang()->txt('sysc_action_list_tree'),
					'command' => 'listTree'
				);
				break;
				
			case self::TYPE_MISSING:

				$actions[] = array(
					'txt' => $this->getLang()->txt('sysc_action_validate'),
					'command' => 'findMissing'
				);
				
				if($repair)
				{
					$actions[] = array(
						'txt' => $this->getLang()->txt('sysc_action_repair'),
						'command' => 'confirmRepairMissing'
					);
				}
				break;
				
				
		}
		return $actions;
	}
	

	/**
	 * List tree
	 */
	public function listTree()
	{
		include_once './Services/Repository/classes/class.ilValidator.php';
		$validator = new ilValidator(TRUE);
		$errors_count = $validator->dumpTree();
		
		
		$GLOBALS['ilLog']->write(print_r($this->getTask(),TRUE));
		
		if($errors_count)
		{
			$this->getTask()->setStatus(ilSCTask::STATUS_FAILED);
			ilUtil::sendFailure($this->getLang()->txt('sysc_tree_list_failures').' '.$errors_count,TRUE);
		}
		else
		{
			$this->getTask()->setStatus(ilSCTask::STATUS_COMPLETED);
			ilUtil::sendFailure($this->getLang()->txt('sysc_messaage_success'),TRUE);
		}
		
		$this->getTask()->setLastUpdate(new ilDateTime(time(),IL_CAL_UNIX));
		$this->getTask()->update();
		
		$this->getCtrl()->returnToParent($this);		
		

	}

	/**
	 * Show already scanned tree
	 */
	public function showTree()
	{
		include_once "./Services/Repository/classes/class.ilValidator.php";
		$validator = new ilValidator();
		$scan_log = $validator->readScanLog();

		if (is_array($scan_log))
		{
			$scan_log = '<pre>'.implode("",$scan_log).'</pre>';
			$GLOBALS['tpl']->setContent($scan_log);
		}
	}
	
	
	/**
	 * start task
	 * @param type $a_task_identifier
	 */
	public function validateDuplicates()
	{
		include_once './Services/Tree/classes/class.ilSCTreeTasks.php';
		$tasks = new ilSCTreeTasks($this->getTask());
		$num_failures = $tasks->validateDuplicates();
		
		if($this->getTask()->getStatus() == ilSCTask::STATUS_FAILED)
		{
			// error message
			ilUtil::sendFailure($this->getLang()->txt('sysc_tree_duplicate_failures').' '.$num_failures,TRUE);
		}
		else
		{
			ilUtil::sendSuccess($this->getLang()->txt('sysc_message_success'),TRUE);
		}
		$this->getCtrl()->returnToParent($this);		
	}
	
	
	
	/**
	 * repair
	 * @param type $a_task_identifier
	 */
	protected function repairTask()
	{
		$GLOBALS['tpl']->setContent('Hallo');
	}
	
	/**
	 * find missing objects
	 */
	protected function findMissing()
	{
		include_once './Services/Tree/classes/class.ilSCTreeTasks.php';
		$tasks = new ilSCTreeTasks($this->getTask());
		$num_failures = $tasks->findMissing();
		
		if($this->getTask()->getStatus() == ilSCTask::STATUS_FAILED)
		{
			// error message
			ilUtil::sendFailure($this->getLang()->txt('sysc_tree_missing_failures').' '.$num_failures,TRUE);
		}
		else
		{
			ilUtil::sendSuccess($this->getLang()->txt('sysc_message_success'),TRUE);
		}
		$this->getCtrl()->returnToParent($this);		
	}
	
	
	
	/**
	 * Show repair missing confirmation
	 * @return type
	 */
	protected function confirmRepairMissing()
	{
		return $this->showSimpleConfirmation(
				$this->getLang()->txt('sysc_message_tree_missing_confirm'),
				$this->getLang()->txt('sysc_btn_tree_missing'),
				'repairMissing'
		);
	}

	/**
	 * REpair missing oobjects
	 */
	protected function repairMissing()
	{
		include_once './Services/Tree/classes/class.ilSCTreeTasks.php';
		$tasks = new ilSCTreeTasks($this->getTask());
		$tasks->repairMissing();
		
		
		$this->getTask()->setStatus(ilSCTask::STATUS_COMPLETED);
		$this->getTask()->setLastUpdate(new ilDateTime(time(),IL_CAL_UNIX));
		$this->getTask()->update();
		
		ilUtil::sendSuccess($this->getLang()->txt('sysc_message_success'),TRUE);
		$this->getCtrl()->returnToParent($this);		
	}

}
?>
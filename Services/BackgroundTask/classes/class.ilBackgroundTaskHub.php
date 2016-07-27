<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/BackgroundTask/classes/class.ilBackgroundTask.php";

/**
 * background task hub (aka ajax handler, GUI)
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 *
 * @ilCtrl_Calls ilBackgroundTaskHub: 
 */
class ilBackgroundTaskHub
{
	protected $task; // [ilBackgroundTask]
	protected $handler; // [ilBackgroundTaskHandler]
	
	/**
	 * Constructor
	 * 
	 * @return \self
	 */
	public function __construct()
	{
		global $lng;
		
		$lng->loadLanguageModule("bgtask");
		
		if((int)$_REQUEST["tid"])
		{
			$this->task = new ilBackgroundTask((int)$_REQUEST["tid"]);		
			$this->handler = $this->task->getHandlerInstance();
		}
	}


	//
	// ajax
	// 
	
	/**
	 * Execute current command	 
	 */
	public function executeCommand()
	{
		global $ilCtrl;
		
		$next_class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd("validate");				
			
		switch ($next_class)
		{			
			default:
				if($cmd == "deliver" || 
					$ilCtrl->isAsynch())
				{
					$this->$cmd();
					break;
				}
		}		
		
		// deliver file and ajax require exit
		exit();
	}	
	
	/**
	 * Send Json to client
	 * 
	 * @param stdClass $a_json
	 */
	protected function sendJson(stdClass $a_json)
	{		
		echo json_encode($a_json);
	}
	
	/**
	 * Validate given task
	 */
	protected function validate()
	{				
		$class = trim($_GET["hid"]);
		$file = "Services/BackgroundTask/classes/class.".$class.".php";
		if(file_exists($file))
		{			
			include_once $file;
			$handler = new $class();		
			$json = $handler->init($_GET["par"]);
		
			$this->sendJson($json);
		}
	}
	
	/**
	 * Cancel all other tasks, start current one
	 * 
	 */
	protected function unblock()
	{
		global $ilUser;
		
		foreach(ilBackgroundTask::getActiveByUserId($ilUser->getId()) as $task_id)
		{
			// leave current task alone
			if($task_id != $this->task->getId())
			{
				// emit cancelling status, running processes will cancel
				$task = new ilBackgroundTask($task_id);
				$task->setStatus(ilBackgroundTask::STATUS_CANCELLING);
				$task->save();
			}				
		}
		
		// init/start current task
		$json = $this->handler->init();
		$this->sendJson($json);
	}
	
	/**
	 * Process current task
	 */
	protected function process()
	{										
		$this->task->setStatus(ilBackgroundTask::STATUS_PROCESSING);
		$this->task->save();
		
		// :TODO: start background/SOAP call
		$this->handler->process();		
	}
	
	/**
	 * Check progress of current task
	 */
	protected function progress() 
	{		
		$json = new stdClass();
		$json->status = $this->task->getStatus();				
		$json->steps = $this->task->getSteps();
		$json->current = $this->task->getCurrentStep();		
		
		// if task has been finished, get result action
		if($this->task->getStatus() == ilBackgroundTask::STATUS_FINISHED)
		{
			$result = $this->handler->finish();
			$json->result_cmd = $result[0];
			$json->result = $result[1];
		}
		
		$this->sendJson($json);
	}
	
	/**
	 * Cancel current task
	 */
	protected function cancel()
	{
		// just emit cancelling status, (background) process will stop ASAP
		$this->task->setStatus(ilBackgroundTask::STATUS_CANCELLING);
		$this->task->save();				
	}
	
	/**
	 * Deliver result
	 */
	protected function deliver()
	{
		// :TODO: delete task?
		
		$this->handler->deliver();
	}	
}
<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/BackgroundTask/classes/class.ilBackgroundTask.php";

/**
 * Class ilBackgroundTaskHub
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 *
 * @ilCtrl_Calls ilBackgroundTaskHub: 
 */
class ilBackgroundTaskHub
{
	protected $task; // [ilBackgroundTask]
	protected $handler; // [ilBackgroundTaskHandler]
	
	public function __construct()
	{
		if((int)$_REQUEST["tid"])
		{
			$this->task = new ilBackgroundTask((int)$_REQUEST["tid"]);		
			$this->handler = $this->task->getHandlerInstance();
		}
	}


	//
	// ajax
	// 
	
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
		
		exit();
	}	
	
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
	
	protected function check()
	{
		// needs user-id 
		
		// a) has running => modal
		// b) no other => continue [::init]
	}
	
	protected function cancelExisting()
	{
		// needs user-id
		
		// => ::init
	}

	protected function process()
	{										
		$this->task->setStatus(ilBackgroundTask::STATUS_PROCESSING);
		$this->task->save();
		
		// :TODO: start background/SOAP call
		$this->handler->process();		
	}
	
	protected function progress() 
	{		
		$json = new stdClass();
		$json->status = $this->task->getStatus();				
		$json->steps = $this->task->getSteps();
		$json->current = $this->task->getCurrentStep();		
		
		if($this->task->getStatus() == ilBackgroundTask::STATUS_FINISHED)
		{
			$result = $this->handler->finish();
			$json->result_cmd = $result[0];
			$json->result = $result[1];
		}
		
		$this->sendJson($json);
	}
	
	protected function cancel()
	{
		$this->task->setStatus(ilBackgroundTask::STATUS_CANCELLING);
		$this->task->save();				
	}
	
	protected function deliver()
	{
		// :TODO: delete task?
		
		$this->handler->deliver();
	}
	
	
	//
	// helper
	//

	/**
	 * Makes the specified string safe for JSON.
	 *
	 * @param string $text The text to make JSON safe.
	 *
	 * @return The JSON safe text.
	 */
	protected static function jsonSafeString($text)
	{
		if (!is_string($text)) 
		{
			return $text;
		}

		$text = htmlentities($text, ENT_COMPAT | ENT_HTML401, "UTF-8");
		$text = str_replace("'", "&#039;", $text);

		return $text;
	}		
}

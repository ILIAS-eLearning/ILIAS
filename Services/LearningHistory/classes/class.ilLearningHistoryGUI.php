<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Learning history main GUI class
 *
 * @author killing@leifos.de
 * @ingroup ServicesLearningHistory
 */
class ilLearningHistoryGUI
{
	/**
	 * Constructor
	 */
	public function __construct()
	{

	}

	/**
	 * Execute command
	 */
	function executeCommand()
	{
		$ctrl = $this->ctrl;
		
		$next_class = $ctrl->getNextClass($this);
		$cmd = $ctrl->getCmd("show");
	
		switch ($next_class)
		{
			default:
				if (in_array($cmd, array("show")))
				{
					$this->$cmd();
				}
		}
	}
	
	/**
	 * Show
	 *
	 * @param
	 * @return
	 */
	protected function show()
	{
		$to = time();
		$from = time() - (365 * 24 * 60 * 60);


	}
	

}
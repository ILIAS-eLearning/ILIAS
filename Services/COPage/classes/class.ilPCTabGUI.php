<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilPCTabGUI
 * @author Alex Killing <killing@leifos.de>
 */
class ilPCTabGUI extends ilPageContentGUI
{

	/**
	 * execute command
	 */
	function executeCommand()
	{
		// get next class that processes or forwards current command
		$next_class = $this->ctrl->getNextClass($this);

		// get current command
		$cmd = $this->ctrl->getCmd();

		switch($next_class)
		{
			default:
				$ret = $this->$cmd();
				break;
		}

		return $ret;
	}

}
?>

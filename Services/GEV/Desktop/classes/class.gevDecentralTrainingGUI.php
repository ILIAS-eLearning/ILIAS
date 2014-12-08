<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Forms for decentral trainings.
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @version	$Id$
*/

require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");

class gevDecentralTrainingGUI {
	public function __construct() {
		global $lng, $ilCtrl, $tpl, $ilUser, $ilLog;

		$this->lng = &$lng;
		$this->ctrl = &$ilCtrl;
		$this->tpl = &$tpl;
		$this->log = &$ilLog;
		$this->current_user = &$ilUser;
/*		$this->user_id = null;
		$this->user_utils = null;
		$this->crs_id = null;
		$this->crs_utils = null;
		$this->is_self_learning = null;
		$this->is_webinar = null;

		$this->tpl->getStandardTemplate();*/
	}

	public function executeCommand() {
		$this->cmd = $this->ctrl->getCmd();
		
		die("here");
		
		switch($this->cmd) {
			default:
				$this->log->write("gevBookingGUI: Unknown command '".$this->cmd."'");
		}
		
		
		if ($cont) {
			$this->insertInTemplate($cont, $this->cmd);
		}
	}
}

?>
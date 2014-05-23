<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Course seraching GUI for Generali
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @version	$Id$
*/

require_once("Services/GEV/Desktop/classes/class.gevCourseHighlightsGUI.php");
require_once("Services/GEV/Desktop/classes/class.gevUserSelectorGUI.php");
require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");

class gevCourseSearchGUI {	
	public function __construct() {
		global $ilLng, $ilCtrl, $tpl, $ilUser;
		
		$this->lng = &$ilLng;
		$this->ctrl = &$ilCtrl;
		$this->tpl = &$tpl;
		$this->user_utils = gevUserUtils::getInstance($ilUser->getId());
		
		if ($this->user_utils->hasUSerSelectorOnSearchGUI()) {
			$this->target_user_id = $_POST["target_user_id"]
								  ? $_POST["target_user_id"]
								  : $ilUser->getId();
		}
		else {
			$this->target_user_id = $ilUser->getId();
		}

		$this->tpl->getStandardTemplate();
	}
	
	public function executeCommand() {
		return $this->render();
	}
	
	public function render() {
		if ($this->user_utils->hasUserSelectorOnSearchGUI()) {
			$user_selector = new gevUserSelectorGUI($this->target_user_id);
			$user_selector->setUsers($this->user_utils->getEmployeesForCourseSearch())
						  ->setCaption("gev_crs_srch_usr_slctr_caption")
						  ->setAction($this->ctrl->getLinkTargetByClass("gevCourseSearchGUI"));
			$usrsel = $user_selector->render();
		}
		else {
			$usrsel = "";
		}
		
		$hls = new gevCourseHighlightsGUI($this->target_user_id);
		
		return $usrsel
			 . $hls->render();
	}
}

?>
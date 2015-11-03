<?php

/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* GUI to show offers to agents of the generali.
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @version	$Id$
*
* @ilCtrl_Calls gevAgentOfferGUI: gevCourseSearchGUI
* @ilCtrl_Calls gevAgentOfferGUI: gevBookingGUI
*/

class gevAgentOfferGUI {
	public function __construct() {
		global $lng, $ilCtrl, $tpl;
		
		$this->lng = &$lng;
		$this->ctrl = &$ilCtrl;
		$this->tpl = &$tpl;

		$this->lng->loadLanguageModule("gev");
		$this->tpl->getStandardTemplate();
	}
	
	public function executeCommand() {
		$next_class = $this->ctrl->getNextClass();
		$cmd = $this->ctrl->getCmd();
		
		if($cmd == "") {
			$cmd = "view";
		}
		
		if ($next_class == "" && $cmd == "view") {
			$next_class = "gevcoursesearchgui";
		}

		switch($next_class) {
			case "gevcoursesearchgui":
				require_once("Services/GEV/CourseSearch/classes/class.gevCourseSearchGUI.php");
				require_once("Services/GEV/Utils/classes/class.gevSettings.php");
				$gui = new gevCourseSearchGUI(gevSettings::getInstance()->get(gevSettings::AGENT_OFFER_USER_ID));
				$ret = $this->ctrl->forwardCommand($gui);
				break;
			case "gevbookinggui":
				require_once("Services/GEV/Desktop/classes/class.gevBookingGUI.php");
				$gui = new gevBookingGUI();
				$ret = $this->ctrl->forwardCommand($gui);
				break;
			case false:
				switch($cmd) {
					default:
						throw new ilException("gevAgentOfferGUI: Unknown command '$cmd'");
				}
			default:
				throw new ilException("gevAgentOfferGUI: Can't forward to '$next_class'");
		}
		
		$this->tpl->setContent($ret);
		$this->tpl->show();
	}
}

?>
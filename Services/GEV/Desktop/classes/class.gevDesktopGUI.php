<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Desktop for the Generali
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @version	$Id$
*
* @ilCtrl_Calls gevDesktopGUI: gevMyCoursesGUI
* @ilCtrl_Calls gevDesktopGUI: gevCourseSearchGUI
* @ilCtrl_Calls gevDesktopGUI: gevBookingGUI
*
*/

class gevDesktopGUI {
	public function __construct() {
		global $lng, $ilCtrl, $tpl;
		
		$this->lng = &$lng;
		$this->ctrl = &$ilCtrl;
		$this->tpl = &$tpl;

		$this->tpl->getStandardTemplate();
	}
	
	public function executeCommand() {
		$next_class = $this->ctrl->getNextClass();
		$cmd = $this->ctrl->getCmd();
		
		if($cmd == "") {
			$cmd = "toMyCourses";
		}

		switch($next_class) {
			case "gevmycoursesgui":
				require_once("Services/GEV/Desktop/classes/class.gevMyCoursesGUI.php");
				$gui = new gevMyCoursesGUI();
				$ret = $this->ctrl->forwardCommand($gui);
				break;
			case "gevcoursesearchgui":
				require_once("Services/GEV/Desktop/classes/class.gevCourseSearchGUI.php");
				$gui = new gevCourseSearchGUI();
				$ret = $this->ctrl->forwardCommand($gui);
				break;
			/*case "gevmemberlistdeliverygui":
				require_once("Services/GEV/Desktop/classes/class.gevMemberListDeliveryGUI.php");
				$gui = new gevMemberListDeliveryGUI();
				$this->ctrl->forward($gui);
				return;*/
			case "gevbookinggui";
				require_once("Services/GEV/Desktop/classes/class.gevBookingGUI.php");
				$gui = new gevBookingGUI();
				$ret = $this->ctrl->forwardCommand($gui);
				break;
			default:	
				$this->dispatchCmd($cmd);
				break;
		}
		
		if (isset($ret)) {
			$this->tpl->setContent($ret);
		}
		
		$this->tpl->show();
	}
	
	public function dispatchCmd($a_cmd) {
		switch($a_cmd) {
			case "toCourseSearch":
			case "toMyCourses":
				$this->$a_cmd();
			default:
				throw new Exception("Unknown command: ".$a_cmd);
		}
	}
	
	protected function toCourseSearch() {
		$this->ctrl->redirectByClass("gevCourseSearchGUI");
	}
	
	protected function toMyCourses() {
		$this->ctrl->redirectByClass("gevMyCoursesGUI");
	}
}

?>
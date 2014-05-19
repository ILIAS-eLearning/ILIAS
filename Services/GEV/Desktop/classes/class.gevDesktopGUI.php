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
*/

class gevDesktopGUI {
	public function __construct() {
		global $ilLng, $ilCtrl, $tpl;
		
		$this->lng = &$ilLng;
		$this->ctrl = &$ilCtrl;
		$this->tpl = &$tpl;

		$this->tpl->getStandardTemplate();
	}
	
	public function executeCommand() {
		$next_class = $this->ctrl->getNextClass();
		
		//echo $next_class;
		//die("---");
		
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
			default:	
				$ret = "Not yet implemented.";
				break;
		}
		
		if (isset($ret)) {
			$this->tpl->setContent($ret);
		}
		
		$this->tpl->show();
	}
}

?>
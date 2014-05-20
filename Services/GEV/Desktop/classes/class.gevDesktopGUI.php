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

/*		Stuff from Databay, to be reviewed
		if ($cmd_class != "ilreportingfoundationgui" && $cmd_class != "")
		{
			$class_path = $this->ilCtrl->lookupClassPath($next_class);
			$class_name = $this->ilCtrl->getClassForClasspath($class_path);
			if (!$class_path)
			{
				$class_path = './Services/Reports/classes/class.'.ilUtil::stripSlashes($_GET['cmdClass']).'.php';
				$class_name = ilUtil::stripSlashes($_GET['cmdClass']);
			}
			if (file_exists($class_path))
			{
				require_once $class_path;
				$gui_obj = new $class_name($_GET["ref_id"]);
				$this->ilCtrl->forwardCommand($gui_obj);
			}
			else
			{
				throw new ilException('No such class: ' . $class_name . ', file ' . $class_path . ' not available.');
			}
		}
		else
		{
			$this->processCommand($this->ilCtrl->getCmd());
		}*/
		
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
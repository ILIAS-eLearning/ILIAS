<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("Services/Table/classes/class.ilTable2GUI.php");
require_once("Modules/TrainingProgramme/classes/model/class.ilTrainingProgrammeProgress.php");

/**
 * Class ilObjTrainingProgrammeMembersTableGUI
 *
 * @author: Richard Klees <richard.klees@concepts-and-training.de>
 *
 */

class ilTrainingProgrammeMembersTableGUI extends ilTable2GUI {
	public function __construct($a_parent_obj, $a_parent_cmd="", $a_template_context="") {
		parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);

		global $ilCtrl, $lng;

		$this->setEnableTitle(true);
		$this->setTopCommands(false);
		$this->setEnableHeader(true);
		$this->setExternalSorting(true);
		$this->setExternalSegmentation(true);
		
		//$this->setFormAction($ilCtrl->getFormAction($a_parent_obj, "view"));

		//$this->setRowTemplate("tpl.il_admin_search_row.html", "Services/GEV/Desktop");

		$columns = array( "name" 				=> array("name")
						, "login" 				=> array("login")
						, "prg_status" 			=> array("status")
						, "prg_completion"		=> array("completion")
						, "prg_points_required" => array("points_required")
						, "prg_points_current"  => array("points_current")
						, "prg_custom_plan"		=> array("custom_plan")
						, "prg_belongs_to"		=> array("belongs_to")
						, "actions"				=> array(null)
						);
		foreach ($columns as $lng_var => $params) {
			$this->addColumn($lng->txt($lng_var), $params[0]);
		}
		
		$this->determineLimit();
		$this->determineOffsetAndOrder();
		
/*		$members_list = ilTrainingProgrammeProgress::getCollection();
		
		//$order = $this->getOrderField();
		
		$this->setMaxCount($members_list->count());
		$this->setData($members_list->getArray());*/
	}
}

?>
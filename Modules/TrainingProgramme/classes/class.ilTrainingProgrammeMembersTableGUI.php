<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("Services/Table/classes/class.ilTable2GUI.php");
require_once("Modules/TrainingProgramme/classes/class.ilTrainingProgrammeUserProgress.php");
require_once("Modules/TrainingProgramme/classes/model/class.ilTrainingProgrammeProgress.php");
require_once("Modules/TrainingProgramme/classes/class.ilObjTrainingProgramme.php");

/**
 * Class ilObjTrainingProgrammeMembersTableGUI
 *
 * @author: Richard Klees <richard.klees@concepts-and-training.de>
 *
 */

class ilTrainingProgrammeMembersTableGUI extends ilTable2GUI {
	public function __construct($a_prg_id, $a_parent_obj, $a_parent_cmd="", $a_template_context="") {
		parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);

		global $ilCtrl, $lng;

		$this->setEnableTitle(true);
		$this->setTopCommands(false);
		$this->setEnableHeader(true);
		$this->setExternalSorting(true);
		$this->setExternalSegmentation(true);
		$this->setRowTemplate("tpl.il_members_table_row.html", "Modules/TrainingProgramme");
		
		//$this->setFormAction($ilCtrl->getFormAction($a_parent_obj, "view"));


		$columns = array( "name" 				=> array("name")
						, "login" 				=> array("login")
						, "prg_status" 			=> array("status")
						, "prg_completion_by"	=> array("completion_by")
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

		$members_list = ilTrainingProgrammeProgress
							::innerjoin("usr_data", "usr_id", "usr_id")
							->where(array
							( "prg_id" => $a_prg_id
							));
		//print_r($members_list->getArray());
		//die();

		//$order = $this->getOrderField();
		
		$this->setMaxCount($members_list->count());
		$this->setData($members_list->getArray());
	}

	protected function fillRow($a_set) {
		$this->tpl->setVariable("NAME", "TODO");
		$this->tpl->setVariable("LOGIN", "TODO");
		$this->tpl->setVariable("STATUS", ilTrainingProgrammeUserProgress::statusToRepr($a_set["status"]));
		$this->tpl->setVariable("COMPLETION_BY", "TODO");
		$this->tpl->setVariable("POINTS_REQUIRED", "TODO");
		$this->tpl->setVariable("POINTS_CURRENT", "TODO");
		$this->tpl->setVariable("CUSTOM_PLAN", "TODO");
		$this->tpl->setVariable("BELONGS_TO", "TODO");
		$this->tpl->setVariable("ACTIONS", "TODO");
	}
}

?>
<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("Services/Table/classes/class.ilTable2GUI.php");
require_once("Modules/TrainingProgramme/classes/class.ilTrainingProgrammeUserProgress.php");
require_once("Modules/TrainingProgramme/classes/class.ilObjTrainingProgramme.php");

/**
 * Class ilTrainingProgrammeIndividualPlanTableGUI
 *
 * @author: Richard Klees <richard.klees@concepts-and-training.de>
 *
 */

class ilTrainingProgrammeIndividualPlanTableGUI extends ilTable2GUI {
	protected $assignment;
	
	public function __construct($a_parent_obj, ilTrainingProgrammeUserAssignment $a_ass) {
		parent::__construct($a_parent_obj);

		global $ilCtrl, $lng, $ilDB;
		$this->ctrl = $ilCtrl;
		$this->lng = $lng;
		$this->db = $ilDB;

		$this->assignment = $a_ass;

		$this->setEnableTitle(true);
		$this->setTopCommands(false);
		$this->setEnableHeader(true);
		// TODO: switch this to internal sorting/segmentation
		$this->setExternalSorting(false);
		$this->setExternalSegmentation(false);
		$this->setRowTemplate("tpl.individual_plan_table_row.html", "Modules/TrainingProgramme");
		
		//$this->setFormAction($ilCtrl->getFormAction($a_parent_obj, "view"));


		$columns = array( "status"
						, "title"
						, "prg_points_current"
						, "prg_points_required"
						, "prg_manual_status"
						, "prg_not_possible"
						, "prg_changed_by"
						, "prg_completion_by"
						);
		foreach ($columns as $lng_var) {
			$this->addColumn($lng->txt($lng_var));
		}
		
		$this->determineLimit();
		$this->determineOffsetAndOrder();

		$plan = $this->fetchData();
	
		$this->setMaxCount(count($plan));
		$this->setData($plan);
	}
	
	protected function fillRow($a_set) {
		$this->tpl->setVariable("STATUS", ilTrainingProgrammeUserProgress::statusToRepr($a_set["status"]));
		$this->tpl->setVariable("TITLE", $a_set["title"]);
		$this->tpl->setVariable("POINTS_CURRENT", $a_set["points_current"]);
		$this->tpl->setVariable("POINTS_REQUIRED", $a_set["points_required"]);
		$this->tpl->setVariable("MANUAL_STATUS", $this->getManualStatusCheckbox($a_set["progress_id"], $a_set["status"]));
		$this->tpl->setVariable("NOT_POSSIBLE", $a_set["not_possible"]);
		$this->tpl->setVariable("CHANGED_BY", $a_set["changed_by"]);
		$this->tpl->setVariable("COMPLETION_BY", $a_set["completion_by"]);
	}
	
	protected function fetchData() {
		$prg = $this->assignment->getTrainingProgramme();
		$prg_id = $prg->getId();
		$ass_id = $this->assignment->getId();
		$usr_id = $this->assignment->getUserId();
		$plan = array();
		
		$prg->applyToSubTreeNodes(function($node) use ($prg_id, $ass_id, $usr_id, &$plan) {
			$progress = ilTrainingProgrammeUserProgress::getInstance($ass_id, $node->getId(), $usr_id);
			$completion_by_id = $progress->getCompletionBy();
			if ($completion_by_id) {
				$completion_by = ilObjUser::_lookupLogin($completion_by_id);
				if (!$completion_by) {
					$completion_by = ilObject::_lookupTitle($completion_by_id);
				}	
			}
			$plan[] = array( "status" => $progress->getStatus()
						   , "title" => $node->getTitle()
						   , "points_current" => $progress->getCurrentAmountOfPoints()
						   , "points_required" => $progress->getAmountOfPoints()
						   , "not_possible" => !$progress->canBeCompleted()
						   , "changed_by" => ilObjUser::_lookupLogin($progress->getLastChangeBy())
						   , "completion_by" => $completion_by
						   , "progress_id" => $progress->getId()
						   );
		});
		return $plan;
	}
	
	const MANUAL_STATUS_NONE = 0;
	const MANUAL_STATUS_NOT_RELEVANT = 1;
	const MANUAL_STATUS_ACCREDITED = 2;
	
	
	protected function getManualStatusCheckbox($a_progress_id, $a_status) {
		if ($a_status == ilTrainingProgrammeProgress::STATUS_COMPLETED) {
			return "";
		}
		
		$status_title = $this->getParentObject()->getManualStatusPostVarTitle();
		
		require_once("Services/Form/classes/class.ilSelectInputGUI.php");
		$select = new ilSelectInputGUI("", "$status_title"."[$a_progress_id]");
		$select->setOptions(array
			( self::MANUAL_STATUS_NONE => "-"
			, self::MANUAL_STATUS_ACCREDITED => $this->lng->txt("prg_status_accredited")
			, self::MANUAL_STATUS_NOT_RELEVANT => $this->lng->txt("prg_status_not_relevant")
			));
		if ($a_status == ilTrainingProgrammeProgress::STATUS_NOT_RELEVANT) {
			$select->setValue(self::MANUAL_STATUS_NOT_RELEVANT);
		}
		else if ($a_status == ilTrainingProgrammeProgress::STATUS_ACCREDITED) {
			$select->setValue(self::MANUAL_STATUS_ACCREDITED);
		}
		
		return $select->render();
	}
}

?>
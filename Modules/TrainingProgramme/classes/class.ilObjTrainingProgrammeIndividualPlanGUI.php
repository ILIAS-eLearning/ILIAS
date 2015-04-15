<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */


/**
 * Class ilObjTrainingProgrammeIndividualPlanGUI
 *
 * @author: Richard Klees <richard.klees@concepts-and-training.de>
 */

class ilObjTrainingProgrammeIndividualPlanGUI {
	/**
	 * @var ilCtrl
	 */
	public $ctrl;
	
	/**
	 * @var ilTemplate
	 */
	public $tpl;
	
	/**
	 * @var ilAccessHandler
	 */
	protected $ilAccess;
	
	/**
	 * @var ilObjTrainingProgramme
	 */
	public $object;
	
	/**
	 * @var ilLog
	 */
	protected $ilLog;
	
	/**
	 * @var Ilias
	 */
	public $ilias;

	/**
	 * @var ilLng
	 */
	public $lng;
	
	/**
	 * @var ilToolbarGUI
	 */
	public $toolbar;

	/**
	 * @var ilObjUser
	 */
	public $user;

	protected $parent_gui;

	public function __construct($a_parent_gui, $a_ref_id) {
		global $tpl, $ilCtrl, $ilAccess, $ilToolbar, $ilLocator, $tree, $lng, $ilLog, $ilias, $ilUser;

		$this->ref_id = $a_ref_id;
		$this->parent_gui = $a_parent_gui;
		$this->tpl = $tpl;
		$this->ctrl = $ilCtrl;
		$this->ilAccess = $ilAccess;
		$this->ilLocator = $ilLocator;
		$this->tree = $tree;
		$this->toolbar = $ilToolbar;
		$this->ilLog = $ilLog;
		$this->ilias = $ilias;
		$this->lng = $lng;
		$this->user = $ilUser;
		$this->assignment_object = null;
		
		$this->object = null;

		$lng->loadLanguageModule("prg");
	}
	
	public function executeCommand() {
		$cmd = $this->ctrl->getCmd();
		
		if ($cmd == "") {
			$cmd = "view";
		}
		
		switch ($cmd) {
			case "view":
			case "manage":
			case "updateFromCurrentPlan":
			case "updateFromInput":
				$cont = $this->$cmd();
				break;
			default:
				throw new ilException("ilObjTrainingProgrammeMembersGUI: ".
									  "Command not supported: $cmd");
		}
		
		$this->tpl->setContent($cont);
	}
	
	protected function getAssignmentId() {
		require_once("Modules/TrainingProgramme/classes/class.ilTrainingProgrammeUserAssignment.php");
		if (!is_numeric($_GET["ass_id"])) {
			throw new ilException("Expected integer 'ass_id'");
		}
		return (int)$_GET["ass_id"];
	}
	
	protected function getAssignmentObject() {
		if ($this->assignment_object === null) {
			$id = $this->getAssignmentId();
			$this->assignment_object = ilTrainingProgrammeUserAssignment::getInstance($id);
		}
		return $this->assignment_object;
	}
	
	protected function view() {
		return $this->buildFrame("view", "NYI!: view");
	}

	protected function manage() {
		require_once("Modules/TrainingProgramme/classes/class.ilTrainingProgrammeIndividualPlanTableGUI.php");
		$table = new ilTrainingProgrammeIndividualPlanTableGUI($this, $this->getAssignmentObject());
		return $this->buildFrame("manage", $table->getHTML());
	}
	
	protected function updateFromCurrentPlan() {
		$ass = $this->getAssignmentObject();
		$ass->updateFromProgram();
		$this->ctrl->setParameter($this, "ass_id", $ass->getId());
		$this->showSuccessMessage("update_from_plan_successfull");
		$this->ctrl->redirect($this, "manage");
	}
	
	protected function updateFromInput() {
		require_once("Modules/TrainingProgramme/classes/class.ilTrainingProgrammeUserProgress.php");
		
		$updates = $this->getManualStatusUpdates();
		foreach ($updates as $prgrs_id => $status) {
			$prgrs = ilTrainingProgrammeUserProgress::getInstanceById($prgrs_id);
			$cur_status = $prgrs->getStatus();
			if ($status == self::MANUAL_STATUS_NONE && $cur_status == ilTrainingProgrammeProgress::STATUS_ACCREDITED) {
				$prgrs->unmarkAccredited($this->user->getId());
			}
			else if ($status == self::MANUAL_STATUS_NONE && $cur_status == ilTrainingProgrammeProgress::STATUS_NOT_RELEVANT) {
				$prgrs->markRelevant($this->user->getId());
			}
			else if($status == self::MANUAL_STATUS_NOT_RELEVANT && $cur_status != ilTrainingProgrammeProgress::STATUS_NOT_RELEVANT) {
				$prgrs->markNotRelevant($this->user->getId());
			}
			else if($status == self::MANUAL_STATUS_ACCREDITED && $cur_status != ilTrainingProgrammeProgress::STATUS_ACCREDITED) {
				$prgrs->markAccredited($this->user->getId());
			}
		}
		$this->ctrl->setParameter($this, "ass_id", $this->getAssignmentId());
		$this->showSuccessMessage("update_successfull");
		$this->ctrl->redirect($this, "manage");
	}
	
	protected function showSuccessMessage($a_lng_var) {
		require_once("Services/Utilities/classes/class.ilUtil.php");
		ilUtil::sendSuccess($this->lng->txt("prg_$a_lng_var"), true);
	}
	
	protected function getManualStatusUpdates() {
		$post_var = $this->getManualStatusPostVarTitle();
		if (!array_key_exists($post_var, $_POST)) {
			throw new ilException("Expected array $post_var in POST");
		}
		return $_POST[$post_var];
	}
	
	protected function buildFrame($tab, $content) {
		$tpl = new ilTemplate("tpl.indivdual_plan_frame.html", true, true, "Modules/TrainingProgramme");
		$ass = $this->getAssignmentObject();
		
		$tpl->setVariable("USERNAME", ilObjUser::_lookupFullname($ass->getUserId()));
		foreach (array("view", "manage") as $_tab) {
			$tpl->setCurrentBlock("sub_tab");
			$tpl->setVariable("CLASS", $_tab == $tab ? "active" : "");
			$tpl->setVariable("LINK", $this->getLinkTargetForSubTab($_tab, $ass->getId()));
			$tpl->setVariable("TITLE", $this->lng->txt("prg_$_tab"));
			$tpl->parseCurrentBlock();
		}
		$tpl->setVariable("CONTENT", $content);
		
		return $tpl->get();
	}
	
	protected function getLinkTargetForSubTab($a_tab, $a_ass_id) {
		$this->ctrl->setParameter($this, "ass_id", $a_ass_id);
		$lnk = $this->ctrl->getLinkTarget($this, $a_tab);
		$this->ctrl->setParameter($this, "ass_id", null);
		return $lnk;
	}
	
	public function appendIndividualPlanActions(ilTable2GUI $a_table) {
		$this->ctrl->setParameter($this, "ass_id", $this->getAssignmentObject()->getId());
		$a_table->setFormAction($this->ctrl->getFormAction($this));
		$a_table->addCommandButton("updateFromCurrentPlan", $this->lng->txt("prg_update_from_current_plan"));
		$a_table->addCommandButton("updateFromInput", $this->lng->txt("save"));
		$this->ctrl->setParameter($this, "ass_id", null);
	}
	
	const MANUAL_STATUS_NONE = 0;
	const MANUAL_STATUS_NOT_RELEVANT = 1;
	const MANUAL_STATUS_ACCREDITED = 2;
	
	public function getManualStatusPostVarTitle() {
		return "status";
	}
	
	public function getManualStatusNone() {
		return self::MANUAL_STATUS_NONE;
	}
	
	public function getManualStatusNotRelevant() {
		return self::MANUAL_STATUS_NOT_RELEVANT;
	}
	
	public function getManualStatusAccredited() {
		return self::MANUAL_STATUS_ACCREDITED;
	}
	
	static public function getLinkTargetView($ctrl, $a_ass_id) {
		$cl = "ilObjTrainingProgrammeIndividualPlanGUI";
		$ctrl->setParameterByClass($cl, "ass_id", $a_ass_id);
		$link = $ctrl->getLinkTargetByClass($cl, "view");
		$ctrl->setParameterByClass($cl, "ass_id", null);
		return $link;
	}
}

?>
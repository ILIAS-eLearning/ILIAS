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

	public function __construct($a_parent_gui, $a_ref_id, ilTrainingProgrammeUserProgress $a_prgrs) {
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
		$this->progress_object = $a_prgrs;
		$this->user_of_progress = null;
		
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
	
	protected function getProgressObject() {
		return $this->progress_object;
	}
	
	protected function getUserOfProgress() {
		if ($this->user_of_progress === null) {
			$prgrs = $this->getProgressObject();
			$this->user_of_progress = new ilObjUser($prgrs->getUserId());
		}
		
		return $this->user_of_progress;
	}
	
	protected function getAssignmentOfProgress() {
		return $this->getProgressObject()->getAssignment();
	}
	
	protected function view() {
		return $this->buildFrame("view", "NYI!: view");
	}

	protected function manage() {
		require_once("Modules/TrainingProgramme/classes/class.ilTrainingProgrammeIndividualPlanTableGUI.php");
		$table = new ilTrainingProgrammeIndividualPlanTableGUI($this, $this->getAssignmentOfProgress());
		return $this->buildFrame("manage", $table->getHTML());
	}
	
	protected function buildFrame($tab, $content) {
		$tpl = new ilTemplate("tpl.indivdual_plan_frame.html", true, true, "Modules/TrainingProgramme");
		$prgrs = $this->getProgressObject();
		
		$tpl->setVariable("USERNAME", ilObjUser::_lookupFullname($prgrs->getUserId()));
		foreach (array("view", "manage") as $_tab) {
			$tpl->setCurrentBlock("sub_tab");
			$tpl->setVariable("CLASS", $_tab == $tab ? "active" : "");
			$tpl->setVariable("LINK", $this->getLinkTargetForSubTab($_tab, $prgrs->getId()));
			$tpl->setVariable("TITLE", $this->lng->txt("prg_$_tab"));
			$tpl->parseCurrentBlock();
		}
		$tpl->setVariable("CONTENT", $content);
		
		return $tpl->get();
	}
	
	public function getLinkTargetForSubTab($a_tab, $a_prgrs_id) {
		$this->ctrl->setParameter($this, "prgrs_id", $a_prgrs_id);
		$lnk = $this->ctrl->getLinkTarget($this, $a_tab);
		$this->ctrl->setParameter($this, "prgrs_id", null);
		return $lnk;
	}
	
	public function appendIndividualPlanActions(ilTable2GUI $a_table) {
		$a_table->setFormAction($this->ctrl->getFormAction($this));
		$a_table->addCommandButton("updateFromCurrentPlan", $this->lng->txt("prg_update_from_current_plan"));
		$a_table->addCommandButton("updateFromInput", $this->lng->txt("save"));
	}
	
	public function getManualStatusPostVarTitle() {
		return "status";
	}
	
	static public function getLinkTargetView($ctrl, $a_prgrs_id) {
		$cl = "ilObjTrainingProgrammeIndividualPlanGUI";
		$ctrl->setParameterByClass($cl, "prgrs_id", $a_prgrs_id);
		$link = $ctrl->getLinkTargetByClass($cl, "view");
		$ctrl->setParameter($cl, "prgrs_id", null);
		return $link;
	}
}

?>
<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */


/**
 * Class ilObjTrainingProgrammeMembersGUI
 *
 * @author: Richard Klees <richard.klees@concepts-and-training.de>
 *
 * @ilCtrl_Calls ilObjTrainingProgrammeMembersGUI: ilRepositorySearchGUI
 * @ilCtrl_Calls ilObjTrainingProgrammeMembersGUI: ilObjTrainingProgrammeIndividualPlanGUI
 */

class ilObjTrainingProgrammeMembersGUI {
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
		$this->progress_object = null;
		
		$this->object = null;

		$lng->loadLanguageModule("prg");
	}
	
	public function executeCommand() {
		$cmd = $this->ctrl->getCmd();
		$next_class = $this->ctrl->getNextClass($this);
		
		if ($cmd == "") {
			$cmd = "view";
		}
		
		# TODO: Check permission of user!!
		
		switch ($next_class) {
			case "ilrepositorysearchgui":		
				require_once("./Services/Search/classes/class.ilRepositorySearchGUI.php");
				$rep_search = new ilRepositorySearchGUI();
				$rep_search->setCallback($this, "addUsers");				
				
				$this->ctrl->setReturn($this, "view");
				$this->ctrl->forwardCommand($rep_search);
				return;
			case "ilobjtrainingprogrammeindividualplangui":
				require_once("./Modules/TrainingProgramme/classes/class.ilObjTrainingProgrammeIndividualPlanGUI.php");
				$individual_plan_gui = new ilObjTrainingProgrammeIndividualPlanGUI( $this, $this->ref_id
																				  , $this->getProgressObject());
				$this->ctrl->forwardCommand($individual_plan_gui);
				return;
			case false:
				switch ($cmd) {
					case "view":
					case "addUserFromAutoComplete":
					case "markAccredited":
					case "unmarkAccredited":
					case "removeUser":
						$cont = $this->$cmd();
						break;
					default:
						throw new ilException("ilObjTrainingProgrammeMembersGUI: ".
											  "Command not supported: $cmd");
				}
				break;
			default:
				throw new ilException("ilObjTrainingProgrammeMembersGUI: Can't forward to next class $next_class");
		}
		
		$this->tpl->setContent($cont);
	}
	
	protected function view() {
		require_once("Modules/TrainingProgramme/classes/class.ilTrainingProgrammeMembersTableGUI.php");
		
		// TODO: if ($this->getTrainingProgramme()->isActive()) {
		$this->initSearchGUI();
		
		$prg_id = ilObject::_lookupObjId($this->ref_id);
		$table = new ilTrainingProgrammeMembersTableGUI($prg_id, $this->ref_id, $this);
		return $table->getHTML();
	}

	public function addUsers($a_users) {
		$prg = $this->getTrainingProgramme();
		foreach ($a_users as $user_id) {
			$prg->assignUser($user_id);
		}
	}
	
	public function markAccredited() {
		require_once("Modules/TrainingProgramme/classes/class.ilTrainingProgrammeUserProgress.php");
		$prgrs = $this->getProgressObject();
		$prgrs->markAccredited($this->user->getId());
		$this->showSuccessMessage("mark_accredited_success");
		$this->ctrl->redirect($this, "view");
	}
	
	public function unmarkAccredited() {
		require_once("Modules/TrainingProgramme/classes/class.ilTrainingProgrammeUserProgress.php");
		$prgrs = $this->getProgressObject();
		$prgrs->unmarkAccredited();
		$this->showSuccessMessage("unmark_accredited_success");
		$this->ctrl->redirect($this, "view");
	}
	
	public function removeUser() {
		require_once("Modules/TrainingProgramme/classes/class.ilTrainingProgrammeUserProgress.php");
		$prgrs = $this->getProgressObject();
		$ass = $prgrs->getAssignment();
		$prg = $ass->getTrainingProgramme();
		if ($prg->getRefId() != $this->ref_id) {
			throw new ilException("Can only remove users from the node they where assigned to.");
		}
		$ass->remove();
		$this->showSuccessMessage("remove_user_success");
		$this->ctrl->redirect($this, "view");
	}
	
	protected function getProgressObject() {
		if ($this->progress_object === null) {
			require_once("Modules/TrainingProgramme/classes/class.ilTrainingProgrammeUserProgress.php");
			if (!is_numeric($_GET["prgrs_id"])) {
				throw new ilException("Expected integer 'prgrs_id'");
			}
			$id = (int)$_GET["prgrs_id"];
			$this->progress_object = ilTrainingProgrammeUserProgress::getInstanceById($id);
		}
		return $this->progress_object;
	}
	
	protected function showSuccessMessage($a_lng_var) {
		require_once("Services/Utilities/classes/class.ilUtil.php");
		ilUtil::sendSuccess($this->lng->txt("prg_$a_lng_var"), true);
	}
	
	protected function initSearchGUI() {
		require_once("./Services/Search/classes/class.ilRepositorySearchGUI.php");
		ilRepositorySearchGUI::fillAutoCompleteToolbar(
			$this,
			$this->toolbar,
			array(
				"auto_complete_name"	=> $this->lng->txt("user"),
				"submit_name"			=> $this->lng->txt("add"),
				"add_search"			=> true
			)
		);
	}
	
	protected function getTrainingProgramme() {
		require_once("Modules/TrainingProgramme/classes/class.ilObjTrainingProgramme.php");
		return ilObjTrainingProgramme::getInstanceByRefId($this->ref_id);
	}
	
	/**
	 * Get the link target for an action on user progress.
	 * 
	 * @param	int		$a_action		One of ilTrainingProgrammeUserProgress::ACTION_*
	 * @param	int		$a_prgrs_id		Id of the progress object to act on.
	 * @return	string					The link to the action.
	 */
	public function getLinkTargetForAction($a_action, $a_prgrs_id) {
		require_once("Modules/TrainingProgramme/classes/class.ilTrainingProgrammeUserProgress.php");
		
		switch ($a_action) {
			case ilTrainingProgrammeUserProgress::ACTION_MARK_ACCREDITED:
				$target_name = "markAccredited";
				break;
			case ilTrainingProgrammeUserProgress::ACTION_UNMARK_ACCREDITED:
				$target_name = "unmarkAccredited";
				break;
			case ilTrainingProgrammeUserProgress::ACTION_SHOW_INDIVIDUAL_PLAN:
				require_once("Modules/TrainingProgramme/classes/class.ilObjTrainingProgrammeIndividualPlanGUI.php");
				return ilObjTrainingProgrammeIndividualPlanGUI::getLinkTargetView($this->ctrl, $a_prgrs_id);
			case ilTrainingProgrammeUserProgress::ACTION_REMOVE_USER:
				$target_name = "removeUser";
				break;
			default:
				throw new ilException("Unknown action: $action");
		}
		
		$this->ctrl->setParameter($this, "prgrs_id", $a_prgrs_id);
		$link = $this->ctrl->getLinkTarget($this, $target_name);
		$this->ctrl->setParameter($this, "prgrs_id", null);
		return $link;
	}
}

?>
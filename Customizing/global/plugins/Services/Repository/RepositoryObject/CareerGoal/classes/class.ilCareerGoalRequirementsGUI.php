<?php

use CaT\Plugins\CareerGoal;

class ilCareerGoalRequirementsGUI {
	const CMD_SHOW = "showRequirements";
	const CMD_ADD = "addRequirement";
	const CMD_EDIT = "editRequirement";
	const CMD_SAVE = "saveRequirement";
	const CMD_UPDATE = "updateRequirement";
	const CMD_DELETE = "deleteRequirement";
	const CMD_SORT = "sortRequirements";
	const CMD_CONFIRMED_DELETE = "confirmedDeleteRequirement";

	const CMD_DELETE_SELECTED_REQUIREMENTS = "deleteSelected";
	const CMD_CONFIRMED_DELETE_SELECTED_REQUIREMENTS = "confirmedDeleteSelected";

	const CMD_SAVE_ORDER = "saveOrder";

	public function __construct(CareerGoal\ilActions $actions, \Closure $txt, $career_goal_id) {
		global $ilCtrl, $tpl, $ilToolbar, $ilTabs;

		$this->gCtrl = $ilCtrl;
		$this->gTpl = $tpl;
		$this->gToolbar = $ilToolbar;
		$this->gTabs = $ilTabs;
		$this->actions = $actions;
		$this->txt = $txt;
		$this->career_goal_id = $career_goal_id;

		$this->possible_cmd = array(
				"CMD_SHOW" => self::CMD_SHOW
				,"CMD_ADD" => self::CMD_ADD
				,"CMD_EDIT" => self::CMD_EDIT
				,"CMD_SAVE" => self::CMD_SAVE
				,"CMD_UPDATE" => self::CMD_UPDATE
				,"CMD_DELETE" => self::CMD_DELETE
				,"CMD_SORT" => self::CMD_SORT
				,"CMD_CONFIRMED_DELETE" => self::CMD_CONFIRMED_DELETE
				,"CMD_DELETE_SELECTED_REQUIREMENTS" => self::CMD_DELETE_SELECTED_REQUIREMENTS
				,"CMD_CONFIRMED_DELETE_SELECTED_REQUIREMENTS" => self::CMD_CONFIRMED_DELETE_SELECTED_REQUIREMENTS
				,"CMD_SAVE_ORDER" => self::CMD_SAVE_ORDER
			);
	}

	/**
	 * @param 	string	$code
	 * @return	string
	 */
	public function txt($code) {
		assert('is_string($code)');

		$txt = $this->txt;

		return $txt($code);
	}

	public function executeCommand() {
		$cmd = $this->gCtrl->getCmd(self::CMD_SHOW);

		switch($cmd) {
			case self::CMD_SHOW:
			case self::CMD_ADD:
			case self::CMD_EDIT:
			case self::CMD_SAVE:
			case self::CMD_UPDATE:
			case self::CMD_DELETE:
			case self::CMD_SORT:
			case self::CMD_CONFIRMED_DELETE:
			case self::CMD_DELETE_SELECTED_REQUIREMENTS:
			case self::CMD_CONFIRMED_DELETE_SELECTED_REQUIREMENTS:
			case self::CMD_SAVE_ORDER:
				$this->$cmd();
				break;
			default:
				throw new Exception("ilCareerGoalRequirementsGUI:: not known command");
		}
	}

	protected function showRequirements() {
		$add_requirement_link = $this->gCtrl->getLinkTarget($this, self::CMD_ADD);
		$this->gToolbar->addButton( $this->txt("add_requirement"), $add_requirement_link);

		$this->setSubTabs(self::CMD_SHOW);

		$gui = new CareerGoal\Requirements\ilCareerGoalRequirementsTableGUI($this);
		$this->gTpl->setContent($gui->getHTML());
	}

	protected function addRequirement() {
		$gui = new CareerGoal\Requirements\ilCareerGoalRequirementGUI($this, CareerGoal\Requirements\ilCareerGoalRequirementGUI::MODE_NEW);
		$gui->render();
	}

	protected function editRequirement() {
		$gui = new CareerGoal\Requirements\ilCareerGoalRequirementGUI($this, CareerGoal\Requirements\ilCareerGoalRequirementGUI::MODE_EDIT, $_GET["obj_id"]);
		$gui->render();
	}

	protected function saveRequirement() {
		$gui = new CareerGoal\Requirements\ilCareerGoalRequirementGUI($this, CareerGoal\Requirements\ilCareerGoalRequirementGUI::MODE_NEW);
		$gui->save();
	}

	protected function updateRequirement() {
		$gui = new CareerGoal\Requirements\ilCareerGoalRequirementGUI($this, CareerGoal\Requirements\ilCareerGoalRequirementGUI::MODE_EDIT);
		$gui->update();
	}

	protected function deleteRequirement() {
		$to_delete_obj_id = $_GET["obj_id"];
		$requirement = $this->getActions()->getRequirement($to_delete_obj_id);

		include_once './Services/Utilities/classes/class.ilConfirmationGUI.php';
		$confirm = new ilConfirmationGUI();
		$confirm->addItem('obj_id', $to_delete_obj_id, $requirement->getTitle());
		$confirm->setHeaderText($this->txt("question_delete_req"));
		$confirm->setFormAction($this->gCtrl->getFormAction($this));
		$confirm->setConfirm($this->txt('remove'), self::CMD_CONFIRMED_DELETE);
		$confirm->setCancel($this->txt('cancel'), self::CMD_SHOW);
		$this->gTpl->setContent($confirm->getHTML());
	}

	protected function confirmedDeleteRequirement() {
		$to_delete_obj_id = $_POST["obj_id"];
		$this->getActions()->deleteRequirement($to_delete_obj_id);

		$this->showRequirements();
	}

	protected function deleteSelected() {
		$to_delete_obj_ids = $_POST["id"];

		include_once './Services/Utilities/classes/class.ilConfirmationGUI.php';
		$confirm = new ilConfirmationGUI();
		$confirm->setHeaderText($this->txt("question_delete_req"));
		$confirm->setFormAction($this->gCtrl->getFormAction($this));
		$confirm->setConfirm($this->txt('remove'), self::CMD_CONFIRMED_DELETE_SELECTED_REQUIREMENTS);
		$confirm->setCancel($this->txt('cancel'), self::CMD_SHOW);

		foreach ($to_delete_obj_ids as $value) {
			$requirement = $this->getActions()->getRequirement($value);
			$confirm->addItem('obj_ids[]', $value, $requirement->getTitle());
		}

		$this->gTpl->setContent($confirm->getHTML());
	}

	protected function confirmedDeleteSelected() {
		$to_delete_obj_ids = $_POST["obj_ids"];
		
		foreach ($to_delete_obj_ids as $value) {
			$this->getActions()->deleteRequirement($value);
		}

		$this->showRequirements();
	}

	protected function sortRequirements() {
		$this->setSubTabs(self::CMD_SORT);

		$gui = new CareerGoal\Requirements\ilCareerGoalRequirementsTableGUI($this,"","",true);
		$this->gTpl->setContent($gui->getHTML());
	}

	protected function saveOrder() {
		$this->getActions()->updateRequirementPosition($_POST);

		\ilUtil::sendSuccess($this->txt("order_saved"), false);
		$this->sortRequirements();
	}

	public function getActions() {
		return $this->actions;
	}

	public function getCareerGoalId() {
		return $this->career_goal_id;
	}

	public function getPossibleCMD() {
		return $this->possible_cmd;
	}

	public function getTXTClosure() {
		return $this->txt;
	}

	protected function setSubTabs($active_sub_tab) {
		$this->gTabs->addSubTab(self::CMD_SHOW, $this->txt("show_requirement")
				,$this->gCtrl->getLinkTarget($this, self::CMD_SHOW));

		$this->gTabs->addSubTab(self::CMD_SORT, $this->txt("sort_requirement")
				,$this->gCtrl->getLinkTarget($this, self::CMD_SORT));

		$this->gTabs->activateSubTab($active_sub_tab);
	}
}
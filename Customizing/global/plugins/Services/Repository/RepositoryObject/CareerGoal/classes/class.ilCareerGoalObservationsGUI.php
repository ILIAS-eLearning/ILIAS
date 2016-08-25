<?php

use CaT\Plugins\CareerGoal;

class ilCareerGoalObservationsGUI {
	const CMD_SHOW = "showObservations";
	const CMD_ADD = "addObservation";
	const CMD_EDIT = "editObservation";
	const CMD_SAVE = "saveObservation";
	const CMD_UPDATE = "updateObservation";
	const CMD_DELETE = "deleteObservation";
	const CMD_SORT = "sortObservations";
	const CMD_CONFIRMED_DELETE = "confirmedDeleteObservation";

	const CMD_DELETE_SELECTED_OBSERVATIONS = "deleteSelectedObservations";
	const CMD_CONFIRMED_DELETE_SELECTED_OBSERVATIONS = "confirmedDeleteSelectedObservations";

	const CMD_SAVE_ORDER = "saveObservationOrder";

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
				,"CMD_DELETE_SELECTED_OBSERVATIONS" => self::CMD_DELETE_SELECTED_OBSERVATIONS
				,"CMD_CONFIRMED_DELETE_SELECTED_OBSERVATIONS" => self::CMD_CONFIRMED_DELETE_SELECTED_OBSERVATIONS
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
			case self::CMD_DELETE_SELECTED_OBSERVATIONS:
			case self::CMD_CONFIRMED_DELETE_SELECTED_OBSERVATIONS:
			case self::CMD_SAVE_ORDER:
				$this->$cmd();
				break;
			default:
				throw new Exception("ilCareerGoalRequirementsGUI:: not known command");
		}
	}

	protected function showObservations() {
		$add_observation_link = $this->gCtrl->getLinkTarget($this, self::CMD_ADD);
		$this->gToolbar->addButton( $this->txt("add_observation"), $add_observation_link);

		$this->setSubTabs(self::CMD_SHOW);

		$gui = new CareerGoal\Observations\ilCareerGoalObservationsTableGUI($this);
		$this->gTpl->setContent($gui->getHTML());
	}

	protected function addObservation() {
		$gui = new CareerGoal\Observations\ilCareerGoalObservationGUI($this, CareerGoal\Observations\ilCareerGoalObservationGUI::MODE_NEW);
		$gui->render();
	}

	protected function editObservation() {
		$gui = new CareerGoal\Observations\ilCareerGoalObservationGUI($this, CareerGoal\Observations\ilCareerGoalObservationGUI::MODE_EDIT, $_GET["obj_id"]);
		$gui->render();
	}

	protected function saveObservation() {
		$gui = new CareerGoal\Observations\ilCareerGoalObservationGUI($this, CareerGoal\Observations\ilCareerGoalObservationGUI::MODE_NEW);
		$gui->save();
	}

	protected function updateObservation() {
		$gui = new CareerGoal\Observations\ilCareerGoalObservationGUI($this, CareerGoal\Observations\ilCareerGoalObservationGUI::MODE_EDIT);
		$gui->update();
	}

	protected function deleteObservation() {
		$to_delete_obj_id = $_GET["obj_id"];
		$observation = $this->getActions()->getObservation($to_delete_obj_id);

		include_once './Services/Utilities/classes/class.ilConfirmationGUI.php';
		$confirm = new ilConfirmationGUI();
		$confirm->addItem('obj_id', $to_delete_obj_id, $observation->getTitle());
		$confirm->setHeaderText($this->txt("question_delete_req"));
		$confirm->setFormAction($this->gCtrl->getFormAction($this));
		$confirm->setConfirm($this->txt('remove'), self::CMD_CONFIRMED_DELETE);
		$confirm->setCancel($this->txt('cancel'), self::CMD_SHOW);
		$this->gTpl->setContent($confirm->getHTML());
	}

	protected function confirmedDeleteObservation() {
		$to_delete_obj_id = $_POST["obj_id"];
		$this->getActions()->deleteObservation($to_delete_obj_id);

		$this->showObservations();
	}

	protected function deleteSelectedObservations() {
		$to_delete_obj_ids = $_POST["id"];

		include_once './Services/Utilities/classes/class.ilConfirmationGUI.php';
		$confirm = new ilConfirmationGUI();
		$confirm->setHeaderText($this->txt("question_delete_req"));
		$confirm->setFormAction($this->gCtrl->getFormAction($this));
		$confirm->setConfirm($this->txt('remove'), self::CMD_CONFIRMED_DELETE_SELECTED_OBSERVATIONS);
		$confirm->setCancel($this->txt('cancel'), self::CMD_SHOW);

		foreach ($to_delete_obj_ids as $value) {
			$observation = $this->getActions()->getObservation($value);
			$confirm->addItem('obj_ids[]', $value, $observation->getTitle());
		}

		$this->gTpl->setContent($confirm->getHTML());
	}

	protected function confirmedDeleteSelectedObservations() {
		$to_delete_obj_ids = $_POST["obj_ids"];
		
		foreach ($to_delete_obj_ids as $value) {
			$this->getActions()->deleteObservation($value);
		}

		$this->showObservations();
	}

	protected function sortObservations() {
		$this->setSubTabs(self::CMD_SORT);

		$gui = new CareerGoal\Observations\ilCareerGoalObservationsTableGUI($this,"","",true);
		$this->gTpl->setContent($gui->getHTML());
	}

	protected function saveObservationOrder() {
		$this->getActions()->updateObservationPosition($_POST);

		\ilUtil::sendSuccess($this->txt("order_saved"), false);
		$this->sortObservations();
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
		$this->gTabs->addSubTab(self::CMD_SHOW, $this->txt("show_observation")
				,$this->gCtrl->getLinkTarget($this, self::CMD_SHOW));

		$this->gTabs->addSubTab(self::CMD_SORT, $this->txt("sort_observation")
				,$this->gCtrl->getLinkTarget($this, self::CMD_SORT));

		$this->gTabs->activateSubTab($active_sub_tab);
	}
}
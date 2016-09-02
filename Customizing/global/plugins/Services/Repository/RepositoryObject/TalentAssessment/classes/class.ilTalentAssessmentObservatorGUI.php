<?php

use CaT\Plugins\TalentAssessment\Observator;

class ilTalentAssessmentObservatorGUI {
	const CMD_SHOW = "showObservator";
	const CMD_ADD = "addObservator";
	const CMD_DELETE = "deleteObservator";
	const CMD_DELETE_SELECTED = "deleteSelectedObservator";
	const CMD_CONFIRMED_DELETE = "confirmedDeleteObservator";

	public function __construct($parent_obj, $actions, Closure $txt, $obj_id) {
		global $tpl, $ilCtrl, $ilToolbar;

		$this->gTpl = $tpl;
		$this->gCtrl = $ilCtrl;
		$this->actions = $actions;
		$this->txt = $txt;
		$this->obj_id = $obj_id;
		$this->gToolbar = $ilToolbar;
		$this->parent_obj = $parent_obj;

		$this->possible_cmd = array(
				"CMD_SHOW" => self::CMD_SHOW
				,"CMD_ADD" => self::CMD_ADD
				,"CMD_DELETE" => self::CMD_DELETE
				,"CMD_DELETE_SELECTED" => self::CMD_DELETE_SELECTED
			);
	}

	public function executeCommand() {
		$cmd = $this->gCtrl->getCMD(self::CMD_SHOW);

		switch($cmd) {
			case self::CMD_SHOW:
			case self::CMD_ADD:
			case self::CMD_DELETE:
			case self::CMD_CONFIRMED_DELETE:
			case self::CMD_DELETE_SELECTED:
				$this->$cmd();
				break;
			default:
				throw new \Exception("ilTalentAssessmentObservatorGUI:: Unknown command ".$cmd);
		}
	}

	public function addObservator(array $user_ids = null, $a_status = null) {
		if(!sizeof($user_ids)) {
			\ilUtil::sendFailure($this->txt("no_users_selected"), true);
			$this->gCtrl->redirect($this, self::CMD_SHOW);
		}

		foreach ($user_ids as $user_id) {
			$this->actions->assignObservator($user_id, $this->obj_id);
		}

		\ilUtil::sendSuccess($this->txt("add_observer_success"), true);
		$this->gCtrl->redirect($this, self::CMD_SHOW);
	}

	protected function showObservator() {
		$gui = new Observator\ilObservatorTableGUI($this);
		$this->gTpl->setContent($gui->getHTML());
	}

	protected function deleteSelectedObservator() {
		$user_ids = $_POST["id"];

		if(!empty($user_ids)) {
			foreach ($user_ids as $user_id) {
				$this->actions->deassignObservator($user_id, $this->obj_id);
			}
		}

		\ilUtil::sendSuccess($this->txt("delete_observer_success"), true);
		$this->gCtrl->redirect($this, self::CMD_SHOW);
	}

	protected function deleteObservator() {
		$user_id = $_GET["usr_id"];

		if(!$user_id) {
			\ilUtil::sendFailure($this->txt("no_users_selected"), true);
			$this->gCtrl->redirect($this, self::CMD_SHOW);
		}

		$this->actions->deassignObservator($user_id, $this->obj_id);
		\ilUtil::sendSuccess($this->txt("delete_observer_success"), true);
		$this->gCtrl->redirect($this, self::CMD_SHOW);
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

	public function getTXTClosure() {
		return $this->txt;
	}

	public function getActions() {
		return $this->actions;
	}

	public function getObjId() {
		return $this->obj_id;
	}

	public function getPossibleCMD() {
		return $this->possible_cmd;
	}
}
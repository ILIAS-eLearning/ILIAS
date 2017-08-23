<?php

use ILIAS\Modules\OrgUnit\ARHelper\BaseCommands;

/**
 * Class ilOrgUnitUserAssignmentGUI
 *
 * @author       Fabian Schmid <fs@studer-raimann.ch>
 *
 * @ilCtrl_Calls ilOrgUnitUserAssignmentGUI: ilRepositorySearchGUI
 */
class ilOrgUnitUserAssignmentGUI extends BaseCommands {

	public function executeCommand() {
		switch ($this->ctrl()->getNextClass()) {
			case strtolower(ilRepositorySearchGUI::class):
				switch ($this->ctrl()->getCmd()) {
					case 'addUserFromAutoComplete':
						if ($_GET['addusertype'] == "staff") {
							$this->addStaff();
						}
						break;
					default:
						$repo = new ilRepositorySearchGUI();
						$this->ctrl()->forwardCommand($repo);
						break;
				}
				break;

			default:
				parent::executeCommand();
				break;
		}
	}


	protected function index() {
		// Header
		$types = ilOrgUnitPosition::getArray('id', 'title');
		//$types = array();
		$this->ctrl()->setParameterByClass(ilRepositorySearchGUI::class, 'addusertype', 'staff');
		ilRepositorySearchGUI::fillAutoCompleteToolbar($this, $this->dic()->toolbar(), array(
			'auto_complete_name' => $this->txt('user'),
			'user_type'          => $types,
			'submit_name'        => $this->txt('add'),
		));

		// Tables
		$html = '';
		foreach (ilOrgUnitPosition::getActiveForPosition($this->getParentRefId()) as $ilOrgUnitPosition) {
			$ilOrgUnitUserAssignmentTableGUI = new ilOrgUnitUserAssignmentTableGUI($this, self::CMD_INDEX, $ilOrgUnitPosition);
			$html .= $ilOrgUnitUserAssignmentTableGUI->getHTML();
		}
		$this->setContent($html);
	}


	protected function cancel() {
		$this->ctrl()->redirect($this, self::CMD_INDEX);
	}


	public function addStaff() {
		if (!$this->dic()->access()->checkAccess("write", "", $this->getParentRefId())) {
			ilUtil::sendFailure($this->lng->txt("permission_denied"), true);
			$this->ctrl()->redirect($this, self::CMD_INDEX);
		}

		$users = explode(',', $_POST['user_login']);
		$user_ids = array();
		foreach ($users as $user) {
			$user_id = ilObjUser::_lookupId($user);
			if ($user_id) {
				$user_ids[] = $user_id;
			}
		}

		if (!count($user_ids)) {
			ilUtil::sendFailure($this->txt("user_not_found"), true);
			$this->ctrl()->redirect($this, "showStaff");
		}

		$position_id = isset($_POST['user_type']) ? $_POST['user_type'] : 0;

		if(!$position_id && !$position = ilOrgUnitPosition::find($position_id)) {
			ilUtil::sendFailure($this->txt("user_not_found"), true);
			$this->ctrl()->redirect($this, "showStaff");
		}
		foreach ($user_ids as $user_id) {
			ilOrgUnitUserAssignment::findOrCreateAssignment($user_id, $position_id, $this->getParentRefId());
		}

		ilUtil::sendSuccess($this->txt("users_successfuly_added"), true);
		$this->ctrl()->redirect($this, self::CMD_INDEX);
	}
}


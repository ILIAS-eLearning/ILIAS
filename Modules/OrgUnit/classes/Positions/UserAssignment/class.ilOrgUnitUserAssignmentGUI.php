<?php

/**
 * Class ilOrgUnitUserAssignmentGUI
 *
 * @author       Fabian Schmid <fs@studer-raimann.ch>
 *
 * @ilCtrl_Calls ilOrgUnitUserAssignmentGUI: ilRepositorySearchGUI
 */
class ilOrgUnitUserAssignmentGUI extends \ILIAS\Modules\OrgUnit\ARHelper\BaseCommands {

	public function executeCommand() {
		switch ($this->ctrl()->getNextClass()) {
			case strtolower(ilRepositorySearchGUI::class):
				$ilRepositorySearchGUI = new ilRepositorySearchGUI();
				$this->ctrl()->forwardCommand($ilRepositorySearchGUI);
				break;

			default:
				parent::executeCommand();
				break;
		}
	}


	protected function index() {
		// Header
		$types = ilOrgUnitPosition::getArray('id', 'title');
		$this->ctrl()->setParameterByClass(ilRepositorySearchGUI::class, 'addusertype', 'staff');
		ilRepositorySearchGUI::fillAutoCompleteToolbar($this, $this->dic()->toolbar(), array(
			'auto_complete_name' => $this->txt('user'),
			'user_type'          => $types,
			'submit_name'        => $this->txt('add'),
		));

		// Tables
		$html = '';
		foreach (ilOrgUnitPosition::get() as $ilOrgUnitPosition) {
			$ilOrgUnitUserAssignmentTableGUI = new ilOrgUnitUserAssignmentTableGUI($this, self::CMD_INDEX, $ilOrgUnitPosition);
			$html .= $ilOrgUnitUserAssignmentTableGUI->getHTML();
		}
		$this->setContent($html);
	}


	protected function cancel() {
		$this->ctrl()->redirect($this, self::CMD_INDEX);
	}
}


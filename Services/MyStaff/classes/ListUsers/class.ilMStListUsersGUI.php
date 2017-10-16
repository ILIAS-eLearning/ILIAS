<?php

/**
 * GUI-Class Table ilMStListUsersGUI
 *
 * @author            Martin Studer <ms@studer-raimann.ch>
 *
 * @ilCtrl_IsCalledBy ilMStListUsersGUI: ilMyStaffGUI
 */
class ilMStListUsersGUI {

	use \ILIAS\Modules\OrgUnit\ARHelper\DIC;
	const CMD_RESET_FILTER = 'resetFilter';
	const CMD_APPLY_FILTER = 'applyFilter';
	const CMD_INDEX = 'index';
	const CMD_ADD_USER_AUTO_COMPLETE = 'addUserAutoComplete';
	/**
	 * @var  ilTable2GUI
	 */
	protected $table;
	/**
	 * @var ilMyStaffAccess
	 */
	protected $access;


	protected function checkAccessOrFail() {
		if (ilMyStaffAccess::getInstance()->hasCurrentUserAccessToMyStaff()) {
			return true;
		} else {
			ilUtil::sendFailure($this->lng()->txt("permission_denied"), true);
			$this->ctrl()->redirectByClass('ilPersonalDesktopGUI', "");
		}
	}


	public function executeCommand() {
		$this->checkAccessOrFail();

		$cmd = $this->ctrl()->getCmd();

		switch ($cmd) {
			case self::CMD_RESET_FILTER:
			case self::CMD_APPLY_FILTER:
			case self::CMD_INDEX:
			case self::CMD_ADD_USER_AUTO_COMPLETE:
				$this->$cmd();
				break;
			default:
				$this->index();
				break;
		}
	}


	public function index() {
		$this->listUsers();
	}


	public function listUsers() {
		$this->table = new ilMStListUsersTableGUI($this, self::CMD_INDEX);
		$this->table->setTitle($this->lng()->txt('mst_list_users'));
		$this->tpl()->setContent($this->table->getHTML());
	}


	public function applyFilter() {
		$this->table = new ilMStListUsersTableGUI($this, self::CMD_APPLY_FILTER);
		$this->table->writeFilterToSession();
		$this->table->resetOffset();
		$this->index();
	}


	public function resetFilter() {
		$this->table = new ilMStListUsersTableGUI($this, self::CMD_RESET_FILTER);
		$this->table->resetOffset();
		$this->table->resetFilter();
		$this->index();
	}


	public function cancel() {
		$this->ctrl()->redirect($this);
	}
}

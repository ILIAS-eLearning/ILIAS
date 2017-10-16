<?php

/**
 * GUI-Class Table ilMStListCoursesGUI
 *
 * @author            Martin Studer <ms@studer-raimann.ch>
 *
 * @ilCtrl_IsCalledBy ilMStListCoursesGUI: ilMyStaffGUI
 * @ilCtrl_Calls      ilMStListCoursesGUI:ilFormPropertyDispatchGUI
 */
class ilMStListCoursesGUI {

	use \ILIAS\Modules\OrgUnit\ARHelper\DIC;
	const CMD_APPLY_FILTER = 'applyFilter';
	const CMD_INDEX = 'index';
	const CMD_RESET_FILTER = 'resetFilter';
	/**
	 * @var \ilMStListCoursesTableGUI
	 */
	protected $table;


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
		$next_class = $this->ctrl()->getNextClass();

		switch ($next_class) {
			case strtolower(ilFormPropertyDispatchGUI::class):
				$this->ctrl()->setReturn($this, self::CMD_INDEX);
				$table = new ilMStListCoursesTableGUI($this, self::CMD_INDEX);
				$table->executeCommand();
				break;
			default:
				switch ($cmd) {
					case self::CMD_RESET_FILTER:
					case self::CMD_APPLY_FILTER:
					case self::CMD_INDEX:
						$this->$cmd();
						break;
					default:
						$this->index();
						break;
				}
				break;
		}
	}


	public function index() {
		$this->listUsers();
	}


	public function listUsers() {
		$this->table = new ilMStListCoursesTableGUI($this, self::CMD_INDEX);
		$this->table->setTitle($this->lng()->txt('mst_list_courses'));
		$this->tpl()->setContent($this->table->getHTML());
	}


	public function applyFilter() {
		$this->table = new ilMStListCoursesTableGUI($this, self::CMD_APPLY_FILTER);
		$this->table->writeFilterToSession();
		$this->table->resetOffset();
		$this->index();
	}


	public function resetFilter() {
		$this->table = new ilMStListCoursesTableGUI($this, self::CMD_RESET_FILTER);
		$this->table->resetOffset();
		$this->table->resetFilter();
		$this->index();
	}


	public function getId() {
		$this->table = new ilMStListCoursesTableGUI($this, self::CMD_INDEX);

		return $this->table->getId();
	}


	public function cancel() {
		$this->ctrl()->redirect($this);
	}
}

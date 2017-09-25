<?php

/**
 * My Staff GUI class
 *
 * @author Martin Studer <ms@studer-raimann.ch>
 */
class ilMyStaffGUI {

	use \ILIAS\Modules\OrgUnit\ARHelper\DIC;
	const CMD_INDEX = 'index';
	const TAB_LIST_USERS = 'list_users';
	const TAB_LIST_COURSES = 'list_courses';


	/**
	 * ilMyStaffGUI constructor.
	 */
	public function __construct() {
		$this->lng()->loadLanguageModule('mst');
		$this->lng()->loadLanguageModule('trac');

		// get the standard template
		$this->tpl()->getStandardTemplate();
		$this->tpl()->setTitle($this->lng()->txt('mst_my_staff'));
	}


	protected function checkAccessOrFail() {
		if (ilMyStaffAccess::getInstance()->hasCurrentUserAccessToMyStaff()) {
			return true;
		} else {
			ilUtil::sendFailure($this->lng()->txt("permission_denied"), true);
			$this->ctrl()->redirectByClass(ilPersonalDesktopGUI::class, "");
		}
	}


	public function executeCommand() {
		$this->checkAccessOrFail();

		// determine next class in the call structure
		$next_class = $this->ctrl()->getNextClass($this);

		switch ($next_class) {
			case strtolower(ilMStListCoursesGUI::class):
				$this->addTabs(self::TAB_LIST_COURSES);
				$list_course_gui = new ilMStListCoursesGUI();
				$this->ctrl()->forwardCommand($list_course_gui);
				break;
			case strtolower(ilMStShowUserGUI::class):
				$list_course_gui = new ilMStShowUserGUI();
				$this->ctrl()->forwardCommand($list_course_gui);
				break;
			default:
				$this->addTabs(self::TAB_LIST_USERS);
				$list_user_gui = new ilMStListUsersGUI();
				$this->ctrl()->forwardCommand($list_user_gui);
				break;
		}

		$this->tpl()->show();
	}


	/**
	 * @param string $active_tab_id
	 */
	protected function addTabs($active_tab_id) {
		$lng = $this->lng();
		$ctrl = $this->ctrl();
		$tabs = $this->tabs();
		$tabs->addTab(self::TAB_LIST_USERS, $lng->txt('mst_list_users'), $ctrl->getLinkTargetByClass(array(
			ilMyStaffGUI::class,
			ilMStListUsersGUI::class,
		), self::CMD_INDEX));
		$tabs->addTab(self::TAB_LIST_COURSES, $lng->txt('mst_list_courses'), $ctrl->getLinkTargetByClass(array(
			ilMyStaffGUI::class,
			ilMStListCoursesGUI::class,
		), self::CMD_INDEX));

		if ($active_tab_id) {
			$tabs->activateTab($active_tab_id);
		}
	}
}

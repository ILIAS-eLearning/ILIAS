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


	public function executeCommand() {
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


	/**
	 * @param ilAdvancedSelectionListGUI $selection
	 * @param int                        $usr_id
	 * @param string                     $return_url
	 *
	 * @return ilAdvancedSelectionListGUI
	 */
	public static function extendActionMenuWithUserActions(ilAdvancedSelectionListGUI $selection, int $usr_id = 0, $return_url = ""): ilAdvancedSelectionListGUI {
		global $DIC;
		$ilUser = $DIC['ilUser'];

		$user_action_collector = ilUserActionCollector::getInstance($ilUser->getId(), new ilAwarenessUserActionContext());
		$action_collection = $user_action_collector->getActionsForTargetUser($usr_id);
		if (count($action_collection->getActions()) > 0) {
			foreach ($action_collection->getActions() as $action) {
				/**
				 * @var ilUserAction $action
				 */
				switch ($action->getType()) {
					case "profile": //personal profile
						$selection->addItem($action->getText(), '', $action->getHref() . "&back_url=" . $return_url);
						break;
					case "compose": //mail
					case "invite": //public chat
					case "invite_osd": //direct chat (start conversation)
						//do only display those actions if the displayed user is not the current user
						if ($usr_id != $ilUser->getId()) {
							$selection->addItem($action->getText(), "", $action->getHref(), "", "", "", "", false, "", "", "", "", true, $action->getData());
						}
						break;
					default:
						$selection->addItem($action->getText(), "", $action->getHref(), "", "", "", "", false, "", "", "", "", true, $action->getData());
						break;
				}
			}
		}

		return $selection;
	}


	public static function getUserLpStatusAsHtml(ilMStListCourse $my_staff_course) {
		global $DIC;
		$dic = $DIC;

		if (ilMyStaffAccess::getInstance()->hasCurrentUserAccessToLearningProgressInObject($my_staff_course->getCrsRefId())) {
			$f = $dic->ui()->factory();
			$renderer = $dic->ui()->renderer();
			$lp_icon = $f->image()
				->standard(ilLearningProgressBaseGUI::_getImagePathForStatus($my_staff_course->getUsrLpStatus()), ilLearningProgressBaseGUI::_getStatusText((int)$my_staff_course->getUsrLpStatus()));

			return $renderer->render($lp_icon) . ' ' . ilLearningProgressBaseGUI::_getStatusText((int)$my_staff_course->getUsrLpStatus());
		}

		return '&nbsp';
	}


	/**
	 * @param ilMStListCourse $my_staff_course
	 *
	 * @return string
	 */
	public static function getUserLpStatusAsText(ilMStListCourse $my_staff_course) {
		if (ilMyStaffAccess::getInstance()->hasCurrentUserAccessToLearningProgressInObject($my_staff_course->getCrsRefId())) {
			return ilLearningProgressBaseGUI::_getStatusText((int)$my_staff_course->getUsrLpStatus());
		}

		return '';
	}
}

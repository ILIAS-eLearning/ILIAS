<?php

/**
 * Class ilMyStaffGUI
 *
 * @author Martin Studer <ms@studer-raimann.ch>
 */
class ilMyStaffGUI
{
    const CMD_INDEX = 'index';
    const TAB_LIST_USERS = 'list_users';
    const TAB_LIST_COURSES = 'list_courses';


    /**
     *
     */
    public function __construct()
    {
        global $DIC;

        $DIC->language()->loadLanguageModule('mst');
        $DIC->language()->loadLanguageModule('trac');

        // get the standard template
        $DIC->ui()->mainTemplate()->getStandardTemplate();
        $DIC->ui()->mainTemplate()->setTitle($DIC->language()->txt('mst_my_staff'));
    }


    /**
     *
     */
    public function executeCommand()
    {
        global $DIC;

        // determine next class in the call structure
        $next_class = $DIC->ctrl()->getNextClass($this);

        switch ($next_class) {
            case strtolower(ilMStListCoursesGUI::class):
                $this->addTabs(self::TAB_LIST_COURSES);
                $list_course_gui = new ilMStListCoursesGUI();
                $DIC->ctrl()->forwardCommand($list_course_gui);
                break;
            case strtolower(ilMStShowUserGUI::class):
                $list_course_gui = new ilMStShowUserGUI();
                $DIC->ctrl()->forwardCommand($list_course_gui);
                break;
            default:
                $this->addTabs(self::TAB_LIST_USERS);
                $list_user_gui = new ilMStListUsersGUI();
                $DIC->ctrl()->forwardCommand($list_user_gui);
                break;
        }

        $DIC->ui()->mainTemplate()->show();
    }


    /**
     * @param string $active_tab_id
     */
    protected function addTabs($active_tab_id)
    {
        global $DIC;

        $DIC->tabs()->addTab(self::TAB_LIST_USERS, $DIC->language()->txt('mst_list_users'), $DIC->ctrl()->getLinkTargetByClass(array(
            self::class,
            ilMStListUsersGUI::class,
        ), self::CMD_INDEX));
        $DIC->tabs()->addTab(self::TAB_LIST_COURSES, $DIC->language()->txt('mst_list_courses'), $DIC->ctrl()->getLinkTargetByClass(array(
            self::class,
            ilMStListCoursesGUI::class,
        ), self::CMD_INDEX));

        if ($active_tab_id) {
            $DIC->tabs()->activateTab($active_tab_id);
        }
    }


    /**
     * @param ilAdvancedSelectionListGUI $selection
     * @param int                        $usr_id
     * @param string                     $return_url
     *
     * @return ilAdvancedSelectionListGUI
     */
    public static function extendActionMenuWithUserActions(ilAdvancedSelectionListGUI $selection, $usr_id = 0, $return_url = "")
    {
        global $DIC;

        $user_action_collector = ilUserActionCollector::getInstance($DIC->user()->getId(), new ilAwarenessUserActionContext());
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
                        if ($usr_id != $DIC->user()->getId()) {
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


    /**
     * @param ilMStListCourse $my_staff_course
     *
     * @return string
     */
    public static function getUserLpStatusAsHtml(ilMStListCourse $my_staff_course)
    {
        global $DIC;

        if (ilMyStaffAccess::getInstance()->hasCurrentUserAccessToLearningProgressInObject($my_staff_course->getCrsRefId())) {
            $lp_icon = $DIC->ui()->factory()->image()
                ->standard(ilLearningProgressBaseGUI::_getImagePathForStatus($my_staff_course->getUsrLpStatus()), ilLearningProgressBaseGUI::_getStatusText(intval($my_staff_course->getUsrLpStatus())));

            return $DIC->ui()->renderer()->render($lp_icon) . ' '
                . ilLearningProgressBaseGUI::_getStatusText(intval($my_staff_course->getUsrLpStatus()));
        }

        return '&nbsp';
    }


    /**
     * @param ilMStListCourse $my_staff_course
     *
     * @return string
     */
    public static function getUserLpStatusAsText(ilMStListCourse $my_staff_course)
    {
        if (ilMyStaffAccess::getInstance()->hasCurrentUserAccessToLearningProgressInObject($my_staff_course->getCrsRefId())) {
            return ilLearningProgressBaseGUI::_getStatusText(intval($my_staff_course->getUsrLpStatus()));
        }

        return '';
    }
}

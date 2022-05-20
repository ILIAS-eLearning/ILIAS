<?php

use ILIAS\MyStaff\ilMyStaffAccess;
use ILIAS\MyStaff\ListCourses\ilMStListCourse;

/**
 * Class ilMyStaffGUI
 * @author Martin Studer <ms@studer-raimann.ch>
 */
class ilMyStaffGUI
{
    public const CMD_INDEX = 'index';
    public const TAB_LIST_USERS = 'list_users';
    public const TAB_LIST_COURSES = 'list_courses';
    public const TAB_LIST_CERTIFICATES = 'list_certificates';
    public const TAB_LIST_COMPETENCES = 'list_competences';
    public const TAB_LIST_STUDY_PROGRAMME = 'list_study_programme';

    public function __construct()
    {
        global $DIC;

        $DIC->language()->loadLanguageModule('mst');
        $DIC->language()->loadLanguageModule('trac');

        // get the standard template
        $DIC->ui()->mainTemplate()->loadStandardTemplate();
        $DIC->ui()->mainTemplate()->setTitle($DIC->language()->txt('mst_my_staff'));
    }

    final public function executeCommand() : void
    {
        global $DIC;

        // determine next class in the call structure
        $next_class = $DIC->ctrl()->getNextClass($this);

        switch ($next_class) {
            case strtolower(ilMStListCoursesGUI::class):
                $list_gui = new ilMStListCoursesGUI();
                $DIC->ctrl()->forwardCommand($list_gui);
                break;
            case strtolower(ilMStListCertificatesGUI::class):
                $list_gui = new ilMStListCertificatesGUI();
                $DIC->ctrl()->forwardCommand($list_gui);
                break;
            case strtolower(ilMStListCompetencesGUI::class):
                $list_gui = new ilMStListCompetencesGUI($DIC);
                $DIC->ctrl()->forwardCommand($list_gui);
                break;
//            case strtolower(ilMStListStudyProgrammesGUI::class):
//                $list_gui = new ilMStListStudyProgrammesGUI();
//                $DIC->ctrl()->forwardCommand($list_gui);
//                break;
            case strtolower(ilMStShowUserGUI::class):
                $user_gui = new ilMStShowUserGUI();
                $DIC->ctrl()->forwardCommand($user_gui);
                break;
            case strtolower(ilEmployeeTalkMyStaffListGUI::class):
                $user_gui = new ilEmployeeTalkMyStaffListGUI();
                $DIC->ctrl()->forwardCommand($user_gui);
                break;
            default:
                $list_gui = new ilMStListUsersGUI();
                $DIC->ctrl()->forwardCommand($list_gui);
                break;
        }

        $DIC->ui()->mainTemplate()->printToStdout();
    }

    final public static function extendActionMenuWithUserActions(
        ilAdvancedSelectionListGUI $selection,
        int $usr_id = 0,
        string $return_url = ""
    ) : ilAdvancedSelectionListGUI {
        global $DIC;

        $user_action_collector = ilUserActionCollector::getInstance(
            $DIC->user()->getId(),
            new ilAwarenessUserActionContext()
        );
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
                            $selection->addItem(
                                $action->getText(),
                                "",
                                $action->getHref(),
                                "",
                                "",
                                "",
                                "",
                                false,
                                "",
                                "",
                                "",
                                "",
                                true,
                                $action->getData()
                            );
                        }
                        break;
                    default:
                        $selection->addItem(
                            $action->getText(),
                            "",
                            $action->getHref(),
                            "",
                            "",
                            "",
                            "",
                            false,
                            "",
                            "",
                            "",
                            "",
                            true,
                            $action->getData()
                        );
                        break;
                }
            }
        }

        return $selection;
    }

    final public static function getUserLpStatusAsHtml(ilMStListCourse $my_staff_course) : string
    {
        global $DIC;

        if (ilMyStaffAccess::getInstance()->hasCurrentUserAccessToLearningProgressInObject($my_staff_course->getCrsRefId())) {
            $lp_icon = $DIC->ui()->factory()->image()
                           ->standard(
                               ilLearningProgressBaseGUI::_getImagePathForStatus($my_staff_course->getUsrLpStatus()),
                               ilLearningProgressBaseGUI::_getStatusText(intval($my_staff_course->getUsrLpStatus()))
                           );

            return $DIC->ui()->renderer()->render($lp_icon) . ' '
                . ilLearningProgressBaseGUI::_getStatusText(intval($my_staff_course->getUsrLpStatus()));
        }

        return '&nbsp';
    }

    final public static function getUserLpStatusAsText(ilMStListCourse $my_staff_course) : string
    {
        if (ilMyStaffAccess::getInstance()->hasCurrentUserAccessToLearningProgressInObject($my_staff_course->getCrsRefId())) {
            return ilLearningProgressBaseGUI::_getStatusText(intval($my_staff_course->getUsrLpStatus()));
        }

        return '';
    }
}

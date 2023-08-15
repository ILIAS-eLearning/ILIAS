<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

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
    }

    final public function executeCommand(): void
    {
        global $DIC;

        // determine next class in the call structure
        $next_class = $DIC->ctrl()->getNextClass($this);
        switch ($next_class) {
            case "ilmstlistcoursesgui":
                $list_gui = new \ilMStListCoursesGUI();
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

    /**
     * @return \ILIAS\UI\Component\Button\Button[]|\ILIAS\UI\Component\Link\Link[]
     */
    final public static function extendActionMenuWithUserActions(
        int $usr_id = 0,
        string $return_url = ""
    ): array {
        global $DIC;
        $ui_fac = $DIC->ui()->factory();

        $user_action_collector = new ilUserActionCollector(
            $DIC->user()->getId(),
            new ilAwarenessUserActionContext(),
            new ilUserActionProviderFactory(),
            new ilUserActionAdmin($DIC['ilDB'])
        );
        $action_collection = $user_action_collector->getActionsForTargetUser($usr_id);
        $actions = [];
        if (count($action_collection->getActions()) > 0) {
            foreach ($action_collection->getActions() as $action) {
                /**
                 * @var ilUserAction $action
                 */
                switch ($action->getType()) {
                    case "profile": //personal profile
                        $actions[] = $ui_fac->link()->standard(
                            $action->getText(),
                            $action->getHref() . "&back_url=" . $return_url
                        );
                        break;
                    case "compose": //mail
                    case "invite": //public chat
                    case "invite_osd": //direct chat (start conversation)
                        //do only display those actions if the displayed user is not the current user
                        if ($usr_id != $DIC->user()->getId()) {
                            $actions[] = self::addButtonWithActionData($action);
                        }
                        break;
                    default:
                        $actions[] = self::addButtonWithActionData($action);
                        break;
                }
            }
        }

        return $actions;
    }

    protected static function addButtonWithActionData(ilUserAction $action): \ILIAS\UI\Component\Button\Shy
    {
        global $DIC;

        $ui_fac = $DIC->ui()->factory();

        $action_data = $action->getData();
        $button = $ui_fac->button()->shy(
            $action->getText(),
            $action->getHref()
        )->withAdditionalOnLoadCode(function ($id) use ($action_data) {
            $r = "var button = document.getElementById('$id');";
            foreach ($action_data as $k => $v) {
                $r .= "button.setAttribute('data-" . $k . "', '" . $v . "');";
            }
            return $r;
        });

        return $button;
    }

    final public static function getUserLpStatusAsHtml(ilMStListCourse $my_staff_course): string
    {
        global $DIC;

        if (ilMyStaffAccess::getInstance()->hasCurrentUserAccessToLearningProgressInObject($my_staff_course->getCrsRefId())) {
            $lp_icons = ilLPStatusIcons::getInstance(ilLPStatusIcons::ICON_VARIANT_LONG);
            $lp_icon = $DIC->ui()->factory()->symbol()->icon()
                           ->custom(
                               $lp_icons->getImagePathForStatus($my_staff_course->getUsrLpStatus()),
                               ilLearningProgressBaseGUI::_getStatusText(intval($my_staff_course->getUsrLpStatus()))
                           );

            return $DIC->ui()->renderer()->render($lp_icon) . ' '
                . ilLearningProgressBaseGUI::_getStatusText(intval($my_staff_course->getUsrLpStatus()));
        }

        return '&nbsp';
    }

    final public static function getUserLpStatusAsText(ilMStListCourse $my_staff_course): string
    {
        if (ilMyStaffAccess::getInstance()->hasCurrentUserAccessToLearningProgressInObject($my_staff_course->getCrsRefId())) {
            return ilLearningProgressBaseGUI::_getStatusText(intval($my_staff_course->getUsrLpStatus()));
        }

        return '';
    }
}

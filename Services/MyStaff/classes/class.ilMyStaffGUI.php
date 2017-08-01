<?php
/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once './Services/MyStaff/classes/ListUsers/class.ilMStListUsersGUI.php';
require_once './Services/MyStaff/classes/ListCourses/class.ilMStListCoursesGUI.php';
require_once './Services/MyStaff/classes/ShowUser/class.ilMStShowUserGUI.php';

/**
 * My Staff GUI class
 *
 * @author Martin Studer <ms@studer-raimann.ch>
 * @version $Id$
 */
class ilMyStaffGUI
{
    /**
     * Constructor
     *
     * @param
     * @return
     */
    function __construct()
    {
        global $tpl, $ilCtrl, $ilTabs, $lng;
        /**
         * @var $tpl ilTemplate
         * @var $ilCtrl ilCtrl
         * @var $ilTabs ilTabsGUI
         * @var $lng ilLanguage
         */

        $this->ctrl = $ilCtrl;
        $this->tabs = $ilTabs;
        $this->lng = $lng;

        // get the standard template
        $tpl->getStandardTemplate();
    }

    /**
     * Execute command
     *
     * @param
     * @return
     */
    function executeCommand()
    {
        global $ilCtrl, $tpl;

        // determine next class in the call structure
        $next_class = $ilCtrl->getNextClass($this);

        switch($next_class)
        {
            case 'ilmstlistcoursesgui':
                $this->addTabs('list_courses');
                $list_course_gui = new ilMStListCoursesGUI();
                $ilCtrl->forwardCommand($list_course_gui);
                break;
            case 'ilmstshowusergui':
                $list_course_gui = new ilMStShowUserGUI();
                $ilCtrl->forwardCommand($list_course_gui);
                break;
            // this would be the way to call a sub-GUI class
            /*                        case "ilbargui":
                                            $bar_gui = new ilBarGUI(...);
                                            $ret = $ilCtrl->forwardCommand($bar_gui);
                                            break;*/

            // process command, if current class is responsible to do so
            default:
                $this->addTabs('list_users');
                $list_user_gui = new ilMStListUsersGUI();
                $ilCtrl->forwardCommand($list_user_gui);
                break;
        }

        $tpl->show();
    }

    /**
     * View hello world...
     *
     */
    function view()
    {
        global $tpl;

        $tpl->setContent("Hello World.");
    }


    public function addTabs($active_tab_id) {

        //TODO
        //if($this->access->hasCurrentUserSkillManagementPermission()) {
            $this->tabs->addTab('list_users', $this->lng->txt('list_users'), $this->ctrl->getLinkTargetByClass(array("ilMyStaffGUI","ilMStListUsersGUI"), 'index'));
        //}

        //TODO
        //if($this->access->hasCurrentUserSkillManagementPermission()) {
            $this->tabs->addTab('list_courses', $this->lng->txt('list_courses'), $this->ctrl->getLinkTargetByClass(array("ilMyStaffGUI","ilMStListCoursesGUI"), 'index'));
        //}


        if($active_tab_id) {
            $this->tabs->activateTab($active_tab_id);
        }

    }

}

?>
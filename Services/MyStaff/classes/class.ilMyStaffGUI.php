<?php
/**
 * My Staff GUI class
 *
 * @author Martin Studer <ms@studer-raimann.ch>
 */
class ilMyStaffGUI
{
    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    /**
     * Constructor
     *
     * @param
     * @return
     */
    function __construct()
    {
        global $tpl, $ilCtrl, $ilTabs, $lng;
        $this->ctrl = $ilCtrl;
        $this->tabs = $ilTabs;
        $this->lng = $lng;
        $this->lng->loadLanguageModule('mst');
        $this->lng->loadLanguageModule('trac');

        // get the standard template
        $tpl->getStandardTemplate();

        $tpl->setTitle($this->lng->txt('mst_my_staff'));
    }

    protected function checkAccessOrFail() {
        if (ilMyStaffAcess::getInstance()->hasCurrentUserAccessToMyStaff()) {
            return true;
        } else {
            ilUtil::sendFailure($this->lng->txt("permission_denied"), true);
            $this->ctrl->redirectByClass('ilPersonalDesktopGUI', "");
        }
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

        $this->checkAccessOrFail();

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
            default:
                $this->addTabs('list_users');
                $list_user_gui = new ilMStListUsersGUI();
                $ilCtrl->forwardCommand($list_user_gui);
                break;
        }

        $tpl->show();
    }

    public function addTabs($active_tab_id) {
        $this->tabs->addTab('list_users', $this->lng->txt('mst_list_users'), $this->ctrl->getLinkTargetByClass(array("ilMyStaffGUI","ilMStListUsersGUI"), 'index'));
        $this->tabs->addTab('list_courses', $this->lng->txt('mst_list_courses'), $this->ctrl->getLinkTargetByClass(array("ilMyStaffGUI","ilMStListCoursesGUI"), 'index'));

        if($active_tab_id) {
            $this->tabs->activateTab($active_tab_id);
        }
    }

}

?>
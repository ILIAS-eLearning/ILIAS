<?php

/**
 * Class ilMStShowUserGUI
 *
 * @author            Martin Studer <ms@studer-raimann.ch>
 *
 * @ilCtrl_IsCalledBy ilMStShowUserGUI: ilMyStaffGUI
 * @ilCtrl_Calls      ilMStShowUserGUI: ilFormPropertyDispatchGUI
 */
class ilMStShowUserGUI
{
    const CMD_INDEX = 'index';
    const CMD_SHOWUSER = 'showUser';
    const CMD_RESET_FILTER = 'resetFilter';
    const CMD_APPLY_FILTER = 'applyFilter';
    const TAB_SHOW_COURSES = 'show_courses';
    const TAB_SHOW_USER = 'show_user';
    /**
     * @var int
     */
    protected $usr_id;
    /**
     * @var ilTable2GUI
     */
    protected $table;
    /**
     * @var ilMyStaffAccess
     */
    protected $access;


    /**
     *
     */
    public function __construct()
    {
        global $DIC;

        $this->access = ilMyStaffAccess::getInstance();

        $this->usr_id = $DIC->http()->request()->getQueryParams()['usr_id'];
        $DIC->ctrl()->setParameter($this, 'usr_id', $this->usr_id);

        $DIC->ui()->mainTemplate()->setTitle(ilUserUtil::getNamePresentation($this->usr_id));
        $DIC->ui()->mainTemplate()->setTitleIcon(ilObjUser::_getPersonalPicturePath($this->usr_id, "xxsmall"));
    }


    /**
     *
     */
    protected function checkAccessOrFail()
    {
        global $DIC;

        if (!$this->usr_id) {
            ilUtil::sendFailure($DIC->language()->txt("permission_denied"), true);
            $DIC->ctrl()->redirectByClass(ilPersonalDesktopGUI::class, "");
        }

        if ($this->access->hasCurrentUserAccessToMyStaff()
            && $this->access->hasCurrentUserAccessToUser($this->usr_id)) {
            return;
        } else {
            ilUtil::sendFailure($DIC->language()->txt("permission_denied"), true);
            $DIC->ctrl()->redirectByClass(ilPersonalDesktopGUI::class, "");
        }
    }


    /**
     *
     */
    public function executeCommand()
    {
        global $DIC;

        $this->checkAccessOrFail();

        $cmd = $DIC->ctrl()->getCmd();
        $next_class = $DIC->ctrl()->getNextClass();

        switch ($next_class) {
            case strtolower(ilFormPropertyDispatchGUI::class):
                $DIC->ctrl()->setReturn($this, self::CMD_INDEX);
                $this->table = new ilMStShowUserCoursesTableGUI($this, self::CMD_INDEX);
                $this->table->executeCommand();
                break;
            default:
                switch ($cmd) {
                    case self::CMD_RESET_FILTER:
                    case self::CMD_APPLY_FILTER:
                    case self::CMD_INDEX:
                        $this->addTabs(self::TAB_SHOW_COURSES);
                        $this->$cmd();
                        break;
                    case self::CMD_SHOWUSER:
                        $this->addTabs(self::TAB_SHOW_USER);
                        $this->$cmd();
                        break;
                    default:
                        $this->addTabs(self::TAB_SHOW_COURSES);
                        $this->index();
                        break;
                }
        }
    }


    /**
     *
     */
    protected function index()
    {
        $this->listUsers();
    }


    /**
     *
     */
    protected function listUsers()
    {
        global $DIC;

        $this->table = new ilMStShowUserCoursesTableGUI($this, self::CMD_INDEX);
        $this->table->setTitle(sprintf($DIC->language()->txt('mst_courses_of'), ilObjCourse::_lookupTitle($this->usr_id)));

        $DIC->ui()->mainTemplate()->setContent($this->table->getHTML());
    }


    /**
     *
     */
    protected function showUser()
    {
        global $DIC;

        //Redirect if Profile is not public
        $user = new ilObjUser($this->usr_id);
        if (!$user->hasPublicProfile()) {
            $DIC->ctrl()->redirectByClass(self::class, self::CMD_INDEX);
        }

        $pub_profile = new ilPublicUserProfileGUI($this->usr_id);
        $DIC->ui()->mainTemplate()->setContent($pub_profile->getEmbeddable());
    }


    /**
     *
     */
    protected function applyFilter()
    {
        $this->table = new ilMStShowUserCoursesTableGUI($this, self::CMD_APPLY_FILTER);
        $this->table->writeFilterToSession();
        $this->table->resetOffset();
        $this->index();
    }


    /**
     *
     */
    protected function resetFilter()
    {
        $this->table = new ilMStShowUserCoursesTableGUI($this, self::CMD_RESET_FILTER);
        $this->table->resetOffset();
        $this->table->resetFilter();
        $this->index();
    }


    /**
     * @return string
     */
    public function getId()
    {
        $this->table = new ilMStShowUserCoursesTableGUI($this, self::CMD_INDEX);

        return $this->table->getId();
    }


    /**
     *
     */
    public function cancel()
    {
        global $DIC;

        $DIC->ctrl()->redirect($this);
    }


    /**
     * @param string $active_tab_id
     */
    protected function addTabs($active_tab_id)
    {
        global $DIC;

        $DIC->tabs()->setBackTarget($DIC->language()->txt('mst_list_users'), $DIC->ctrl()->getLinkTargetByClass(array(
            ilMyStaffGUI::class,
            ilMStListUsersGUI::class,
        )));
        $DIC->tabs()->addTab(self::TAB_SHOW_COURSES, $DIC->language()->txt('mst_show_courses'), $DIC->ctrl()->getLinkTargetByClass(array(
            ilMyStaffGUI::class,
            self::class,
        ), self::CMD_INDEX));

        $user = new ilObjUser($this->usr_id);
        if ($user->hasPublicProfile()) {
            $DIC->ctrl()->setParameterByClass(self::class, 'usr_id', $this->usr_id);
            $public_profile_url = $DIC->ctrl()->getLinkTargetByClass(self::class, self::CMD_SHOWUSER);
            $DIC->tabs()->addTab(self::TAB_SHOW_USER, $DIC->language()->txt('public_profile'), $public_profile_url);
        }

        if ($active_tab_id) {
            $DIC->tabs()->activateTab($active_tab_id);
        }
    }
}

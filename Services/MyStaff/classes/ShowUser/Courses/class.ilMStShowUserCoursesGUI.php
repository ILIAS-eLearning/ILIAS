<?php

use ILIAS\MyStaff\Courses\ShowUser\ilMStShowUserCoursesTableGUI;
use ILIAS\MyStaff\ilMyStaffAccess;

/**
 * Class ilMStShowUserCoursesGUI
 *
 * @package           ILIAS\MyStaff\Courses\ShowUser
 *
 * @author            Theodor Truffer <tt@studer-raimann.ch>
 *
 * @ilCtrl_IsCalledBy ilMStShowUserCoursesGUI: ilMStShowUserGUI
 * @ilCtrl_Calls      ilMStShowUserCoursesGUI: ilFormPropertyDispatchGUI
 */
class ilMStShowUserCoursesGUI
{
    const CMD_INDEX = 'index';
    const CMD_RESET_FILTER = 'resetFilter';
    const CMD_APPLY_FILTER = 'applyFilter';
    const CMD_GET_ACTIONS = "getActions";
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
    }


    /**
     *
     */
    protected function checkAccessOrFail()
    {
        global $DIC;

        if (!$this->usr_id) {
            ilUtil::sendFailure($DIC->language()->txt("permission_denied"), true);
            $DIC->ctrl()->redirectByClass(ilDashboardGUI::class, "");
        }

        if ($this->access->hasCurrentUserAccessToUser($this->usr_id)
            && $this->access->hasCurrentUserAccessToCourseMemberships()
        ) {
            return;
        } else {
            ilUtil::sendFailure($DIC->language()->txt("permission_denied"), true);
            $DIC->ctrl()->redirectByClass(ilDashboardGUI::class, "");
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
                    case self::CMD_GET_ACTIONS:
                        $this->$cmd();
                        break;
                    default:
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
}

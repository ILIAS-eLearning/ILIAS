<?php

use ILIAS\MyStaff\ilMyStaffAccess;
use ILIAS\MyStaff\ListCourses\ilMStListCoursesTableGUI;

/**
 * Class ilMStListCoursesGUI
 *
 * @author            Martin Studer <ms@studer-raimann.ch>
 *
 * @ilCtrl_IsCalledBy ilMStListCoursesGUI: ilMyStaffGUI
 * @ilCtrl_Calls      ilMStListCoursesGUI: ilFormPropertyDispatchGUI
 */
class ilMStListCoursesGUI
{
    const CMD_APPLY_FILTER = 'applyFilter';
    const CMD_INDEX = 'index';
    const CMD_GET_ACTIONS = "getActions";
    const CMD_RESET_FILTER = 'resetFilter';
    /**
     * @var ilTable2GUI
     */
    protected $table;
    /**
     * @var ilMyStaffAccess
     */
    protected $access;
    /**
     * @var ilHelp
     */
    protected $help;


    /**
     *
     */
    public function __construct()
    {
        global $DIC;
        $this->access = ilMyStaffAccess::getInstance();
        $this->help = $DIC->help();
        $this->help->setScreenIdComponent('msta');
    }


    /**
     *
     */
    protected function checkAccessOrFail()
    {
        global $DIC;

        if ($this->access->hasCurrentUserAccessToCourseMemberships()) {
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

        $cmd = $DIC->ctrl()->getCmd();
        $next_class = $DIC->ctrl()->getNextClass();

        switch ($next_class) {
            case strtolower(ilFormPropertyDispatchGUI::class):
                $this->checkAccessOrFail();

                $DIC->ctrl()->setReturn($this, self::CMD_INDEX);
                $this->table = new ilMStListCoursesTableGUI($this, self::CMD_INDEX);
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
                break;
        }
    }


    /**
     *
     */
    public function index()
    {
        $this->listUsers();
    }


    /**
     *
     */
    public function listUsers()
    {
        global $DIC;

        $this->checkAccessOrFail();
        $this->help->setScreenId('courses_list');

        $this->table = new ilMStListCoursesTableGUI($this, self::CMD_INDEX);
        $DIC->ui()->mainTemplate()->setTitle($DIC->language()->txt('mst_list_courses'));
        $DIC->ui()->mainTemplate()->setTitleIcon(ilUtil::getImagePath('icon_enrl.svg'));
        $DIC->ui()->mainTemplate()->setContent($this->table->getHTML());
    }


    /**
     *
     */
    public function applyFilter()
    {
        $this->table = new ilMStListCoursesTableGUI($this, self::CMD_APPLY_FILTER);
        $this->table->writeFilterToSession();
        $this->table->resetOffset();
        $this->index();
    }


    /**
     *
     */
    public function resetFilter()
    {
        $this->table = new ilMStListCoursesTableGUI($this, self::CMD_RESET_FILTER);
        $this->table->resetOffset();
        $this->table->resetFilter();
        $this->index();
    }


    /**
     * @return string
     */
    public function getId()
    {
        $this->table = new ilMStListCoursesTableGUI($this, self::CMD_INDEX);

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

<?php

use ILIAS\MyStaff\ilMyStaffAccess;
use ILIAS\MyStaff\ListUsers\ilMStListUsersTableGUI;

/**
 * Class ilMStListUsersGUI
 *
 * @author            Martin Studer <ms@studer-raimann.ch>
 *
 * @ilCtrl_IsCalledBy ilMStListUsersGUI: ilMyStaffGUI
 */
class ilMStListUsersGUI
{
    const CMD_RESET_FILTER = 'resetFilter';
    const CMD_APPLY_FILTER = 'applyFilter';
    const CMD_INDEX = 'index';
    const CMD_GET_ACTIONS = "getActions";
    const CMD_ADD_USER_AUTO_COMPLETE = 'addUserAutoComplete';
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

        if ($this->access->hasCurrentUserAccessToMyStaff()) {
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

        switch ($cmd) {
            case self::CMD_RESET_FILTER:
            case self::CMD_APPLY_FILTER:
            case self::CMD_INDEX:
            case self::CMD_ADD_USER_AUTO_COMPLETE:
            case self::CMD_GET_ACTIONS:
                $this->$cmd();
                break;
            default:
                $this->index();
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

        $this->help->setScreenId('users_list');
        $this->table = new ilMStListUsersTableGUI($this, self::CMD_INDEX);
        $DIC->ui()->mainTemplate()->setTitle($DIC->language()->txt('mst_list_users'));
        $DIC->ui()->mainTemplate()->setTitleIcon(ilUtil::getImagePath('icon_stff.svg'));
        $DIC->ui()->mainTemplate()->setContent($this->table->getHTML());
    }


    /**
     *
     */
    public function applyFilter()
    {
        $this->table = new ilMStListUsersTableGUI($this, self::CMD_APPLY_FILTER);
        $this->table->writeFilterToSession();
        $this->table->resetOffset();
        $this->index();
    }


    /**
     *
     */
    public function resetFilter()
    {
        $this->table = new ilMStListUsersTableGUI($this, self::CMD_RESET_FILTER);
        $this->table->resetOffset();
        $this->table->resetFilter();
        $this->index();
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

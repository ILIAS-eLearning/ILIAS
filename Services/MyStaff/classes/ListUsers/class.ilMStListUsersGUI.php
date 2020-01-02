<?php

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
     *
     */
    public function __construct()
    {
        $this->access = ilMyStaffAccess::getInstance();
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

        $this->table = new ilMStListUsersTableGUI($this, self::CMD_INDEX);
        $this->table->setTitle($DIC->language()->txt('mst_list_users'));
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


    /**
     *
     */
    public function getActions()
    {
        global $DIC;

        $mst_lus_usr_id = $DIC->http()->request()->getQueryParams()['mst_lus_usr_id'];
        if ($mst_lus_usr_id > 0) {
            $selection = new ilAdvancedSelectionListGUI();

            $DIC->ctrl()->setParameterByClass(ilMStShowUserGUI::class, 'usr_id', $mst_lus_usr_id);
            $selection->addItem($DIC->language()->txt('mst_show_courses'), '', $DIC->ctrl()->getLinkTargetByClass(array(
                ilPersonalDesktopGUI::class,
                ilMyStaffGUI::class,
                ilMStShowUserGUI::class,
            )));

            $selection = ilMyStaffGUI::extendActionMenuWithUserActions($selection, $mst_lus_usr_id, rawurlencode($DIC->ctrl()
                ->getLinkTarget($this, self::CMD_INDEX)));

            echo $selection->getHTML(true);
        }
        exit;
    }
}

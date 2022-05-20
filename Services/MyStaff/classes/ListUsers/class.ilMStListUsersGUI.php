<?php
declare(strict_types=1);

use ILIAS\MyStaff\ilMyStaffAccess;
use ILIAS\MyStaff\ListUsers\ilMStListUsersTableGUI;

/**
 * Class ilMStListUsersGUI
 * @author            Martin Studer <ms@studer-raimann.ch>
 * @ilCtrl_IsCalledBy ilMStListUsersGUI: ilMyStaffGUI
 */
class ilMStListUsersGUI
{
    public const CMD_RESET_FILTER = 'resetFilter';
    public const CMD_APPLY_FILTER = 'applyFilter';
    public const CMD_INDEX = 'index';
    public const CMD_GET_ACTIONS = "getActions";
    public const CMD_ADD_USER_AUTO_COMPLETE = 'addUserAutoComplete';
    protected ilMStListUsersTableGUI $table;
    protected ilMyStaffAccess $access;
    private \ilGlobalTemplateInterface $main_tpl;
    private ILIAS\HTTP\Services $http;
    private ILIAS\Refinery\Factory $refinery;
    private ilHelpGUI $help;
    private ilCtrlInterface $ctrl;
    private ilLanguage $language;

    public function __construct()
    {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->access = ilMyStaffAccess::getInstance();
        $this->http = $DIC->http();
        $this->help = $DIC->help();
        $this->refinery = $DIC->refinery();
        $this->ctrl = $DIC->ctrl();
        $this->language = $DIC->language();
        $this->help->setScreenIdComponent('msta');
    }

    protected function checkAccessOrFail() : void
    {
        if ($this->access->hasCurrentUserAccessToMyStaff()) {
            return;
        } else {
            $this->main_tpl->setOnScreenMessage('failure', $this->language->txt("permission_denied"), true);
            $this->ctrl->redirectByClass(ilDashboardGUI::class, "");
        }
    }

    final public function executeCommand() : void
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

    final public function index() : void
    {
        $this->listUsers();
    }

    final public function listUsers() : void
    {
        global $DIC;

        $this->help->setScreenId('users_list');
        $this->table = new ilMStListUsersTableGUI($this, self::CMD_INDEX);
        $DIC->ui()->mainTemplate()->setTitle($DIC->language()->txt('mst_list_users'));
        $DIC->ui()->mainTemplate()->setContent($this->table->getHTML());
    }

    final public function applyFilter() : void
    {
        $this->table = new ilMStListUsersTableGUI($this, self::CMD_APPLY_FILTER);
        $this->table->writeFilterToSession();
        $this->table->resetOffset();
        $this->index();
    }

    final public function resetFilter() : void
    {
        $this->table = new ilMStListUsersTableGUI($this, self::CMD_RESET_FILTER);
        $this->table->resetOffset();
        $this->table->resetFilter();
        $this->index();
    }

    final public function cancel() : void
    {
        global $DIC;

        $DIC->ctrl()->redirect($this);
    }

    final public function getActions() : void
    {
        global $DIC;
        
        if (!$this->http->wrapper()->query()->has('mst_lus_usr_id')) {
            exit;
        }
        
        $mst_lus_usr_id = $this->http->wrapper()->query()->retrieve('mst_lus_usr_id', $this->refinery->kindlyTo()->int());

        if ($mst_lus_usr_id > 0) {
            exit;
        }

        $selection = new ilAdvancedSelectionListGUI();

        if ($this->access->hasCurrentUserAccessToMyStaff()) {
            $DIC->ctrl()->setParameterByClass(ilMStShowUserCoursesGUI::class, 'usr_id', $mst_lus_usr_id);
            $selection->addItem(
                $DIC->language()->txt('mst_show_courses'),
                '',
                $DIC->ctrl()->getLinkTargetByClass(array(
                    ilDashboardGUI::class,
                    ilMyStaffGUI::class,
                    ilMStShowUserGUI::class,
                    ilMStShowUserCoursesGUI::class,
                ))
            );
        }

        if ($this->access->hasCurrentUserAccessToCertificates()) {
            $DIC->ctrl()->setParameterByClass(ilUserCertificateGUI::class, 'usr_id', $mst_lus_usr_id);
            $selection->addItem(
                $DIC->language()->txt('mst_list_certificates'),
                '',
                $DIC->ctrl()->getLinkTargetByClass(array(
                    ilDashboardGUI::class,
                    ilMyStaffGUI::class,
                    ilMStShowUserGUI::class,
                    ilUserCertificateGUI::class,
                ))
            );
        }

        if ($this->access->hasCurrentUserAccessToCompetences()) {
            $DIC->ctrl()->setParameterByClass(ilMStShowUserCompetencesGUI::class, 'usr_id', $mst_lus_usr_id);
            $selection->addItem(
                $DIC->language()->txt('mst_list_competences'),
                '',
                $DIC->ctrl()->getLinkTargetByClass(array(
                    ilDashboardGUI::class,
                    ilMyStaffGUI::class,
                    ilMStShowUserGUI::class,
                    ilMStShowUserCompetencesGUI::class,
                ))
            );
        }

        $selection = ilMyStaffGUI::extendActionMenuWithUserActions(
            $selection,
            $mst_lus_usr_id,
            rawurlencode($DIC->ctrl()->getLinkTarget($this, self::CMD_INDEX))
        );

        echo $selection->getHTML(true);
        exit;
    }
}

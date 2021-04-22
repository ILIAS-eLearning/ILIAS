<?php

/**
 * Class ilObjAdministrativeNotificationGUI
 * @ilCtrl_IsCalledBy ilObjAdministrativeNotificationGUI: ilAdministrationGUI
 * @ilCtrl_Calls      ilObjAdministrativeNotificationGUI: ilPermissionGUI
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 */
class ilObjAdministrativeNotificationGUI extends ilObject2GUI
{

    /**
     * @var ilADNTabHandling
     */
    private $tab_handling;
    /**
     * @var ilRbacSystem
     */
    protected $rbacsystem;
    /**
     * @var ilTabsGUI
     */
    protected $tabs;
    /**
     * @var ilLanguage
     */
    public $lng;
    /**
     * @var ilCtrl
     */
    protected $ctrl;
    /**
     * @var ilTemplate
     */
    public $tpl;
    /**
     * @var ilTree
     */
    public $tree;
    const TAB_PERMISSIONS = 'perm_settings';
    const TAB_MAIN = 'main';
    /**
     * @var ilErrorHandling
     */
    protected $error_handling;

    /**
     * ilObjAdministrativeNotificationGUI constructor.
     */
    public function __construct()
    {
        global $DIC;

        $ref_id = (int) $_GET['ref_id'];
        parent::__construct($ref_id);

        $this->tabs = $DIC['ilTabs'];
        $this->lng  = $DIC->language();
        $this->lng->loadLanguageModule('adn');
        $this->ctrl         = $DIC['ilCtrl'];
        $this->tpl          = $DIC['tpl'];
        $this->tree         = $DIC['tree'];
        $this->rbacsystem   = $DIC['rbacsystem'];
        $this->tab_handling = new ilADNTabHandling($ref_id);
        $this->error_handling = $DIC["ilErr"];
        $this->access = new ilObjAdministrativeNotificationAccess();

        $this->assignObject();
    }

    public function executeCommand()
    {
        $this->access->checkAccessAndThrowException("visible,read");

        $next_class = $this->ctrl->getNextClass();

        if ($next_class == '') {
            $this->ctrl->redirectByClass(ilADNNotificationGUI::class);

            return;
        }

        $this->prepareOutput();

        switch ($next_class) {
            case strtolower(ilPermissionGUI::class):
                $this->tab_handling->initTabs(self::TAB_PERMISSIONS);
                $this->tabs->activateTab(self::TAB_PERMISSIONS);
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;
            case strtolower(ilADNNotificationGUI::class):
                $g = new ilADNNotificationGUI($this->tab_handling);
                $this->ctrl->forwardCommand($g);
                break;
            default:
                break;
        }
    }

    /**
     * @inheritDoc
     */
    public function getType()
    {
        return null;
    }
}

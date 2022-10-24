<?php

declare(strict_types=1);

/**
 * Class ilObjMainMenuGUI
 * @ilCtrl_IsCalledBy ilObjMainMenuGUI: ilAdministrationGUI
 * @ilCtrl_Calls      ilObjMainMenuGUI: ilPermissionGUI
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 */
class ilObjMainMenuGUI extends ilObject2GUI
{
    private ilMMTabHandling $tab_handling;
    protected ilRbacSystem $rbac_system;
    protected ilTabsGUI $tabs;
    public ilLanguage $lng;
    protected ilCtrl $ctrl;
    public ilGlobalTemplateInterface $tpl;
    public ilTree $tree;

    public const TAB_PERMISSIONS = 'perm_settings';
    public const TAB_MAIN = 'main';

    /**
     * ilObjMainMenuGUI constructor.
     */
    public function __construct()
    {
        global $DIC;

        $this->ref_id = $DIC->http()->wrapper()->query()->has('ref_id')
            ? $DIC->http()->wrapper()->query()->retrieve('ref_id', $DIC->refinery()->kindlyTo()->int())
            : null;

        parent::__construct($this->ref_id);

        $this->tabs = $DIC['ilTabs'];
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('mme');
        $this->ctrl = $DIC['ilCtrl'];
        $this->tpl = $DIC['tpl'];
        $this->tree = $DIC['tree'];
        $this->rbac_system = $DIC['rbacsystem'];
        $this->tab_handling = new ilMMTabHandling($this->ref_id);

        $this->assignObject();
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass();

        if ($next_class == '') {
            $this->ctrl->redirectByClass(ilMMTopItemGUI::class);

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
            case strtolower(ilMMTopItemGUI::class):
                // $this->tab_handling->initTabs(self::TAB_MAIN, self::SUBTAB_SLATES);
                $g = new ilMMTopItemGUI($this->tab_handling);
                $this->ctrl->forwardCommand($g);
                break;
            case strtolower(ilMMSubItemGUI::class):
                // $this->tab_handling->initTabs(self::TAB_MAIN, self::SUBTAB_SLATES);
                $g = new ilMMSubItemGUI($this->tab_handling);
                $this->ctrl->forwardCommand($g);
                break;
            case strtolower(ilMMUploadHandlerGUI::class):
                $g = new ilMMUploadHandlerGUI();
                $this->ctrl->forwardCommand($g);
                break;
            default:
                break;
        }
    }

    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return "";
    }
}

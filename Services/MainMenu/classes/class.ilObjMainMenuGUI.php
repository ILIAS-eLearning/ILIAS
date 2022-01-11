<?php

/**
 * Class ilObjMainMenuGUI
 * @ilCtrl_IsCalledBy ilObjMainMenuGUI: ilAdministrationGUI
 * @ilCtrl_Calls      ilObjMainMenuGUI: ilPermissionGUI
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 */
class ilObjMainMenuGUI extends ilObject2GUI
{
    
    private ilMMTabHandling $tab_handling;
    /**
     * @var ilRbacSystem (not yet typed in ilObject2GUI)
     */
    protected $rbacsystem;
    protected ilTabsGUI $tabs;
    /**
     * @var ilLanguage (not yet typed in ilObject2GUI)
     */
    public $lng;
    /**
     * @var ilCtrl (not yet typed in ilObject2GUI)
     */
    protected $ctrl;
    /**
     * @var ilTemplate  (not yet typed in ilObject2GUI)
     */
    public $tpl;
    /**
     * @var ilTree (not yet typed in ilObject2GUI)
     */
    public $tree;
    
    const TAB_PERMISSIONS = 'perm_settings';
    const TAB_MAIN = 'main';
    
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
        $this->lng  = $DIC->language();
        $this->lng->loadLanguageModule('mme');
        $this->ctrl         = $DIC['ilCtrl'];
        $this->tpl          = $DIC['tpl'];
        $this->tree         = $DIC['tree'];
        $this->rbacsystem   = $DIC['rbacsystem'];
        $this->tab_handling = new ilMMTabHandling($this->ref_id);
        
        $this->assignObject();
    }
    
    public function executeCommand()
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
    public function getType()
    {
        return null;
    }
}

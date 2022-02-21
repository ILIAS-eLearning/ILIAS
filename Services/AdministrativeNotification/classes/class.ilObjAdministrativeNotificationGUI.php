<?php

/******************************************************************************
 * This file is part of ILIAS, a powerful learning management system.
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *****************************************************************************/

/**
 * Class ilObjAdministrativeNotificationGUI
 * @ilCtrl_IsCalledBy ilObjAdministrativeNotificationGUI: ilAdministrationGUI
 * @ilCtrl_Calls      ilObjAdministrativeNotificationGUI: ilPermissionGUI
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 */
class ilObjAdministrativeNotificationGUI extends ilObject2GUI
{
    
    private \ilADNTabHandling $tab_handling;
    /**
     * @var ilRbacSystem (not yet typed in parent class)
     */
    protected $rbacsystem;
    protected ilTabsGUI $tabs;
    /**
     * @var ilLanguage  (not yet typed in parent class)
     */
    public $lng;
    /**
     * @var ilCtrl  (not yet typed in parent class)
     */
    protected $ctrl;
    /**
     * @var ilTemplate  (not yet typed in parent class)
     */
    public $tpl;
    /**
     * @var ilTree  (not yet typed in parent class)
     */
    public $tree;
    const TAB_PERMISSIONS = 'perm_settings';
    const TAB_MAIN = 'main';
    
    protected ilErrorHandling $error_handling;
    
    /**
     * ilObjAdministrativeNotificationGUI constructor.
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
        $this->lng->loadLanguageModule('adn');
        $this->ctrl           = $DIC['ilCtrl'];
        $this->tpl            = $DIC['tpl'];
        $this->tree           = $DIC['tree'];
        $this->rbacsystem     = $DIC['rbacsystem'];
        $this->tab_handling   = new ilADNTabHandling($this->ref_id);
        $this->error_handling = $DIC["ilErr"];
        $this->access         = new ilObjAdministrativeNotificationAccess();
        
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
    
    public function getType()
    {
        return null;
    }
}

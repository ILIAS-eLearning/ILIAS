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
    const TAB_PERMISSIONS = 'perm_settings';
    const TAB_MAIN = 'main';
    
    private ilADNTabHandling $tab_handling;
    private ilObjAdministrativeNotificationAccess $admin_notification_access;
    
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

        $this->lng->loadLanguageModule('adn');
        $this->tab_handling   = new ilADNTabHandling($this->ref_id);
        $this->admin_notification_access = new ilObjAdministrativeNotificationAccess();
        
        $this->assignObject();
    }
    
    public function executeCommand() : void
    {
        $this->admin_notification_access->checkAccessAndThrowException("visible,read");
        
        $next_class = $this->ctrl->getNextClass();
        
        if ($next_class == '') {
            $this->ctrl->redirectByClass(ilADNNotificationGUI::class);
            
            return;
        }
        
        $this->prepareOutput();
        
        switch ($next_class) {
            case strtolower(ilPermissionGUI::class):
                $this->tab_handling->initTabs(self::TAB_PERMISSIONS);
                $this->tabs_gui->activateTab(self::TAB_PERMISSIONS);
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
    
    public function getType() : string
    {
        return null;
    }
}

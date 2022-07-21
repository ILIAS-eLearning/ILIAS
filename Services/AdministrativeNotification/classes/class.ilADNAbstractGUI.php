<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Class ilADNAbstractGUI
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class ilADNAbstractGUI
{
    public const IDENTIFIER = 'identifier';
    
    protected \ILIAS\DI\UIServices $ui;
    
    protected \ILIAS\HTTP\Services $http;
    
    protected ilToolbarGUI $toolbar;
    protected \ilADNTabHandling $tab_handling;
    
    protected ilTabsGUI $tabs;
    
    public ilLanguage $lng;
    
    protected ilCtrl $ctrl;
    
    public ilGlobalTemplateInterface $tpl;
    
    public ilTree $tree;
    protected \ilObjAdministrativeNotificationAccess $access;
    
    /**
     * ilADNAbstractGUI constructor.
     */
    public function __construct(ilADNTabHandling $tab_handling)
    {
        global $DIC;
        
        $this->tab_handling = $tab_handling;
        $this->tabs = $DIC['ilTabs'];
        $this->lng = $DIC->language();
        $this->ctrl = $DIC['ilCtrl'];
        $this->tpl = $DIC['tpl'];
        $this->tree = $DIC['tree'];
        $this->toolbar = $DIC['ilToolbar'];
        $this->http = $DIC->http();
        $this->ui = $DIC->ui();
        $this->access = new ilObjAdministrativeNotificationAccess();
        
        $this->lng->loadLanguageModule('form');
    }
    
    /**
     * @throws ilException
     */
    protected function determineCommand(?string $standard = null) : ?string
    {
        $this->access->checkAccessAndThrowException('visible,read');
        $cmd = $this->ctrl->getCmd();
        if ($cmd !== '') {
            return $cmd;
        }
        
        return $standard;
    }
    
    abstract protected function dispatchCommand(string $cmd) : string;
    
    public function executeCommand() : void
    {
        $next_class = $this->ctrl->getNextClass();
        
        if ($next_class === '') {
            $cmd = $this->determineCommand();
            $this->tpl->setContent($this->dispatchCommand($cmd));
            
            return;
        }
        
        switch ($next_class) {
            case strtolower(ilADNNotificationGUI::class):
                $this->tab_handling->initTabs(ilObjAdministrativeNotificationGUI::TAB_MAIN, ilADNNotificationGUI::TAB_TABLE, false);
                $g = new ilADNNotificationGUI($this->tab_handling);
                $this->ctrl->forwardCommand($g);
                break;
            default:
                break;
        }
    }
}

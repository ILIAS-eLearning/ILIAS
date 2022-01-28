<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
*
* @ingroup ServicesWebServicesECS
*/
class ilECSParticipantSettingsGUI
{
    private int $server_id = 0;
    private int $mid = 0;
    
    private ilECSParticipantSetting $participant;
    
    protected ilGlobalTemplateInterface $tpl;
    protected ilLanguage $lng;
    protected ilCtrl $ctrl;
    protected ilTabsGUI $tabs;
    
    public function __construct(int $a_server_id, int $a_mid)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->ctrl = $DIC->ctrl();
        $this->tabs = $DIC->tabs();
        
        $this->server_id = $a_server_id;
        $this->mid = $a_mid;
        
        $this->lng->loadLanguageModule('ecs');

        $this->participant = new ilECSParticipantSetting($this->getServerId(), $this->getMid());
    }
    
    public function getServerId() : int
    {
        return $this->server_id;
    }
    
    public function getMid() : int
    {
        return $this->mid;
    }
    
    private function getParticipant() : ilECSParticipantSetting
    {
        return $this->participant;
    }


    /**
     * Execute command
     *
     * @access public
     * @param
     *
     */
    public function executeCommand()
    {
        $this->ctrl->saveParameter($this, 'server_id');
        $this->ctrl->saveParameter($this, 'mid');
        
        
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd('settings');

        $this->setTabs();
        //TODO check if this is needed
        switch ($next_class) {
            default:
                $this->$cmd();
                break;
        }
        
        return true;
    }
    
    /**
     * Abort editing
     */
    private function abort()
    {
        $this->ctrl->returnToParent($this);
    }


    /**
     * Settings
     * @param ilPropertyFormGUI $form
     */
    private function settings(ilPropertyFormGUI $form = null)
    {
        if (!$form instanceof ilPropertyFormGUI) {
            $form = $this->initFormSettings();
        }
        $this->tpl->setContent($form->getHTML());
    }
    
    /**
     * Save settings
     */
    protected function saveSettings()
    {
        $form = $this->initFormSettings();
        if ($form->checkInput()) {
            $this->getParticipant()->enableToken(boolval($form->getInput('token')));
            $this->getParticipant()->enableDeprecatedToken(boolval($form->getInput('dtoken')));
            $this->getParticipant()->enableExport(boolval($form->getInput('export')));
            $this->getParticipant()->setExportTypes($form->getInput('export_types'));
            $this->getParticipant()->enableImport(boolval($form->getInput('import')));
            $this->getParticipant()->setImportTypes($form->getInput('import_types'));
            $this->getParticipant()->update();
            
            ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
            $this->ctrl->redirect($this, 'settings');
            return true;
        }
        $form->setValuesByPost();
        ilUtil::sendFailure($this->lng->txt('err_check_input'));
        $this->settings($form);
    }
    
    /**
     * Init settings form
     */
    protected function initFormSettings() : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt('ecs_part_settings') . ' ' . $this->getParticipant()->getTitle());
        
        
        $token = new ilCheckboxInputGUI($this->lng->txt('ecs_token_mechanism'), 'token');
        $token->setInfo($this->lng->txt('ecs_token_mechanism_info'));
        $token->setValue("1");
        $token->setChecked($this->getParticipant()->isTokenEnabled());
        $form->addItem($token);
        
        $dtoken = new ilCheckboxInputGUI($this->lng->txt('ecs_deprecated_token'), 'dtoken');
        $dtoken->setInfo($this->lng->txt('ecs_deprecated_token_info'));
        $dtoken->setValue("1");
        $dtoken->setChecked($this->getParticipant()->isDeprecatedTokenEnabled());
        $form->addItem($dtoken);
        
        // Export
        $export = new ilCheckboxInputGUI($this->lng->txt('ecs_tbl_export'), 'export');
        $export->setValue("1");
        $export->setChecked($this->getParticipant()->isExportEnabled());
        $form->addItem($export);
        
        // Export types
        $obj_types = new ilCheckboxGroupInputGUI($this->lng->txt('ecs_export_types'), 'export_types');
        $obj_types->setValue($this->getParticipant()->getExportTypes());
        
        
        foreach (ilECSUtils::getPossibleReleaseTypes(true) as $type => $trans) {
            $obj_types->addOption(new ilCheckboxOption($trans, $type));
        }
        $export->addSubItem($obj_types);
        

        // Import
        $import = new ilCheckboxInputGUI($this->lng->txt('ecs_tbl_import'), 'import');
        $import->setValue("1");
        $import->setChecked($this->getParticipant()->isImportEnabled());
        $form->addItem($import);
        
        // Import types
        $imp_types = new ilCheckboxGroupInputGUI($this->lng->txt('ecs_import_types'), 'import_types');
        $imp_types->setValue($this->getParticipant()->getImportTypes());
        
        
        foreach (ilECSUtils::getPossibleRemoteTypes(true) as $type => $trans) {
            $imp_types->addOption(new ilCheckboxOption($trans, $type));
        }
        $import->addSubItem($imp_types);

        $form->addCommandButton('saveSettings', $this->lng->txt('save'));
        $form->addCommandButton('abort', $this->lng->txt('cancel'));
        return $form;
    }

    
    /**
     * Set tabs
     */
    private function setTabs()
    {
        $this->tabs->clearTargets();
        $this->tabs->setBackTarget(
            $this->lng->txt('back'),
            $this->ctrl->getParentReturnByClass(self::class)
        );
    }
}

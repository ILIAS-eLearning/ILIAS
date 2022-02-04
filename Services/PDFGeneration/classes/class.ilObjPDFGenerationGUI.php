<?php
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
class ilObjPDFGenerationGUI extends ilObject2GUI
{
    protected string $active_tab;

    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    public function __construct(int $a_id = 0, int $a_id_type = self::REPOSITORY_NODE_ID, int $a_parent_node_id = 0)
    {
        global $DIC;
        parent::__construct($a_id, $a_id_type, $a_parent_node_id);
        $this->lng->loadLanguageModule('pdfgen');
        $this->toolbar = $DIC['ilToolbar'];
        $this->ctrl = $DIC->ctrl();
        $this->tabs = $DIC->tabs();
    }

    protected function hasWritePermission() : bool
    {
        return $this->checkPermissionBool('write');
    }

    public function getType() : string
    {
        return 'pdfg';
    }

    public function executeCommand() : void
    {
        $next_class = $this->ctrl->getNextClass();
        $cmd = $this->ctrl->getCmd();
        $this->prepareOutput();

        switch ($next_class) {
            case 'ilpermissiongui':
                $this->tabs_gui->setTabActive('perm_settings');
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;

            default:
                if ($cmd == '' || $cmd == 'view') {
                    $cmd = 'configForm';
                }
                if (substr($cmd, 0, 21) == 'saveandconf_selected_') {
                    $this->handleSaveAndConf(substr($cmd, 21));
                } else {
                    $this->$cmd();
                }
                break;
        }
    }

    public function configForm() : void
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, 'view'));
        $purpose_map = ilPDFGeneratorUtils::getPurposeMap();
        $selection_map = ilPDFGeneratorUtils::getSelectionMap();
        $renderers = ilPDFGeneratorUtils::getRenderers();
        foreach ($purpose_map as $service => $purposes) {
            foreach ($purposes as $purpose) {
                $section = new ilFormSectionHeaderGUI();
                $section->setTitle(ucfirst($service) . ' / ' . ucfirst($purpose));
                $form->addItem($section);

                $preferred = new ilTextInputGUI($this->lng->txt('preferred_renderer'));
                $preferred->setValue($selection_map[$service][$purpose]['preferred']);
                $preferred->setDisabled(true);
                $form->addItem($preferred);

                $selected = new ilSelectInputGUI($this->lng->txt('selected_renderer'), 'selected_' . $service . '::' . $purpose);
                $selected->setOptions($renderers[$service][$purpose]);
                $selected_renderer = $selection_map[$service][$purpose]['selected'];
                $selected_index = 0;
                foreach ($renderers[$service][$purpose] as $key => $value) {
                    if ($value == $selected_renderer) {
                        $selected_index = $key;
                    }
                }
                $selected->setValue($selected_index);
                $form->addItem($selected);

                $s_button = ilSubmitButton::getInstance();
                $s_button->setCaption('configure');
                $s_button->setCommand('saveandconf_selected_' . $service . '::' . $purpose);
                if (!$this->hasWritePermission()) {
                    $s_button->setDisabled(true);
                }
                $input_selected = new ilCustomInputGUI($this->lng->txt('configure'));
                $input_selected->setHtml($s_button->getToolbarHTML());
                $form->addItem($input_selected);
            }
        }

        if ($this->hasWritePermission()) {
            $form->addCommandButton("saveSettings", $this->lng->txt("save"));
        }

        if (ilPDFCompInstaller::checkForMultipleServiceAndPurposeCombination()) {
            ilUtil::sendInfo($this->lng->txt('problem_with_purposes'));
            $clean_btn = ilLinkButton::getInstance();
            $clean_btn->setCaption('cleanup');
            $clean_btn->setUrl($this->ctrl->getLinkTarget($this, 'doCleanUp'));
            $this->toolbar->addButtonInstance($clean_btn);
        }
        $this->tpl->setContent($form->getHTML());
        $this->setActiveTab('settings');
    }

    public function saveSettings(bool $redirect_after = true) : void
    {
        if ($this->hasWritePermission()) {
            $form = new ilPropertyFormGUI();
            $purpose_map = ilPDFGeneratorUtils::getPurposeMap();
            $selection_map = ilPDFGeneratorUtils::getSelectionMap();
            $renderers = ilPDFGeneratorUtils::getRenderers();

            foreach ($purpose_map as $service => $purposes) {
                foreach ($purposes as $purpose) {
                    $posted_renderer = $renderers[$service][$purpose][$_POST['selected_' . $service . '::' . $purpose]];
                    $selected_renderer = $selection_map[$service][$purpose]['selected'];
                    if ($posted_renderer != $selected_renderer) {
                        ilPDFGeneratorUtils::updateRendererSelection($service, $purpose, $posted_renderer);
                    }
                }
            }
            $form->setTitle($this->lng->txt('pdf_config'));

            if ($redirect_after) {
                ilUtil::sendSuccess($this->lng->txt('config_saved'), true);
                $this->ctrl->redirect($this, "view");
            }
        } else {
            ilUtil::sendFailure($this->lng->txt('no_permission'), true);
            $this->ctrl->redirect($this, "view");
        }
    }
    
    protected function handleSaveAndConf(string $command) : void
    {
        if ($this->checkPermissionBool('edit')) {
            $this->saveSettings(false);
    
            $parts = explode('::', $command);
            $service = $parts[0];
            $purpose = $parts[1];
    
            $renderers = ilPDFGeneratorUtils::getRenderers();
            $posted_renderer = $renderers[$service][$purpose][$_POST['selected_' . $service . '::' . $purpose]];
    
    
            $form = new ilPropertyFormGUI();
            $form->setFormAction($this->ctrl->getFormAction($this, 'view'));
    
            $form->setTitle($this->lng->txt('settings') . ' ' . $posted_renderer . ' / ' . $service . ' / ' . $purpose);
            $service_hidden = new ilHiddenInputGUI('service');
            $service_hidden->setValue($service);
            $form->addItem($service_hidden);
    
            $purpose_hidden = new ilHiddenInputGUI('purpose');
            $purpose_hidden->setValue($purpose);
            $form->addItem($purpose_hidden);
    
            $renderer_hidden = new ilHiddenInputGUI('renderer');
            $renderer_hidden->setValue($posted_renderer);
            $form->addItem($renderer_hidden);
    
            // Add In RendererConfig
            $renderer = ilPDFGeneratorUtils::getRendererInstance($posted_renderer);
            $config = ilPDFGeneratorUtils::getRendererConfig($service, $purpose, $posted_renderer);
    
            /** @var ilRendererConfig $renderer */
            $renderer->addConfigElementsToForm($form, $service, $purpose);
            $renderer->populateConfigElementsInForm($form, $service, $purpose, $config);
    
            $form->addCommandButton("saveConfig", $this->lng->txt("save"));
            $form->addCommandButton("view", $this->lng->txt("cancel"));
            $form->addCommandButton("resetSettings", $this->lng->txt("reset_to_default"));
            $this->tpl->setContent($form->getHTML());
            $this->setActiveTab('settings');
        } else {
            ilUtil::sendFailure($this->lng->txt('no_permission'), true);
            $this->ctrl->redirect($this, "view");
        }
    }
    
    public function resetSettings() : void
    {
        $renderer = ilUtil::stripSlashes($_POST['renderer']);
        $service = ilUtil::stripSlashes($_POST['service']);
        $purpose = ilUtil::stripSlashes($_POST['purpose']);

        ilPDFGeneratorUtils::removeRendererConfig($service, $purpose, $renderer);
        $this->ctrl->redirect($this, "view");
    }

    protected function saveConfig() : void
    {
        $form = new ilPropertyFormGUI();

        $renderer = $_POST['renderer'];
        $service = $_POST['service'];
        $purpose = $_POST['purpose'];

        /** @var ilRendererConfig $renderer_obj */
        $renderer_obj = ilPDFGeneratorUtils::getRendererInstance($renderer);
        $renderer_obj->addConfigElementsToForm($form, $service, $purpose);

        $form->setValuesByPost();
        if ($renderer_obj->validateConfigInForm($form, $service, $purpose)) {
            $config = $renderer_obj->getConfigFromForm($form, $service, $purpose);
            ilPDFGeneratorUtils::saveRendererPurposeConfig($service, $purpose, $renderer, $config);
            ilUtil::sendSuccess($this->lng->txt('config_saved'), true);
            $this->ctrl->redirect($this, "view");
        } else {
            ilUtil::sendFailure($this->lng->txt('config_not_saved'), true); // TODO: Needs better handling.
            $this->ctrl->redirect($this, "view");
        }
    }

    protected function doCleanUp() : void
    {
        ilPDFCompInstaller::doCleanUp();
        ilUtil::sendSuccess($this->lng->txt('config_saved'), true);
        $this->ctrl->redirect($this, "view");
    }

    /**
     * {@inheritdoc}
     */
    public function getAdminTabs() : void
    {
        if (strpos($this->ctrl->getCmd(), 'saveandconf') !== 0) {
            if ($this->checkPermissionBool('read')) {
                $this->tabs->addTarget('settings', $this->ctrl->getLinkTarget($this, 'view'), array(), __CLASS__);
            }

            if ($this->checkPermissionBool('edit_permission')) {
                $this->tabs->addTarget(
                    'perm_settings',
                    $this->ctrl->getLinkTargetByClass('ilpermissiongui', 'perm'),
                    array(),
                    'ilpermissiongui'
                );
            }
        } else {
            $this->tabs->setBackTarget(
                $this->lng->txt("back"),
                $this->ctrl->getLinkTargetByClass("ilobjpdfgenerationgui", "view")
            );
        }
    }

    protected function setActiveTab(string $tab = '') : void
    {
        $this->tabs->setTabActive($tab == '' ? $this->active_tab : $tab);
    }
}

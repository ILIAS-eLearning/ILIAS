<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * GUI class that manages the editing of general test question pool settings/properties
 * shown on "general" subtab
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/TestQuestionPool
 *
 * @ilCtrl_Calls ilObjQuestionPoolSettingsGeneralGUI: ilPropertyFormGUI
 */
class ilObjQuestionPoolSettingsGeneralGUI
{
    /**
     * command constants
     */
    const CMD_SHOW_FORM				= 'showForm';
    const CMD_SAVE_FORM				= 'saveForm';
    
    /**
     * global $ilCtrl object
     *
     * @var ilCtrl
     */
    protected $ctrl = null;
    
    /**
     * global $ilAccess object
     *
     * @var ilAccess
     */
    protected $access = null;
    
    /**
     * global $lng object
     *
     * @var ilLanguage
     */
    protected $lng = null;
    
    /**
     * global $tpl object
     *
     * @var ilTemplate
     */
    protected $tpl = null;
    
    /**
     * global $ilTabs object
     *
     * @var ilTabsGUI
     */
    protected $tabs = null;
    
    /**
     * gui instance for current question pool
     *
     * @var ilObjTestQuestionPoolGUI
     */
    protected $poolGUI = null;
    
    /**
     * object instance for current question pool
     *
     * @var ilObjTestQuestionPool
     */
    protected $poolOBJ = null;
    
    /**
     * Constructor
     */
    public function __construct(ilCtrl $ctrl, ilAccessHandler $access, ilLanguage $lng, ilTemplate $tpl, ilTabsGUI $tabs, ilObjQuestionPoolGUI $poolGUI)
    {
        $this->ctrl = $ctrl;
        $this->access = $access;
        $this->lng = $lng;
        $this->tpl = $tpl;
        $this->tabs = $tabs;
        
        $this->poolGUI = $poolGUI;
        $this->poolOBJ = $poolGUI->object;
    }
    
    /**
     * Command Execution
     */
    public function executeCommand()
    {
        // allow only write access
        
        if (!$this->access->checkAccess('write', '', $this->poolGUI->ref_id)) {
            ilUtil::sendInfo($this->lng->txt('cannot_edit_question_pool'), true);
            $this->ctrl->redirectByClass('ilObjQuestionPoolGUI', 'infoScreen');
        }
        
        // activate corresponding tab (auto activation does not work in ilObjTestGUI-Tabs-Salad)
        
        $this->tabs->activateTab('settings');
        
        // process command
        
        $nextClass = $this->ctrl->getNextClass();
        
        switch ($nextClass) {
            default:
                $cmd = $this->ctrl->getCmd(self::CMD_SHOW_FORM) . 'Cmd';
                $this->$cmd();
        }
    }

    private function showFormCmd(ilPropertyFormGUI $form = null)
    {
        if ($form === null) {
            $form = $this->buildForm();
        }
        
        $this->tpl->setContent($this->ctrl->getHTML($form));
    }
    
    private function saveFormCmd()
    {
        $form = $this->buildForm();
        
        // form validation and initialisation
        
        $errors = !$form->checkInput(); // ALWAYS CALL BEFORE setValuesByPost()
        $form->setValuesByPost(); // NEVER CALL THIS BEFORE checkInput()

        // return to form when any form validation errors exist
        
        if ($errors) {
            ilUtil::sendFailure($this->lng->txt('form_input_not_valid'));
            return $this->showFormCmd($form);
        }
        
        // perform saving the form data
        
        $this->performSaveForm($form);
        
        // redirect to form output
        
        ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirect($this, self::CMD_SHOW_FORM);
    }

    private function performSaveForm(ilPropertyFormGUI $form)
    {
        include_once 'Services/MetaData/classes/class.ilMD.php';
        $md_obj = new ilMD($this->poolOBJ->getId(), 0, "qpl");
        $md_section = $md_obj->getGeneral();

        // title
        $md_section->setTitle($form->getItemByPostVar('title')->getValue());
        $md_section->update();

        // Description
        $md_desc_ids = $md_section->getDescriptionIds();
        if ($md_desc_ids) {
            $md_desc = $md_section->getDescription(array_pop($md_desc_ids));
            $md_desc->setDescription($form->getItemByPostVar('description')->getValue());
            $md_desc->update();
        } else {
            $md_desc = $md_section->addDescription();
            $md_desc->setDescription($form->getItemByPostVar('description')->getValue());
            $md_desc->save();
        }

        $this->poolOBJ->setTitle($form->getItemByPostVar('title')->getValue());
        $this->poolOBJ->setDescription($form->getItemByPostVar('description')->getValue());
        $this->poolOBJ->update();

        $online = $form->getItemByPostVar('online');
        $this->poolOBJ->setOnline($online->getChecked());

        $showTax = $form->getItemByPostVar('show_taxonomies');
        $this->poolOBJ->setShowTaxonomies($showTax->getChecked());
        
        $navTax = $form->getItemByPostVar('nav_taxonomy');
        $this->poolOBJ->setNavTaxonomyId($navTax->getValue());

        if ($this->formPropertyExists($form, 'skill_service')) {
            $skillService = $form->getItemByPostVar('skill_service');
            $this->poolOBJ->setSkillServiceEnabled($skillService->getChecked());
        }
        
        $this->poolOBJ->saveToDb();
    }
    
    private function buildForm()
    {
        require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
        $form = new ilPropertyFormGUI();
        
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->addCommandButton(self::CMD_SAVE_FORM, $this->lng->txt('save'));
        
        $form->setTitle($this->lng->txt('qpl_form_general_settings'));
        $form->setId('properties');

        include_once 'Services/MetaData/classes/class.ilMD.php';
        $md_obj = new ilMD($this->poolOBJ->getId(), 0, "qpl");
        $md_section = $md_obj->getGeneral();

        $title = new ilTextInputGUI($this->lng->txt("title"), "title");
        $title->setRequired(true);
        $title->setValue($md_section->getTitle());
        $form->addItem($title);

        $ids = $md_section->getDescriptionIds();
        if ($ids) {
            $desc_obj = $md_section->getDescription(array_pop($ids));

            $desc = new ilTextAreaInputGUI($this->lng->txt("description"), "description");
            $desc->setCols(50);
            $desc->setRows(4);
            $desc->setValue($desc_obj->getDescription());
            $form->addItem($desc);
        }

        // online
        
        $online = new ilCheckboxInputGUI($this->lng->txt('qpl_settings_general_form_property_online'), 'online');
        $online->setInfo($this->lng->txt('qpl_settings_general_form_property_online_description'));
        $online->setChecked($this->poolOBJ->getOnline());
        $form->addItem($online);
        
        // show taxonomies
        
        $showTax = new ilCheckboxInputGUI($this->lng->txt('qpl_settings_general_form_property_show_taxonomies'), 'show_taxonomies');
        $showTax->setInfo($this->lng->txt('qpl_settings_general_form_prop_show_tax_desc'));
        $showTax->setChecked($this->poolOBJ->getShowTaxonomies());
        $form->addItem($showTax);
    
        $taxSelectOptions = $this->getTaxonomySelectInputOptions();
    
        // pool navigation taxonomy

        $navTax = new ilSelectInputGUI($this->lng->txt('qpl_settings_general_form_property_nav_taxonomy'), 'nav_taxonomy');
        $navTax->setInfo($this->lng->txt('qpl_settings_general_form_property_nav_taxonomy_description'));
        $navTax->setValue($this->poolOBJ->getNavTaxonomyId());
        $navTax->setOptions($taxSelectOptions);
        $showTax->addSubItem($navTax);

        // skill service activation
        
        if (ilObjQuestionPool::isSkillManagementGloballyActivated()) {
            $skillService = new ilCheckboxInputGUI($this->lng->txt('tst_activate_skill_service'), 'skill_service');
            $skillService->setChecked($this->poolOBJ->isSkillServiceEnabled());
            $form->addItem($skillService);
        }
        
        return $form;
    }
    
    private function getTaxonomySelectInputOptions()
    {
        $taxSelectOptions = array(
            '0' => $this->lng->txt('qpl_settings_general_form_property_opt_notax_selected')
        );
        
        foreach ($this->poolOBJ->getTaxonomyIds() as $taxId) {
            $taxSelectOptions[$taxId] = ilObject::_lookupTitle($taxId);
        }
        
        return $taxSelectOptions;
    }

    protected function formPropertyExists(ilPropertyFormGUI $form, $propertyId)
    {
        return $form->getItemByPostVar($propertyId) instanceof ilFormPropertyGUI;
    }
}

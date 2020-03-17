<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Test/classes/class.ilObjTestDynamicQuestionSetConfig.php';

/**
 * GUI class that manages the question set configuration for continues tests
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/Test
 *
 * @ilCtrl_Calls ilObjTestDynamicQuestionSetConfigGUI: ilPropertyFormGUI
 */
class ilObjTestDynamicQuestionSetConfigGUI
{
    /**
     * command constants
     */
    const CMD_SHOW_FORM = 'showForm';
    const CMD_SAVE_FORM = 'saveForm';
    const CMD_GET_TAXONOMY_OPTIONS_ASYNC = 'getTaxonomyOptionsAsync';
    
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
     * global $ilTabs object
     *
     * @var ilTabsGUI
     */
    protected $tabs = null;
    
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
     * global $ilDB object
     *
     * @var ilDBInterface
     */
    protected $db = null;
    
    /**
     * global $tree object
     *
     * @var ilTree
     */
    protected $tree = null;
    
    /**
     * object instance for current test
     *
     * @var ilObjTest
     */
    protected $testOBJ = null;
    
    /**
     * object instance managing the dynamic question set config
     *
     * @var ilObjTestDynamicQuestionSetConfig
     */
    protected $questionSetConfig = null;
    
    const QUESTION_ORDERING_TYPE_UPDATE_DATE = 'ordering_by_date';
    const QUESTION_ORDERING_TYPE_TAXONOMY = 'ordering_by_tax';
    
    /**
     * Constructor
     */
    public function __construct(ilCtrl $ctrl, ilAccessHandler $access, ilTabsGUI $tabs, ilLanguage $lng, ilTemplate $tpl, ilDBInterface $db, ilTree $tree, ilPluginAdmin $pluginAdmin, ilObjTest $testOBJ)
    {
        $this->ctrl = $ctrl;
        $this->access = $access;
        $this->tabs = $tabs;
        $this->lng = $lng;
        $this->tpl = $tpl;
        $this->db = $db;
        $this->tree = $tree;
        $this->pluginAdmin = $pluginAdmin;

        $this->testOBJ = $testOBJ;
        
        $this->questionSetConfig = new ilObjTestDynamicQuestionSetConfig($this->tree, $this->db, $this->pluginAdmin, $this->testOBJ);
    }
    
    /**
     * Command Execution
     */
    public function executeCommand()
    {
        // allow only write access
        
        if (!$this->access->checkAccess("write", "", $this->testOBJ->getRefId())) {
            ilUtil::sendInfo($this->lng->txt("cannot_edit_test"), true);
            $this->ctrl->redirectByClass('ilObjTestGUI', "infoScreen");
        }
        
        // activate corresponding tab (auto activation does not work in ilObjTestGUI-Tabs-Salad)
        
        $this->tabs->activateTab('assQuestions');
        
        // process command
        
        $nextClass = $this->ctrl->getNextClass();
        
        switch ($nextClass) {
            default:
                $cmd = $this->ctrl->getCmd(self::CMD_SHOW_FORM) . 'Cmd';
                $this->$cmd();
        }
    }
    
    public function getTaxonomyOptionsAsyncCmd()
    {
        $questionPoolId = (int) $_POST['question_pool_id'];

        echo $this->buildTaxonomySelectInputOptionJson($questionPoolId);
        exit;
    }
    
    /**
     * command method that prints the question set config form
     *
     * @param ilPropertyFormGUI $form
     */
    public function showFormCmd(ilPropertyFormGUI $form = null)
    {
        $this->questionSetConfig->loadFromDb();
        
        if ($this->questionSetConfig->areDepenciesBroken($this->tree)) {
            ilUtil::sendFailure($this->questionSetConfig->getDepenciesBrokenMessage($this->lng));
        } elseif ($this->questionSetConfig->areDepenciesInVulnerableState($this->tree)) {
            ilUtil::sendInfo($this->questionSetConfig->getDepenciesInVulnerableStateMessage($this->lng));
        }
            
        if ($form === null) {
            $form = $this->buildForm($this->questionSetConfig->getSourceQuestionPoolId());
        }
        
        $this->tpl->setContent($this->ctrl->getHTML($form));
        
        $this->tpl->addJavaScript('Modules/Test/js/ilTestDynamicQuestionSetConfig.js');
    }
    
    /**
     * @return integer
     */
    protected function getSubmittedSourceQuestionPoolId()
    {
        return (int) $_POST['source_qpl_id'];
    }
    
    /**
     * command method that checks the question set config form
     *
     * if form is valid it gets saved to the database,
     * otherwise it will be reprinted with alerts
     */
    public function saveFormCmd()
    {
        $form = $this->buildForm(
            $this->getSubmittedSourceQuestionPoolId()
        );

        if ($this->testOBJ->participantDataExist()) {
            ilUtil::sendFailure($this->lng->txt("tst_msg_cannot_modify_dynamic_question_set_conf_due_to_part"), true);
            return $this->showFormCmd($form);
        }
        
        $errors = !$form->checkInput(); // ALWAYS CALL BEFORE setValuesByPost()
        $form->setValuesByPost(); // NEVER CALL THIS BEFORE checkInput()

        if ($errors) {
            ilUtil::sendFailure($this->lng->txt('form_input_not_valid'));
            return $this->showFormCmd($form);
        }
        
        $this->performSaveForm($form);
        
        $this->testOBJ->saveCompleteStatus($this->questionSetConfig);

        ilUtil::sendSuccess($this->lng->txt("tst_msg_dynamic_question_set_config_modified"), true);
        $this->ctrl->redirect($this, self::CMD_SHOW_FORM);
    }
    
    /**
     * saves the form fields to the database
     *
     * @param ilPropertyFormGUI $form
     */
    private function performSaveForm(ilPropertyFormGUI $form)
    {
        $this->questionSetConfig->setSourceQuestionPoolId(
            $form->getItemByPostVar('source_qpl_id')->getValue()
        );

        $this->questionSetConfig->setSourceQuestionPoolTitle(ilObject::_lookupTitle(
            $form->getItemByPostVar('source_qpl_id')->getValue()
        ));

        switch ($form->getItemByPostVar('question_ordering')->getValue()) {
            case self::QUESTION_ORDERING_TYPE_UPDATE_DATE:
                $this->questionSetConfig->setOrderingTaxonomyId(null);
                break;
            
            case self::QUESTION_ORDERING_TYPE_TAXONOMY:
                $this->questionSetConfig->setOrderingTaxonomyId(
                    $form->getItemByPostVar('ordering_tax')->getValue()
                );
                break;
        }

        $this->questionSetConfig->setTaxonomyFilterEnabled(
            $form->getItemByPostVar('tax_filter_enabled')->getChecked()
        );

        $this->questionSetConfig->setAnswerStatusFilterEnabled(
            $form->getItemByPostVar('answer_status_filter_enabled')->getChecked()
        );
        
        $this->questionSetConfig->saveToDb($this->testOBJ->getTestId());
    }
    
    /**
     * builds the question set config form and initialises the fields
     * with the config currently saved in database
     *
     * @return ilPropertyFormGUI $form
     */
    private function buildForm($sourceQuestionPoolId)
    {
        $this->questionSetConfig->loadFromDb($this->testOBJ->getTestId());
        
        require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
        $form = new ilPropertyFormGUI();
        
        $form->setFormAction($this->ctrl->getFormAction($this));

        $form->setId("tst_form_dynamic_question_set_config");
        $form->setTitle($this->lng->txt('tst_form_dynamic_question_set_config'));
        $form->setTableWidth("100%");
        
        $hiddenInputTaxSelectOptAsyncUrl = new ilHiddenInputGUI('taxSelectOptAsyncUrl');
        $hiddenInputTaxSelectOptAsyncUrl->setValue(
            $this->ctrl->getLinkTarget($this, self::CMD_GET_TAXONOMY_OPTIONS_ASYNC, '', true)
        );
        $form->addItem($hiddenInputTaxSelectOptAsyncUrl);

        if ($this->testOBJ->participantDataExist()) {
            $pool = new ilNonEditableValueGUI($this->lng->txt('tst_input_dynamic_question_set_source_questionpool'), 'source_qpl_title');
            $pool->setValue($this->questionSetConfig->getSourceQuestionPoolSummaryString($this->lng, $this->tree));
            $pool->setDisabled(true);
            $form->addItem($pool);
        } else {
            $poolInput = new ilSelectInputGUI($this->lng->txt('tst_input_dynamic_question_set_source_questionpool'), 'source_qpl_id');
            $poolInput->setOptions($this->buildQuestionPoolSelectInputOptionArray(
                $this->testOBJ->getAvailableQuestionpools(true, false, false, true, true)
            ));
            $poolInput->setValue($sourceQuestionPoolId);
            $poolInput->setRequired(true);
            $form->addItem($poolInput);
        }
        
        $questionOderingInput = new ilRadioGroupInputGUI(
            $this->lng->txt('tst_input_dynamic_question_set_question_ordering'),
            'question_ordering'
        );
        $questionOderingInput->setValue(
            $this->questionSetConfig->getOrderingTaxonomyId() ?
            self::QUESTION_ORDERING_TYPE_TAXONOMY : self::QUESTION_ORDERING_TYPE_UPDATE_DATE
        );
        $optionOrderByDate = new ilRadioOption(
            $this->lng->txt('tst_input_dynamic_question_set_question_ordering_by_date'),
            self::QUESTION_ORDERING_TYPE_UPDATE_DATE,
            $this->lng->txt('tst_inp_dyn_quest_set_quest_ordering_by_date_desc')
        );
        $questionOderingInput->addOption($optionOrderByDate);
        $optionOrderByTax = new ilRadioOption(
            $this->lng->txt('tst_input_dynamic_question_set_question_ordering_by_tax'),
            self::QUESTION_ORDERING_TYPE_TAXONOMY,
            $this->lng->txt('tst_inp_dyn_quest_set_quest_ordering_by_tax_desc')
        );
        $orderTaxInput = new ilSelectInputGUI($this->lng->txt('tst_input_dynamic_question_set_ordering_tax'), 'ordering_tax');
        $orderTaxInput->setInfo($this->lng->txt('tst_input_dynamic_question_set_ordering_tax_description'));
        $orderTaxInput->setValue($this->questionSetConfig->getOrderingTaxonomyId());
        $orderTaxInput->setRequired(true);
        $orderTaxInput->setOptions($this->buildTaxonomySelectInputOptionArray($sourceQuestionPoolId));
        $optionOrderByTax->addSubItem($orderTaxInput);
        $questionOderingInput->addOption($optionOrderByTax);
        $form->addItem($questionOderingInput);
        
        $taxFilterInput = new ilCheckboxInputGUI($this->lng->txt('tst_input_dynamic_question_set_taxonomie_filter_enabled'), 'tax_filter_enabled');
        $taxFilterInput->setValue(1);
        $taxFilterInput->setChecked($this->questionSetConfig->isTaxonomyFilterEnabled());
        $form->addItem($taxFilterInput);
        
        $answStatusFilterInput = new ilCheckboxInputGUI(
            $this->lng->txt('tst_input_dyn_quest_set_answer_status_filter_enabled'),
            'answer_status_filter_enabled'
        );
        $answStatusFilterInput->setValue(1);
        $answStatusFilterInput->setChecked($this->questionSetConfig->isAnswerStatusFilterEnabled());
        $form->addItem($answStatusFilterInput);

        if ($this->testOBJ->participantDataExist()) {
            $questionOderingInput->setDisabled(true);
            $taxFilterInput->setDisabled(true);
            $answStatusFilterInput->setDisabled(true);
        } else {
            $form->addCommandButton(self::CMD_SAVE_FORM, $this->lng->txt('save'));
        }
        
        return $form;
    }
    
    /**
     * converts the passed question pools data array to select input option array
     *
     * @param array $questionPoolsData
     * @return array
     */
    private function buildQuestionPoolSelectInputOptionArray($questionPoolsData)
    {
        $questionPoolSelectInputOptions = array( '' => $this->lng->txt('please_select') );
        
        foreach ($questionPoolsData as $qplId => $qplData) {
            $questionPoolSelectInputOptions[$qplId] = $qplData['title'];
        }
        
        return $questionPoolSelectInputOptions;
    }
    
    private function buildTaxonomySelectInputOptionArray($questionPoolId)
    {
        $taxSelectOptions = array(
            0 => $this->lng->txt('please_select')
        );
        
        if ($questionPoolId) {
            require_once 'Services/Taxonomy/classes/class.ilObjTaxonomy.php';

            $taxIds = ilObjTaxonomy::getUsageOfObject($questionPoolId);

            foreach ($taxIds as $taxId) {
                $taxSelectOptions[$taxId] = ilObject::_lookupTitle($taxId);
            }
        }
        
        return $taxSelectOptions;
    }
    
    private function buildTaxonomySelectInputOptionJson($questionPoolId)
    {
        $options = array();

        foreach ($this->buildTaxonomySelectInputOptionArray($questionPoolId) as $optValue => $optLabel) {
            $options[] = array('value' => $optValue, 'label' => $optLabel);
        }

        return json_encode($options);
    }
}

<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

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
	const CMD_SHOW_FORM	= 'showForm';
	const CMD_SAVE_FORM	= 'saveForm';
	
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
	 * @var ilDB
	 */
	protected $db = null;
	
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
	
	/**
	 * Constructor
	 */
	public function __construct(ilCtrl $ctrl, ilAccessHandler $access, ilTabsGUI $tabs, ilLanguage $lng, ilTemplate $tpl, ilDB $db, ilObjTestGUI $testGUI)
	{
		$this->ctrl = $ctrl;
		$this->access = $access;
		$this->tabs = $tabs;
		$this->lng = $lng;
		$this->tpl = $tpl;
		$this->db = $db;
		
		$this->testGUI = $testGUI;
		$this->testOBJ = $testGUI->object;
		
		$this->questionSetConfig = new ilObjTestDynamicQuestionSetConfig($db, $testGUI->object);
	}
	
	/**
	 * Command Execution
	 */
	public function executeCommand()
	{
		// allow only write access
		
		if (!$this->access->checkAccess("write", "", $this->testGUI->ref_id)) 
		{
			ilUtil::sendInfo($this->lng->txt("cannot_edit_test"), true);
			$this->ctrl->redirect($this->testGUI, "infoScreen");
		}
		
		// activate corresponding tab (auto activation does not work in ilObjTestGUI-Tabs-Salad)
		
		$this->tabs->activateTab('assQuestions');
		
		// process command
		
		$nextClass = $this->ctrl->getNextClass();
		
		switch($nextClass)
		{
			default:
				$cmd = $this->ctrl->getCmd(self::CMD_SHOW_FORM).'Cmd';
				$this->$cmd();
		}
	}
	
	/**
	 * command method that prints the question set config form
	 * 
	 * @param ilPropertyFormGUI $form
	 */
	public function showFormCmd(ilPropertyFormGUI $form = null)
	{
		if( $form === null )
		{
			$form = $this->buildForm();
		}
		
		$this->tpl->setContent( $this->ctrl->getHTML($form) );
	}
	
	/**
	 * command method that checks the question set config form
	 * 
	 * if form is valid it gets saved to the database,
	 * otherwise it will be reprinted with alerts
	 */
	public function saveFormCmd()
	{
		$form = $this->buildForm();

		if( $this->testOBJ->participantDataExist() )
		{
			ilUtil::sendFailure($this->lng->txt("tst_msg_cannot_modify_dynamic_question_set_conf_due_to_part"), true);
			return $this->showFormCmd($form);
		}
		
		$errors = !$form->checkInput(); // ALWAYS CALL BEFORE setValuesByPost()
		$form->setValuesByPost(); // NEVER CALL THIS BEFORE checkInput()

		if($errors)
		{
			ilUtil::sendFailure($this->lng->txt('form_input_not_valid'));
			return $this->showFormCmd($form);
		}
		
		$saved = $this->performSaveForm($form);
		
		if( !$saved )
		{
			return $this->showFormCmd($form);
		}
		
		$this->testOBJ->saveCompleteStatus();

		ilUtil::sendSuccess($this->lng->txt("tst_msg_dynamic_question_set_config_modified"), true);
		$this->ctrl->redirect($this, self::CMD_SHOW_FORM);
	}
	
	/**
	 * saves the form fields to the database
	 * 
	 * @param ilPropertyFormGUI $form
	 * @return boolean
	 */
	private function performSaveForm(ilPropertyFormGUI $form)
	{
		$this->questionSetConfig->setSourceQuestionPoolId(
				$form->getItemByPostVar('source_qpl_id')->getValue()
		);
		
		$this->questionSetConfig->setOrderingTaxonomyId(
				$form->getItemByPostVar('order_taxonomy')->getValue()
		);
		
		$this->questionSetConfig->setTaxonomyFilterEnabled(
				$form->getItemByPostVar('tax_filter_enabled')->getChecked()
		);
		
		return $this->questionSetConfig->saveToDb( $this->testOBJ->getTestId() );
	}
	
	/**
	 * builds the question set config form and initialises the fields
	 * with the config currently saved in database
	 * 
	 * @return ilPropertyFormGUI $form
	 */
	private function buildForm()
	{
		$this->questionSetConfig->loadFromDb( $this->testOBJ->getTestId() );
		
		require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
		$form = new ilPropertyFormGUI();
		
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->addCommandButton(self::CMD_SAVE_FORM, $this->lng->txt("save"));
		
		$form->setId("tst_form_dynamic_question_set_config");
		$form->setTitle($this->lng->txt('tst_form_dynamic_question_set_config'));
		$form->setTableWidth("100%");
		
		$poolInput = new ilSelectInputGUI($this->lng->txt('tst_input_dynamic_question_set_source_questionpool'), 'source_qpl_id');
		$poolInput->setOptions($this->buildQuestionPoolSelectInputOptionArray(
				$this->testOBJ->getAvailableQuestionpools(true, false, false, true, true)
		));
		$poolInput->setValue( $this->questionSetConfig->getSourceQuestionPoolId() );
		$poolInput->setRequired(true);
		$form->addItem($poolInput);
		
		$taxOrderInput = new ilSelectInputGUI($this->lng->txt('qpl_settings_general_form_property_order_taxonomy'), 'order_taxonomy');
		$taxOrderInput->setInfo($this->lng->txt('qpl_settings_general_form_property_order_taxonomy_description'));
		$taxOrderInput->setValue($this->questionSetConfig->getOrderingTaxonomyId());
		$taxOrderInput->setOptions($this->buildTaxonomySelectInputOptionnArray(
				$this->questionSetConfig->getSourceQuestionPoolId()
		));
		$form->addItem($taxOrderInput);
		
		$taxFilterInput = new ilCheckboxInputGUI($this->lng->txt('tst_input_dynamic_question_set_taxonomie_filter_enabled'), 'tax_filter_enabled');
		$taxFilterInput->setValue(1);
		$taxFilterInput->setChecked( $this->questionSetConfig->isTaxonomyFilterEnabled() );
		$taxFilterInput->setRequired(true);
		$form->addItem($taxFilterInput);

		if( $this->testOBJ->participantDataExist() )
		{
			$poolInput->setDisabled(true);
			$taxOrderInput->setDisabled(true);
			$taxFilterInput->setDisabled(true);
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
		
		foreach($questionPoolsData as $qplId => $qplData)
		{
			$questionPoolSelectInputOptions[$qplId] = $qplData['title'];
		}
		
		return $questionPoolSelectInputOptions;
	}
	
	private function buildTaxonomySelectInputOptionnArray($questionPoolId)
	{
		$taxSelectOptions = array(
			'0' => $this->lng->txt('qpl_settings_general_form_property_opt_notax_selected')
		);
		
		require_once 'Services/Taxonomy/classes/class.ilObjTaxonomy.php';
		
		$taxIds = ilObjTaxonomy::getUsageOfObject($questionPoolId);
		
		foreach($taxIds as $taxId)
		{
			$taxSelectOptions[$taxId] = ilObject::_lookupTitle($taxId);
		}
		
		return $taxSelectOptions;
	}
}

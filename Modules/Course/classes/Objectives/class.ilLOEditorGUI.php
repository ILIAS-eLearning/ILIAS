<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */


include_once './Modules/Course/classes/Objectives/class.ilLOSettings.php';
include_once './Modules/Course/classes/Objectives/class.ilLOEditorStatus.php';

/**
* Class ilLOEditorGUI
*
* @author Stefan Meyer <smeyer.ilias@gmx.de> 
* $Id$
*
* @ilCtrl_isCalledBy ilLOEditorGUI: ilObjCourseGUI
* @ilCtrl_Calls ilLOEditorGUI: ilCourseObjectivesGUI, ilContainerStartObjectsGUI, ilConditionHandlerGUI
* @ilCtrl_Calls ilLOEditorGUI: ilLOPageGUI
*
*/
class ilLOEditorGUI
{
	const TEST_TYPE_IT = 1;
	const TEST_TYPE_QT = 2;

	const TEST_NEW = 1;
	const TEST_ASSIGN = 2;
	
	const SETTINGS_TEMPLATE_IT = 'il_astpl_loc_initial';
	const SETTINGS_TEMPLATE_QT = 'il_astpl_loc_qualified';


	private $parent_obj;
	private $settings = NULL;
	private $lng = NULL;
	private $ctrl = NULL;

	private $test_type = 0;
	
	
	/**
	 * Constructor
	 * @param type $a_parent_obj
	 */
	public function __construct($a_parent_obj)
	{
		$this->parent_obj = $a_parent_obj;
		$this->settings = ilLOSettings::getInstanceByObjId($this->getParentObject()->getId());
		$this->lng = $GLOBALS['lng'];
		$this->ctrl = $GLOBALS['ilCtrl'];
	}
	
	/**
	 * Execute command
	 * @return <type> 
	 */
	public function executeCommand()
	{
		global $ilCtrl;

		$next_class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd();
		
		$this->setTabs();
		switch($next_class)
		{
			case 'ilcourseobjectivesgui':

				$this->ctrl->setReturn($this,'listObjectives');
				$GLOBALS['ilTabs']->clearTargets();
				$GLOBALS['ilTabs']->setBackTarget(
						$this->lng->txt('back'),
						$this->ctrl->getLinkTarget($this,'listObjectives'));
				
				include_once './Modules/Course/classes/class.ilCourseObjectivesGUI.php';
				$reg_gui =& new ilCourseObjectivesGUI($this->getParentObject()->getRefId());
				$this->ctrl->forwardCommand($reg_gui);
				break;
			
			case 'ilcontainerstartobjectsgui':
				
				include_once './Services/Container/classes/class.ilContainerStartObjectsGUI.php';
				$stgui = new ilContainerStartObjectsGUI($this->getParentObject());
				$ret = $this->ctrl->forwardCommand($stgui);
				
				$GLOBALS['ilTabs']->activateSubTab('start');
				$GLOBALS['ilTabs']->removeSubTab('manage');
				
				#$GLOBALS['tpl']->setContent($this->ctrl->getHTML($stgui));
				break;
			
			case 'ilconditionhandlergui':
				
				$this->ctrl->saveParameterByClass('ilconditionhandlergui','objective_id');
				
				$GLOBALS['ilTabs']->clearTargets();
				$GLOBALS['ilTabs']->setBackTarget(
						$this->lng->txt('back'),
						$this->ctrl->getLinkTarget($this,'listObjectives'));

				include_once './Services/AccessControl/classes/class.ilConditionHandlerInterface.php';
				$cond = new ilConditionHandlerGUI($this);
				$cond->setBackButtons(array());
				$cond->setAutomaticValidation(false);
				$cond->setTargetType("lobj");
				$cond->setTargetRefId($this->getParentObject()->getRefId());
				
				$cond->setTargetId((int) $_REQUEST['objective_id']);
				
				// objecitve
				include_once './Modules/Course/classes/class.ilCourseObjective.php';
				$obj = new ilCourseObjective($this->getParentObject(),(int) $_REQUEST['objective_id']);
				$cond->setTargetTitle($obj->getTitle());
				$this->ctrl->forwardCommand($cond);
				break;
			
			case 'illopagegui':
				$this->ctrl->saveParameterByClass('illopagegui','objective_id');
				
				$GLOBALS['ilTabs']->clearTargets();
				$GLOBALS['ilTabs']->setBackTarget(
						$this->lng->txt('back'),
						$this->ctrl->getLinkTarget($this,'listObjectives'));
				
				$objtv_id = (int)$_REQUEST['objective_id'];
				
				include_once 'Modules/Course/classes/Objectives/class.ilLOPage.php';
				if(!ilLOPage::_exists('lobj', $objtv_id))
				{
					// doesn't exist -> create new one
					$new_page_object = new ilLOPage();
					$new_page_object->setParentId($objtv_id);
					$new_page_object->setId($objtv_id);
					$new_page_object->createFromXML();
					unset($new_page_object);
				}
				
				$this->ctrl->setReturn($this, 'listObjectives');				
				include_once 'Modules/Course/classes/Objectives/class.ilLOPageGUI.php';
				$pgui = new ilLOPageGUI($objtv_id);										
				$pgui->setPresentationTitle(ilCourseObjective::lookupObjectiveTitle($objtv_id));
				
				// needed for editor?
				include_once('./Services/Style/classes/class.ilObjStyleSheet.php');
				$pgui->setStyleId(ilObjStyleSheet::getEffectiveContentStyleId(0));	
				
				// #14895
				$GLOBALS['tpl']->setCurrentBlock("ContentStyle");
				$GLOBALS['tpl']->setVariable("LOCATION_CONTENT_STYLESHEET",
					ilObjStyleSheet::getContentStylePath(0));
				$GLOBALS['tpl']->parseCurrentBlock();
				
				$ret = $this->ctrl->forwardCommand($pgui);
				if($ret)
				{
					$GLOBALS['tpl']->setContent($ret);
				}
				break;
			
			default:
				if(!$cmd)
				{
					// get first unaccomplished step
					include_once './Modules/Course/classes/Objectives/class.ilLOEditorStatus.php';
					$cmd = ilLOEditorStatus::getInstance($this->getParentObject())->getFirstFailedStep();
				}
				$this->$cmd();

				break;
		}
		return true;
	}
	
	/**
	 * @return ilObject
	 */
	public function getParentObject()
	{
		return $this->parent_obj;
	}
	
	/**
	 * Settings
	 * @return ilLOSettings
	 */
	public function getSettings()
	{
		return $this->settings;
	}
	
	public function setTestType($a_type)
	{
		$this->test_type = $a_type;
	}
	
	public function getTestType()
	{
		return $this->test_type;
	}


	/**
	 * Objective Settings
	 */
	protected function settings(ilPropertyFormGUI $form = NULL)
	{
		if(!$form instanceof ilPropertyFormGUI)
		{
			$form = $this->initSettingsForm();
		}
		
		$GLOBALS['ilTabs']->activateSubTab('settings');
		$GLOBALS['tpl']->setContent($form->getHTML());
		
		$this->showStatus(ilLOEditorStatus::SECTION_SETTINGS);
	}
	
	/**
	 * 
	 */
	protected function saveSettings()
	{
		$form = $this->initSettingsForm();
		if($form->checkInput())
		{
			$settings = ilLOSettings::getInstanceByObjId($this->getParentObject()->getId());
			$settings->setType($form->getInput('type'));
			
			$qtv_values = (array) $form->getInput('qtv');
			$settings->setGeneralQualifiedTestVisibility(in_array(ilLOSettings::QT_VISIBLE_ALL, $qtv_values));
			$settings->setQualifiedTestPerObjectiveVisibility(in_array(ilLOSettings::QT_VISIBLE_OBJECTIVE, $qtv_values));
			$settings->resetResults($form->getInput('reset'));
			$settings->update();
			
			
			
			$this->updateStartObjects();
			
			ilUtil::sendSuccess($this->lng->txt('settings_saved'),true);
			$this->ctrl->redirect($this,'settings');
		}
		
		// Error
		ilUtil::sendFailure($this->lng->txt('err_check_input'));
		$form->setValuesByPost();
		$this->settings($form);
	}
	
	
	/**
	 * Init settings form
	 */
	protected function initSettingsForm()
	{
		include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->lng->txt('crs_loc_settings_tbl'));
		
		$type = new ilRadioGroupInputGUI($this->lng->txt('crs_loc_settings_type'), 'type');
		$type->setRequired(true);
		$type->setValue(ilLOSettings::getInstanceByObjId($this->getParentObject()->getId())->getType());
		
		$type_1 = new ilRadioOption($this->lng->txt('crs_loc_type_initial_all'), ilLOSettings::LOC_INITIAL_ALL);
		$type_1->setInfo($this->lng->txt('crs_loc_type_initial_all_info'));
		$type->addOption($type_1);
		
		$type_2 = new ilRadioOption($this->lng->txt('crs_loc_type_initial_sel'), ilLOSettings::LOC_INITIAL_SEL);
		#$type->addOption($type_2);

		$type_3 = new ilRadioOption($this->lng->txt('crs_loc_type_qualified'), ilLOSettings::LOC_QUALIFIED);
		$type_3->setInfo($this->lng->txt('crs_loc_type_qualified_info'));
		$type->addOption($type_3);

		$type_4 = new ilRadioOption($this->lng->txt('crs_loc_type_practise'), ilLOSettings::LOC_PRACTISE);
		$type_4->setInfo($this->lng->txt('crs_loc_type_practise_info'));
		$type->addOption($type_4);
		$form->addItem($type);
		
		$form->addCommandButton('saveSettings', $this->lng->txt('save'));
		
		// qualified test visibility
		$qtv = new ilCheckboxGroupInputGUI($this->lng->txt('crs_loc_qt_visibility'),'qtv');
		
		$qtv_values = array();
		if($this->getSettings()->isGeneralQualifiedTestVisible())
		{
			$qtv_values[] = ilLOSettings::QT_VISIBLE_ALL;
		}
		if($this->getSettings()->isQualifiedTestPerObjectiveVisible())
		{
			$qtv_values[] = ilLOSettings::QT_VISIBLE_OBJECTIVE;
		}
		$qtv->setValue($qtv_values);
		$qtv->setRequired(true);
		
		$qtv->addOption(new ilCheckboxOption(
				$this->lng->txt('crs_loc_qt_visibility_all'),
				ilLOSettings::QT_VISIBLE_ALL)
		);
		
		$qtv->addOption(new ilCheckboxOption(
				$this->lng->txt('crs_loc_qt_visibility_lo'),
				ilLOSettings::QT_VISIBLE_OBJECTIVE)
		);
		#$form->addItem($qtv);
		
		// reset results
		$reset = new ilCheckboxInputGUI($this->lng->txt('crs_loc_settings_reset'),'reset');
		$reset->setValue(1);
		$reset->setChecked($this->getSettings()->isResetResultsEnabled());
		$reset->setOptionTitle($this->lng->txt('crs_loc_settings_reset_enable'));
		$reset->setInfo($this->lng->txt('crs_loc_settings_reset_enable_info'));
		$form->addItem($reset);
		
				
		
		return $form;
	}
	
	protected function materials()
	{
		$GLOBALS['ilTabs']->activateSubTab('materials');
		
		include_once "Services/Object/classes/class.ilObjectAddNewItemGUI.php";
		$gui = new ilObjectAddNewItemGUI($this->getParentObject()->getRefId());
		$gui->setDisabledObjectTypes(array("itgr"));
		#$gui->setAfterCreationCallback($this->getParentObject()->getRefId());
		$gui->render();
		
		include_once './Services/Object/classes/class.ilObjectTableGUI.php';
		$obj_table = new ilObjectTableGUI(
				$this,
				'materials',
				$this->getParentObject()->getRefId()
		);
		$obj_table->init();
		$obj_table->setObjects($GLOBALS['tree']->getChildIds($this->getParentObject()->getRefId()));
		$obj_table->parse();
		$GLOBALS['tpl']->setContent($obj_table->getHTML());
		
		$this->showStatus(ilLOEditorStatus::SECTION_MATERIALS);
	}
	
	
	/**
	 * Show test overview
	 */
	protected function testOverview()
	{
		$this->setTestType((int) $_REQUEST['tt']);
		$this->ctrl->setParameter($this,'tt',$this->getTestType());
		
		$settings = ilLOSettings::getInstanceByObjId($this->getParentObject()->getId());
		switch($this->getTestType())
		{
			case ilLOSettings::TYPE_TEST_INITIAL:
				$GLOBALS['ilTabs']->activateSubTab('itest');
				break;
			
			case ilLOSettings::TYPE_TEST_QUALIFIED:
				$GLOBALS['ilTabs']->activateSubTab('qtest');
				break;
		}

		
		// Check if test is assigned
		if(!$settings->getTestByType($this->getTestType()))
		{
			return $this->testSettings();
		}
		
		try {
			include_once './Modules/Course/classes/Objectives/class.ilLOTestAssignmentTableGUI.php';
			$table = new ilLOTestAssignmentTableGUI(
					$this,
					'testOverview',
					$this->getParentObject()->getId(),
					$this->getTestType()
			);
			$table->setTitle($this->lng->txt('crs_loc_tst_assignment'));
			$table->init();
			$table->parse(ilLOSettings::getInstanceByObjId($this->getParentObject()->getId())->getTestByType($this->getTestType()));
			$GLOBALS['tpl']->setContent($table->getHTML());
			
			$this->showStatus(
					($this->getTestType() == ilLOEditorGUI::TEST_TYPE_IT) ?
					ilLOEditorStatus::SECTION_ITES :
					ilLOEditorStatus::SECTION_QTEST
			);
		}
		catch(ilLOInvalidConfigurationException $ex)
		{
			$GLOBALS['ilLog']->write(__METHOD__.': Show new assignment sceen because of : '. $ex->getMessage());
			$this->testSettings();
		}
	}
	
	/**
	 * Show delete confirmation screen
	 */
	protected function confirmDeleteTest()
	{
		$this->setTestType((int) $_REQUEST['tt']);
		$this->ctrl->setParameter($this,'tt',$this->getTestType());
		
		$settings = ilLOSettings::getInstanceByObjId($this->getParentObject()->getId());
		switch($this->getTestType())
		{
			case ilLOSettings::TYPE_TEST_INITIAL:
				$GLOBALS['ilTabs']->activateSubTab('itest');
				break;
			
			case ilLOSettings::TYPE_TEST_QUALIFIED:
				$GLOBALS['ilTabs']->activateSubTab('qtest');
				break;
		}
		
		if(!(int) $_REQUEST['tst'])
		{
			ilUtil::sendFailure($this->lng->txt('select_one'),true);
			$this->ctrl->redirect($this,'testOverview');
		}
		
		include_once './Services/Utilities/classes/class.ilConfirmationGUI.php';
		$confirm = new ilConfirmationGUI();
		$confirm->setHeaderText($this->lng->txt('crs_loc_confirm_delete_tst'));
		$confirm->setFormAction($this->ctrl->getFormAction($this));
		$confirm->setConfirm($this->lng->txt('delete'), 'deleteTest');
		$confirm->setCancel($this->lng->txt('cancel'), 'testOverview');
		
		foreach((array) $_REQUEST['tst'] as $tst_id)
		{
			$obj_id = ilObject::_lookupObjId($tst_id);
			$confirm->addItem('tst[]', $tst_id, ilObject::_lookupTitle($obj_id));
		}
		
		$GLOBALS['tpl']->setContent($confirm->getHTML());
		
		$this->showStatus(
				($this->getTestType() == ilLOEditorGUI::TEST_TYPE_IT) ?
				ilLOEditorStatus::SECTION_ITES :
				ilLOEditorStatus::SECTION_QTEST
		);
	}
	
	/**
	 * Delete test assignment
	 */
	protected function deleteTest()
	{
		$this->setTestType((int) $_REQUEST['tt']);
		$this->ctrl->setParameter($this,'tt',$this->getTestType());
		
		$settings = ilLOSettings::getInstanceByObjId($this->getParentObject()->getId());
		switch($this->getTestType())
		{
			case ilLOSettings::TYPE_TEST_INITIAL:
				$GLOBALS['ilTabs']->activateSubTab('itest');
				break;
			
			case ilLOSettings::TYPE_TEST_QUALIFIED:
				$GLOBALS['ilTabs']->activateSubTab('qtest');
				break;
		}
		
		foreach((array) $_REQUEST['tst'] as $tst_id)
		{
			switch($this->getTestType())
			{
				case ilLOSettings::TYPE_TEST_INITIAL:
					$settings->setInitialTest(0);
					break;
				
				case ilLOSettings::TYPE_TEST_QUALIFIED:
					$settings->setQualifiedTest(0);
					break;
			}
			$settings->update();
			
			// finally delete start object assignment
			include_once './Services/Container/classes/class.ilContainerStartObjects.php';
			$start = new ilContainerStartObjects(
					$this->getParentObject()->getRefId(),
					$this->getParentObject()->getId()
			);
			$start->deleteItem($tst_id);
			
			// ... and assigned questions
			include_once './Modules/Course/classes/class.ilCourseObjectiveQuestion.php';
			ilCourseObjectiveQuestion::deleteTest($tst_id);
		}
		
		
		ilUtil::sendSuccess($this->lng->txt('settings_saved'),true);
		$this->ctrl->redirect($this,'testOverview');
	}
	
	/**
	 * Show test settings
	 * @param ilPropertyFormGUI $form
	 */
	protected function testSettings(ilPropertyFormGUI $form = NULL)
	{
		$this->ctrl->setParameter($this,'tt',(int) $_REQUEST['tt']);
		switch($this->getTestType())
		{
			case ilLOSettings::TYPE_TEST_INITIAL:
				$GLOBALS['ilTabs']->activateSubTab('itest');
				break;
			
			case ilLOSettings::TYPE_TEST_QUALIFIED:
				$GLOBALS['ilTabs']->activateSubTab('qtest');
				break;
		}
		
		if(!$form instanceof ilPropertyFormGUI)
		{
			$form = $this->initTestForm();
		}
		$GLOBALS['tpl']->setContent($form->getHTML());
		
		$this->showStatus(
				($this->getTestType() == self::TEST_TYPE_IT) ?
				ilLOEditorStatus::SECTION_ITES :
				ilLOEditorStatus::SECTION_QTEST
		);
	}
	
	/**
	 * Get assignable tests
	 */
	protected function getAssignableTests()
	{
		$tests = array();
		foreach($GLOBALS['tree']->getChildsByType($this->getParentObject()->getRefId(),'tst') as $tree_node)
		{
			if(!in_array($tree_node['child'], $this->getSettings()->getTests()))
			{
				$tests[] = $tree_node['child'];
			}
		}
		return $tests;
	}
	
	/**
	 * Show test config form
	 * @return \ilPropertyFormGUI
	 */
	protected function initTestForm()
	{
		include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->addCommandButton('saveTest', $this->lng->txt('save'));
		
		switch($this->getTestType())
		{
			case self::TEST_TYPE_IT:
				$form->setTitle($this->lng->txt('crs_loc_settings_itest_tbl'));
				break;
			
			case self::TEST_TYPE_QT:
				$form->setTitle($this->lng->txt('crs_loc_settings_qtest_tbl'));
				break;
				
		}

		$assignable = $this->getAssignableTests();

		$cr_mode = new ilRadioGroupInputGUI($this->lng->txt('crs_loc_form_assign_it'),'mode');
		$cr_mode->setRequired(true);
		$cr_mode->setValue(self::TEST_NEW);
		
		$new = new ilRadioOption($this->lng->txt('crs_loc_form_tst_new'),self::TEST_NEW);

		switch($this->getTestType())
		{
			case ilLOSettings::TYPE_TEST_INITIAL:
				$new->setInfo($this->lng->txt("crs_loc_form_tst_new_initial_info"));
				break;

			case ilLOSettings::TYPE_TEST_QUALIFIED:
				$new->setInfo($this->lng->txt("crs_loc_form_tst_new_qualified_info"));
				break;
		}

		// title
		$ti = new ilTextInputGUI($this->lng->txt("title"), "title");
		$ti->setValue(
				ilObject::_lookupTitle(ilObject::_lookupObjId($this->getSettings()->getTestByType($this->getTestType())))
		);
		$ti->setMaxLength(128);
		$ti->setSize(40);
		$ti->setRequired(true);
		$new->addSubItem($ti);

		// description
		$ta = new ilTextAreaInputGUI($this->lng->txt("description"), "desc");
		$ta->setValue(
				ilObject::_lookupDescription(ilObject::_lookupObjId($this->getSettings()->getTestByType($this->getTestType())))
		);
		$ta->setCols(40);
		$ta->setRows(2);
		$new->addSubItem($ta);
		
		// Question assignment type
		include_once './Modules/Test/classes/class.ilObjTest.php';
		$this->lng->loadLanguageModule('assessment');
		$qst = new ilRadioGroupInputGUI($this->lng->txt('tst_question_set_type'),'qtype');
		$qst->setRequired(true);
		$qst->setValue(
				$this->getSettings()->isRandomTestType($this->getTestType()) ? 
				ilObjTest::QUESTION_SET_TYPE_RANDOM:
				ilObjTest::QUESTION_SET_TYPE_FIXED
		);
		
		$random = new ilRadioOption(
				$this->lng->txt('tst_question_set_type_random'),
				ilObjTest::QUESTION_SET_TYPE_RANDOM
		);
		$qst->addOption($random);
		
		$fixed = new ilRadioOption(
				$this->lng->txt('tst_question_set_type_fixed'),
				ilObjTest::QUESTION_SET_TYPE_FIXED
		);
		$qst->addOption($fixed);
		$new->addSubItem($qst);
		$cr_mode->addOption($new);
		
		// assign existing
		$existing = new ilRadioOption($this->lng->txt('crs_loc_form_assign'),self::TEST_ASSIGN);

		switch($this->getTestType())
		{
			case ilLOSettings::TYPE_TEST_INITIAL:
				$existing->setInfo($this->lng->txt("crs_loc_form_assign_initial_info"));
				break;

			case ilLOSettings::TYPE_TEST_QUALIFIED:
				$existing->setInfo($this->lng->txt("crs_loc_form_assign_qualified_info"));
				break;
		}

		if(!$assignable)
		{
			$existing->setDisabled(true);
		}
		$cr_mode->addOption($existing);
		
		$options = array();
		$options[0] = $this->lng->txt('select_one');
		foreach((array) $assignable as $tst_ref_id)
		{
			$tst_obj_id = ilObject::_lookupObjId($tst_ref_id);
			$options[$tst_ref_id] = ilObject::_lookupTitle($tst_obj_id);
		}
		$selectable = new ilSelectInputGUI($this->lng->txt('crs_loc_form_available_tsts'),'tst');
		$selectable->setRequired(true);
		$selectable->setOptions($options);
		$existing->addSubItem($selectable);
		
		$form->addItem($cr_mode);
		return $form;
	}

	/**
	 * Apply auto generated setttings template
	 * @param ilObjTest $tst
	 */
	protected function applySettingsTemplate(ilObjTest $tst)
	{
		include_once "Services/Administration/classes/class.ilSettingsTemplate.php";
		include_once './Modules/Test/classes/class.ilObjAssessmentFolderGUI.php';
		
		$tpl_id = 0;
		foreach(ilSettingsTemplate::getAllSettingsTemplates('tst', true) as $nr => $template)
		{
			switch($this->getTestType())
			{
				case self::TEST_TYPE_IT:
					if($template['title'] == self::SETTINGS_TEMPLATE_IT)
					{
						$tpl_id = $template['id'];
					}
					break;
				case self::TEST_TYPE_QT:
					if($template['title'] == self::SETTINGS_TEMPLATE_QT)
					{
						$tpl_id = $template['id'];
					}
					break;
			}
			if($tpl_id)
			{
				break;
			}
		}
		
		if(!$tpl_id)
		{
			return false;
		}

		include_once "Services/Administration/classes/class.ilSettingsTemplate.php";
		include_once './Modules/Test/classes/class.ilObjAssessmentFolderGUI.php';
		$template = new ilSettingsTemplate($tpl_id, ilObjAssessmentFolderGUI::getSettingsTemplateConfig());
		$template_settings = $template->getSettings();
		if($template_settings)
		{
			include_once './Modules/Test/classes/class.ilObjTestGUI.php';
			$tst_gui = new ilObjTestGUI();
			$tst_gui->applyTemplate($template_settings, $tst);
		}
		$tst->setTemplate($tpl_id);
		return true;
	}
	
	/**
	 * Add Test as start object
	 * @param ilObjTest $tst
	 */
	protected function updateStartObjects()
	{
		include_once './Services/Container/classes/class.ilContainerStartObjects.php';
		$start = new ilContainerStartObjects(0, $this->getParentObject()->getId());
		$this->getSettings()->updateStartObjects($start);
		return true;
	}

	/**
	 * Save Test
	 */
	protected function saveTest()
	{
		$this->ctrl->setParameter($this,'tt',(int) $_REQUEST['tt']);
		$this->setTestType((int) $_REQUEST['tt']);
		
		$settings = ilLOSettings::getInstanceByObjId($this->getParentObject()->getId());
		
		$form = $this->initTestForm();
		if($form->checkInput())
		{
			$mode = $form->getInput('mode');
			
			if($mode == self::TEST_NEW)
			{
				$tst = new ilObjTest();
				$tst->setType('tst');
				$tst->setTitle($form->getInput('title'));
				$tst->setDescription($form->getInput('desc'));
				$tst->create();
				$tst->createReference();
				$tst->putInTree($this->getParentObject()->getRefId());
				$tst->setPermissions($this->getParentObject()->getRefId());

				// apply settings template 
				$this->applySettingsTemplate($tst);

				$tst->setQuestionSetType($form->getInput('qtype'));
				
				$tst->saveToDb();

				if($this->getTestType() == self::TEST_TYPE_IT)
				{
					$this->getSettings()->setInitialTest($tst->getRefId());
				}
				else
				{
					$this->getSettings()->setQualifiedTest($tst->getRefId());
				}
				$this->getSettings()->update();
			}
			else
			{
				if($this->getTestType() == self::TEST_TYPE_IT)
				{
					$this->getSettings()->setInitialTest($form->getInput('tst'));
				}
				else
				{
					$this->getSettings()->setQualifiedTest($form->getInput('tst'));
				}
				
				$this->getSettings()->update();
				$tst = new ilObjTest($settings->getTestByType($this->getTestType()),true);
				$this->applySettingsTemplate($tst);
				$tst->saveToDb();
			}
			
			$this->updateStartObjects();
			
			ilUtil::sendSuccess($this->lng->txt('settings_saved'));
			$this->ctrl->redirect($this,'testOverview');
		}

		// Error
		ilUtil::sendFailure($this->lng->txt('err_check_input'));
		$form->setValuesByPost();
		$this->testSettings($form);
	}
	
	/**
	 * List all abvailable objectives
	 */
	protected function listObjectives()
	{
		global $ilToolbar;
		
		$GLOBALS['ilTabs']->activateSubTab('objectives');
		
		$ilToolbar->addButton(
				$this->lng->txt('crs_add_objective'),
				$this->ctrl->getLinkTargetByClass('ilcourseobjectivesgui', "create"));

		include_once('./Modules/Course/classes/class.ilCourseObjectivesTableGUI.php');
		$table = new ilCourseObjectivesTableGUI($this,$this->getParentObject());
		$table->setTitle($this->lng->txt('crs_objectives'),'',$this->lng->txt('crs_objectives'));
		$table->parse(ilCourseObjective::_getObjectiveIds($this->getParentObject()->getId(),false));
		$GLOBALS['tpl']->setContent($table->getHTML());
		
		$this->showStatus(ilLOEditorStatus::SECTION_OBJECTIVES);
	}
	
	/**
	 * Show objective creation form
	 * @param ilPropertyFormGUI $form
	 */
	protected function showObjectiveCreation(ilPropertyFormGUI $form = NULL)
	{
		$GLOBALS['ilTabs']->activateSubTab('objectives');
		
		if(!$form instanceof ilPropertyFormGUI)
		{
			$form = $this->initSimpleObjectiveForm();
		}
		
		$GLOBALS['tpl']->setContent($form->getHTML());
		
		$this->showStatus(ilLOEditorStatus::SECTION_OBJECTIVES_NEW);
	}
	
	/**
	 * Show objective creation form
	 */
	protected function initSimpleObjectiveForm()
	{
		include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
		$form = new ilPropertyFormGUI();
		$form->setTitle($this->lng->txt('crs_loc_form_create_objectives'));
		$form->setFormAction($this->ctrl->getFormAction($this));
		
		$txt = new ilTextWizardInputGUI($this->lng->txt('crs_objectives'), 'objectives');
		$txt->setValues(array(0 => ''));
		$txt->setRequired(true);
		$form->addItem($txt);
		
		$form->addCommandButton('saveObjectiveCreation', $this->lng->txt('save'));
		
		return $form;
	}
	
	protected function saveObjectiveCreation()
	{
		$form = $this->initSimpleObjectiveForm();
		if($form->checkInput())
		{
			foreach((array) $form->getInput('objectives') as $idx => $title)
			{
				include_once './Modules/Course/classes/class.ilCourseObjective.php';
				$obj = new ilCourseObjective($this->getParentObject());
				$obj->setActive(false);
				$obj->setTitle($title);
				$obj->add();
			}
			ilUtil::sendSuccess($this->lng->txt('settings_saved'),true);
			$this->ctrl->redirect($this,'');
		}
		
		$form->setValuesByPost();
		$GLOBALS['ilTabs']->activateSubTab('objectives');
		$this->showStatus(ilLOEditorStatus::SECTION_OBJECTIVES);
	}
	
	/**
	 * save position
	 *
	 * @access protected
	 * @return
	 */
	protected function saveSorting()
	{
	 	global $ilAccess,$ilErr,$ilObjDataCache;
	 	
		asort($_POST['position'],SORT_NUMERIC);
		
		$counter = 1;
		foreach($_POST['position'] as $objective_id => $position)
		{
			include_once './Modules/Course/classes/class.ilCourseObjective.php';
			$objective = new ilCourseObjective($this->getParentObject(),$objective_id);
			$objective->writePosition($counter++);
		}
		ilUtil::sendSuccess($this->lng->txt('crs_objective_saved_sorting'));
		$this->listObjectives();
	}
	
	/**
	 * Confirm delete objectives
	 */
	protected function askDeleteObjectives()
	{
		$GLOBALS['ilTabs']->activateSubTab('objectives');

		include_once './Services/Utilities/classes/class.ilConfirmationGUI.php';
		$confirm = new ilConfirmationGUI();
		$confirm->setFormAction($this->ctrl->getFormAction($this));
		$confirm->setHeaderText($this->lng->txt('crs_delete_objectve_sure'));
		$confirm->setConfirm($this->lng->txt('delete'), 'deleteObjectives');
		$confirm->setCancel($this->lng->txt('cancel'), 'listObjectives');
		
		foreach($_POST['objective'] as $objective_id)
		{
			include_once './Modules/Course/classes/class.ilCourseObjective.php';
			$obj = new ilCourseObjective($this->getParentObject(),$objective_id);
			$name = $obj->getTitle();
			
			$confirm->addItem(
				'objective_ids[]',
				$objective_id,
				$name
			);
		}		
		$GLOBALS['tpl']->setContent($confirm->getHTML());
		$this->showStatus(ilLOEditorStatus::SECTION_OBJECTIVES);
	}
	
	/**
	 * activate chosen objectives
	 */
	protected function activateObjectives()
	{
		$enabled = (array) $_REQUEST['objective'];
		
		include_once './Modules/Course/classes/class.ilCourseObjective.php';
		$objectives = ilCourseObjective::_getObjectiveIds($this->getParentObject()->getId(),false);
		foreach((array) $objectives as $objective_id)
		{
			$objective = new ilCourseObjective($this->getParentObject(),$objective_id);
			if(in_array($objective_id, $enabled))
			{
				$objective->setActive(true);
				$objective->update();
			}
		}

		ilUtil::sendSuccess($this->lng->txt('settings_saved'),true);
		$this->ctrl->redirect($this,'listObjectives');
	}
	
	/**
	 * activate chosen objectives
	 */
	protected function deactivateObjectives()
	{
		$disabled = (array) $_REQUEST['objective'];
		
		include_once './Modules/Course/classes/class.ilCourseObjective.php';
		$objectives = ilCourseObjective::_getObjectiveIds($this->getParentObject()->getId(),false);
		foreach((array) $objectives as $objective_id)
		{
			$objective = new ilCourseObjective($this->getParentObject(),$objective_id);
			if(in_array($objective_id, $disabled))
			{
				$objective->setActive(false);
				$objective->update();
			}
		}

		ilUtil::sendSuccess($this->lng->txt('settings_saved'),true);
		$this->ctrl->redirect($this,'listObjectives');
	}

	/**
	 * Delete objectives
	 * @global type $rbacsystem
	 * @return boolean
	 */
	protected function deleteObjectives()
	{
		global $rbacsystem;

		foreach($_POST['objective_ids'] as $objective_id)
		{
			include_once './Modules/Course/classes/class.ilCourseObjective.php';
			$objective_obj = new ilCourseObjective($this->getParentObject(),$objective_id);
			$objective_obj->delete();
		}

		ilUtil::sendSuccess($this->lng->txt('crs_objectives_deleted'),true);
		$this->ctrl->redirect($this,'listObjectives');

		return true;
	}
	
	/**
	 * Show status panel
	 */
	protected function showStatus($a_section)
	{
		include_once './Modules/Course/classes/Objectives/class.ilLOEditorStatus.php';
		$status = new ilLOEditorStatus($this->getParentObject());
		$status->setSection($a_section);
		$status->setCmdClass($this);
		$GLOBALS['tpl']->setRightContent($status->getHTML());
	}
	

	
	/**
	 * Set tabs
	 * @param type $a_section
	 */
	protected function setTabs($a_section = '')
	{
		// objective settings
		$GLOBALS['ilTabs']->addSubTab(
				'settings',
				$this->lng->txt('settings'),
				$this->ctrl->getLinkTarget($this,'settings')
		);
		// learning objectives
		$GLOBALS['ilTabs']->addSubTab(
				'objectives',
				$this->lng->txt('crs_loc_tab_objectives'),
				$this->ctrl->getLinkTarget($this,'listObjectives')
		);
		// materials
		/*
		$GLOBALS['ilTabs']->addTab(
				'materials',
				$this->lng->txt('crs_loc_tab_materials'),
				$this->ctrl->getLinkTarget($this,'materials')
		);
		 */
		// tests
		$settings = ilLOSettings::getInstanceByObjId($this->getParentObject()->getId());
		if($settings->worksWithInitialTest())
		{
			$this->ctrl->setParameter($this,'tt',self::TEST_TYPE_IT);
			$GLOBALS['ilTabs']->addSubTab(
					'itest',
					$this->lng->txt('crs_loc_tab_itest'),
					$this->ctrl->getLinkTarget($this,'testOverview')
			);
			
		}
		$this->ctrl->setParameter($this,'tt',self::TEST_TYPE_QT);
		$GLOBALS['ilTabs']->addSubTab(
				'qtest',
				$this->lng->txt('crs_loc_tab_qtest'),
				$this->ctrl->getLinkTarget($this,'testOverview')
		);
		// start objects
		$GLOBALS['ilTabs']->addSubTab(
				'start',
				$this->lng->txt('crs_loc_tab_start'),
				$this->ctrl->getLinkTargetByClass('ilcontainerstartobjectsgui','')
		);
		
		// Member view
		#include_once './Services/Container/classes/class.ilMemberViewGUI.php';
		#ilMemberViewGUI::showMemberViewSwitch($this->getParentObject()->getRefId());
	}
}
?>

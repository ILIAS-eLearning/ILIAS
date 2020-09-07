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


    /**
     * @var \ilLogger
     */
    private $logger = null;


    private $parent_obj;
    private $settings = null;
    private $lng = null;
    private $ctrl = null;

    private $test_type = 0;
    
    
    /**
     * Constructor
     * @param type $a_parent_obj
     */
    public function __construct($a_parent_obj)
    {
        $this->parent_obj = $a_parent_obj;
        $this->settings = ilLOSettings::getInstanceByObjId($this->getParentObject()->getId());

        $this->lng = $GLOBALS['DIC']['lng'];
        $this->ctrl = $GLOBALS['DIC']['ilCtrl'];
        $this->logger = $GLOBALS['DIC']->logger()->crs();
    }
    
    /**
     * Execute command
     * @return <type>
     */
    public function executeCommand()
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];

        $next_class = $ilCtrl->getNextClass($this);
        $cmd = $ilCtrl->getCmd();
        
        $this->setTabs();
        switch ($next_class) {
            case 'ilcourseobjectivesgui':

                $this->ctrl->setReturn($this, 'returnFromObjectives');
                $GLOBALS['DIC']['ilTabs']->clearTargets();
                $GLOBALS['DIC']['ilTabs']->setBackTarget(
                    $this->lng->txt('back'),
                    $this->ctrl->getLinkTarget($this, 'listObjectives')
                );
                
                include_once './Modules/Course/classes/class.ilCourseObjectivesGUI.php';
                $reg_gui = new ilCourseObjectivesGUI($this->getParentObject()->getRefId());
                $this->ctrl->forwardCommand($reg_gui);
                break;
            
            case 'ilcontainerstartobjectsgui':
                
                include_once './Services/Container/classes/class.ilContainerStartObjectsGUI.php';
                $stgui = new ilContainerStartObjectsGUI($this->getParentObject());
                $ret = $this->ctrl->forwardCommand($stgui);
                
                $GLOBALS['DIC']['ilTabs']->activateSubTab('start');
                $GLOBALS['DIC']['ilTabs']->removeSubTab('manage');
                
                #$GLOBALS['DIC']['tpl']->setContent($this->ctrl->getHTML($stgui));
                break;
            
            case 'ilconditionhandlergui':
                
                $this->ctrl->saveParameterByClass('ilconditionhandlergui', 'objective_id');
                
                $GLOBALS['DIC']['ilTabs']->clearTargets();
                $GLOBALS['DIC']['ilTabs']->setBackTarget(
                    $this->lng->txt('back'),
                    $this->ctrl->getLinkTarget($this, 'listObjectives')
                );

                include_once './Services/AccessControl/classes/class.ilConditionHandlerInterface.php';
                $cond = new ilConditionHandlerGUI($this);
                $cond->setBackButtons(array());
                $cond->setAutomaticValidation(false);
                $cond->setTargetType("lobj");
                $cond->setTargetRefId($this->getParentObject()->getRefId());
                
                $cond->setTargetId((int) $_REQUEST['objective_id']);
                
                // objecitve
                include_once './Modules/Course/classes/class.ilCourseObjective.php';
                $obj = new ilCourseObjective($this->getParentObject(), (int) $_REQUEST['objective_id']);
                $cond->setTargetTitle($obj->getTitle());
                $this->ctrl->forwardCommand($cond);
                break;
            
            case 'illopagegui':
                $this->ctrl->saveParameterByClass('illopagegui', 'objective_id');
                
                $GLOBALS['DIC']['ilTabs']->clearTargets();
                $GLOBALS['DIC']['ilTabs']->setBackTarget(
                    $this->lng->txt('back'),
                    $this->ctrl->getLinkTarget($this, 'listObjectives')
                );
                
                $objtv_id = (int) $_REQUEST['objective_id'];
                
                include_once 'Modules/Course/classes/Objectives/class.ilLOPage.php';
                if (!ilLOPage::_exists('lobj', $objtv_id)) {
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

                include_once('./Services/Style/Content/classes/class.ilObjStyleSheet.php');
                $pgui->setStyleId(ilObjStyleSheet::getEffectiveContentStyleId(
                    $this->parent_obj->getStyleSheetId(),
                    $this->parent_obj->getType()
                ));

                // #14895
                $GLOBALS['DIC']['tpl']->setCurrentBlock("ContentStyle");
                $GLOBALS['DIC']['tpl']->setVariable(
                    "LOCATION_CONTENT_STYLESHEET",
                    ilObjStyleSheet::getContentStylePath(ilObjStyleSheet::getEffectiveContentStyleId(
                        $this->parent_obj->getStyleSheetId(),
                        $this->parent_obj->getType()
                    ))
                );
                $GLOBALS['DIC']['tpl']->parseCurrentBlock();
                
                $ret = $this->ctrl->forwardCommand($pgui);
                if ($ret) {
                    $GLOBALS['DIC']['tpl']->setContent($ret);
                }
                break;
            
            default:
                if (!$cmd) {
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
     * Return from objectives
     * @return type
     */
    protected function returnFromObjectives()
    {
        include_once './Modules/Course/classes/class.ilCourseObjectivesGUI.php';
        $_SESSION['objective_mode'] = ilCourseObjectivesGUI::MODE_UNDEFINED;
        return $this->listObjectives();
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
    protected function settings(ilPropertyFormGUI $form = null)
    {
        if (!$form instanceof ilPropertyFormGUI) {
            $form = $this->initSettingsForm();
        }
        
        $GLOBALS['DIC']['ilTabs']->activateSubTab('settings');
        $GLOBALS['DIC']['tpl']->setContent($form->getHTML());
        
        $this->showStatus(ilLOEditorStatus::SECTION_SETTINGS);
    }
    
    /**
     * Delete assignments
     * @param type $a_type
     */
    protected function deleteAssignments($a_type)
    {
        include_once './Modules/Course/classes/Objectives/class.ilLOTestAssignments.php';
        $assignments = ilLOTestAssignments::getInstance($this->getParentObject()->getId());
        foreach ($assignments->getAssignmentsByType($a_type) as $assignment) {
            $assignment->delete();
        }
        return;
    }
    
    /**
     * Update Test assignments
     * @param ilLOSettings $settings
     */
    protected function updateTestAssignments(ilLOSettings $settings)
    {
        switch ($settings->getInitialTestType()) {
            case ilLOSettings::TYPE_INITIAL_NONE:
                $settings->setInitialTest(0);
                $this->deleteAssignments(ilLOSettings::TYPE_TEST_INITIAL);
            
                // no break
            case ilLOSettings::TYPE_INITIAL_PLACEMENT_ALL:
            case ilLOSettings::TYPE_INITIAL_QUALIFYING_ALL:
                $this->deleteAssignments(ilLOSettings::TYPE_TEST_INITIAL);
                
                break;
            
            case ilLOSettings::TYPE_INITIAL_PLACEMENT_SELECTED:
            case ilLOSettings::TYPE_INITIAL_QUALIFYING_SELECTED:
                $settings->setInitialTest(0);
                break;
        }
        
        switch ($settings->getQualifyingTestType()) {
            case ilLOSettings::TYPE_QUALIFYING_ALL:
                $this->deleteAssignments(ilLOSettings::TYPE_TEST_QUALIFIED);
                break;
                
            case ilLOSettings::TYPE_QUALIFYING_SELECTED:
                $settings->setQualifiedTest(0);
                break;
        }
        $settings->update();
    }


    /**
     *
     */
    protected function saveSettings()
    {
        $form = $this->initSettingsForm();
        if ($form->checkInput()) {
            $settings = ilLOSettings::getInstanceByObjId($this->getParentObject()->getId());
            $settings->setInitialTestType($form->getInput('ittype'));
            switch ($settings->getInitialTestType()) {
                case ilLOSettings::TYPE_INITIAL_PLACEMENT_ALL:
                    $settings->setInitialTestAsStart($form->getInput('start_ip'));
                    break;
                
                case ilLOSettings::TYPE_INITIAL_PLACEMENT_SELECTED:
                    $settings->setInitialTestAsStart(false);
                    break;
                
                case ilLOSettings::TYPE_INITIAL_QUALIFYING_ALL:
                    $settings->setInitialTestAsStart($form->getInput('start_iq'));
                    break;
                    
                case ilLOSettings::TYPE_INITIAL_QUALIFYING_SELECTED:
                    $settings->setInitialTestAsStart(false);
                    break;
                
                case ilLOSettings::TYPE_INITIAL_NONE:
                    $settings->setInitialTestAsStart(false);
                    break;
            }
            
            $settings->setQualifyingTestType($form->getInput('qttype'));
            switch ($settings->getQualifyingTestType()) {
                case ilLOSettings::TYPE_QUALIFYING_ALL:
                    $settings->setQualifyingTestAsStart($form->getInput('start_q'));
                    break;
                    
                case ilLOSettings::TYPE_QUALIFYING_SELECTED:
                    $settings->setQualifyingTestAsStart(false);
                    break;
            }
            
            
            $settings->resetResults($form->getInput('reset'));
            $settings->setPassedObjectiveMode($form->getInput('passed_mode'));

            if (
                ($settings->getInitialTestType() != ilLOSettings::TYPE_INITIAL_NONE) &&
                ($settings->isQualifyingTestStart())
            ) {
                $settings->setQualifyingTestAsStart(false);
                ilUtil::sendInfo($this->lng->txt('crs_loc_settings_err_qstart'), true);
            }
            
            $settings->update();
            $this->updateStartObjects();
            $this->updateTestAssignments($settings);
            
            include_once './Services/Tracking/classes/class.ilLPStatusWrapper.php';
            ilLPStatusWrapper::_refreshStatus($this->getParentObject()->getId());
            
            ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
            $this->ctrl->redirect($this, 'settings');
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
        
        // initial test
        $type_selector = new ilRadioGroupInputGUI($this->lng->txt('crs_loc_settings_it_type'), 'ittype');
        $type_selector->setRequired(true);
        $type_selector->setValue($this->getSettings()->getInitialTestType());
        
        $type_ipa = new ilRadioOption($this->lng->txt('crs_loc_settings_type_it_placement_all'), ilLOSettings::TYPE_INITIAL_PLACEMENT_ALL);
        $type_ipa->setInfo($this->lng->txt('crs_loc_settings_type_it_placement_all_info'));
        $type_selector->addOption($type_ipa);

        $start_ip = new ilCheckboxInputGUI($this->lng->txt('crs_loc_settings_it_start_object'), 'start_ip');
        $start_ip->setValue(1);
        $start_ip->setChecked($this->getSettings()->isInitialTestStart());
        $type_ipa->addSubItem($start_ip);
        
        $type_ips = new ilRadioOption($this->lng->txt('crs_loc_settings_type_it_placement_sel'), ilLOSettings::TYPE_INITIAL_PLACEMENT_SELECTED);
        $type_ips->setInfo($this->lng->txt('crs_loc_settings_type_it_placement_sel_info'));
        $type_selector->addOption($type_ips);
        
        $type_iqa = new ilRadioOption($this->lng->txt('crs_loc_settings_type_it_qualifying_all'), ilLOSettings::TYPE_INITIAL_QUALIFYING_ALL);
        $type_iqa->setInfo($this->lng->txt('crs_loc_settings_type_it_qualifying_all_info'));
        $type_selector->addOption($type_iqa);
        
        $start_iq = new ilCheckboxInputGUI($this->lng->txt('crs_loc_settings_it_start_object'), 'start_iq');
        $start_iq->setValue(1);
        $start_iq->setChecked($this->getSettings()->isInitialTestStart());
        $type_iqa->addSubItem($start_iq);

        $type_iqs = new ilRadioOption($this->lng->txt('crs_loc_settings_type_it_qualifying_sel'), ilLOSettings::TYPE_INITIAL_QUALIFYING_SELECTED);
        $type_iqs->setInfo($this->lng->txt('crs_loc_settings_type_it_qualifying_sel_info'));
        $type_selector->addOption($type_iqs);
        
        $type_ino = new ilRadioOption($this->lng->txt('crs_loc_settings_type_it_none'), ilLOSettings::TYPE_INITIAL_NONE);
        $type_ino->setInfo($this->lng->txt('crs_loc_settings_type_it_none_info'));
        $type_selector->addOption($type_ino);
        
        $form->addItem($type_selector);
        
        // qualifying test
        $qt_selector = new ilRadioGroupInputGUI($this->lng->txt('crs_loc_settings_qt_all'), 'qttype');
        $qt_selector->setRequired(true);
        $qt_selector->setValue($this->getSettings()->getQualifyingTestType());
        
        $type_qa = new ilRadioOption($this->lng->txt('crs_loc_settings_type_q_all'), ilLOSettings::TYPE_QUALIFYING_ALL);
        $type_qa->setInfo($this->lng->txt('crs_loc_settings_type_q_all_info'));
        $qt_selector->addOption($type_qa);
        
        $start_q = new ilCheckboxInputGUI($this->lng->txt('crs_loc_settings_qt_start_object'), 'start_q');
        $start_q->setValue(1);
        $start_q->setChecked($this->getSettings()->isQualifyingTestStart());
        $type_qa->addSubItem($start_q);
        
        $passed_mode = new ilRadioGroupInputGUI($this->lng->txt('crs_loc_settings_passed_mode'), 'passed_mode');
        $passed_mode->setValue($this->getSettings()->getPassedObjectiveMode());
        
        $passed_mode->addOption(
            new ilRadioOption(
                $this->lng->txt('crs_loc_settings_passed_mode_hide'),
                ilLOSettings::HIDE_PASSED_OBJECTIVE_QST
            )
        );
        $passed_mode->addOption(
            new ilRadioOption(
                $this->lng->txt('crs_loc_settings_passed_mode_mark'),
                ilLOSettings::MARK_PASSED_OBJECTIVE_QST
            )
        );
        $type_qa->addSubItem($passed_mode);
        
        $type_qs = new ilRadioOption($this->lng->txt('crs_loc_settings_type_q_selected'), ilLOSettings::TYPE_QUALIFYING_SELECTED);
        $type_qs->setInfo($this->lng->txt('crs_loc_settings_type_q_selected_info'));
        $qt_selector->addOption($type_qs);
        
        $form->addItem($qt_selector);
        
        // reset results
        $reset = new ilCheckboxInputGUI($this->lng->txt('crs_loc_settings_reset'), 'reset');
        $reset->setValue(1);
        $reset->setChecked($this->getSettings()->isResetResultsEnabled());
        $reset->setOptionTitle($this->lng->txt('crs_loc_settings_reset_enable'));
        $reset->setInfo($this->lng->txt('crs_loc_settings_reset_enable_info'));
        $form->addItem($reset);
        
        $form->addCommandButton('saveSettings', $this->lng->txt('save'));
        
        
        return $form;
    }
    
    protected function materials()
    {
        $GLOBALS['DIC']['ilTabs']->activateSubTab('materials');
        
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
        $obj_table->setObjects($GLOBALS['DIC']['tree']->getChildIds($this->getParentObject()->getRefId()));
        $obj_table->parse();
        $GLOBALS['DIC']['tpl']->setContent($obj_table->getHTML());
        
        $this->showStatus(ilLOEditorStatus::SECTION_MATERIALS);
    }
    
    /**
     * View test assignments ()
     */
    protected function testsOverview()
    {
        $this->setTestType((int) $_REQUEST['tt']);
        $this->ctrl->setParameter($this, 'tt', $this->getTestType());

        $GLOBALS['DIC']['ilToolbar']->setFormAction($this->ctrl->getFormAction($this));
        $GLOBALS['DIC']['ilToolbar']->addButton(
            $this->lng->txt('crs_loc_btn_new_assignment'),
            $this->ctrl->getLinkTarget($this, 'testAssignment')
        );
        
        
        $settings = ilLOSettings::getInstanceByObjId($this->getParentObject()->getId());
        switch ($this->getTestType()) {
            case ilLOSettings::TYPE_TEST_INITIAL:
                $GLOBALS['DIC']['ilTabs']->activateSubTab('itests');
                break;
            
            case ilLOSettings::TYPE_TEST_QUALIFIED:
                $GLOBALS['DIC']['ilTabs']->activateSubTab('qtests');
                break;
        }
        
        try {
            include_once './Modules/Course/classes/Objectives/class.ilLOTestAssignmentTableGUI.php';
            $table = new ilLOTestAssignmentTableGUI(
                $this,
                'testsOverview',
                $this->getParentObject()->getId(),
                $this->getTestType(),
                ilLOTestAssignmentTableGUI::TYPE_MULTIPLE_ASSIGNMENTS
            );
            $table->init();
            $table->parseMultipleAssignments();
            $GLOBALS['DIC']['tpl']->setContent($table->getHTML());
            
            $this->showStatus(
                ($this->getTestType() == ilLOEditorGUI::TEST_TYPE_IT) ?
                    ilLOEditorStatus::SECTION_ITES :
                    ilLOEditorStatus::SECTION_QTEST
            );
        } catch (ilLOInvalidConfigurationException $ex) {
            $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': Show new assignment screen because of : ' . $ex->getMessage());
            $this->testSettings();
        }
    }
    
    
    /**
     * Show test overview
     */
    protected function testOverview()
    {
        $this->setTestType((int) $_REQUEST['tt']);
        $this->ctrl->setParameter($this, 'tt', $this->getTestType());
        
        $settings = ilLOSettings::getInstanceByObjId($this->getParentObject()->getId());
        switch ($this->getTestType()) {
            case ilLOSettings::TYPE_TEST_INITIAL:
                $GLOBALS['DIC']['ilTabs']->activateSubTab('itest');
                break;
            
            case ilLOSettings::TYPE_TEST_QUALIFIED:
                $GLOBALS['DIC']['ilTabs']->activateSubTab('qtest');
                break;
        }

        
        // Check if test is assigned
        if (!$settings->getTestByType($this->getTestType())) {
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
            $table->init();
            $table->parse(ilLOSettings::getInstanceByObjId($this->getParentObject()->getId())->getTestByType($this->getTestType()));
            $GLOBALS['DIC']['tpl']->setContent($table->getHTML());
            
            $this->showStatus(
                ($this->getTestType() == ilLOEditorGUI::TEST_TYPE_IT) ?
                    ilLOEditorStatus::SECTION_ITES :
                    ilLOEditorStatus::SECTION_QTEST
            );
        } catch (ilLOInvalidConfigurationException $ex) {
            $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': Show new assignment screen because of : ' . $ex->getMessage());
            $this->testSettings();
        }
    }
    
    /**
     * Show delete test confirmation
     */
    protected function confirmDeleteTests()
    {
        $this->setTestType((int) $_REQUEST['tt']);
        $this->ctrl->setParameter($this, 'tt', $this->getTestType());
        
        $settings = ilLOSettings::getInstanceByObjId($this->getParentObject()->getId());
        switch ($this->getTestType()) {
            case ilLOSettings::TYPE_TEST_INITIAL:
                $GLOBALS['DIC']['ilTabs']->activateSubTab('itests');
                break;
            
            case ilLOSettings::TYPE_TEST_QUALIFIED:
                $GLOBALS['DIC']['ilTabs']->activateSubTab('qtests');
                break;
        }
        
        if (!(int) $_REQUEST['tst']) {
            ilUtil::sendFailure($this->lng->txt('select_one'), true);
            $this->ctrl->redirect($this, 'testsOverview');
        }
        
        include_once './Services/Utilities/classes/class.ilConfirmationGUI.php';
        $confirm = new ilConfirmationGUI();
        $confirm->setHeaderText($this->lng->txt('crs_loc_confirm_delete_tst'));
        $confirm->setFormAction($this->ctrl->getFormAction($this));
        $confirm->setConfirm($this->lng->txt('crs_loc_delete_assignment'), 'deleteTests');
        $confirm->setCancel($this->lng->txt('cancel'), 'testsOverview');
        
        foreach ((array) $_REQUEST['tst'] as $assign_id) {
            include_once './Modules/Course/classes/Objectives/class.ilLOTestAssignment.php';
            $assignment = new ilLOTestAssignment($assign_id);
            
            
            $obj_id = ilObject::_lookupObjId($assignment->getTestRefId());
            $confirm->addItem('tst[]', $assign_id, ilObject::_lookupTitle($obj_id));
        }
        
        $GLOBALS['DIC']['tpl']->setContent($confirm->getHTML());
        
        $this->showStatus(
            ($this->getTestType() == ilLOSettings::TYPE_TEST_INITIAL) ?
                ilLOEditorStatus::SECTION_ITES :
                ilLOEditorStatus::SECTION_QTEST
        );
    }
    
    /**
     * Show delete confirmation screen
     */
    protected function confirmDeleteTest()
    {
        $this->setTestType((int) $_REQUEST['tt']);
        $this->ctrl->setParameter($this, 'tt', $this->getTestType());
        
        $settings = ilLOSettings::getInstanceByObjId($this->getParentObject()->getId());
        switch ($this->getTestType()) {
            case ilLOSettings::TYPE_TEST_INITIAL:
                $GLOBALS['DIC']['ilTabs']->activateSubTab('itest');
                break;
            
            case ilLOSettings::TYPE_TEST_QUALIFIED:
                $GLOBALS['DIC']['ilTabs']->activateSubTab('qtest');
                break;
        }
        
        if (!(int) $_REQUEST['tst']) {
            ilUtil::sendFailure($this->lng->txt('select_one'), true);
            $this->ctrl->redirect($this, 'testOverview');
        }
        
        include_once './Services/Utilities/classes/class.ilConfirmationGUI.php';
        $confirm = new ilConfirmationGUI();
        $confirm->setHeaderText($this->lng->txt('crs_loc_confirm_delete_tst'));
        $confirm->setFormAction($this->ctrl->getFormAction($this));
        $confirm->setConfirm($this->lng->txt('crs_loc_delete_assignment'), 'deleteTest');
        $confirm->setCancel($this->lng->txt('cancel'), 'testOverview');
        
        foreach ((array) $_REQUEST['tst'] as $tst_id) {
            $obj_id = ilObject::_lookupObjId($tst_id);
            $confirm->addItem('tst[]', $tst_id, ilObject::_lookupTitle($obj_id));
        }
        
        $GLOBALS['DIC']['tpl']->setContent($confirm->getHTML());
        
        $this->showStatus(
            ($this->getTestType() == ilLOEditorGUI::TEST_TYPE_IT) ?
                ilLOEditorStatus::SECTION_ITES :
                ilLOEditorStatus::SECTION_QTEST
        );
    }
    
    /**
     * Delete test assignments
     */
    protected function deleteTests()
    {
        $this->setTestType((int) $_REQUEST['tt']);
        $this->ctrl->setParameter($this, 'tt', $this->getTestType());
        
        $settings = ilLOSettings::getInstanceByObjId($this->getParentObject()->getId());
        switch ($this->getTestType()) {
            case ilLOSettings::TYPE_TEST_INITIAL:
                $GLOBALS['DIC']['ilTabs']->activateSubTab('itests');
                break;
            
            case ilLOSettings::TYPE_TEST_QUALIFIED:
                $GLOBALS['DIC']['ilTabs']->activateSubTab('qtests');
                break;
        }
        
        foreach ((array) $_REQUEST['tst'] as $assign_id) {
            include_once './Modules/Course/classes/Objectives/class.ilLOTestAssignment.php';
            $assignment = new ilLOTestAssignment($assign_id);
            $assignment->delete();
            
            // finally delete start object assignment
            include_once './Services/Container/classes/class.ilContainerStartObjects.php';
            $start = new ilContainerStartObjects(
                $this->getParentObject()->getRefId(),
                $this->getParentObject()->getId()
            );
            $start->deleteItem($assignment->getTestRefId());
            
            // ... and assigned questions
            include_once './Modules/Course/classes/class.ilCourseObjectiveQuestion.php';
            ilCourseObjectiveQuestion::deleteTest($assignment->getTestRefId());
        }
        
        
        ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, 'testsOverview');
    }
    
    /**
     * Delete test assignment
     */
    protected function deleteTest()
    {
        $this->setTestType((int) $_REQUEST['tt']);
        $this->ctrl->setParameter($this, 'tt', $this->getTestType());
        
        $settings = ilLOSettings::getInstanceByObjId($this->getParentObject()->getId());
        switch ($this->getTestType()) {
            case ilLOSettings::TYPE_TEST_INITIAL:
                $GLOBALS['DIC']['ilTabs']->activateSubTab('itest');
                break;
            
            case ilLOSettings::TYPE_TEST_QUALIFIED:
                $GLOBALS['DIC']['ilTabs']->activateSubTab('qtest');
                break;
        }
        
        foreach ((array) $_REQUEST['tst'] as $tst_id) {
            switch ($this->getTestType()) {
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
        
        
        ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, 'testOverview');
    }
    
    /**
     * new test assignment
     */
    protected function testAssignment(ilPropertyFormGUI $form = null)
    {
        $this->setTestType((int) $_REQUEST['tt']);
        $this->ctrl->setParameter($this, 'tt', $this->getTestType());

        switch ($this->getTestType()) {
            case ilLOSettings::TYPE_TEST_INITIAL:
                $GLOBALS['DIC']['ilTabs']->activateSubTab('itests');
                break;
            
            case ilLOSettings::TYPE_TEST_QUALIFIED:
                $GLOBALS['DIC']['ilTabs']->activateSubTab('qtests');
                break;
        }
        if (!$form instanceof ilPropertyFormGUI) {
            include_once './Modules/Course/classes/Objectives/class.ilLOTestAssignmentForm.php';
            $form_helper = new ilLOTestAssignmentForm($this, $this->getParentObject(), $this->getTestType());
            $form = $form_helper->initForm(true);
        }
        $GLOBALS['DIC']['tpl']->setContent($form->getHTML());
        
        $this->showStatus(
            ($this->getTestType() == self::TEST_TYPE_IT) ?
                ilLOEditorStatus::SECTION_ITES :
                ilLOEditorStatus::SECTION_QTEST
        );
    }
    
    /**
     * Show test settings
     * @param ilPropertyFormGUI $form
     */
    protected function testSettings(ilPropertyFormGUI $form = null)
    {
        $this->ctrl->setParameter($this, 'tt', (int) $_REQUEST['tt']);
        switch ($this->getTestType()) {
            case ilLOSettings::TYPE_TEST_INITIAL:
                $GLOBALS['DIC']['ilTabs']->activateSubTab('itest');
                break;
            
            case ilLOSettings::TYPE_TEST_QUALIFIED:
                $GLOBALS['DIC']['ilTabs']->activateSubTab('qtest');
                break;
        }
        
        if (!$form instanceof ilPropertyFormGUI) {
            include_once './Modules/Course/classes/Objectives/class.ilLOTestAssignmentForm.php';
            $form_helper = new ilLOTestAssignmentForm($this, $this->getParentObject(), $this->getTestType());
            $form = $form_helper->initForm(false);
        }
        $GLOBALS['DIC']['tpl']->setContent($form->getHTML());
        
        $this->showStatus(
            ($this->getTestType() == self::TEST_TYPE_IT) ?
                ilLOEditorStatus::SECTION_ITES :
                ilLOEditorStatus::SECTION_QTEST
        );
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
        foreach (ilSettingsTemplate::getAllSettingsTemplates('tst', true) as $nr => $template) {
            switch ($this->getTestType()) {
                case self::TEST_TYPE_IT:
                    if ($template['title'] == self::SETTINGS_TEMPLATE_IT) {
                        $tpl_id = $template['id'];
                    }
                    break;
                case self::TEST_TYPE_QT:
                    if ($template['title'] == self::SETTINGS_TEMPLATE_QT) {
                        $tpl_id = $template['id'];
                    }
                    break;
            }
            if ($tpl_id) {
                break;
            }
        }
        
        if (!$tpl_id) {
            return false;
        }

        include_once "Services/Administration/classes/class.ilSettingsTemplate.php";
        include_once './Modules/Test/classes/class.ilObjAssessmentFolderGUI.php';
        $template = new ilSettingsTemplate($tpl_id, ilObjAssessmentFolderGUI::getSettingsTemplateConfig());
        $template_settings = $template->getSettings();
        if ($template_settings) {
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
    
    protected function saveMultiTestAssignment()
    {
        $this->ctrl->setParameter($this, 'tt', (int) $_REQUEST['tt']);
        $this->setTestType((int) $_REQUEST['tt']);
        
        $settings = ilLOSettings::getInstanceByObjId($this->getParentObject()->getId());
        
        include_once './Modules/Course/classes/Objectives/class.ilLOTestAssignmentForm.php';
        $form_helper = new ilLOTestAssignmentForm($this, $this->getParentObject(), $this->getTestType());
        $form = $form_helper->initForm(true);
        
        if ($form->checkInput()) {
            $mode = $form->getInput('mode');
            
            if ($mode == self::TEST_NEW) {
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

                include_once './Modules/Course/classes/Objectives/class.ilLOTestAssignment.php';
                $assignment = new ilLOTestAssignment();
                $assignment->setContainerId($this->getParentObject()->getId());
                $assignment->setAssignmentType($this->getTestType());
                $assignment->setObjectiveId($form->getInput('objective'));
                $assignment->setTestRefId($tst->getRefId());
                $assignment->save();
            } else {
                include_once './Modules/Course/classes/Objectives/class.ilLOTestAssignment.php';
                $assignment = new ilLOTestAssignment();
                $assignment->setContainerId($this->getParentObject()->getId());
                $assignment->setAssignmentType($this->getTestType());
                $assignment->setObjectiveId($form->getInput('objective'));
                $assignment->setTestRefId($form->getInput('tst'));
                $assignment->save();
                
                $tst = new ilObjTest($form->getInput('tst'), true);
                $this->applySettingsTemplate($tst);
                $tst->saveToDb();
            }

            // deassign as objective material
            if ($tst instanceof  ilObjTest) {
                $this->updateMaterialAssignments($tst);
            }
            $this->updateStartObjects();
            
            ilUtil::sendSuccess($this->lng->txt('settings_saved'));
            $this->ctrl->redirect($this, 'testsOverview');
        }

        // Error
        ilUtil::sendFailure($this->lng->txt('err_check_input'));
        $form->setValuesByPost();
        $this->testAssignment($form);
    }

    /**
     * @param \ilObjTest $test
     */
    protected function updateMaterialAssignments(ilObjTest $test)
    {
        include_once './Modules/Course/classes/class.ilCourseObjective.php';
        foreach (ilCourseObjective::_getObjectiveIds($this->getParentObject()->getId()) as $objective_id) {
            include_once './Modules/Course/classes/class.ilCourseObjectiveMaterials.php';
            $materials = new ilCourseObjectiveMaterials($objective_id);
            foreach ($materials->getMaterials() as $key => $material) {
                if ($material['ref_id'] == $test->getRefId()) {
                    $materials->delete($material['lm_ass_id']);
                }
            }
        }
    }

    /**
     * Save Test
     */
    protected function saveTest()
    {
        $this->ctrl->setParameter($this, 'tt', (int) $_REQUEST['tt']);
        $this->setTestType((int) $_REQUEST['tt']);
        
        $settings = ilLOSettings::getInstanceByObjId($this->getParentObject()->getId());
        
        include_once './Modules/Course/classes/Objectives/class.ilLOTestAssignmentForm.php';
        $form_helper = new ilLOTestAssignmentForm($this, $this->getParentObject(), $this->getTestType());
        $form = $form_helper->initForm(false);
        
        if ($form->checkInput()) {
            $mode = $form->getInput('mode');
            
            if ($mode == self::TEST_NEW) {
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

                if ($this->getTestType() == self::TEST_TYPE_IT) {
                    $this->getSettings()->setInitialTest($tst->getRefId());
                } else {
                    $this->getSettings()->setQualifiedTest($tst->getRefId());
                }
                $this->getSettings()->update();
            } else {
                if ($this->getTestType() == self::TEST_TYPE_IT) {
                    $this->getSettings()->setInitialTest($form->getInput('tst'));
                } else {
                    $this->getSettings()->setQualifiedTest($form->getInput('tst'));
                }
                
                $this->getSettings()->update();
                $tst = new ilObjTest($settings->getTestByType($this->getTestType()), true);
                $this->applySettingsTemplate($tst);
                $tst->saveToDb();
            }

            // deassign as objective material
            if ($tst instanceof  ilObjTest) {
                $this->updateMaterialAssignments($tst);
            }
            $this->updateStartObjects();
            
            ilUtil::sendSuccess($this->lng->txt('settings_saved'));
            $this->ctrl->redirect($this, 'testOverview');
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
        global $DIC;

        $ilToolbar = $DIC['ilToolbar'];

        include_once './Modules/Course/classes/class.ilCourseObjectivesGUI.php';
        $_SESSION['objective_mode'] = ilCourseObjectivesGUI::MODE_UNDEFINED;
        
        
        $GLOBALS['DIC']['ilTabs']->activateSubTab('objectives');
        
        $objectives = ilCourseObjective::_getObjectiveIds(
            $this->getParentObject()->getId(),
            false
        );
        
        if (!count($objectives)) {
            return $this->showObjectiveCreation();
        }
        
        $ilToolbar->addButton(
            $this->lng->txt('crs_add_objective'),
            $this->ctrl->getLinkTargetByClass('ilcourseobjectivesgui', "create")
        );

        include_once('./Modules/Course/classes/class.ilCourseObjectivesTableGUI.php');
        $table = new ilCourseObjectivesTableGUI($this, $this->getParentObject());
        $table->setTitle($this->lng->txt('crs_objectives'), '', $this->lng->txt('crs_objectives'));
        $table->parse($objectives);
        $GLOBALS['DIC']['tpl']->setContent($table->getHTML());
        
        $this->showStatus(ilLOEditorStatus::SECTION_OBJECTIVES);
    }
    
    /**
     * Show objective creation form
     * @param ilPropertyFormGUI $form
     */
    protected function showObjectiveCreation(ilPropertyFormGUI $form = null)
    {
        $GLOBALS['DIC']['ilTabs']->activateSubTab('objectives');
        
        if (!$form instanceof ilPropertyFormGUI) {
            $form = $this->initSimpleObjectiveForm();
        }
        
        $GLOBALS['DIC']['tpl']->setContent($form->getHTML());
        
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
        if ($form->checkInput()) {
            foreach ((array) $form->getInput('objectives') as $idx => $title) {
                include_once './Modules/Course/classes/class.ilCourseObjective.php';
                $obj = new ilCourseObjective($this->getParentObject());
                $obj->setActive(true);
                $obj->setTitle($title);
                $obj->add();
            }
            ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
            $this->ctrl->redirect($this, '');
        }
        
        $form->setValuesByPost();
        $GLOBALS['DIC']['ilTabs']->activateSubTab('objectives');
        $this->showStatus(ilLOEditorStatus::SECTION_OBJECTIVES);
        $this->showObjectiveCreation($form);
    }
    
    /**
     * save position
     *
     * @access protected
     * @return
     */
    protected function saveSorting()
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];
        $ilErr = $DIC['ilErr'];
        $ilObjDataCache = $DIC['ilObjDataCache'];
        
        asort($_POST['position'], SORT_NUMERIC);
        
        $counter = 1;
        foreach ($_POST['position'] as $objective_id => $position) {
            include_once './Modules/Course/classes/class.ilCourseObjective.php';
            $objective = new ilCourseObjective($this->getParentObject(), $objective_id);
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
        $GLOBALS['DIC']['ilTabs']->activateSubTab('objectives');

        include_once './Services/Utilities/classes/class.ilConfirmationGUI.php';
        $confirm = new ilConfirmationGUI();
        $confirm->setFormAction($this->ctrl->getFormAction($this));
        $confirm->setHeaderText($this->lng->txt('crs_delete_objectve_sure'));
        $confirm->setConfirm($this->lng->txt('delete'), 'deleteObjectives');
        $confirm->setCancel($this->lng->txt('cancel'), 'listObjectives');
        
        foreach ($_POST['objective'] as $objective_id) {
            include_once './Modules/Course/classes/class.ilCourseObjective.php';
            $obj = new ilCourseObjective($this->getParentObject(), $objective_id);
            $name = $obj->getTitle();
            
            $confirm->addItem(
                'objective_ids[]',
                $objective_id,
                $name
            );
        }
        $GLOBALS['DIC']['tpl']->setContent($confirm->getHTML());
        $this->showStatus(ilLOEditorStatus::SECTION_OBJECTIVES);
    }
    
    /**
     * activate chosen objectives
     */
    protected function activateObjectives()
    {
        $enabled = (array) $_REQUEST['objective'];
        
        include_once './Modules/Course/classes/class.ilCourseObjective.php';
        $objectives = ilCourseObjective::_getObjectiveIds($this->getParentObject()->getId(), false);
        foreach ((array) $objectives as $objective_id) {
            $objective = new ilCourseObjective($this->getParentObject(), $objective_id);
            if (in_array($objective_id, $enabled)) {
                $objective->setActive(true);
                $objective->update();
            }
        }

        include_once './Services/Tracking/classes/class.ilLPStatusWrapper.php';
        ilLPStatusWrapper::_refreshStatus($this->getParentObject()->getId());

        ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, 'listObjectives');
    }
    
    /**
     * activate chosen objectives
     */
    protected function deactivateObjectives()
    {
        $disabled = (array) $_REQUEST['objective'];
        
        include_once './Modules/Course/classes/class.ilCourseObjective.php';
        $objectives = ilCourseObjective::_getObjectiveIds($this->getParentObject()->getId(), false);
        foreach ((array) $objectives as $objective_id) {
            $objective = new ilCourseObjective($this->getParentObject(), $objective_id);
            if (in_array($objective_id, $disabled)) {
                $objective->setActive(false);
                $objective->update();
            }
        }
        
        include_once './Services/Tracking/classes/class.ilLPStatusWrapper.php';
        ilLPStatusWrapper::_refreshStatus($this->getParentObject()->getId());

        ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, 'listObjectives');
    }

    /**
     * Delete objectives
     * @global type $rbacsystem
     * @return boolean
     */
    protected function deleteObjectives()
    {
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];

        foreach ($_POST['objective_ids'] as $objective_id) {
            include_once './Modules/Course/classes/class.ilCourseObjective.php';
            $objective_obj = new ilCourseObjective($this->getParentObject(), $objective_id);
            $objective_obj->delete();
        }
        
        include_once './Services/Tracking/classes/class.ilLPStatusWrapper.php';
        ilLPStatusWrapper::_refreshStatus($this->getParentObject()->getId());

        ilUtil::sendSuccess($this->lng->txt('crs_objectives_deleted'), true);
        $this->ctrl->redirect($this, 'listObjectives');

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
        $GLOBALS['DIC']['tpl']->setRightContent($status->getHTML());
    }
    

    
    /**
     * Set tabs
     * @param type $a_section
     */
    protected function setTabs($a_section = '')
    {
        // objective settings
        $GLOBALS['DIC']['ilTabs']->addSubTab(
            'settings',
            $this->lng->txt('settings'),
            $this->ctrl->getLinkTarget($this, 'settings')
        );
        // learning objectives
        $GLOBALS['DIC']['ilTabs']->addSubTab(
            'objectives',
            $this->lng->txt('crs_loc_tab_objectives'),
            $this->ctrl->getLinkTarget($this, 'listObjectives')
        );
        // materials
        /*
        $GLOBALS['DIC']['ilTabs']->addTab(
                'materials',
                $this->lng->txt('crs_loc_tab_materials'),
                $this->ctrl->getLinkTarget($this,'materials')
        );
         */
        // tests
        $settings = ilLOSettings::getInstanceByObjId($this->getParentObject()->getId());
        if ($settings->worksWithInitialTest()) {
            if (
                $settings->getInitialTestType() == ilLOSettings::TYPE_INITIAL_PLACEMENT_ALL ||
                $settings->getInitialTestType() == ilLOSettings::TYPE_INITIAL_QUALIFYING_ALL
            ) {
                $this->ctrl->setParameter($this, 'tt', ilLOSettings::TYPE_TEST_INITIAL);
                $GLOBALS['DIC']['ilTabs']->addSubTab(
                    'itest',
                    $this->lng->txt('crs_loc_tab_itest'),
                    $this->ctrl->getLinkTarget($this, 'testOverview')
                );
            } else {
                $this->ctrl->setParameter($this, 'tt', ilLOSettings::TYPE_TEST_INITIAL);
                $GLOBALS['DIC']['ilTabs']->addSubTab(
                    'itests',
                    $this->lng->txt('crs_loc_tab_itests'),
                    $this->ctrl->getLinkTarget($this, 'testsOverview')
                );
            }
        }
        
        if ($settings->getQualifyingTestType() == ilLOSettings::TYPE_QUALIFYING_ALL) {
            $this->ctrl->setParameter($this, 'tt', ilLOSettings::TYPE_TEST_QUALIFIED);
            $GLOBALS['DIC']['ilTabs']->addSubTab(
                'qtest',
                $this->lng->txt('crs_loc_tab_qtest'),
                $this->ctrl->getLinkTarget($this, 'testOverview')
            );
        } else {
            $this->ctrl->setParameter($this, 'tt', ilLOSettings::TYPE_TEST_QUALIFIED);
            $GLOBALS['DIC']['ilTabs']->addSubTab(
                'qtests',
                $this->lng->txt('crs_loc_tab_qtests'),
                $this->ctrl->getLinkTarget($this, 'testsOverview')
            );
        }
        
        if ($settings->worksWithStartObjects()) {
            $GLOBALS['DIC']['ilTabs']->addSubTab(
                'start',
                $this->lng->txt('crs_loc_tab_start'),
                $this->ctrl->getLinkTargetByClass('ilcontainerstartobjectsgui', '')
            );
        }
        
        // Member view
        #include_once './Services/Container/classes/class.ilMemberViewGUI.php';
        #ilMemberViewGUI::showMemberViewSwitch($this->getParentObject()->getRefId());
    }
}

<?php

declare(strict_types=1);

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

use ILIAS\Style\Content\Object\ObjectFacade;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory;

/**
 * Class ilLOEditorGUI
 * @author            Stefan Meyer <smeyer.ilias@gmx.de>
 * @ilCtrl_isCalledBy ilLOEditorGUI: ilObjCourseGUI
 * @ilCtrl_Calls      ilLOEditorGUI: ilCourseObjectivesGUI, ilContainerStartObjectsGUI, ilConditionHandlerGUI
 * @ilCtrl_Calls      ilLOEditorGUI: ilLOPageGUI
 */
class ilLOEditorGUI
{
    public const TEST_TYPE_UNDEFINED = 0;
    public const TEST_TYPE_IT = 1;
    public const TEST_TYPE_QT = 2;

    public const TEST_NEW = 1;
    public const TEST_ASSIGN = 2;

    private ilLogger $logger;

    private ilObject $parent_obj;
    private ilLOSettings $settings;
    private ilLanguage $lng;
    private ilCtrlInterface $ctrl;
    private ilTabsGUI $tabs;
    protected ilGlobalTemplateInterface $main_tpl;
    protected ilToolbarGUI $toolbar;
    private ilGlobalTemplateInterface $tpl;
    private ObjectFacade $content_style_domain;
    protected GlobalHttpState $http;
    protected Factory $refinery;

    private int $test_type = self::TEST_TYPE_UNDEFINED;

    public function __construct(ilObject $a_parent_obj)
    {
        global $DIC;

        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->parent_obj = $a_parent_obj;
        $this->settings = ilLOSettings::getInstanceByObjId($this->getParentObject()->getId());

        $cs = $DIC->contentStyle();
        $this->content_style_domain = $cs->domain()->styleForRefId($this->parent_obj->getRefId());

        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->tabs = $DIC->tabs();
        $this->toolbar = $DIC->toolbar();
        $this->logger = $DIC->logger()->crs();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $this->setTabs();
        switch ($next_class) {
            case 'ilcourseobjectivesgui':

                $this->ctrl->setReturn($this, 'returnFromObjectives');
                $this->tabs->clearTargets();
                $this->tabs->setBackTarget(
                    $this->lng->txt('back'),
                    $this->ctrl->getLinkTarget($this, 'listObjectives')
                );

                $reg_gui = new ilCourseObjectivesGUI($this->getParentObject()->getRefId());
                $this->ctrl->forwardCommand($reg_gui);
                break;

            case 'ilcontainerstartobjectsgui':

                $stgui = new ilContainerStartObjectsGUI($this->getParentObject());
                $ret = $this->ctrl->forwardCommand($stgui);

                $this->tabs->activateSubTab('start');
                $this->tabs->removeSubTab('manage');
                break;

            case 'ilconditionhandlergui':

                $this->ctrl->saveParameterByClass('ilconditionhandlergui', 'objective_id');

                $this->tabs->clearTargets();
                $this->tabs->setBackTarget(
                    $this->lng->txt('back'),
                    $this->ctrl->getLinkTarget($this, 'listObjectives')
                );

                $cond = new ilConditionHandlerGUI();
                $cond->setBackButtons(array());
                $cond->setAutomaticValidation(false);
                $cond->setTargetType("lobj");
                $cond->setTargetRefId($this->getParentObject()->getRefId());
                $cond->setTargetId($this->initObjectiveIdFromQuery());

                // objective
                $obj = new ilCourseObjective($this->getParentObject(), $this->initObjectiveIdFromQuery());
                $cond->setTargetTitle($obj->getTitle());
                $this->ctrl->forwardCommand($cond);
                break;

            case 'illopagegui':
                $this->ctrl->saveParameterByClass('illopagegui', 'objective_id');

                $this->tabs->clearTargets();
                $this->tabs->setBackTarget(
                    $this->lng->txt('back'),
                    $this->ctrl->getLinkTarget($this, 'listObjectives')
                );

                $objtv_id = $this->initObjectiveIdFromQuery();

                if (!ilLOPage::_exists('lobj', $objtv_id)) {
                    // doesn't exist -> create new one
                    $new_page_object = new ilLOPage();
                    $new_page_object->setParentId($objtv_id);
                    $new_page_object->setId($objtv_id);
                    $new_page_object->createFromXML();
                    unset($new_page_object);
                }

                $this->ctrl->setReturn($this, 'listObjectives');
                $pgui = new ilLOPageGUI($objtv_id);
                $pgui->setPresentationTitle(ilCourseObjective::lookupObjectiveTitle($objtv_id));

                $pgui->setStyleId($this->content_style_domain->getEffectiveStyleId());

                // #14895
                $this->tpl->setCurrentBlock("ContentStyle");
                $this->tpl->setVariable(
                    "LOCATION_CONTENT_STYLESHEET",
                    ilObjStyleSheet::getContentStylePath($this->content_style_domain->getEffectiveStyleId())
                );
                $this->tpl->parseCurrentBlock();

                $ret = $this->ctrl->forwardCommand($pgui);
                if ($ret) {
                    $this->tpl->setContent($ret);
                }
                break;

            default:
                if (!$cmd) {
                    // get first unaccomplished step
                    $cmd = ilLOEditorStatus::getInstance($this->getParentObject())->getFirstFailedStep();
                }
                $this->$cmd();

                break;
        }
    }

    protected function initObjectiveIdFromQuery(): int
    {
        if ($this->http->wrapper()->query()->has('objective_id')) {
            return $this->http->wrapper()->query()->retrieve(
                'objective_id',
                $this->refinery->kindlyTo()->int()
            );
        }
        return 0;
    }

    protected function initObjectiveIdsFromPost(): array
    {
        if ($this->http->wrapper()->post()->has('objective')) {
            return $this->http->wrapper()->post()->retrieve(
                'objective',
                $this->refinery->kindlyTo()->listOf(
                    $this->refinery->kindlyTo()->int()
                )
            );
        }
        return [];
    }

    protected function initTestTypeFromQuery(): int
    {
        if ($this->http->wrapper()->query()->has('tt')) {
            return $this->http->wrapper()->query()->retrieve(
                'tt',
                $this->refinery->kindlyTo()->int()
            );
        }
        return 0;
    }

    protected function returnFromObjectives(): void
    {
        ilSession::set('objective_mode', ilCourseObjectivesGUI::MODE_UNDEFINED);
        $this->listObjectives();
    }

    public function getParentObject(): ilObject
    {
        return $this->parent_obj;
    }

    public function getSettings(): ilLOSettings
    {
        return $this->settings;
    }

    public function setTestType(int $a_type): void
    {
        $this->test_type = $a_type;
    }

    public function getTestType(): int
    {
        return $this->test_type;
    }

    protected function settings(?ilPropertyFormGUI $form = null): void
    {
        if (!$form instanceof ilPropertyFormGUI) {
            $form = $this->initSettingsForm();
        }
        $this->tabs->activateSubTab('settings');
        $this->tpl->setContent($form->getHTML());
        $this->showStatus(ilLOEditorStatus::SECTION_SETTINGS);
    }

    protected function deleteAssignments(int $a_type): void
    {
        $assignments = ilLOTestAssignments::getInstance($this->getParentObject()->getId());
        foreach ($assignments->getAssignmentsByType($a_type) as $assignment) {
            $assignment->delete();
        }
    }

    protected function updateTestAssignments(ilLOSettings $settings): void
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

    protected function saveSettings(): void
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
                $this->main_tpl->setOnScreenMessage('info', $this->lng->txt('crs_loc_settings_err_qstart'), true);
            }

            $settings->update();
            $this->updateStartObjects();
            $this->updateTestAssignments($settings);

            ilLPStatusWrapper::_refreshStatus($this->getParentObject()->getId());

            $this->main_tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
            $this->ctrl->redirect($this, 'settings');
        }

        // Error
        $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt('err_check_input'));
        $form->setValuesByPost();
        $this->settings($form);
    }

    /**
     * Init settings form
     */
    protected function initSettingsForm(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt('crs_loc_settings_tbl'));

        // initial test
        $type_selector = new ilRadioGroupInputGUI($this->lng->txt('crs_loc_settings_it_type'), 'ittype');
        $type_selector->setRequired(true);
        $type_selector->setValue((string) $this->getSettings()->getInitialTestType());

        $type_ipa = new ilRadioOption(
            $this->lng->txt('crs_loc_settings_type_it_placement_all'),
            (string) ilLOSettings::TYPE_INITIAL_PLACEMENT_ALL
        );
        $type_ipa->setInfo($this->lng->txt('crs_loc_settings_type_it_placement_all_info'));
        $type_selector->addOption($type_ipa);

        $start_ip = new ilCheckboxInputGUI($this->lng->txt('crs_loc_settings_it_start_object'), 'start_ip');
        $start_ip->setValue((string) 1);
        $start_ip->setChecked($this->getSettings()->isInitialTestStart());
        $type_ipa->addSubItem($start_ip);

        $type_ips = new ilRadioOption(
            $this->lng->txt('crs_loc_settings_type_it_placement_sel'),
            (string) ilLOSettings::TYPE_INITIAL_PLACEMENT_SELECTED
        );
        $type_ips->setInfo($this->lng->txt('crs_loc_settings_type_it_placement_sel_info'));
        $type_selector->addOption($type_ips);

        $type_iqa = new ilRadioOption(
            $this->lng->txt('crs_loc_settings_type_it_qualifying_all'),
            (string) ilLOSettings::TYPE_INITIAL_QUALIFYING_ALL
        );
        $type_iqa->setInfo($this->lng->txt('crs_loc_settings_type_it_qualifying_all_info'));
        $type_selector->addOption($type_iqa);

        $start_iq = new ilCheckboxInputGUI($this->lng->txt('crs_loc_settings_it_start_object'), 'start_iq');
        $start_iq->setValue('1');
        $start_iq->setChecked($this->getSettings()->isInitialTestStart());
        $type_iqa->addSubItem($start_iq);

        $type_iqs = new ilRadioOption(
            $this->lng->txt('crs_loc_settings_type_it_qualifying_sel'),
            (string) ilLOSettings::TYPE_INITIAL_QUALIFYING_SELECTED
        );
        $type_iqs->setInfo($this->lng->txt('crs_loc_settings_type_it_qualifying_sel_info'));
        $type_selector->addOption($type_iqs);

        $type_ino = new ilRadioOption(
            $this->lng->txt('crs_loc_settings_type_it_none'),
            (string) ilLOSettings::TYPE_INITIAL_NONE
        );
        $type_ino->setInfo($this->lng->txt('crs_loc_settings_type_it_none_info'));
        $type_selector->addOption($type_ino);

        $form->addItem($type_selector);

        // qualifying test
        $qt_selector = new ilRadioGroupInputGUI($this->lng->txt('crs_loc_settings_qt_all'), 'qttype');
        $qt_selector->setRequired(true);
        $qt_selector->setValue((string) $this->getSettings()->getQualifyingTestType());

        $type_qa = new ilRadioOption(
            $this->lng->txt('crs_loc_settings_type_q_all'),
            (string) ilLOSettings::TYPE_QUALIFYING_ALL
        );
        $type_qa->setInfo($this->lng->txt('crs_loc_settings_type_q_all_info'));
        $qt_selector->addOption($type_qa);

        $start_q = new ilCheckboxInputGUI($this->lng->txt('crs_loc_settings_qt_start_object'), 'start_q');
        $start_q->setValue('1');
        $start_q->setChecked($this->getSettings()->isQualifyingTestStart());
        $type_qa->addSubItem($start_q);

        $passed_mode = new ilRadioGroupInputGUI($this->lng->txt('crs_loc_settings_passed_mode'), 'passed_mode');
        $passed_mode->setValue((string) $this->getSettings()->getPassedObjectiveMode());

        $passed_mode->addOption(
            new ilRadioOption(
                $this->lng->txt('crs_loc_settings_passed_mode_hide'),
                (string) ilLOSettings::HIDE_PASSED_OBJECTIVE_QST
            )
        );
        $passed_mode->addOption(
            new ilRadioOption(
                $this->lng->txt('crs_loc_settings_passed_mode_mark'),
                (string) ilLOSettings::MARK_PASSED_OBJECTIVE_QST
            )
        );
        $type_qa->addSubItem($passed_mode);

        $type_qs = new ilRadioOption(
            $this->lng->txt('crs_loc_settings_type_q_selected'),
            (string) ilLOSettings::TYPE_QUALIFYING_SELECTED
        );
        $type_qs->setInfo($this->lng->txt('crs_loc_settings_type_q_selected_info'));
        $qt_selector->addOption($type_qs);

        $form->addItem($qt_selector);

        // reset results
        $reset = new ilCheckboxInputGUI($this->lng->txt('crs_loc_settings_reset'), 'reset');
        $reset->setValue('1');
        $reset->setChecked($this->getSettings()->isResetResultsEnabled());
        $reset->setOptionTitle($this->lng->txt('crs_loc_settings_reset_enable'));
        $reset->setInfo($this->lng->txt('crs_loc_settings_reset_enable_info'));
        $form->addItem($reset);

        $form->addCommandButton('saveSettings', $this->lng->txt('save'));
        return $form;
    }

    protected function materials(): void
    {
        $this->tabs->activateSubTab('materials');

        $gui = new ilObjectAddNewItemGUI($this->getParentObject()->getRefId());
        $gui->setDisabledObjectTypes(array("itgr"));
        #$gui->setAfterCreationCallback($this->getParentObject()->getRefId());
        $gui->render();

        $obj_table = new ilObjectTableGUI(
            $this,
            'materials',
            $this->getParentObject()->getRefId()
        );
        $obj_table->init();
        $obj_table->setObjects($GLOBALS['DIC']['tree']->getChildIds($this->getParentObject()->getRefId()));
        $obj_table->parse();
        $this->tpl->setContent($obj_table->getHTML());

        $this->showStatus(ilLOEditorStatus::SECTION_MATERIALS);
    }

    protected function testsOverview(): void
    {
        $this->setTestType($this->initTestTypeFromQuery());
        $this->ctrl->setParameter($this, 'tt', $this->getTestType());

        $this->toolbar->setFormAction($this->ctrl->getFormAction($this));
        $this->toolbar->addButton(
            $this->lng->txt('crs_loc_btn_new_assignment'),
            $this->ctrl->getLinkTarget($this, 'testAssignment')
        );

        $settings = ilLOSettings::getInstanceByObjId($this->getParentObject()->getId());
        switch ($this->getTestType()) {
            case ilLOSettings::TYPE_TEST_INITIAL:
                $this->tabs->activateSubTab('itests');
                break;

            case ilLOSettings::TYPE_TEST_QUALIFIED:
                $this->tabs->activateSubTab('qtests');
                break;
        }

        try {
            $table = new ilLOTestAssignmentTableGUI(
                $this,
                'testsOverview',
                $this->getParentObject()->getId(),
                $this->getTestType(),
                ilLOTestAssignmentTableGUI::TYPE_MULTIPLE_ASSIGNMENTS
            );
            $table->init();
            $table->parseMultipleAssignments();
            $this->tpl->setContent($table->getHTML());

            $this->showStatus(
                ($this->getTestType() == ilLOEditorGUI::TEST_TYPE_IT) ?
                    ilLOEditorStatus::SECTION_ITES :
                    ilLOEditorStatus::SECTION_QTEST
            );
        } catch (ilLOInvalidConfigurationException $ex) {
            $this->logger->debug(': Show new assignment screen because of : ' . $ex->getMessage());
            $this->testSettings();
        }
    }

    /**
     * Show test overview
     */
    protected function testOverview(): void
    {
        $this->setTestType($this->initTestTypeFromQuery());
        $this->ctrl->setParameter($this, 'tt', $this->getTestType());

        $settings = ilLOSettings::getInstanceByObjId($this->getParentObject()->getId());
        switch ($this->getTestType()) {
            case ilLOSettings::TYPE_TEST_INITIAL:
                $this->tabs->activateSubTab('itest');
                break;

            case ilLOSettings::TYPE_TEST_QUALIFIED:
                $this->tabs->activateSubTab('qtest');
                break;
        }

        // Check if test is assigned
        if (!$settings->getTestByType($this->getTestType())) {
            $this->testSettings();
            return;
        }

        try {
            $table = new ilLOTestAssignmentTableGUI(
                $this,
                'testOverview',
                $this->getParentObject()->getId(),
                $this->getTestType()
            );
            $table->init();
            $table->parse(ilLOSettings::getInstanceByObjId($this->getParentObject()->getId())->getTestByType($this->getTestType()));
            $this->tpl->setContent($table->getHTML());

            $this->showStatus(
                ($this->getTestType() == ilLOEditorGUI::TEST_TYPE_IT) ?
                    ilLOEditorStatus::SECTION_ITES :
                    ilLOEditorStatus::SECTION_QTEST
            );
        } catch (ilLOInvalidConfigurationException $ex) {
            $this->logger->debug(': Show new assignment screen because of : ' . $ex->getMessage());
            $this->testSettings();
        }
    }

    /**
     * Show delete test confirmation
     */
    protected function confirmDeleteTests(): void
    {
        $this->setTestType($this->initTestTypeFromQuery());
        $this->ctrl->setParameter($this, 'tt', $this->getTestType());

        $settings = ilLOSettings::getInstanceByObjId($this->getParentObject()->getId());
        switch ($this->getTestType()) {
            case ilLOSettings::TYPE_TEST_INITIAL:
                $this->tabs->activateSubTab('itests');
                break;

            case ilLOSettings::TYPE_TEST_QUALIFIED:
                $this->tabs->activateSubTab('qtests');
                break;
        }

        $tests = [];
        if ($this->http->wrapper()->post()->has('tst')) {
            $tests = $this->http->wrapper()->post()->retrieve(
                'tst',
                $this->refinery->kindlyTo()->listOf(
                    $this->refinery->kindlyTo()->int()
                )
            );
        }
        if (!count($tests)) {
            $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'), true);
            $this->ctrl->redirect($this, 'testsOverview');
        }
        $confirm = new ilConfirmationGUI();
        $confirm->setHeaderText($this->lng->txt('crs_loc_confirm_delete_tst'));
        $confirm->setFormAction($this->ctrl->getFormAction($this));
        $confirm->setConfirm($this->lng->txt('crs_loc_delete_assignment'), 'deleteTests');
        $confirm->setCancel($this->lng->txt('cancel'), 'testsOverview');
        foreach ($tests as $assign_id) {
            $assignment = new ilLOTestAssignment($assign_id);

            $obj_id = ilObject::_lookupObjId($assignment->getTestRefId());
            $confirm->addItem('tst[]', $assign_id, ilObject::_lookupTitle($obj_id));
        }

        $this->tpl->setContent($confirm->getHTML());

        $this->showStatus(
            ($this->getTestType() == ilLOSettings::TYPE_TEST_INITIAL) ?
                ilLOEditorStatus::SECTION_ITES :
                ilLOEditorStatus::SECTION_QTEST
        );
    }

    /**
     * Show delete confirmation screen
     */
    protected function confirmDeleteTest(): void
    {
        $this->setTestType($this->initTestTypeFromQuery());
        $this->ctrl->setParameter($this, 'tt', $this->getTestType());

        $settings = ilLOSettings::getInstanceByObjId($this->getParentObject()->getId());
        switch ($this->getTestType()) {
            case ilLOSettings::TYPE_TEST_INITIAL:
                $this->tabs->activateSubTab('itest');
                break;

            case ilLOSettings::TYPE_TEST_QUALIFIED:
                $this->tabs->activateSubTab('qtest');
                break;
        }

        $tests = [];
        if ($this->http->wrapper()->post()->has('tst')) {
            $tests = $this->http->wrapper()->post()->retrieve(
                'tst',
                $this->refinery->kindlyTo()->listOf(
                    $this->refinery->kindlyTo()->int()
                )
            );
        }
        if (count($tests) === 0) {
            $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'), true);
            $this->ctrl->redirect($this, 'testOverview');
        }

        $confirm = new ilConfirmationGUI();
        $confirm->setHeaderText($this->lng->txt('crs_loc_confirm_delete_tst'));
        $confirm->setFormAction($this->ctrl->getFormAction($this));
        $confirm->setConfirm($this->lng->txt('crs_loc_delete_assignment'), 'deleteTest');
        $confirm->setCancel($this->lng->txt('cancel'), 'testOverview');

        foreach ($tests as $tst_id) {
            $obj_id = ilObject::_lookupObjId($tst_id);
            $confirm->addItem('tst[]', $tst_id, ilObject::_lookupTitle($obj_id));
        }
        $this->tpl->setContent($confirm->getHTML());

        $this->showStatus(
            ($this->getTestType() == ilLOEditorGUI::TEST_TYPE_IT) ?
                ilLOEditorStatus::SECTION_ITES :
                ilLOEditorStatus::SECTION_QTEST
        );
    }

    protected function deleteTests(): void
    {
        $this->setTestType($this->initTestTypeFromQuery());
        $this->ctrl->setParameter($this, 'tt', $this->getTestType());

        $settings = ilLOSettings::getInstanceByObjId($this->getParentObject()->getId());
        switch ($this->getTestType()) {
            case ilLOSettings::TYPE_TEST_INITIAL:
                $this->tabs->activateSubTab('itests');
                break;

            case ilLOSettings::TYPE_TEST_QUALIFIED:
                $this->tabs->activateSubTab('qtests');
                break;
        }

        $tests = [];
        if ($this->http->wrapper()->post()->has('tst')) {
            $tests = $this->http->wrapper()->post()->retrieve(
                'tst',
                $this->refinery->kindlyTo()->listOf(
                    $this->refinery->kindlyTo()->int()
                )
            );
        }
        foreach ($tests as $assign_id) {
            $assignment = new ilLOTestAssignment($assign_id);
            $assignment->delete();

            // finally delete start object assignment
            $start = new ilContainerStartObjects(
                $this->getParentObject()->getRefId(),
                $this->getParentObject()->getId()
            );
            $start->deleteItem($assignment->getTestRefId());

            // ... and assigned questions
            ilCourseObjectiveQuestion::deleteTest($assignment->getTestRefId());
        }

        $this->main_tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, 'testsOverview');
    }

    protected function deleteTest(): void
    {
        $this->setTestType($this->initTestTypeFromQuery());
        $this->ctrl->setParameter($this, 'tt', $this->getTestType());

        $settings = ilLOSettings::getInstanceByObjId($this->getParentObject()->getId());
        switch ($this->getTestType()) {
            case ilLOSettings::TYPE_TEST_INITIAL:
                $this->tabs->activateSubTab('itest');
                break;

            case ilLOSettings::TYPE_TEST_QUALIFIED:
                $this->tabs->activateSubTab('qtest');
                break;
        }

        $tests = [];
        if ($this->http->wrapper()->post()->has('tst')) {
            $tests = $this->http->wrapper()->post()->retrieve(
                'tst',
                $this->refinery->kindlyTo()->listOf(
                    $this->refinery->kindlyTo()->int()
                )
            );
        }
        foreach ($tests as $tst_id) {
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
            $start = new ilContainerStartObjects(
                $this->getParentObject()->getRefId(),
                $this->getParentObject()->getId()
            );
            $start->deleteItem($tst_id);

            // ... and assigned questions
            ilCourseObjectiveQuestion::deleteTest($tst_id);
        }

        $this->main_tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, 'testOverview');
    }

    protected function testAssignment(ilPropertyFormGUI $form = null): void
    {
        $this->setTestType($this->initTestTypeFromQuery());
        $this->ctrl->setParameter($this, 'tt', $this->getTestType());

        switch ($this->getTestType()) {
            case ilLOSettings::TYPE_TEST_INITIAL:
                $this->tabs->activateSubTab('itests');
                break;

            case ilLOSettings::TYPE_TEST_QUALIFIED:
                $this->tabs->activateSubTab('qtests');
                break;
        }
        if (!$form instanceof ilPropertyFormGUI) {
            $form_helper = new ilLOTestAssignmentForm($this, $this->getParentObject(), $this->getTestType());
            $form = $form_helper->initForm(true);
        }
        $this->tpl->setContent($form->getHTML());

        $this->showStatus(
            ($this->getTestType() == self::TEST_TYPE_IT) ?
                ilLOEditorStatus::SECTION_ITES :
                ilLOEditorStatus::SECTION_QTEST
        );
    }

    protected function testSettings(ilPropertyFormGUI $form = null): void
    {
        $this->ctrl->setParameter($this, 'tt', $this->initTestTypeFromQuery());
        switch ($this->getTestType()) {
            case ilLOSettings::TYPE_TEST_INITIAL:
                $this->tabs->activateSubTab('itest');
                break;

            case ilLOSettings::TYPE_TEST_QUALIFIED:
                $this->tabs->activateSubTab('qtest');
                break;
        }

        if (!$form instanceof ilPropertyFormGUI) {
            $form_helper = new ilLOTestAssignmentForm($this, $this->getParentObject(), $this->getTestType());
            $form = $form_helper->initForm(false);
        }
        $this->tpl->setContent($form->getHTML());

        $this->showStatus(
            ($this->getTestType() == self::TEST_TYPE_IT) ?
                ilLOEditorStatus::SECTION_ITES :
                ilLOEditorStatus::SECTION_QTEST
        );
    }

    protected function updateStartObjects(): void
    {
        $start = new ilContainerStartObjects(0, $this->getParentObject()->getId());
        $this->getSettings()->updateStartObjects($start);
    }

    protected function saveMultiTestAssignment(): void
    {
        $this->ctrl->setParameter($this, 'tt', $this->initTestTypeFromQuery());
        $this->setTestType($this->initTestTypeFromQuery());

        $settings = ilLOSettings::getInstanceByObjId($this->getParentObject()->getId());

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
                $tst->setQuestionSetType($form->getInput('qtype'));

                $tst->saveToDb();

                $assignment = new ilLOTestAssignment();
                $assignment->setContainerId($this->getParentObject()->getId());
                $assignment->setAssignmentType($this->getTestType());
                $assignment->setObjectiveId($form->getInput('objective'));
                $assignment->setTestRefId($tst->getRefId());
                $assignment->save();
            } else {
                $assignment = new ilLOTestAssignment();
                $assignment->setContainerId($this->getParentObject()->getId());
                $assignment->setAssignmentType($this->getTestType());
                $assignment->setObjectiveId($form->getInput('objective'));
                $assignment->setTestRefId($form->getInput('tst'));
                $assignment->save();

                $tst = new ilObjTest($form->getInput('tst'), true);
                $tst->saveToDb();
            }

            // deassign as objective material
            if ($tst instanceof ilObjTest) {
                $this->updateMaterialAssignments($tst);
            }
            $this->updateStartObjects();

            $this->main_tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'));
            $this->ctrl->redirect($this, 'testsOverview');
        }

        // Error
        $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt('err_check_input'));
        $form->setValuesByPost();
        $this->testAssignment($form);
    }

    protected function updateMaterialAssignments(ilObjTest $test): void
    {
        foreach (ilCourseObjective::_getObjectiveIds($this->getParentObject()->getId()) as $objective_id) {
            $materials = new ilCourseObjectiveMaterials($objective_id);
            foreach ($materials->getMaterials() as $material) {
                if ($material['ref_id'] == $test->getRefId()) {
                    $materials->delete($material['lm_ass_id']);
                }
            }
        }
    }

    protected function saveTest(): void
    {
        $this->ctrl->setParameter($this, 'tt', $this->initTestTypeFromQuery());
        $this->setTestType($this->initTestTypeFromQuery());

        $settings = ilLOSettings::getInstanceByObjId($this->getParentObject()->getId());

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
                $tst->saveToDb();
            }

            // deassign as objective material
            if ($tst instanceof ilObjTest) {
                $this->updateMaterialAssignments($tst);
            }
            $this->updateStartObjects();

            $this->main_tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'));
            $this->ctrl->redirect($this, 'testOverview');
        }

        // Error
        $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt('err_check_input'));
        $form->setValuesByPost();
        $this->testSettings($form);
    }

    protected function listObjectives(): void
    {
        ilSession::set('objective_mode', ilCourseObjectivesGUI::MODE_UNDEFINED);
        $this->tabs->activateSubTab('objectives');

        $objectives = ilCourseObjective::_getObjectiveIds(
            $this->getParentObject()->getId(),
            false
        );

        if ($objectives === []) {
            $this->showObjectiveCreation();
            return;
        }

        $this->toolbar->addButton(
            $this->lng->txt('crs_add_objective'),
            $this->ctrl->getLinkTargetByClass('ilcourseobjectivesgui', "create")
        );

        $table = new ilCourseObjectivesTableGUI($this, $this->getParentObject());
        $table->setTitle($this->lng->txt('crs_objectives'), '', $this->lng->txt('crs_objectives'));
        $table->parse($objectives);
        $this->tpl->setContent($table->getHTML());

        $this->showStatus(ilLOEditorStatus::SECTION_OBJECTIVES);
    }

    protected function showObjectiveCreation(ilPropertyFormGUI $form = null): void
    {
        $this->tabs->activateSubTab('objectives');
        if (!$form instanceof ilPropertyFormGUI) {
            $form = $this->initSimpleObjectiveForm();
        }
        $this->tpl->setContent($form->getHTML());
        $this->showStatus(ilLOEditorStatus::SECTION_OBJECTIVES_NEW);
    }

    protected function initSimpleObjectiveForm(): ilPropertyFormGUI
    {
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

    protected function saveObjectiveCreation(): void
    {
        $form = $this->initSimpleObjectiveForm();
        if ($form->checkInput()) {
            foreach ((array) $form->getInput('objectives') as $title) {
                $obj = new ilCourseObjective($this->getParentObject());
                $obj->setActive(true);
                $obj->setTitle($title);
                $obj->add();
            }
            $this->main_tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
            $this->ctrl->redirect($this, '');
        }

        $form->setValuesByPost();
        $this->tabs->activateSubTab('objectives');
        $this->showStatus(ilLOEditorStatus::SECTION_OBJECTIVES);
        $this->showObjectiveCreation($form);
    }

    protected function saveSorting(): void
    {
        $post_position = $this->http->wrapper()->post()->retrieve(
            'position',
            $this->refinery->kindlyTo()->dictOf(
                $this->refinery->kindlyTo()->int()
            )
        );
        asort($post_position, SORT_NUMERIC);
        $counter = 1;
        foreach ($post_position as $objective_id => $position) {
            $objective = new ilCourseObjective($this->getParentObject(), $objective_id);
            $objective->writePosition($counter++);
        }
        $this->main_tpl->setOnScreenMessage('success', $this->lng->txt('crs_objective_saved_sorting'));
        $this->listObjectives();
    }

    protected function askDeleteObjectives(): void
    {
        $this->tabs->activateSubTab('objectives');

        $confirm = new ilConfirmationGUI();
        $confirm->setFormAction($this->ctrl->getFormAction($this));
        $confirm->setHeaderText($this->lng->txt('crs_delete_objectve_sure'));
        $confirm->setConfirm($this->lng->txt('delete'), 'deleteObjectives');
        $confirm->setCancel($this->lng->txt('cancel'), 'listObjectives');

        $objective_ids = [];
        if ($this->http->wrapper()->post()->has('objective')) {
            $objective_ids = $this->http->wrapper()->post()->retrieve(
                'objective',
                $this->refinery->kindlyTo()->dictOf(
                    $this->refinery->kindlyTo()->int()
                )
            );
        }
        foreach ($objective_ids as $objective_id) {
            $obj = new ilCourseObjective($this->getParentObject(), $objective_id);
            $name = $obj->getTitle();

            $confirm->addItem(
                'objective_ids[]',
                $objective_id,
                $name
            );
        }
        $this->tpl->setContent($confirm->getHTML());
        $this->showStatus(ilLOEditorStatus::SECTION_OBJECTIVES);
    }

    protected function activateObjectives(): void
    {
        $enabled = $this->initObjectiveIdsFromPost();
        $objectives = ilCourseObjective::_getObjectiveIds($this->getParentObject()->getId(), false);
        foreach ($objectives as $objective_id) {
            $objective = new ilCourseObjective($this->getParentObject(), $objective_id);
            if (in_array($objective_id, $enabled)) {
                $objective->setActive(true);
                $objective->update();
            }
        }
        ilLPStatusWrapper::_refreshStatus($this->getParentObject()->getId());
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, 'listObjectives');
    }

    protected function deactivateObjectives(): void
    {
        $disabled = $this->initObjectiveIdsFromPost();
        $objectives = ilCourseObjective::_getObjectiveIds($this->getParentObject()->getId(), false);
        foreach ($objectives as $objective_id) {
            $objective = new ilCourseObjective($this->getParentObject(), $objective_id);
            if (in_array($objective_id, $disabled)) {
                $objective->setActive(false);
                $objective->update();
            }
        }
        ilLPStatusWrapper::_refreshStatus($this->getParentObject()->getId());
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, 'listObjectives');
    }

    protected function deleteObjectives(): void
    {
        $objective_ids = [];
        if ($this->http->wrapper()->post()->has('objective_ids')) {
            $objective_ids = $this->http->wrapper()->post()->retrieve(
                'objective_ids',
                $this->refinery->kindlyTo()->dictOf(
                    $this->refinery->kindlyTo()->int()
                )
            );
        }
        foreach ($objective_ids as $objective_id) {
            $objective_obj = new ilCourseObjective($this->getParentObject(), $objective_id);
            $objective_obj->delete();
        }
        ilLPStatusWrapper::_refreshStatus($this->getParentObject()->getId());
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('crs_objectives_deleted'), true);
        $this->ctrl->redirect($this, 'listObjectives');
    }

    protected function showStatus(int $a_section): void
    {
        $status = new ilLOEditorStatus($this->getParentObject());
        $status->setSection($a_section);
        $status->setCmdClass($this);
        $this->tpl->setRightContent($status->getHTML());
    }

    protected function setTabs(string $a_section = ''): void
    {
        // objective settings
        $this->tabs->addSubTab(
            'settings',
            $this->lng->txt('settings'),
            $this->ctrl->getLinkTarget($this, 'settings')
        );
        // learning objectives
        $this->tabs->addSubTab(
            'objectives',
            $this->lng->txt('crs_loc_tab_objectives'),
            $this->ctrl->getLinkTarget($this, 'listObjectives')
        );
        // materials
        // tests
        $settings = ilLOSettings::getInstanceByObjId($this->getParentObject()->getId());
        if ($settings->worksWithInitialTest()) {
            if (
                $settings->getInitialTestType() == ilLOSettings::TYPE_INITIAL_PLACEMENT_ALL ||
                $settings->getInitialTestType() == ilLOSettings::TYPE_INITIAL_QUALIFYING_ALL
            ) {
                $this->ctrl->setParameter($this, 'tt', ilLOSettings::TYPE_TEST_INITIAL);
                $this->tabs->addSubTab(
                    'itest',
                    $this->lng->txt('crs_loc_tab_itest'),
                    $this->ctrl->getLinkTarget($this, 'testOverview')
                );
            } else {
                $this->ctrl->setParameter($this, 'tt', ilLOSettings::TYPE_TEST_INITIAL);
                $this->tabs->addSubTab(
                    'itests',
                    $this->lng->txt('crs_loc_tab_itests'),
                    $this->ctrl->getLinkTarget($this, 'testsOverview')
                );
            }
        }

        if ($settings->getQualifyingTestType() == ilLOSettings::TYPE_QUALIFYING_ALL) {
            $this->ctrl->setParameter($this, 'tt', ilLOSettings::TYPE_TEST_QUALIFIED);
            $this->tabs->addSubTab(
                'qtest',
                $this->lng->txt('crs_loc_tab_qtest'),
                $this->ctrl->getLinkTarget($this, 'testOverview')
            );
        } else {
            $this->ctrl->setParameter($this, 'tt', ilLOSettings::TYPE_TEST_QUALIFIED);
            $this->tabs->addSubTab(
                'qtests',
                $this->lng->txt('crs_loc_tab_qtests'),
                $this->ctrl->getLinkTarget($this, 'testsOverview')
            );
        }

        if ($settings->worksWithStartObjects()) {
            $this->tabs->addSubTab(
                'start',
                $this->lng->txt('crs_loc_tab_start'),
                $this->ctrl->getLinkTargetByClass('ilcontainerstartobjectsgui', '')
            );
        }

        // Member view
        #ilMemberViewGUI::showMemberViewSwitch($this->getParentObject()->getRefId());
    }
}

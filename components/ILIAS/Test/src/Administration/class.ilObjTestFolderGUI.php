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

declare(strict_types=1);

use ILIAS\Test\TestDIC;
use ILIAS\Test\RequestDataCollector;
use ILIAS\Test\Logging\TestLogViewer;
use ILIAS\Test\Logging\LogTable;

use ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository;

use ILIAS\Data\Factory as DataFactory;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\Component\Input\Container\Form\Form;

/**
 * @author Helmut Schottmüller <hschottm@gmx.de>
 * @ilCtrl_Calls ilObjTestFolderGUI: ilPermissionGUI, ilGlobalUnitConfigurationGUI
 */
class ilObjTestFolderGUI extends ilObjectGUI
{
    private const SHOW_LOGS_CMD = 'logs';

    private RequestDataCollector $testrequest;
    private TestLogViewer $log_viewer;

    private GeneralQuestionPropertiesRepository $questionrepository;

    private DataFactory $data_factory;

    public function __construct(
        $a_data,
        int $a_id = 0,
        bool $a_call_by_reference = true,
        bool $a_prepare_output = true
    ) {
        global $DIC;
        $rbacsystem = $DIC['rbacsystem'];
        $this->data_factory = new DataFactory();

        $local_dic = TestDIC::dic();
        $this->testrequest = $local_dic['request_data_collector'];
        $this->log_viewer = $local_dic['logging.viewer'];
        $this->questionrepository = $local_dic['question.general_properties.repository'];

        $this->type = 'assf';

        parent::__construct($a_data, $a_id, $a_call_by_reference, false);

        if (!$rbacsystem->checkAccess('read', $this->object->getRefId())) {
            $this->ilias->raiseError($this->lng->txt("msg_no_perm_read_assf"), $this->ilias->error_obj->WARNING);
        }

        $this->lng->loadLanguageModule('assessment');
    }

    private function getTestFolder(): ilObjTestFolder
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->object;
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        $this->prepareOutput();

        switch ($next_class) {
            case 'ilpermissiongui':
                $this->tabs_gui->activateTab('perm_settings');
                $perm_gui = new \ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;
            case 'ilglobalunitconfigurationgui':
                if (!$this->rbac_system->checkAccess('visible,read', $this->getTestFolder()->getRefId())) {
                    $this->ilias->raiseError($this->lng->txt('permission_denied'), $this->ilias->error_obj->WARNING);
                }

                $this->tabs_gui->setTabActive('units');

                $gui = new \ilGlobalUnitConfigurationGUI(
                    new \ilUnitConfigurationRepository(0)
                );
                $this->ctrl->forwardCommand($gui);
                break;

            default:
                if ($cmd === null || $cmd === "" || $cmd === "view") {
                    $cmd = "settings";
                }
                $cmd .= "Object";
                $this->$cmd();

                break;
        }
    }

    public function settingsObject(ilPropertyFormGUI $form = null): void
    {
        $this->tabs_gui->setTabActive('settings');

        if ($form === null) {
            $form = $this->buildSettingsForm();
        }

        $this->tpl->setVariable("ADM_CONTENT", $form->getHTML());
    }

    private function buildSettingsForm(): ilPropertyFormGUI
    {
        $form = new \ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTableWidth("100%");
        $form->setId("settings");

        $header = new \ilFormSectionHeaderGUI();
        $header->setTitle($this->lng->txt('settings'));
        $form->addItem($header);

        // question process locking behaviour (e.g. on saving users working data)
        $chb = new \ilCheckboxInputGUI($this->lng->txt('ass_process_lock'), 'ass_process_lock');
        $chb->setChecked($this->getTestFolder()->getAssessmentProcessLockMode() !== ilObjTestFolder::ASS_PROC_LOCK_MODE_NONE);
        $chb->setInfo($this->lng->txt('ass_process_lock_desc'));
        $form->addItem($chb);
        $rg = new \ilRadioGroupInputGUI($this->lng->txt('ass_process_lock_mode'), 'ass_process_lock_mode');
        $rg->setRequired(true);
        $opt = new \ilRadioOption(
            $this->lng->txt('ass_process_lock_mode_file'),
            ilObjTestFolder::ASS_PROC_LOCK_MODE_FILE
        );
        $opt->setInfo($this->lng->txt('ass_process_lock_mode_file_desc'));
        $rg->addOption($opt);
        $opt = new \ilRadioOption(
            $this->lng->txt('ass_process_lock_mode_db'),
            ilObjTestFolder::ASS_PROC_LOCK_MODE_DB
        );
        $opt->setInfo($this->lng->txt('ass_process_lock_mode_db_desc'));
        $rg->addOption($opt);
        if ($this->getTestFolder()->getAssessmentProcessLockMode() !== ilObjTestFolder::ASS_PROC_LOCK_MODE_NONE) {
            $rg->setValue($this->getTestFolder()->getAssessmentProcessLockMode());
        }
        $chb->addSubItem($rg);

        $assessmentSetting = new \ilSetting('assessment');
        $imap_line_color = $assessmentSetting->get('imap_line_color');
        if ($this->testrequest->isset('imap_line_color')) {
            $imap_line_color = $this->testrequest->strVal('imap_line_color');
        }
        if ($imap_line_color == '') {
            $imap_line_color = 'FF0000';
        }

        $linepicker = new \ilColorPickerInputGUI($this->lng->txt('imap_line_color'), 'imap_line_color');
        $linepicker->setValue($imap_line_color);
        $form->addItem($linepicker);

        $user_criteria = $assessmentSetting->get('user_criteria');
        if ($this->testrequest->isset('user_criteria')) {
            $user_criteria = $this->testrequest->strVal('user_criteria');
        }
        $userCriteria = new \ilSelectInputGUI($this->lng->txt('user_criteria'), 'user_criteria');
        $userCriteria->setInfo($this->lng->txt('user_criteria_desc'));
        $userCriteria->setRequired(true);

        $fields = ['usr_id', 'login', 'email', 'matriculation', 'ext_account'];
        $usr_fields = [];
        foreach ($fields as $field) {
            $usr_fields[$field] = $field;
        }
        $userCriteria->setOptions($usr_fields);
        $userCriteria->setValue($user_criteria);
        $form->addItem($userCriteria);

        $numRequiredAnswers = new \ilNumberInputGUI(
            $this->lng->txt('tst_skill_triggerings_num_req_answers'),
            'num_req_answers'
        );
        $numRequiredAnswers->setInfo($this->lng->txt('tst_skill_triggerings_num_req_answers_desc'));
        $numRequiredAnswers->setSize(4);
        $numRequiredAnswers->allowDecimals(false);
        $numRequiredAnswers->setMinValue(1);
        $numRequiredAnswers->setMinvalueShouldBeGreater(false);
        $numRequiredAnswers->setValue($this->getTestFolder()->getSkillTriggeringNumAnswersBarrier());
        $form->addItem($numRequiredAnswers);

        $ceeqwh = new \ilCheckboxInputGUI($this->lng->txt('export_essay_qst_with_html'), 'export_essay_qst_with_html');
        $ceeqwh->setChecked($this->getTestFolder()->getExportEssayQuestionsWithHtml());
        $ceeqwh->setInfo($this->lng->txt('export_essay_qst_with_html_desc'));
        $form->addItem($ceeqwh);

        // question settings
        $header = new \ilFormSectionHeaderGUI();
        $header->setTitle($this->lng->txt("assf_questiontypes"));
        $form->addItem($header);

        // available question types
        $allowed = new \ilCheckboxGroupInputGUI(
            $this->lng->txt('assf_allowed_questiontypes'),
            "chb_allowed_questiontypes"
        );
        $questiontypes = ilObjQuestionPool::_getQuestionTypes(true);
        $forbidden_types = ilObjTestFolder::_getForbiddenQuestionTypes();
        $allowedtypes = [];
        foreach ($questiontypes as $qt) {
            if (!in_array($qt['question_type_id'], $forbidden_types)) {
                $allowedtypes[] = $qt['question_type_id'];
            }
        }
        $allowed->setValue($allowedtypes);
        foreach ($questiontypes as $type_name => $question_type) {
            $allowed->addOption(new \ilCheckboxOption($type_name, (string) $question_type["question_type_id"]));
        }
        $allowed->setInfo($this->lng->txt('assf_allowed_questiontypes_desc'));
        $form->addItem($allowed);

        // manual scoring
        $manual = new \ilCheckboxGroupInputGUI(
            $this->lng->txt('activate_manual_scoring'),
            'chb_manual_scoring'
        );
        $manscoring = ilObjTestFolder::_getManualScoring();
        $manual->setValue($manscoring);
        foreach ($questiontypes as $type_name => $question_type) {
            $manual->addOption(new \ilCheckboxOption($type_name, (string) $question_type["question_type_id"]));
        }
        $manual->setInfo($this->lng->txt('activate_manual_scoring_desc'));
        $form->addItem($manual);

        // scoring adjustment active
        $scoring_activation = new \ilCheckboxInputGUI(
            $this->lng->txt('assessment_scoring_adjust'),
            'chb_scoring_adjust'
        );
        $scoring_activation->setChecked($this->getTestFolder()->getScoringAdjustmentEnabled());
        $scoring_activation->setInfo($this->lng->txt('assessment_scoring_adjust_desc'));
        $form->addItem($scoring_activation);

        // scoring adjustment
        $scoring = new \ilCheckboxGroupInputGUI(
            $this->lng->txt('assessment_log_scoring_adjustment_activate'),
            "chb_scoring_adjustment"
        );
        $scoring_active = $this->getTestFolder()->getScoringAdjustableQuestions();
        $scoring->setValue($scoring_active);

        foreach ($this->getTestFolder()->fetchScoringAdjustableTypes($questiontypes) as $type_name => $question_type) {
            $scoring->addOption(
                new \ilCheckboxOption($type_name, (string) $question_type["question_type_id"])
            );
        }
        $scoring->setInfo($this->lng->txt('assessment_log_scoring_adjustment_desc'));
        $form->addItem($scoring);

        if ($this->access->checkAccess("write", "", $this->getTestFolder()->getRefId())) {
            $form->addCommandButton("saveSettings", $this->lng->txt("save"));
        }

        return $form;
    }

    /**
     * Save Assessment settings
     */
    public function saveSettingsObject(): void
    {
        if (!$this->access->checkAccess("write", "", $this->getTestFolder()->getRefId())) {
            $this->ctrl->redirect($this, 'settings');
        }

        $form = $this->buildSettingsForm();
        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->settingsObject($form);
            return;
        }

        $this->getTestFolder()->setSkillTriggeringNumAnswersBarrier((int) $form->getInput('num_req_answers'));
        $this->getTestFolder()->setExportEssayQuestionsWithHtml(
            (bool) ($form->getInput('export_essay_qst_with_html') ?? '0')
        );
        $this->getTestFolder()->_setManualScoring($form->getInput('chb_manual_scoring'));
        $question_types = ilObjQuestionPool::_getQuestionTypes(true);
        $forbidden_types = [];
        foreach ($question_types as $name => $row) {
            if (!$form->getItemByPostVar('chb_allowed_questiontypes') ||
                !in_array($row["question_type_id"], $form->getInput('chb_allowed_questiontypes'))) {
                $forbidden_types[] = (int) $row["question_type_id"];
            }
        }
        $this->getTestFolder()->_setForbiddenQuestionTypes($forbidden_types);
        $this->getTestFolder()->setScoringAdjustmentEnabled(
            (bool) ($form->getInput('chb_scoring_adjust') ?? '0')
        );
        $scoring_types = [];
        foreach ($question_types as $name => $row) {
            if ($form->getItemByPostVar('chb_scoring_adjustment') &&
                in_array($row["question_type_id"], $form->getInput('chb_scoring_adjustment'))) {
                $scoring_types[] = $row["question_type_id"];
            }
        }
        $this->getTestFolder()->setScoringAdjustableQuestions($scoring_types);
        if (!$form->getInput('ass_process_lock')) {
            $this->getTestFolder()->setAssessmentProcessLockMode(ilObjTestFolder::ASS_PROC_LOCK_MODE_NONE);
        } elseif (in_array(
            $form->getInput('ass_process_lock_mode'),
            ilObjTestFolder::getValidAssessmentProcessLockModes(),
            true
        )) {
            $this->getTestFolder()->setAssessmentProcessLockMode($form->getInput('ass_process_lock_mode'));
        }

        $assessmentSetting = new \ilSetting('assessment');
        $assessmentSetting->set('use_javascript', '1');
        if (strlen($form->getInput('imap_line_color') ?? '') === 6) {
            $assessmentSetting->set('imap_line_color', ilUtil::stripSlashes($form->getInput('imap_line_color')));
        }
        $assessmentSetting->set('user_criteria', ilUtil::stripSlashes($form->getInput('user_criteria')));

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);

        $this->ctrl->redirect($this, 'settings');
    }

    public function exportLegacyLogsObject(): void
    {
        $csv_output = $this->getTestFolder()->getTestLogViewer()->getLegacyLogExportForObjId();

        ilUtil::deliverData(
            $csv_output,
            'legacy_logs.csv'
        );
    }

    public function logsObject(): void
    {
        $this->tabs_gui->activateTab('logs');
        $this->toolbar->addComponent(
            $this->ui_factory->button()->standard(
                $this->lng->txt('export_legacy_logs'),
                $this->ctrl->getLinkTargetByClass(self::class, 'exportLegacyLogs')
            )
        );
        $here_uri = $this->data_factory->uri(ILIAS_HTTP_PATH
            . '/' . $this->ctrl->getLinkTargetByClass(self::class, self::SHOW_LOGS_CMD));
        list($url_builder, $action_parameter_token, $row_id_token) = (new URLBuilder($here_uri))->acquireParameters(
            LogTable::QUERY_PARAMETER_NAME_SPACE,
            LogTable::ACTION_TOKEN_STRING,
            LogTable::ENTRY_TOKEN_STRING
        );

        if ($this->request_wrapper->has($action_parameter_token->getName())) {
            $this->object->getTestLogViewer()->executeLogTableAction(
                $url_builder,
                $action_parameter_token,
                $row_id_token
            );
        }

        $table_gui = $this->log_viewer->getLogTable(
            $url_builder,
            $action_parameter_token,
            $row_id_token
        );
        $this->tpl->setVariable('ADM_CONTENT', $this->ui_renderer->render($table_gui));
    }

    public function getAdminTabs(): void
    {
        $this->getTabs();
    }

    public function getLogdataSubtabs(): void
    {
        $this->tabs_gui->addSubTabTarget(
            'settings',
            $this->ctrl->getLinkTarget($this, 'showLogSettings'),
            ['saveLogSettings', 'showLogSettings'],
            ''
        );

        // log output
        $this->tabs_gui->addSubTabTarget(
            'logs_output',
            $this->ctrl->getLinkTargetByClass(self::class, self::SHOW_LOGS_CMD),
            [self::SHOW_LOGS_CMD],
            ''
        );
    }

    protected function getTabs(): void
    {
        if (in_array($this->ctrl->getCmd(), ['saveLogSettings', 'showLogSettings', self::SHOW_LOGS_CMD])) {
            $this->getLogdataSubtabs();
        }

        if ($this->rbac_system->checkAccess('visible,read', $this->getTestFolder()->getRefId())) {
            $this->tabs_gui->addTarget(
                'settings',
                $this->ctrl->getLinkTarget($this, 'settings'),
                ['settings', '', 'view'],
                '',
                ''
            );

            $this->tabs_gui->addTarget(
                "logs",
                $this->ctrl->getLinkTarget($this, "showLogSettings"),
                ['saveLogSettings', 'showLogSettings', self::SHOW_LOGS_CMD, 'showLog', 'exportLog', 'logAdmin', 'deleteLog'],
                '',
                ''
            );

            $this->tabs_gui->addTarget(
                'units',
                $this->ctrl->getLinkTargetByClass('ilGlobalUnitConfigurationGUI', ''),
                '',
                'ilglobalunitconfigurationgui'
            );
        }

        if ($this->rbac_system->checkAccess('edit_permission', $this->getTestFolder()->getRefId())) {
            $this->tabs_gui->addTarget(
                "perm_settings",
                $this->ctrl->getLinkTargetByClass([get_class($this), 'ilpermissiongui'], "perm"),
                ["perm", "info", "owner"],
                'ilpermissiongui'
            );
        }
    }

    /**
     * @param ilPropertyFormGUI $form
     */
    protected function showLogSettingsObject(Form $form = null): void
    {
        $this->tabs_gui->activateTab('logs');

        if ($form === null) {
            $form = $this->buildLogSettingsForm();
        }

        $this->tpl->setContent($this->ui_renderer->render($form));
    }

    /**
     *
     */
    protected function saveLogSettingsObject(): void
    {
        if (!$this->access->checkAccess('write', '', $this->getTestFolder()->getRefId())) {
            $this->ilias->raiseError($this->lng->txt("permission_denied"), $this->ilias->error_obj->WARNING);
        }

        $form = $this->buildLogSettingsForm()->withRequest($this->request);
        $data = $form->getData();
        if ($data === null) {
            $this->showLogSettingsObject($form);
        }

        $this->getTestFolder()->getGlobalSettingsRepository()
            ->storeLoggingSettings($data['logging']);

        $this->showLogSettingsObject($form);
    }

    protected function buildLogSettingsForm(): Form
    {
        $inputs = $this->getTestFolder()->getGlobalSettingsRepository()->getLoggingSettings()->toForm(
            $this->ui_factory,
            $this->refinery,
            $this->lng
        );

        return $this->ui_factory->input()->container()->form()->standard(
            $this->ctrl->getFormAction($this, 'saveLogSettings'),
            $inputs
        );
    }
}

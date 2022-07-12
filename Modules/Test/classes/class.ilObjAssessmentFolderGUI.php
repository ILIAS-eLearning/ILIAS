<?php declare(strict_types=1);

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
 * Class ilObjAssessmentFolderGUI
 * @author Helmut Schottmüller <hschottm@gmx.de>
 * @ilCtrl_Calls ilObjAssessmentFolderGUI: ilPermissionGUI, ilSettingsTemplateGUI, ilGlobalUnitConfigurationGUI
 */
class ilObjAssessmentFolderGUI extends ilObjectGUI
{
    protected \ILIAS\Test\InternalRequestService $testrequest;

    public function __construct($a_data, int $a_id = 0, bool $a_call_by_reference = true, bool $a_prepare_output = true)
    {
        global $DIC;
        $rbacsystem = $DIC['rbacsystem'];
        $this->testrequest = $DIC->test()->internal()->request();
        $this->type = "assf";
        parent::__construct($a_data, $a_id, $a_call_by_reference, false);

        if (!$rbacsystem->checkAccess('read', $this->object->getRefId())) {
            $this->ilias->raiseError($this->lng->txt("msg_no_perm_read_assf"), $this->ilias->error_obj->WARNING);
        }

        $this->lng->loadLanguageModule('assessment');
    }
    
    private function getAssessmentFolder() : ilObjAssessmentFolder
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->object;
    }

    public function executeCommand() : void
    {
        /**
         * @var $rbacsystem ilRbacSystem
         * @var $ilTabs     ilTabsGUI
         */
        global $DIC;
        $rbacsystem = $DIC['rbacsystem'];
        $ilTabs = $DIC['ilTabs'];

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        $this->prepareOutput();

        switch ($next_class) {
            case 'ilpermissiongui':
                $ilTabs->activateTab('perm_settings');
                $perm_gui = new ilPermissionGUI($this);
                $ret = $this->ctrl->forwardCommand($perm_gui);
                break;

            case 'ilsettingstemplategui':
                $this->forwardToSettingsTemplateGUI();
                break;

            case 'ilglobalunitconfigurationgui':
                if (!$rbacsystem->checkAccess('visible,read', $this->getAssessmentFolder()->getRefId())) {
                    $this->ilias->raiseError($this->lng->txt('permission_denied'), $this->ilias->error_obj->WARNING);
                }

                $ilTabs->setTabActive('units');

                $gui = new ilGlobalUnitConfigurationGUI(
                    new ilUnitConfigurationRepository(0)
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

    public function settingsObject(ilPropertyFormGUI $form = null) : void
    {
        global $DIC;
        $ilTabs = $DIC['ilTabs'];

        $ilTabs->setTabActive('settings');

        if ($form === null) {
            $form = $this->buildSettingsForm();
        }

        $this->tpl->setVariable("ADM_CONTENT", $form->getHTML());
    }

    private function buildSettingsForm() : ilPropertyFormGUI
    {
        /**
         * @var $ilAccess ilAccessHandler
         */
        global $DIC;
        $ilAccess = $DIC['ilAccess'];

        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTableWidth("100%");
        $form->setId("settings");

        $header = new ilFormSectionHeaderGUI();
        $header->setTitle($this->lng->txt('settings'));
        $form->addItem($header);

        // question process locking behaviour (e.g. on saving users working data)
        $chb = new ilCheckboxInputGUI($this->lng->txt('ass_process_lock'), 'ass_process_lock');
        $chb->setChecked($this->getAssessmentFolder()->getAssessmentProcessLockMode() !== ilObjAssessmentFolder::ASS_PROC_LOCK_MODE_NONE);
        $chb->setInfo($this->lng->txt('ass_process_lock_desc'));
        $form->addItem($chb);
        $rg = new ilRadioGroupInputGUI($this->lng->txt('ass_process_lock_mode'), 'ass_process_lock_mode');
        $rg->setRequired(true);
        $opt = new ilRadioOption(
            $this->lng->txt('ass_process_lock_mode_file'),
            ilObjAssessmentFolder::ASS_PROC_LOCK_MODE_FILE
        );
        $opt->setInfo($this->lng->txt('ass_process_lock_mode_file_desc'));
        $rg->addOption($opt);
        $opt = new ilRadioOption(
            $this->lng->txt('ass_process_lock_mode_db'),
            ilObjAssessmentFolder::ASS_PROC_LOCK_MODE_DB
        );
        $opt->setInfo($this->lng->txt('ass_process_lock_mode_db_desc'));
        $rg->addOption($opt);
        if ($this->getAssessmentFolder()->getAssessmentProcessLockMode() !== ilObjAssessmentFolder::ASS_PROC_LOCK_MODE_NONE) {
            $rg->setValue($this->getAssessmentFolder()->getAssessmentProcessLockMode());
        }
        $chb->addSubItem($rg);

        $assessmentSetting = new ilSetting('assessment');
        $imap_line_color = $assessmentSetting->get('imap_line_color');
        if ($this->testrequest->isset('imap_line_color')) {
            $imap_line_color = $this->testrequest->strVal('imap_line_color');
        }
        if ($imap_line_color == '') {
            $imap_line_color = 'FF0000';
        }

        $linepicker = new ilColorPickerInputGUI($this->lng->txt('assessment_imap_line_color'), 'imap_line_color');
        $linepicker->setValue($imap_line_color);
        $form->addItem($linepicker);

        $user_criteria = $assessmentSetting->get('user_criteria');
        if ($this->testrequest->isset('user_criteria')) {
            $user_criteria = $this->testrequest->strVal('user_criteria');
        }
        $userCriteria = new ilSelectInputGUI($this->lng->txt('user_criteria'), 'user_criteria');
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

        $numRequiredAnswers = new ilNumberInputGUI(
            $this->lng->txt('tst_skill_triggerings_num_req_answers'),
            'num_req_answers'
        );
        $numRequiredAnswers->setInfo($this->lng->txt('tst_skill_triggerings_num_req_answers_desc'));
        $numRequiredAnswers->setSize(4);
        $numRequiredAnswers->allowDecimals(false);
        $numRequiredAnswers->setMinValue(1);
        $numRequiredAnswers->setMinvalueShouldBeGreater(false);
        $numRequiredAnswers->setValue((string) $this->getAssessmentFolder()->getSkillTriggeringNumAnswersBarrier());
        $form->addItem($numRequiredAnswers);

        $ceeqwh = new ilCheckboxInputGUI($this->lng->txt('export_essay_qst_with_html'), 'export_essay_qst_with_html');
        $ceeqwh->setChecked($this->getAssessmentFolder()->getExportEssayQuestionsWithHtml());
        $ceeqwh->setInfo($this->lng->txt('export_essay_qst_with_html_desc'));
        $form->addItem($ceeqwh);

        // question settings
        $header = new ilFormSectionHeaderGUI();
        $header->setTitle($this->lng->txt("assf_questiontypes"));
        $form->addItem($header);

        // available question types
        $allowed = new ilCheckboxGroupInputGUI(
            $this->lng->txt('assf_allowed_questiontypes'),
            "chb_allowed_questiontypes"
        );
        $questiontypes = ilObjQuestionPool::_getQuestionTypes(true);
        $forbidden_types = ilObjAssessmentFolder::_getForbiddenQuestionTypes();
        $allowedtypes = [];
        foreach ($questiontypes as $qt) {
            if (!in_array($qt['question_type_id'], $forbidden_types)) {
                $allowedtypes[] = $qt['question_type_id'];
            }
        }
        $allowed->setValue($allowedtypes);
        foreach ($questiontypes as $type_name => $qtype) {
            $allowed->addOption(new ilCheckboxOption($type_name, $qtype["question_type_id"]));
        }
        $allowed->setInfo($this->lng->txt('assf_allowed_questiontypes_desc'));
        $form->addItem($allowed);

        // manual scoring
        $manual = new ilCheckboxGroupInputGUI(
            $this->lng->txt('assessment_log_manual_scoring_activate'),
            "chb_manual_scoring"
        );
        $manscoring = ilObjAssessmentFolder::_getManualScoring();
        $manual->setValue($manscoring);
        foreach ($questiontypes as $type_name => $qtype) {
            $manual->addOption(new ilCheckboxOption($type_name, (string) $qtype["question_type_id"]));
        }
        $manual->setInfo($this->lng->txt('assessment_log_manual_scoring_desc'));
        $form->addItem($manual);

        // scoring adjustment active
        $scoring_activation = new ilCheckboxInputGUI(
            $this->lng->txt('assessment_scoring_adjust'),
            'chb_scoring_adjust'
        );
        $scoring_activation->setChecked($this->getAssessmentFolder()->getScoringAdjustmentEnabled());
        $scoring_activation->setInfo($this->lng->txt('assessment_scoring_adjust_desc'));
        $form->addItem($scoring_activation);

        // scoring adjustment
        $scoring = new ilCheckboxGroupInputGUI(
            $this->lng->txt('assessment_log_scoring_adjustment_activate'),
            "chb_scoring_adjustment"
        );
        $scoring_active = $this->getAssessmentFolder()->getScoringAdjustableQuestions();
        $scoring->setValue($scoring_active);

        foreach ($this->getAssessmentFolder()->fetchScoringAdjustableTypes($questiontypes) as $type_name => $qtype) {
            $scoring->addOption(
                new ilCheckboxOption($type_name, (string) $qtype["question_type_id"])
            );
        }
        $scoring->setInfo($this->lng->txt('assessment_log_scoring_adjustment_desc'));
        $form->addItem($scoring);

        if ($ilAccess->checkAccess("write", "", $this->getAssessmentFolder()->getRefId())) {
            $form->addCommandButton("saveSettings", $this->lng->txt("save"));
        }

        return $form;
    }

    /**
     * Save Assessment settings
     */
    public function saveSettingsObject() : void
    {
        global $DIC;
        $ilAccess = $DIC['ilAccess'];
        if (!$ilAccess->checkAccess("write", "", $this->getAssessmentFolder()->getRefId())) {
            $this->ctrl->redirect($this, 'settings');
        }

        $form = $this->buildSettingsForm();
        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->settingsObject($form);
            return;
        }

        $this->getAssessmentFolder()->setSkillTriggeringNumAnswersBarrier((int) $_POST['num_req_answers']);
        $this->getAssessmentFolder()->setExportEssayQuestionsWithHtml((bool) $_POST["export_essay_qst_with_html"]);
        $this->getAssessmentFolder()->_setManualScoring($_POST["chb_manual_scoring"]);
        $questiontypes = ilObjQuestionPool::_getQuestionTypes(true);
        $forbidden_types = [];
        foreach ($questiontypes as $name => $row) {
            if (!in_array($row["question_type_id"], $_POST["chb_allowed_questiontypes"])) {
                $forbidden_types[] = (int) $row["question_type_id"];
            }
        }
        $this->getAssessmentFolder()->_setForbiddenQuestionTypes($forbidden_types);

        $this->getAssessmentFolder()->setScoringAdjustmentEnabled((bool) $_POST['chb_scoring_adjust']);
        $scoring_types = [];
        foreach ($questiontypes as $name => $row) {
            if (in_array($row["question_type_id"], (array) $_POST["chb_scoring_adjustment"])) {
                $scoring_types[] = $row["question_type_id"];
            }
        }
        $this->getAssessmentFolder()->setScoringAdjustableQuestions($scoring_types);

        if (!$_POST['ass_process_lock']) {
            $this->getAssessmentFolder()->setAssessmentProcessLockMode(ilObjAssessmentFolder::ASS_PROC_LOCK_MODE_NONE);
        } elseif (in_array(
            $_POST['ass_process_lock_mode'],
            ilObjAssessmentFolder::getValidAssessmentProcessLockModes(),
            true
        )) {
            $this->getAssessmentFolder()->setAssessmentProcessLockMode($_POST['ass_process_lock_mode']);
        }

        $assessmentSetting = new ilSetting('assessment');
        $assessmentSetting->set('use_javascript', '1');
        if (strlen($_POST['imap_line_color']) == 6) {
            $assessmentSetting->set('imap_line_color', ilUtil::stripSlashes($_POST['imap_line_color']));
        }
        $assessmentSetting->set('user_criteria', ilUtil::stripSlashes($_POST['user_criteria']));

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);

        $this->ctrl->redirect($this, 'settings');
    }

    /**
     * Called when the a log should be shown
     */
    public function showLogObject() : void
    {
        $form = $this->getLogDataOutputForm();
        $form->checkInput();

        $form->setValuesByPost();
        $this->logsObject($form);
    }

    /**
     * Called when the a log should be exported
     */
    public function exportLogObject() : void
    {
        $form = $this->getLogDataOutputForm();
        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->logsObject($form);
            return;
        }

        $test = (int) $form->getInput('sel_test');
        $from = $form->getItemByPostVar('log_from')->getDate()->get(IL_CAL_UNIX);
        $until = $form->getItemByPostVar('log_until')->getDate()->get(IL_CAL_UNIX);

        $csv = [];
        $separator = ";";
        $row = [
            $this->lng->txt("assessment_log_datetime"),
            $this->lng->txt("user"),
            $this->lng->txt("assessment_log_text"),
            $this->lng->txt("question")
        ];

        $available_tests = ilObjTest::_getAvailableTests(1);
        $csv[] = ilCSVUtil::processCSVRow($row, true, $separator);
        $log_output = ilObjAssessmentFolder::getLog($from, $until, $test);
        $users = [];
        foreach ($log_output as $key => $log) {
            if (!array_key_exists($log["user_fi"], $users)) {
                $users[$log["user_fi"]] = ilObjUser::_lookupName((int) $log["user_fi"]);
            }
            $title = "";
            if ($log["question_fi"] || $log["original_fi"]) {
                $title = assQuestion::_getQuestionTitle((int) $log["question_fi"]);
                if ($title === '') {
                    $title = assQuestion::_getQuestionTitle((int) $log["original_fi"]);
                }
                $title = $this->lng->txt("assessment_log_question") . ": " . $title;
            }
            $csvrow = [];
            $date = new ilDateTime((int) $log['tstamp'], IL_CAL_UNIX);
            $csvrow[] = $date->get(IL_CAL_FKT_DATE, 'Y-m-d H:i');
            $csvrow[] = trim($users[$log["user_fi"]]["title"] . " " . $users[$log["user_fi"]]["firstname"] . " " . $users[$log["user_fi"]]["lastname"]);
            $csvrow[] = trim($log["logtext"]);
            $csvrow[] = $title;
            $csv[] = ilCSVUtil::processCSVRow($csvrow, true, $separator);
        }
        $csvoutput = "";
        foreach ($csv as $row) {
            $csvoutput .= implode($separator, $row) . "\n";
        }
        ilUtil::deliverData(
            $csvoutput,
            str_replace(" ", "_", "log_" . $from . "_" . $until . "_" . $available_tests[$test]) . ".csv"
        );
    }

    /**
     * @return ilPropertyFormGUI
     */
    protected function getLogDataOutputForm() : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setPreventDoubleSubmission(false);
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTableWidth("100%");
        $form->setId("logs");

        $header = new ilFormSectionHeaderGUI();
        $header->setTitle($this->lng->txt("assessment_log"));
        $form->addItem($header);

        // from
        $from = new ilDateTimeInputGUI($this->lng->txt('cal_from'), "log_from");
        $from->setShowTime(true);
        $from->setRequired(true);
        $form->addItem($from);

        // until
        $until = new ilDateTimeInputGUI($this->lng->txt('cal_until'), "log_until");
        $until->setShowTime(true);
        $until->setRequired(true);
        $form->addItem($until);

        $available_tests = ilObjTest::_getAvailableTests(1);

        // tests
        $fortest = new ilSelectInputGUI($this->lng->txt('assessment_log_for_test'), "sel_test");
        $fortest->setRequired(true);
        $sorted_options = [];
        foreach ($available_tests as $key => $value) {
            $sorted_options[] = [
                'title' => ilLegacyFormElementsUtil::prepareFormOutput($value) . " [" . $this->getAssessmentFolder()->getNrOfLogEntries((int) $key) . " " . $this->lng->txt("assessment_log_log_entries") . "]",
                'key' => $key
            ];
        }
        $sorted_options = ilArrayUtil::sortArray($sorted_options, 'title', 'asc');
        $options = ['' => $this->lng->txt('please_choose')];
        foreach ($sorted_options as $option) {
            $options[$option['key']] = $option['title'];
        }
        $fortest->setOptions($options);
        $form->addItem($fortest);

        $form->addCommandButton('showLog', $this->lng->txt('show'));
        $form->addCommandButton('exportLog', $this->lng->txt('export'));

        return $form;
    }

    /**
     * @param ilPropertyFormGUI|null $form
     */
    public function logsObject(ilPropertyFormGUI $form = null) : void
    {
        /**
         * @var $ilTabs ilTabsGUI
         */
        global $DIC;
        $ilTabs = $DIC['ilTabs'];

        $ilTabs->activateTab('logs');

        $template = new ilTemplate("tpl.assessment_logs.html", true, true, "Modules/Test");

        $p_test = 0;
        $fromdate = 0;
        $untildate = 0;

        if (!($form instanceof ilPropertyFormGUI)) {
            $form = $this->getLogDataOutputForm();

            $values = [];
            if ($this->testrequest->isset('sel_test')) {
                $p_test = $values['sel_test'] = $this->testrequest->int('sel_test');
            }

            if ($this->testrequest->isset('log_from')) {
                $fromdate = $this->testrequest->int('log_from');
            } else {
                $fromdate = mktime(0, 0, 0, 1, 1, (int) date('Y'));
            }

            if ($this->testrequest->isset('log_until')) {
                $untildate = $this->testrequest->int('log_until');
            } else {
                $untildate = time();
            }

            $values['log_from'] = (new ilDateTime($fromdate, IL_CAL_UNIX))->get(IL_CAL_DATETIME);
            $values['log_until'] = (new ilDateTime($untildate, IL_CAL_UNIX))->get(IL_CAL_DATETIME);

            $form->setValuesByArray($values);
        } else {
            $fromdate_input = $form->getItemByPostVar('log_from')->getDate();
            $untildate_input = $form->getItemByPostVar('log_until')->getDate();
            if ($fromdate_input instanceof ilDateTime && $untildate_input instanceof ilDateTime) {
                $p_test = (int) $form->getInput('sel_test');

                $fromdate = $fromdate_input->get(IL_CAL_UNIX);
                $untildate = $untildate_input->get(IL_CAL_UNIX);
            }
        }

        $this->ctrl->setParameter($this, 'sel_test', (int) $p_test);
        $this->ctrl->setParameter($this, 'log_until', (int) $untildate);
        $this->ctrl->setParameter($this, 'log_from', (int) $fromdate);

        $template->setVariable("FORM", $form->getHTML());

        if ($p_test) {
            $table_gui = new ilAssessmentFolderLogTableGUI($this, 'logs');
            $log_output = ilObjAssessmentFolder::getLog($fromdate, $untildate, $p_test);

            $self = $this;
            array_walk($log_output, static function (&$row) use ($self) {
                $row['location_href'] = '';
                $row['location_txt'] = '';
                if (is_numeric($row['ref_id']) && $row['ref_id'] > 0) {
                    $row['location_href'] = ilLink::_getLink((int) $row['ref_id'], 'tst');
                    $row['location_txt'] = $self->lng->txt("perma_link");
                }
            });

            $table_gui->setData($log_output);
            $template->setVariable('LOG', $table_gui->getHTML());
        }
        $this->tpl->setVariable("ADM_CONTENT", $template->get());
    }

    /**
     * Deletes the log entries for one or more tests
     */
    public function deleteLogObject() : void
    {
        if (is_array($_POST["chb_test"]) && (count($_POST["chb_test"]))) {
            $this->getAssessmentFolder()->deleteLogEntries($_POST["chb_test"]);
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("ass_log_deleted"));
        } else {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("ass_log_delete_no_selection"));
        }
        $this->logAdminObject();
    }

    /**
     * Administration output for assessment log files
     */
    public function logAdminObject() : void
    {
        global $DIC;
        $ilAccess = $DIC['ilAccess'];
        $ilTabs = $DIC['ilTabs'];

        $ilTabs->activateTab('logs');

        $a_write_access = ($ilAccess->checkAccess("write", "", $this->getAssessmentFolder()->getRefId())) ? true : false;

        $table_gui = new ilAssessmentFolderLogAdministrationTableGUI($this, 'logAdmin', $a_write_access);

        $available_tests = ilObjTest::_getAvailableTests(false);
        $data = [];
        foreach ($available_tests as $ref_id => $title) {
            $obj_id = ilObject::_lookupObjectId($ref_id);
            $data[] = [
                "title" => $title,
                "nr" => $this->getAssessmentFolder()->getNrOfLogEntries((int) $obj_id),
                "id" => $obj_id,
                "location_href" => ilLink::_getLink($ref_id, 'tst'),
                "location_txt" => $this->lng->txt("perma_link")
            ];
        }
        $table_gui->setData($data);
        $this->tpl->setVariable('ADM_CONTENT', $table_gui->getHTML());
    }

    public function getAdminTabs() : void
    {
        $this->getTabs();
    }

    public function getLogdataSubtabs() : void
    {
        global $DIC;
        $ilTabs = $DIC['ilTabs'];

        // log settings
        $ilTabs->addSubTabTarget(
            "settings",
            $this->ctrl->getLinkTarget($this, "showLogSettings"),
            ["saveLogSettings", "showLogSettings"],
            ""
        );

        // log output
        $ilTabs->addSubTabTarget(
            "ass_log_output",
            $this->ctrl->getLinkTarget($this, "logs"),
            ["logs", "showLog", "exportLog"],
            ""
        );

        // log administration
        $ilTabs->addSubTabTarget(
            "ass_log_admin",
            $this->ctrl->getLinkTarget($this, "logAdmin"),
            ["logAdmin", "deleteLog"],
            "",
            ""
        );
    }

    protected function getTabs() : void
    {
        global $DIC;
        $rbacsystem = $DIC['rbacsystem'];
        $lng = $DIC['lng'];

        switch ($this->ctrl->getCmd()) {
            case "saveLogSettings":
            case "showLogSettings":
            case "logs":
            case "showLog":
            case "exportLog":
            case "logAdmin":
            case "deleteLog":
                $this->getLogdataSubtabs();
                break;
        }

        if ($rbacsystem->checkAccess("visible,read", $this->getAssessmentFolder()->getRefId())) {
            $this->tabs_gui->addTarget(
                "settings",
                $this->ctrl->getLinkTarget($this, "settings"),
                ["settings", "", "view"],
                "",
                ""
            );

            $this->tabs_gui->addTarget(
                "logs",
                $this->ctrl->getLinkTarget($this, "showLogSettings"),
                ['saveLogSettings', 'showLogSettings', "logs", "showLog", "exportLog", "logAdmin", "deleteLog"],
                "",
                ""
            );

            $this->tabs_gui->addTab(
                "templates",
                $lng->txt("adm_settings_templates"),
                $this->ctrl->getLinkTargetByClass("ilsettingstemplategui", "")
            );

            $this->tabs_gui->addTarget(
                'units',
                $this->ctrl->getLinkTargetByClass('ilGlobalUnitConfigurationGUI', ''),
                '',
                'ilglobalunitconfigurationgui'
            );
        }

        if ($rbacsystem->checkAccess('edit_permission', $this->getAssessmentFolder()->getRefId())) {
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
    protected function showLogSettingsObject(ilPropertyFormGUI $form = null) : void
    {
        $this->tabs_gui->activateTab('logs');

        if (!($form instanceof ilPropertyFormGUI)) {
            $form = $this->getLogSettingsForm();
            $form->setValuesByArray([
                'chb_assessment_logging' => ilObjAssessmentFolder::_enabledAssessmentLogging(),
                'reporting_language' => ilObjAssessmentFolder::_getLogLanguage()
            ]);
        }

        $this->tpl->setContent($form->getHTML());
    }

    /**
     *
     */
    protected function saveLogSettingsObject() : void
    {
        /**
         * @var $ilAccess ilAccessHandler
         */
        global $DIC;
        $ilAccess = $DIC['ilAccess'];

        if (!$ilAccess->checkAccess('write', '', $this->getAssessmentFolder()->getRefId())) {
            $this->ilias->raiseError($this->lng->txt("permission_denied"), $this->ilias->error_obj->WARNING);
        }

        $form = $this->getLogSettingsForm();
        if ($form->checkInput()) {
            $this->getAssessmentFolder()->_enableAssessmentLogging((bool) $form->getInput('chb_assessment_logging'));
            $this->getAssessmentFolder()->_setLogLanguage((string) $form->getInput('reporting_language'));
            $this->getAssessmentFolder()->update();
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'));
        }

        $form->setValuesByPost();
        $this->showLogSettingsObject($form);
    }

    /**
     * @return ilPropertyFormGUI
     */
    protected function getLogSettingsForm() : ilPropertyFormGUI
    {
        /**
         * @var $ilAccess ilAccessHandler
         */
        global $DIC;
        $ilAccess = $DIC['ilAccess'];

        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, 'saveLogSettings'));
        $form->setTitle($this->lng->txt('assessment_log_logging'));

        $logging = new ilCheckboxInputGUI('', 'chb_assessment_logging');
        $logging->setValue('1');
        $logging->setOptionTitle($this->lng->txt('activate_assessment_logging'));
        $form->addItem($logging);

        $reporting = new ilSelectInputGUI(
            $this->lng->txt('assessment_settings_reporting_language'),
            'reporting_language'
        );
        $languages = $this->lng->getInstalledLanguages();
        $this->lng->loadLanguageModule('meta');
        $options = [];
        foreach ($languages as $lang) {
            $options[$lang] = $this->lng->txt('meta_l_' . $lang);
        }
        $reporting->setOptions($options);
        $form->addItem($reporting);

        if ($ilAccess->checkAccess('write', '', $this->getAssessmentFolder()->getRefId())) {
            $form->addCommandButton('saveLogSettings', $this->lng->txt('save'));
        }

        return $form;
    }

    private function forwardToSettingsTemplateGUI() : void
    {
        global $DIC;
        $ilTabs = $DIC['ilTabs'];

        $ilTabs->setTabActive('templates');

        $gui = new ilSettingsTemplateGUI(self::getSettingsTemplateConfig());

        $this->ctrl->forwardCommand($gui);
    }

    /**
     * @return ilTestSettingsTemplateConfig
     */
    public static function getSettingsTemplateConfig() : ilTestSettingsTemplateConfig
    {
        global $DIC;
        $lng = $DIC['lng'];

        $config = new ilTestSettingsTemplateConfig($lng);
        $config->init();

        return $config;
    }
}

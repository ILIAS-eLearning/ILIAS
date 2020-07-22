<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObjectGUI.php";

require_once 'Modules/Test/classes/class.ilObjTest.php';
require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionProcessLocker.php';

/**
* Class ilObjAssessmentFolderGUI
*
* @author Helmut Schottmüller <hschottm@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilObjAssessmentFolderGUI: ilPermissionGUI, ilSettingsTemplateGUI, ilGlobalUnitConfigurationGUI
*
* @extends ilObjectGUI
*/
class ilObjAssessmentFolderGUI extends ilObjectGUI
{
    public $conditions;
    
    /**
     * Constructor
     */
    public function __construct($a_data, $a_id = 0, $a_call_by_reference = true, $a_prepare_output = true)
    {
        global $DIC;
        $rbacsystem = $DIC['rbacsystem'];

        $this->type = "assf";
        parent::__construct($a_data, $a_id, $a_call_by_reference, false);

        if (!$rbacsystem->checkAccess('read', $this->object->getRefId())) {
            $this->ilias->raiseError($this->lng->txt("msg_no_perm_read_assf"), $this->ilias->error_obj->WARNING);
        }

        $this->lng->loadLanguageModule('assessment');
    }
    
    public function executeCommand()
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
                include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
                $perm_gui = new ilPermissionGUI($this);
                $ret = &$this->ctrl->forwardCommand($perm_gui);
                break;

            case 'ilsettingstemplategui':
                $this->forwardToSettingsTemplateGUI();
                break;

            case 'ilglobalunitconfigurationgui':
                if (!$rbacsystem->checkAccess('write', $this->object->getRefId())) {
                    $this->ilias->raiseError($this->lng->txt('permission_denied'), $this->ilias->error_obj->WARNING);
                }

                $ilTabs->setTabActive('units');

                require_once 'Modules/TestQuestionPool/classes/class.ilGlobalUnitConfigurationGUI.php';
                require_once 'Modules/TestQuestionPool/classes/class.ilUnitConfigurationRepository.php';
                $gui = new ilGlobalUnitConfigurationGUI(
                    new ilUnitConfigurationRepository(0)
                );
                $this->ctrl->forwardCommand($gui);
                break;

            default:
                if ($cmd == "" || $cmd == "view") {
                    $cmd = "settings";
                }
                $cmd .= "Object";
                $this->$cmd();

                break;
        }
        return true;
    }


    /**
    * save object
    * @access	public
    */
    public function saveObject()
    {
        global $DIC;
        $rbacadmin = $DIC['rbacadmin'];

        // create and insert forum in objecttree
        $newObj = parent::saveObject();

        // put here object specific stuff

        // always send a message
        ilUtil::sendSuccess($this->lng->txt("object_added"), true);

        $this->ctrl->redirect($this);
    }


    /**
    * display assessment folder settings form
    */
    public function settingsObject(ilPropertyFormGUI $form = null)
    {
        global $DIC;
        $ilTabs = $DIC['ilTabs'];
                
        $ilTabs->setTabActive('settings');

        if ($form === null) {
            $form = $this->buildSettingsForm();
        }
        
        $this->tpl->setVariable("ADM_CONTENT", $form->getHTML());
    }
    
    private function buildSettingsForm()
    {
        /**
         * @var $ilAccess ilAccessHandler
         */
        global $DIC;
        $ilAccess = $DIC['ilAccess'];
        
        include_once "./Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php";
        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTableWidth("100%");
        $form->setId("settings");

        $header = new ilFormSectionHeaderGUI();
        $header->setTitle($this->lng->txt('settings'));
        $form->addItem($header);

        // question process locking behaviour (e.g. on saving users working data)
        $chb = new ilCheckboxInputGUI($this->lng->txt('ass_process_lock'), 'ass_process_lock');
        $chb->setChecked($this->object->getAssessmentProcessLockMode() != ilObjAssessmentFolder::ASS_PROC_LOCK_MODE_NONE);
        $chb->setInfo($this->lng->txt('ass_process_lock_desc'));
        $form->addItem($chb);
        $rg = new ilRadioGroupInputGUI($this->lng->txt('ass_process_lock_mode'), 'ass_process_lock_mode');
        $rg->setRequired(true);
        $opt = new ilRadioOption($this->lng->txt('ass_process_lock_mode_file'), ilObjAssessmentFolder::ASS_PROC_LOCK_MODE_FILE);
        $opt->setInfo($this->lng->txt('ass_process_lock_mode_file_desc'));
        $rg->addOption($opt);
        $opt = new ilRadioOption($this->lng->txt('ass_process_lock_mode_db'), ilObjAssessmentFolder::ASS_PROC_LOCK_MODE_DB);
        $opt->setInfo($this->lng->txt('ass_process_lock_mode_db_desc'));
        $rg->addOption($opt);
        if ($this->object->getAssessmentProcessLockMode() != ilObjAssessmentFolder::ASS_PROC_LOCK_MODE_NONE) {
            $rg->setValue($this->object->getAssessmentProcessLockMode());
        }
        $chb->addSubItem($rg);

        $assessmentSetting = new ilSetting('assessment');
        $imap_line_color = array_key_exists('imap_line_color', $_GET) ? $_GET['imap_line_color'] : $assessmentSetting->get('imap_line_color');
        if (strlen($imap_line_color) == 0) {
            $imap_line_color = 'FF0000';
        }

        $linepicker = new ilColorPickerInputGUI($this->lng->txt('assessment_imap_line_color'), 'imap_line_color');
        $linepicker->setValue($imap_line_color);
        $form->addItem($linepicker);

        $user_criteria = array_key_exists('user_criteria', $_GET) ? $_GET['user_criteria'] : $assessmentSetting->get('user_criteria');
        $userCriteria = new ilSelectInputGUI($this->lng->txt('user_criteria'), 'user_criteria');
        $userCriteria->setInfo($this->lng->txt('user_criteria_desc'));
        $userCriteria->setRequired(true);

        $fields = array('usr_id', 'login', 'email', 'matriculation', 'ext_account');
        $usr_fields = array();
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
        $numRequiredAnswers->setValue($this->object->getSkillTriggeringNumAnswersBarrier());
        $form->addItem($numRequiredAnswers);

        $ceeqwh = new ilCheckboxInputGUI($this->lng->txt('export_essay_qst_with_html'), 'export_essay_qst_with_html');
        $ceeqwh->setChecked($this->object->getExportEssayQuestionsWithHtml());
        $ceeqwh->setInfo($this->lng->txt('export_essay_qst_with_html_desc'));
        $form->addItem($ceeqwh);
        
        // question settings
        $header = new ilFormSectionHeaderGUI();
        $header->setTitle($this->lng->txt("assf_questiontypes"));
        $form->addItem($header);

        // available question types
        $allowed = new ilCheckboxGroupInputGUI($this->lng->txt('assf_allowed_questiontypes'), "chb_allowed_questiontypes");
        $questiontypes = &ilObjQuestionPool::_getQuestionTypes(true);
        $forbidden_types = ilObjAssessmentFolder::_getForbiddenQuestionTypes();
        $allowedtypes = array();
        foreach ($questiontypes as $qt) {
            if (!in_array($qt['question_type_id'], $forbidden_types)) {
                array_push($allowedtypes, $qt['question_type_id']);
            }
        }
        $allowed->setValue($allowedtypes);
        foreach ($questiontypes as $type_name => $qtype) {
            $allowed->addOption(new ilCheckboxOption($type_name, $qtype["question_type_id"]));
        }
        $allowed->setInfo($this->lng->txt('assf_allowed_questiontypes_desc'));
        $form->addItem($allowed);

        // manual scoring
        $manual = new ilCheckboxGroupInputGUI($this->lng->txt('assessment_log_manual_scoring_activate'), "chb_manual_scoring");
        $manscoring = ilObjAssessmentFolder::_getManualScoring();
        $manual->setValue($manscoring);
        foreach ($questiontypes as $type_name => $qtype) {
            $manual->addOption(new ilCheckboxOption($type_name, $qtype["question_type_id"]));
        }
        $manual->setInfo($this->lng->txt('assessment_log_manual_scoring_desc'));
        $form->addItem($manual);

        // scoring adjustment active
        $scoring_activation = new ilCheckboxInputGUI($this->lng->txt('assessment_scoring_adjust'), 'chb_scoring_adjust');
        $scoring_activation->setChecked($this->object->getScoringAdjustmentEnabled());
        $scoring_activation->setInfo($this->lng->txt('assessment_scoring_adjust_desc'));
        $form->addItem($scoring_activation);

        // scoring adjustment
        $scoring = new ilCheckboxGroupInputGUI($this->lng->txt('assessment_log_scoring_adjustment_activate'), "chb_scoring_adjustment");
        $scoring_active = $this->object->getScoringAdjustableQuestions();
        $scoring->setValue($scoring_active);
        foreach ($this->object->fetchScoringAdjustableTypes($questiontypes) as $type_name => $qtype) {
            $scoring->addOption(new ilCheckboxOption($type_name, $qtype["question_type_id"]));
        }
        $scoring->setInfo($this->lng->txt('assessment_log_scoring_adjustment_desc'));
        $form->addItem($scoring);

        if ($ilAccess->checkAccess("write", "", $this->object->getRefId())) {
            $form->addCommandButton("saveSettings", $this->lng->txt("save"));
        }

        return $form;
    }
    
    /**
    * Save Assessment settings
    */
    public function saveSettingsObject()
    {
        global $DIC;
        $ilAccess = $DIC['ilAccess'];
        if (!$ilAccess->checkAccess("write", "", $this->object->getRefId())) {
            $this->ctrl->redirect($this, 'settings');
        }
    
        $form = $this->buildSettingsForm();
        if (!$form->checkInput()) {
            $form->setValuesByPost();
            return $this->settingsObject($form);
        }
        
        $this->object->setSkillTriggeringNumAnswersBarrier((int) $_POST['num_req_answers']);
        $this->object->setExportEssayQuestionsWithHtml((int) $_POST["export_essay_qst_with_html"] == 1);
        $this->object->_setManualScoring($_POST["chb_manual_scoring"]);
        include_once "./Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php";
        $questiontypes = &ilObjQuestionPool::_getQuestionTypes(true);
        $forbidden_types = array();
        foreach ($questiontypes as $name => $row) {
            if (!in_array($row["question_type_id"], $_POST["chb_allowed_questiontypes"])) {
                array_push($forbidden_types, $row["question_type_id"]);
            }
        }
        $this->object->_setForbiddenQuestionTypes($forbidden_types);
        
        $this->object->setScoringAdjustmentEnabled($_POST['chb_scoring_adjust']);
        $scoring_types = array();
        foreach ($questiontypes as $name => $row) {
            if (in_array($row["question_type_id"], (array) $_POST["chb_scoring_adjustment"])) {
                array_push($scoring_types, $row["question_type_id"]);
            }
        }
        $this->object->setScoringAdjustableQuestions($scoring_types);
        
        if (!$_POST['ass_process_lock']) {
            $this->object->setAssessmentProcessLockMode(ilObjAssessmentFolder::ASS_PROC_LOCK_MODE_NONE);
        } elseif (in_array($_POST['ass_process_lock_mode'], ilObjAssessmentFolder::getValidAssessmentProcessLockModes())) {
            $this->object->setAssessmentProcessLockMode($_POST['ass_process_lock_mode']);
        }

        $assessmentSetting = new ilSetting('assessment');
        $assessmentSetting->set('use_javascript', '1');
        if (strlen($_POST['imap_line_color']) == 6) {
            $assessmentSetting->set('imap_line_color', ilUtil::stripSlashes($_POST['imap_line_color']));
        }
        $assessmentSetting->set('user_criteria', ilUtil::stripSlashes($_POST['user_criteria']));

        ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);

        $this->ctrl->redirect($this, 'settings');
    }
    
    /**
    * Called when the a log should be shown
    */
    public function showLogObject()
    {
        $form = $this->getLogDataOutputForm();
        $form->checkInput();

        $form->setValuesByPost();
        $this->logsObject($form);
    }
    
    /**
    * Called when the a log should be exported
    */
    public function exportLogObject()
    {
        $form = $this->getLogDataOutputForm();
        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->logsObject($form);
            return;
        }

        $test = $form->getInput('sel_test');
        $from = $form->getItemByPostVar('log_from')->getDate()->get(IL_CAL_UNIX);
        $until = $form->getItemByPostVar('log_until')->getDate()->get(IL_CAL_UNIX);

        $csv = array();
        $separator = ";";
        $row = array(
                $this->lng->txt("assessment_log_datetime"),
                $this->lng->txt("user"),
                $this->lng->txt("assessment_log_text"),
                $this->lng->txt("question")
        );
        include_once "./Modules/Test/classes/class.ilObjTest.php";
        include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
        $available_tests = &ilObjTest::_getAvailableTests(1);
        array_push($csv, ilUtil::processCSVRow($row, true, $separator));
        $log_output = ilObjAssessmentFolder::getLog($from, $until, $test);
        $users = array();
        foreach ($log_output as $key => $log) {
            if (!array_key_exists($log["user_fi"], $users)) {
                $users[$log["user_fi"]] = ilObjUser::_lookupName($log["user_fi"]);
            }
            $title = "";
            if ($log["question_fi"] || $log["original_fi"]) {
                $title = assQuestion::_getQuestionTitle($log["question_fi"]);
                if (strlen($title) == 0) {
                    $title = assQuestion::_getQuestionTitle($log["original_fi"]);
                }
                $title = $this->lng->txt("assessment_log_question") . ": " . $title;
            }
            $csvrow = array();
            $date = new ilDateTime($log['tstamp'], IL_CAL_UNIX);
            array_push($csvrow, $date->get(IL_CAL_FKT_DATE, 'Y-m-d H:i'));
            array_push($csvrow, trim($users[$log["user_fi"]]["title"] . " " . $users[$log["user_fi"]]["firstname"] . " " . $users[$log["user_fi"]]["lastname"]));
            array_push($csvrow, trim($log["logtext"]));
            array_push($csvrow, $title);
            array_push($csv, ilUtil::processCSVRow($csvrow, true, $separator));
        }
        $csvoutput = "";
        foreach ($csv as $row) {
            $csvoutput .= join($row, $separator) . "\n";
        }
        ilUtil::deliverData($csvoutput, str_replace(" ", "_", "log_" . $from . "_" . $until . "_" . $available_tests[$test]) . ".csv");
    }

    /**
     * @return ilPropertyFormGUI
     */
    protected function getLogDataOutputForm()
    {
        require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
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

        require_once 'Modules/Test/classes/class.ilObjTest.php';
        require_once 'Modules/TestQuestionPool/classes/class.assQuestion.php';
        $available_tests = ilObjTest::_getAvailableTests(1);

        // tests
        $fortest = new ilSelectInputGUI($this->lng->txt('assessment_log_for_test'), "sel_test");
        $fortest->setRequired(true);
        $sorted_options = array();
        foreach ($available_tests as $key => $value) {
            $sorted_options[] = array(
                'title' => ilUtil::prepareFormOutput($value) . " [" . $this->object->getNrOfLogEntries($key) . " " . $this->lng->txt("assessment_log_log_entries") . "]",
                'key' => $key
            );
        }
        $sorted_options = ilUtil::sortArray($sorted_options, 'title', 'asc');
        $options = array('' => $this->lng->txt('please_choose'));
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
     * @param $form ilPropertyFormGUI|null
     */
    public function logsObject(ilPropertyFormGUI $form = null)
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
            
            $values = array();
            if (isset($_GET['sel_test'])) {
                $p_test = $values['sel_test'] = (int) $_GET['sel_test'];
            }

            if (isset($_GET['log_from'])) {
                $fromdate = (int) $_GET['log_from'];
            } else {
                $fromdate = mktime(0, 0, 0, 1, 1, date('Y'));
            }

            if (isset($_GET['log_until'])) {
                $untildate = (int) $_GET['log_until'];
            } else {
                $untildate = time();
            }

            $values['log_from'] = new ilDateTime($fromdate, IL_CAL_UNIX);
            $values['log_until'] = new ilDateTime($untildate, IL_CAL_UNIX);

            $form->setValuesByArray($values);
        } else {
            $fromdate_input = $form->getItemByPostVar('log_from')->getDate();
            $untildate_input = $form->getItemByPostVar('log_until')->getDate();
            if ($fromdate_input instanceof ilDateTime && $untildate_input instanceof ilDateTime) {
                $p_test = $form->getInput('sel_test');

                $fromdate = $fromdate_input->get(IL_CAL_UNIX);
                $untildate = $untildate_input->get(IL_CAL_UNIX);
            }
        }

        $this->ctrl->setParameter($this, 'sel_test', (int) $p_test);
        $this->ctrl->setParameter($this, 'log_until', (int) $untildate);
        $this->ctrl->setParameter($this, 'log_from', (int) $fromdate);

        $template->setVariable("FORM", $form->getHTML());

        if ($p_test) {
            require_once "Services/Link/classes/class.ilLink.php";
            include_once "./Modules/Test/classes/tables/class.ilAssessmentFolderLogTableGUI.php";
            $table_gui = new ilAssessmentFolderLogTableGUI($this, 'logs');
            $log_output = ilObjAssessmentFolder::getLog($fromdate, $untildate, $p_test);

            $self = $this;
            array_walk($log_output, function (&$row) use ($self) {
                if (is_numeric($row['ref_id']) && $row['ref_id'] > 0) {
                    $row['location_href'] = ilLink::_getLink($row['ref_id'], 'tst');
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
    public function deleteLogObject()
    {
        if (is_array($_POST["chb_test"]) && (count($_POST["chb_test"]))) {
            $this->object->deleteLogEntries($_POST["chb_test"]);
            ilUtil::sendSuccess($this->lng->txt("ass_log_deleted"));
        } else {
            ilUtil::sendInfo($this->lng->txt("ass_log_delete_no_selection"));
        }
        $this->logAdminObject();
    }
    
    /**
    * Administration output for assessment log files
    */
    public function logAdminObject()
    {
        global $DIC;
        $ilAccess = $DIC['ilAccess'];
        $ilTabs = $DIC['ilTabs'];

        $ilTabs->activateTab('logs');

        $a_write_access = ($ilAccess->checkAccess("write", "", $this->object->getRefId())) ? true : false;
        
        include_once "./Modules/Test/classes/tables/class.ilAssessmentFolderLogAdministrationTableGUI.php";
        $table_gui = new ilAssessmentFolderLogAdministrationTableGUI($this, 'logAdmin', $a_write_access);
        include_once "./Modules/Test/classes/class.ilObjTest.php";
        require_once "./Services/Link/classes/class.ilLink.php";
        $available_tests = ilObjTest::_getAvailableTests(false);
        $data = array();
        foreach ($available_tests as $ref_id => $title) {
            $obj_id = ilObject::_lookupObjectId($ref_id);
            array_push($data, array(
                "title" => $title,
                "nr" => $this->object->getNrOfLogEntries($obj_id),
                "id" => $obj_id,
                "location_href" => ilLink::_getLink($ref_id, 'tst'),
                "location_txt" => $this->lng->txt("perma_link")
            ));
        }
        $table_gui->setData($data);
        $this->tpl->setVariable('ADM_CONTENT', $table_gui->getHTML());
    }

    public function getAdminTabs()
    {
        $this->getTabs();
    }

    public function getLogdataSubtabs()
    {
        global $DIC;
        $ilTabs = $DIC['ilTabs'];

        // log settings
        $ilTabs->addSubTabTarget(
            "settings",
            $this->ctrl->getLinkTarget($this, "showLogSettings"),
            array("saveLogSettings", "showLogSettings"),
            ""
        );

        // log output
        $ilTabs->addSubTabTarget(
            "ass_log_output",
            $this->ctrl->getLinkTarget($this, "logs"),
            array("logs", "showLog", "exportLog"),
            ""
        );
    
        // log administration
        $ilTabs->addSubTabTarget(
            "ass_log_admin",
            $this->ctrl->getLinkTarget($this, "logAdmin"),
            array("logAdmin", "deleteLog"),
            "",
            ""
        );
    }

    /**
    * get tabs
    *
    * @param	object	tabs gui object
    */
    public function getTabs()
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
        
        if ($rbacsystem->checkAccess("visible,read", $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "settings",
                $this->ctrl->getLinkTarget($this, "settings"),
                array("settings","","view"),
                "",
                ""
            );

            $this->tabs_gui->addTarget(
                "logs",
                $this->ctrl->getLinkTarget($this, "showLogSettings"),
                array('saveLogSettings', 'showLogSettings', "logs","showLog", "exportLog", "logAdmin", "deleteLog"),
                "",
                ""
            );

            $this->tabs_gui->addTab(
                "templates",
                $lng->txt("adm_settings_templates"),
                $this->ctrl->getLinkTargetByClass("ilsettingstemplategui", "")
            );
        }

        if ($rbacsystem->checkAccess("write", $this->object->getRefId())) {
            $this->tabs_gui->addTarget('units', $this->ctrl->getLinkTargetByClass('ilGlobalUnitConfigurationGUI', ''), '', 'ilglobalunitconfigurationgui');
        }

        if ($rbacsystem->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "perm_settings",
                $this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"),
                array("perm","info","owner"),
                'ilpermissiongui'
            );
        }
    }

    /**
     * @param ilPropertyFormGUI $form
     */
    protected function showLogSettingsObject(ilPropertyFormGUI $form = null)
    {
        $this->tabs_gui->activateTab('logs');

        if (!($form instanceof ilPropertyFormGUI)) {
            $form = $this->getLogSettingsForm();
            $form->setValuesByArray(array(
                'chb_assessment_logging' => ilObjAssessmentFolder::_enabledAssessmentLogging(),
                'reporting_language' => ilObjAssessmentFolder::_getLogLanguage()
            ));
        }

        $this->tpl->setContent($form->getHTML());
    }

    /**
     *
     */
    protected function saveLogSettingsObject()
    {
        /**
         * @var $ilAccess ilAccessHandler
         */
        global $DIC;
        $ilAccess = $DIC['ilAccess'];

        if (!$ilAccess->checkAccess('write', '', $this->object->getRefId())) {
            $this->ilias->raiseError($this->lng->txt("permission_denied"), $this->ilias->error_obj->WARNING);
        }

        $form = $this->getLogSettingsForm();
        if ($form->checkInput()) {
            $this->object->_enableAssessmentLogging((int) $form->getInput('chb_assessment_logging'));
            $this->object->_setLogLanguage($form->getInput('reporting_language'));
            $this->object->update();
            ilUtil::sendSuccess($this->lng->txt('saved_successfully'));
        }

        $form->setValuesByPost();
        $this->showLogSettingsObject($form);
    }

    /**
     * @return ilPropertyFormGUI
     */
    protected function getLogSettingsForm()
    {
        /**
         * @var $ilAccess ilAccessHandler
         */
        global $DIC;
        $ilAccess = $DIC['ilAccess'];

        require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, 'saveLogSettings'));
        $form->setTitle($this->lng->txt('assessment_log_logging'));

        $logging = new ilCheckboxInputGUI('', 'chb_assessment_logging');
        $logging->setValue(1);
        $logging->setOptionTitle($this->lng->txt('activate_assessment_logging'));
        $form->addItem($logging);

        $reporting = new ilSelectInputGUI($this->lng->txt('assessment_settings_reporting_language'), 'reporting_language');
        $languages = $this->lng->getInstalledLanguages();
        $this->lng->loadLanguageModule('meta');
        $options = array();
        foreach ($languages as $lang) {
            $options[$lang] = $this->lng->txt('meta_l_' . $lang);
        }
        $reporting->setOptions($options);
        $form->addItem($reporting);

        if ($ilAccess->checkAccess('write', '', $this->object->getRefId())) {
            $form->addCommandButton('saveLogSettings', $this->lng->txt('save'));
        }

        return $form;
    }

    private function forwardToSettingsTemplateGUI()
    {
        global $DIC;
        $ilTabs = $DIC['ilTabs'];

        $ilTabs->setTabActive('templates');

        require_once 'Services/Administration/classes/class.ilSettingsTemplateGUI.php';
        $gui = new ilSettingsTemplateGUI(self::getSettingsTemplateConfig());

        $this->ctrl->forwardCommand($gui);
    }
    
    /**
     * @return ilTestSettingsTemplateConfig
     */
    public static function getSettingsTemplateConfig()
    {
        global $DIC;
        $lng = $DIC['lng'];

        require_once 'Modules/Test/classes/class.ilTestSettingsTemplateConfig.php';
        $config = new ilTestSettingsTemplateConfig($lng);
        $config->init();

        return $config;
    }
}

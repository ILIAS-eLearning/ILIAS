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

use ILIAS\Survey\Participants;

/**
 * Class ilSurveyParticipantsGUI
 * @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @ilCtrl_Calls ilSurveyParticipantsGUI: ilRepositorySearchGUI, ilSurveyRaterGUI
 */
class ilSurveyParticipantsGUI
{
    protected \ILIAS\Survey\Mode\FeatureConfig $feature_config;
    protected \ILIAS\Survey\Editing\EditingGUIRequest $edit_request;
    protected \ILIAS\Survey\Editing\EditManager $edit_manager;
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;
    protected ilTabsGUI $tabs;
    protected ilToolbarGUI $toolbar;
    protected ilAccessHandler $access;
    protected ilRbacSystem $rbacsystem;
    protected ilObjUser $user;
    protected ilLogger $log;
    protected ilObjSurveyGUI $parent_gui;
    protected ilObjSurvey $object;
    protected int $ref_id;
    protected bool $has_write;
    protected Participants\InvitationsManager $invitation_manager;
    protected \ILIAS\Survey\InternalService $survey_service;
    protected \ILIAS\Survey\Code\CodeManager $code_manager;
    protected \ILIAS\Survey\InternalDataService $data_manager;

    public function __construct(
        ilObjSurveyGUI $a_parent_gui,
        bool $a_has_write_access
    ) {
        global $DIC;

        $this->survey_service = $DIC->survey()->internal();

        $this->tabs = $DIC->tabs();
        $this->toolbar = $DIC->toolbar();
        $this->access = $DIC->access();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->user = $DIC->user();
        $this->log = $DIC["ilLog"];
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        $tpl = $DIC["tpl"];

        $this->parent_gui = $a_parent_gui;
        /** @var ilObjSurvey $survey */
        $survey = $this->parent_gui->getObject();
        $this->object = $survey;
        $this->ref_id = $this->object->getRefId();
        $this->has_write = $a_has_write_access;

        $this->ctrl = $ilCtrl;
        $this->lng = $lng;
        $this->tpl = $tpl;
        $this->invitation_manager = $this
            ->survey_service
            ->domain()
            ->participants()
            ->invitations();
        $this->code_manager = $this
            ->survey_service
            ->domain()
            ->code($this->object, $this->user->getId());
        $this->data_manager = $this
            ->survey_service
            ->data();
        $this->feature_config = $this
            ->survey_service
            ->domain()->modeFeatureConfig($this->object->getMode());
        $this->edit_manager = $this->survey_service
            ->domain()
            ->edit();
        $this->edit_request = $this->survey_service
            ->gui()
            ->editing()
            ->request();
    }

    public function getObject(): ilObjSurvey
    {
        return $this->object;
    }

    protected function handleWriteAccess(): void
    {
        if (!$this->has_write) {
            throw new ilSurveyException("Permission denied");
        }
    }

    public function executeCommand(): void
    {
        $ilCtrl = $this->ctrl;
        $ilTabs = $this->tabs;
        $lng = $this->lng;

        $cmd = $ilCtrl->getCmd("maintenance");
        $next_class = $this->ctrl->getNextClass($this);

        switch ($next_class) {
            case 'ilrepositorysearchgui':
                $rep_search = new ilRepositorySearchGUI();

                if (!$this->edit_request->getAppr360() && !$this->edit_request->getRate360()) {
                    $ilTabs->clearTargets();
                    $ilTabs->setBackTarget(
                        $this->lng->txt("btn_back"),
                        $this->ctrl->getLinkTarget($this, "maintenance")
                    );

                    $rep_search->setCallback(
                        $this,
                        'inviteUsers',
                        array(
                            )
                    );
                    $rep_search->setTitle($lng->txt("svy_invite_participants"));
                    // Set tabs
                    $this->ctrl->setReturn($this, 'maintenance');
                    $this->ctrl->forwardCommand($rep_search);
                    $ilTabs->setTabActive('maintenance');
                } elseif ($this->edit_request->getRate360()) {
                    $ilTabs->clearTargets();
                    $ilTabs->setBackTarget(
                        $this->lng->txt("btn_back"),
                        $this->ctrl->getLinkTarget($this, "listAppraisees")
                    );

                    $this->ctrl->setParameter($this, "rate360", 1);
                    $this->ctrl->saveParameter($this, "appr_id");

                    $rep_search->setCallback(
                        $this,
                        'addRater',
                        array(
                            )
                    );

                    // Set tabs
                    $this->ctrl->setReturn($this, 'editRaters');
                    $this->ctrl->forwardCommand($rep_search);
                } else {
                    $ilTabs->activateTab("survey_360_appraisees");
                    $this->ctrl->setParameter($this, "appr360", 1);

                    $rep_search->setCallback(
                        $this,
                        'addAppraisee',
                        array(
                            )
                    );

                    // Set tabs
                    $this->ctrl->setReturn($this, 'listAppraisees');
                    $this->ctrl->forwardCommand($rep_search);
                }
                break;

            case 'ilsurveyratergui':
                $ilTabs->activateTab("survey_360_edit_raters");
                $rater_gui = new ilSurveyRaterGUI($this, $this->object);
                $this->ctrl->forwardCommand($rater_gui);
                break;

            default:
                $cmd .= "Object";
                $this->$cmd();
                break;
        }
    }

    protected function filterSurveyParticipantsByAccess(
        array $a_finished_ids = null
    ): array {
        $all_participants = $this->object->getSurveyParticipants($a_finished_ids, false, true);
        $participant_ids = [];
        foreach ($all_participants as $participant) {
            if (isset($participant['usr_id'])) {
                $participant_ids[] = $participant['usr_id'];
            }
        }

        $filtered_participant_ids = $this->access->filterUserIdsByRbacOrPositionOfCurrentUser(
            'read_results',
            'access_results',
            $this->object->getRefId(),
            $participant_ids
        );

        $participants = [];
        foreach ($all_participants as $username => $user_data) {
            if (!($user_data['usr_id'] ?? false)) {
                $participants[$username] = $user_data;
            }
            if (in_array(($user_data['usr_id'] ?? -1), $filtered_participant_ids)) {
                $participants[$username] = $user_data;
            }
        }

        return $participants;
    }


    /**
     * Participants maintenance
     */
    public function maintenanceObject(): void
    {
        $ilToolbar = $this->toolbar;

        if ($this->object->get360Mode()) {
            $this->listAppraiseesObject();
            return;
        }

        //Btn Determine Competence Levels
        if ($this->object->getMode() === ilObjSurvey::MODE_SELF_EVAL) {
            $skmg_set = new ilSkillManagementSettings();
            if ($this->object->getSkillService() && $skmg_set->isActivated()) {
                $ilToolbar->addButton(
                    $this->lng->txt("survey_calc_skills"),
                    $this->ctrl->getLinkTargetByClass("ilsurveyskilldeterminationgui"),
                    ""
                );
            }
        }

        $this->handleWriteAccess();
        $this->setParticipantSubTabs("overview");

        $ilToolbar->addButton(
            $this->lng->txt('svy_remove_all_participants'),
            $this->ctrl->getLinkTarget($this, 'deleteAllUserData')
        );

        $ilToolbar->addSeparator();

        if ($this->object->isAccessibleWithoutCode()) {
            $ilToolbar->addButton(
                $this->lng->txt("svy_invite_participants"),
                $this->ctrl->getLinkTargetByClass('ilRepositorySearchGUI', '')
            );
        }

        $table_gui = new ilSurveyMaintenanceTableGUI($this, 'maintenance');

        $total = $this->filterSurveyParticipantsByAccess();
        $data = array();
        foreach ($total as $user_data) {
            $finished = false;
            if ($user_data["finished"]) {
                $finished = $user_data["finished_tstamp"];
            }
            if (isset($user_data["active_id"])) {
                $wt = $this->object->getWorkingtimeForParticipant($user_data["active_id"]);
                $last_access = $this->object->getLastAccess($user_data["active_id"]);
                $active_id = $user_data["active_id"];
            } else {
                $wt = 0;
                $last_access = null;
                $active_id = 0;
            }
            $data[] = array(
                'id' => $active_id,
                'name' => $user_data["sortname"],
                'usr_id' => $user_data["usr_id"] ?? null,
                'login' => $user_data["login"],
                'last_access' => $last_access,
                'workingtime' => $wt,
                'finished' => $finished,
                'invited' => $user_data["invited"] ?? false
            );
        }
        $table_gui->setData($data);
        $this->tpl->setVariable('ADM_CONTENT', $table_gui->getHTML());
    }

    protected function isAnonymousListActive(): bool
    {
        $surveySetting = new ilSetting("survey");

        if ($surveySetting->get("anonymous_participants", false) && $this->object->hasAnonymizedResults() &&
            $this->object->hasAnonymousUserList()) {
            $end = $this->object->getEndDate();
            if ($end && $end < date("YmdHis")) {
                $min = (int) $surveySetting->get("anonymous_participants_min", '0');
                $total = $this->object->getSurveyParticipants();
                if (!$min || count($total) >= $min) {
                    return true;
                }
            }
        }
        return false;
    }

    protected function setParticipantSubTabs(
        string $active
    ): void {
        $ilTabs = $this->tabs;

        // not used in 360° mode

        // overview
        $ilTabs->addSubTab(
            "overview",
            $this->lng->txt("svy_part_overview"),
            $this->ctrl->getLinkTarget($this, 'maintenance')
        );

        if ($this->isAnonymousListActive()) {
            $ilTabs->addSubTab(
                "anon_participants",
                $this->lng->txt("svy_anonymous_participants_svy"),
                $this->ctrl->getLinkTarget($this, 'listParticipants')
            );
        }

        if (!$this->object->isAccessibleWithoutCode()) {
            $ilTabs->addSubTab(
                "codes",
                $this->lng->txt("svy_codes"),
                $this->ctrl->getLinkTarget($this, 'codes')
            );
        }


        $data = $this->object->getExternalCodeRecipients();
        if (count($data)) {
            $ilTabs->addSubTab(
                "mail_survey_codes",
                $this->lng->txt("mail_survey_codes"),
                $this->ctrl->getLinkTarget($this, "mailCodes")
            );
        }

        $ilTabs->activateSubTab($active);
    }


    /**
     * Creates a confirmation form for delete all user data
     */
    public function deleteAllUserDataObject(): void
    {
        $cgui = new ilConfirmationGUI();
        $cgui->setHeaderText($this->lng->txt("confirm_delete_all_user_data"));
        $cgui->setFormAction($this->ctrl->getFormAction($this, "deleteAllUserData"));
        $cgui->setCancel($this->lng->txt("cancel"), "cancelDeleteAllUserData");
        $cgui->setConfirm($this->lng->txt("confirm"), "confirmDeleteAllUserData");
        $this->tpl->setContent($cgui->getHTML());
    }

    /**
     * Deletes all user data of the survey after confirmation
     */
    public function confirmDeleteAllUserDataObject(): void
    {
        if ($this->access->checkAccess('write', '', $this->object->getRefId())) {
            $this->object->deleteAllUserData();
        } else {
            $participants = $this->filterSurveyParticipantsByAccess();
            foreach ($participants as $something => $participant_data) {
                $this->object->removeSelectedSurveyResults([$participant_data['active_id']]);
            }
        }

        // #11558 - re-open closed appraisees
        if ($this->object->get360Mode()) {
            $this->object->openAllAppraisees();
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("svy_all_user_data_deleted"), true);
        $this->ctrl->redirect($this, "maintenance");
    }

    /**
     * Cancels delete of all user data in maintenance
     */
    public function cancelDeleteAllUserDataObject(): void
    {
        $this->ctrl->redirect($this, "maintenance");
    }

    /**
     * Deletes all user data for the test object
     */
    public function confirmDeleteSelectedUserDataObject(): void
    {
        $user_ids = $this->edit_request->getUserIds();
        if (count($user_ids) > 0) {
            $this->object->removeSelectedSurveyResults(array_filter($user_ids, static function ($i): bool {
                return is_numeric($i);
            }));

            $invitations = array_filter($user_ids, static function ($i): bool {
                return strpos($i, "inv") === 0;
            });
            foreach ($invitations as $i) {
                $this->invitation_manager->remove($this->object->getSurveyId(), (int) substr($i, 3));
            }

            $this->tpl->setOnScreenMessage('success', $this->lng->txt("svy_selected_user_data_deleted"), true);
        }
        $this->ctrl->redirect($this, "maintenance");
    }

    /**
     * Cancels the deletion of all user data
     */
    public function cancelDeleteSelectedUserDataObject(): void
    {
        $this->tpl->setOnScreenMessage('info', $this->lng->txt('msg_cancel'), true);
        $this->ctrl->redirect($this, "maintenance");
    }

    /**
     * Asks for a confirmation to delete selected user data
     */
    public function deleteSingleUserResultsObject(): void
    {
        $this->handleWriteAccess();

        $user_ids = $this->edit_request->getUserIds();
        if (count($user_ids) === 0) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('no_checkbox'), true);
            $this->ctrl->redirect($this, "maintenance");
        }

        $this->tpl->setOnScreenMessage('question', $this->lng->txt("confirm_delete_single_user_data"));
        $table_gui = new ilSurveyMaintenanceTableGUI($this, 'maintenance', true);
        $total = $this->object->getSurveyParticipants(null, false, true);
        $data = array();
        foreach ($total as $user_data) {
            if (in_array($user_data['active_id'], $user_ids)
                || ($user_data['invited'] && in_array("inv" . $user_data['usr_id'], $user_ids))) {
                $last_access = $this->object->getLastAccess($user_data["active_id"]);
                $data[] = array(
                    'id' => $user_data["active_id"],
                    'name' => $user_data["sortname"],
                    'login' => $user_data["login"],
                    'last_access' => $last_access,
                    'usr_id' => $user_data["usr_id"],
                    'invited' => $user_data["invited"]
                );
            }
        }
        $table_gui->setData($data);
        $this->tpl->setVariable('ADM_CONTENT', $table_gui->getHTML());
    }

    /**
     * Change survey language for direct access URL's
     */
    public function setCodeLanguageObject(): void
    {
        if (strcmp($this->edit_request->getLang(), "-1") !== 0) {
            $ilUser = $this->user;
            $ilUser->writePref("survey_code_language", $this->edit_request->getLang());
        }
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('language_changed'), true);
        $this->ctrl->redirect($this, 'codes');
    }

    /**
     * Display the survey access codes tab
     */
    public function codesObject(): void
    {
        $ilUser = $this->user;
        $ilToolbar = $this->toolbar;

        $this->handleWriteAccess();
        $this->setParticipantSubTabs("codes");

        if ($this->object->isAccessibleWithoutCode()) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("survey_codes_no_anonymization"));
            return;
        }

        $default_lang = $ilUser->getPref("survey_code_language");

        // creation buttons
        $ilToolbar->setFormAction($this->ctrl->getFormAction($this));

        $si = new ilTextInputGUI($this->lng->txt("new_survey_codes"), "nrOfCodes");
        $si->setValue(1);
        $si->setSize(3);
        $ilToolbar->addInputItem($si, true);

        $button = ilSubmitButton::getInstance();
        $button->setCaption("create");
        $button->setCommand("createSurveyCodes");
        $ilToolbar->addButtonInstance($button);

        $ilToolbar->addSeparator();

        $button = ilSubmitButton::getInstance();
        $button->setCaption("import_from_file");
        $button->setCommand("importExternalMailRecipientsFromFileForm");
        $ilToolbar->addButtonInstance($button);

        $button = ilSubmitButton::getInstance();
        $button->setCaption("import_from_text");
        $button->setCommand("importExternalMailRecipientsFromTextForm");
        $ilToolbar->addButtonInstance($button);

        $ilToolbar->addSeparator();

        $button = ilSubmitButton::getInstance();
        $button->setCaption("svy_import_codes");
        $button->setCommand("importAccessCodes");
        $ilToolbar->addButtonInstance($button);

        $ilToolbar->addSeparator();

        $languages = $this->lng->getInstalledLanguages();
        $options = array();
        $this->lng->loadLanguageModule("meta");
        foreach ($languages as $lang) {
            $options[$lang] = $this->lng->txt("meta_l_" . $lang);
        }
        $si = new ilSelectInputGUI($this->lng->txt("survey_codes_lang"), "lang");
        $si->setOptions($options);
        $si->setValue($default_lang);
        $ilToolbar->addInputItem($si, true);

        $button = ilSubmitButton::getInstance();
        $button->setCaption("set");
        $button->setCommand("setCodeLanguage");
        $ilToolbar->addButtonInstance($button);

        $table_gui = new ilSurveyCodesTableGUI($this, 'codes');
        $survey_codes = $this->object->getSurveyCodesTableData(null, $default_lang);
        $table_gui->setData($survey_codes);
        $this->tpl->setContent($table_gui->getHTML());
    }

    public function editCodesObject(): void
    {
        $ids = $this->edit_request->getCodeIds();
        if (count($ids) === 0) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('no_checkbox'), true);
            $this->ctrl->redirect($this, 'codes');
        }

        $this->handleWriteAccess();
        $this->setParticipantSubTabs("codes");

        $table_gui = new ilSurveyCodesEditTableGUI($this, 'editCodes');
        $table_gui->setData($this->object->getSurveyCodesTableData($ids));
        $this->tpl->setContent($table_gui->getHTML());
    }

    public function updateCodesObject(): void
    {
        $codes = $this->edit_request->getCodes();
        $mails = $this->edit_request->getCodesPar("mail");
        $lnames = $this->edit_request->getCodesPar("lname");
        $fnames = $this->edit_request->getCodesPar("fname");
        $sents = $this->edit_request->getCodesPar("sent");
        if (count($codes) === 0) {
            $this->ctrl->redirect($this, 'codes');
        }

        $errors = array();
        $error_message = "";
        foreach ($codes as $id) {
            if (!$this->object->updateCode(
                $id,
                $mails[$id] ?? "",
                $lnames[$id] ?? "",
                $fnames[$id] ?? "",
                $sents[$id] ?? 0
            )) {
                $errors[] = array($mails[$id], $lnames[$id], $fnames[$id]);
            }
        }
        if (empty($errors)) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
        } else {
            foreach ($errors as $error) {
                $error_message .= sprintf($this->lng->txt("error_save_code"), $error[0], $error[1], $error[2]);
            }
            $this->tpl->setOnScreenMessage('failure', $error_message, true);
        }

        $this->ctrl->redirect($this, 'codes');
    }

    public function deleteCodesConfirmObject(): void
    {
        $codes = $this->edit_request->getCodes();
        if (count($codes) > 0) {
            $cgui = new ilConfirmationGUI();
            $cgui->setHeaderText($this->lng->txt("survey_code_delete_sure"));

            $cgui->setFormAction($this->ctrl->getFormAction($this));
            $cgui->setCancel($this->lng->txt("cancel"), "codes");
            $cgui->setConfirm($this->lng->txt("confirm"), "deleteCodes");

            $data = $this->object->getSurveyCodesTableData($codes);

            foreach ($data as $item) {
                if ($item["used"]) {
                    continue;
                }

                $title = array($item["code"]);
                $title[] = $item["email"] ?? "";
                $title[] = $item["last_name"] ?? "";
                $title[] = $item["first_name"] ?? "";
                $title = implode(", ", $title);

                $cgui->addItem("chb_code[]", $item["code"], $title);
            }

            $this->tpl->setContent($cgui->getHTML());
        } else {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('no_checkbox'), true);
            $this->ctrl->redirect($this, 'codes');
        }
    }

    /**
     * Delete a list of survey codes
     */
    public function deleteCodesObject(): void
    {
        $codes = $this->edit_request->getCodes();
        if (count($codes) > 0) {
            foreach ($codes as $survey_code) {
                $this->object->deleteSurveyCode($survey_code);
            }
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('codes_deleted'), true);
        } else {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('no_checkbox'), true);
        }
        $this->ctrl->redirect($this, 'codes');
    }

    /**
     * Exports a list of survey codes
     */
    public function exportCodesObject(): void
    {
        $codes = $this->edit_request->getCodes();
        if (count($codes) > 0) {
            $export = $this->object->getSurveyCodesForExport(null, $codes);
            ilUtil::deliverData($export, ilFileUtils::getASCIIFilename($this->object->getTitle() . ".csv"));
        } else {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, 'codes');
        }
    }

    /**
     * Exports all survey codes
     */
    public function exportAllCodesObject(): void
    {
        $export = $this->object->getSurveyCodesForExport();
        ilUtil::deliverData($export, ilFileUtils::getASCIIFilename($this->object->getTitle() . ".csv"));
    }

    /**
     * Import codes from export codes file (upload form)
     */
    protected function importAccessCodesObject(): void
    {
        $this->handleWriteAccess();
        $this->setParticipantSubTabs("codes");

        $form_import_file = new ilPropertyFormGUI();
        $form_import_file->setFormAction($this->ctrl->getFormAction($this));
        $form_import_file->setTableWidth("100%");
        $form_import_file->setId("codes_import_file");

        $headerfile = new ilFormSectionHeaderGUI();
        $headerfile->setTitle($this->lng->txt("svy_import_codes"));
        $form_import_file->addItem($headerfile);

        $export_file = new ilFileInputGUI($this->lng->txt("codes"), "codes");
        $export_file->setInfo(sprintf(
            $this->lng->txt('svy_import_codes_info'),
            $this->lng->txt("export_all_survey_codes")
        ));
        $export_file->setSuffixes(array("csv"));
        $export_file->setRequired(true);
        $form_import_file->addItem($export_file);

        $form_import_file->addCommandButton("importAccessCodesAction", $this->lng->txt("import"));
        $form_import_file->addCommandButton("codes", $this->lng->txt("cancel"));

        $this->tpl->setContent($form_import_file->getHTML());
    }

    /**
     * Import codes from export codes file
     */
    protected function importAccessCodesActionObject(): void
    {
        if (trim($_FILES['codes']['tmp_name'])) {
            $existing = array();
            foreach ($this->object->getSurveyCodesTableData() as $item) {
                $existing[$item["code"]] = $item["id"];
            }

            $reader = new ilCSVReader();
            $reader->open($_FILES['codes']['tmp_name']);
            foreach ($reader->getCsvAsArray() as $row) {
                // numeric check of used column due to #26176
                if (count($row) === 8 && is_numeric($row[5])) {
                    // used/sent/url are not relevant when importing
                    [$code, $email, $last_name, $first_name, $created, $used, $sent, $url] = $row;

                    // unique code?
                    if (!array_key_exists($code, $existing)) {
                        // could be date or datetime
                        try {
                            if (strlen($created) === 10) {
                                $created = new ilDate($created, IL_CAL_DATE);
                            } else {
                                $created = new ilDateTime($created, IL_CAL_DATETIME);
                            }
                            $created = $created->get(IL_CAL_UNIX);
                        } catch (Exception $e) {
                            $this->tpl->setOnScreenMessage('failure', $e->getMessage(), true);
                            $this->ctrl->redirect($this, 'codes');
                        }

                        $user_data = array(
                            "email" => $email
                            ,"lastname" => $last_name
                            ,"firstname" => $first_name
                        );
                        $this->object->importSurveyCode($code, $created, $user_data);
                    }
                }
            }

            $this->tpl->setOnScreenMessage('success', $this->lng->txt('codes_created'), true);
        }

        $this->ctrl->redirect($this, 'codes');
    }

    /**
     * Create access codes for the survey
     */
    public function createSurveyCodesObject(): void
    {
        if ($this->edit_request->getNrOfCodes() > 0) {
            $ids = $this->code_manager->addCodes($this->edit_request->getNrOfCodes());
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('codes_created'), true);
            $this->ctrl->setParameter($this, "new_ids", implode(";", $ids));
            $this->ctrl->redirect($this, 'editCodes');
        } else {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("enter_valid_number_of_codes"), true);
            $this->ctrl->redirect($this, 'codes');
        }
    }

    public function insertSavedMessageObject(): void
    {
        $this->handleWriteAccess();
        $this->setParticipantSubTabs("codes");

        $form_gui = new FormMailCodesGUI($this);
        $form_gui->setValuesByPost();
        try {
            if ($form_gui->getSavedMessages()->getValue() > 0) {
                $ilUser = $this->user;
                $settings = $this->object->getUserSettings($ilUser->getId(), 'savemessage');
                $form_gui->getMailMessage()->setValue($settings[$form_gui->getSavedMessages()->getValue()]['value']);
                $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_message_inserted'));
            } else {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt('msg_no_message_inserted'));
            }
        } catch (Exception $e) {
            $ilLog = $this->log;
            $ilLog->write('Error: ' . $e->getMessage());
        }
        $this->tpl->setVariable("ADM_CONTENT", $form_gui->getHTML());
    }

    public function deleteSavedMessageObject(): void
    {
        $this->handleWriteAccess();
        $this->setParticipantSubTabs("codes");

        $form_gui = new FormMailCodesGUI($this);
        $form_gui->setValuesByPost();
        try {
            if ($form_gui->getSavedMessages()->getValue() > 0) {
                $this->object->deleteUserSettings($form_gui->getSavedMessages()->getValue());
                $form_gui = new FormMailCodesGUI($this);
                $form_gui->setValuesByPost();
                $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_message_deleted'));
            } else {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt('msg_no_message_deleted'));
            }
        } catch (Exception $e) {
            $ilLog = $this->log;
            $ilLog->write('Error: ' . $e->getMessage());
        }
        $this->tpl->setVariable("ADM_CONTENT", $form_gui->getHTML());
    }

    public function mailCodesObject(): void
    {
        $this->handleWriteAccess();
        $this->setParticipantSubTabs("codes");

        $mailData['m_subject'] =
            $this->edit_request->getCodeMailPart("subject")
            ?: sprintf($this->lng->txt('default_codes_mail_subject'), $this->object->getTitle());
        $mailData['m_message'] =
            $this->edit_request->getCodeMailPart("message")
                ?: $this->lng->txt('default_codes_mail_message');
        $mailData['m_notsent'] =
            $this->edit_request->getCodeMailPart("notsent")
                ?: '1';

        $form_gui = new FormMailCodesGUI($this);
        $form_gui->setValuesByArray($mailData);
        $this->tpl->setVariable("ADM_CONTENT", $form_gui->getHTML());
    }

    public function sendCodesMailObject(): void
    {
        $ilUser = $this->user;

        $this->handleWriteAccess();
        $this->setParticipantSubTabs("mail_survey_codes");

        $form_gui = new FormMailCodesGUI($this);
        if ($form_gui->checkInput()) {
            $url_exists = strpos($this->edit_request->getCodeMailPart("message"), '[url]') !== false;
            if (!$url_exists) {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt('please_enter_mail_url'));
                $form_gui->setValuesByPost();
            } else {
                if ($this->edit_request->getSaveMessage() === 1) {
                    $ilUser = $this->user;
                    $title = ($this->edit_request->getSaveMessageTitle())
                        ?: ilStr::subStr($this->edit_request->getCodeMailPart("message"), 0, 40) . '...';
                    $this->object->saveUserSettings($ilUser->getId(), 'savemessage', $title, $this->edit_request->getCodeMailPart("message"));
                }

                $lang = $ilUser->getPref("survey_code_language");
                if (!$lang) {
                    $lang = $this->lng->getDefaultLanguage();
                }
                $this->object->sendCodes(
                    $this->edit_request->getCodeMailPart("notsent"),
                    $this->edit_request->getCodeMailPart("subject"),
                    nl2br($this->edit_request->getCodeMailPart("message")),
                    $lang
                );
                $this->tpl->setOnScreenMessage('success', $this->lng->txt('mail_sent'), true);
                $this->ctrl->redirect($this, 'mailCodes');
            }
        } else {
            $form_gui->setValuesByPost();
        }
        $this->tpl->setVariable("ADM_CONTENT", $form_gui->getHTML());
    }

    public function importExternalRecipientsFromTextObject(): void
    {
        if (trim($this->edit_request->getExternalText())) {
            $data = preg_split("/[\n\r]/", $this->edit_request->getExternalText());
            $fields = explode(";", array_shift($data));
            if (!in_array('email', $fields, true)) {
                $this->edit_manager->setExternalText($this->edit_request->getExternalText());
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt('err_external_rcp_no_email_column'), true);
                $this->ctrl->redirect($this, 'importExternalMailRecipientsFromTextForm');
            }
            $existingdata = $this->object->getExternalCodeRecipients();
            $existingcolumns = array();
            if (count($existingdata)) {
                $first = array_shift($existingdata);
                foreach ($first as $key => $value) {
                    $existingcolumns[] = $key;
                }
            }
            $founddata = array();
            foreach ($data as $datarow) {
                $row = explode(";", $datarow);
                if (count($row) === count($fields)) {
                    $dataset = array();
                    foreach ($fields as $idx => $fieldname) {
                        if (count($existingcolumns)) {
                            if (array_key_exists($idx, $existingcolumns)) {
                                $dataset[$fieldname] = $row[$idx];
                            }
                        } else {
                            $dataset[$fieldname] = $row[$idx];
                        }
                    }
                    if ($dataset['email'] !== '') {
                        $this->addCodeForExternal(
                            $dataset['email'],
                            $dataset['lastname'] ?? "",
                            $dataset['firstname'] ?? ""
                        );
                    }
                }
            }
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('external_recipients_imported'), true);
            $this->ctrl->redirect($this, 'codes');
        }

        $this->ctrl->redirect($this, 'importExternalMailRecipientsFromTextForm');
    }

    /**
     * Add code for an external rater
     */
    public function addCodeForExternal(
        string $email,
        string $lastname,
        string $firstname
    ): int {
        $code = $this->data_manager->code("")
           ->withEmail($email)
           ->withLastName($lastname)
           ->withFirstName($firstname);
        $code_id = $this->code_manager->add($code);
        return $code_id;
    }

    // used for importing external participants
    // @todo move to manager/transformation class
    protected function _convertCharset(
        string $a_string,
        string $a_from_charset = "",
        string $a_to_charset = "UTF-8"
    ): string {
        if (extension_loaded("mbstring")) {
            if (!$a_from_charset) {
                mb_detect_order("UTF-8, ISO-8859-1, Windows-1252, ASCII");
                $a_from_charset = mb_detect_encoding($a_string);
            }
            if (strtoupper($a_from_charset) !== $a_to_charset) {
                return mb_convert_encoding($a_string, $a_to_charset, $a_from_charset);
            }
        }
        return $a_string;
    }

    // @todo move to manager/transformation class
    protected function removeUTF8Bom(string $a_text): string
    {
        $bom = pack('H*', 'EFBBBF');
        return preg_replace('/^' . $bom . '/', '', $a_text);
    }

    public function importExternalRecipientsFromFileObject(): void
    {
        if (trim($_FILES['externalmails']['tmp_name'])) {
            $reader = new ilCSVReader();
            $reader->open($_FILES['externalmails']['tmp_name']);
            $data = $reader->getCsvAsArray();
            $fields = array_shift($data);
            foreach ($fields as $idx => $field) {
                $fields[$idx] = $this->removeUTF8Bom($field);
            }
            if (!in_array('email', $fields, true)) {
                $reader->close();
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt('err_external_rcp_no_email'), true);
                $this->ctrl->redirect($this, 'codes');
            }
            $existingdata = $this->object->getExternalCodeRecipients();
            $existingcolumns = array();
            if (count($existingdata)) {
                $first = array_shift($existingdata);
                foreach ($first as $key => $value) {
                    $existingcolumns[] = $key;
                }
            }

            $founddata = array();
            foreach ($data as $row) {
                if (count($row) === count($fields)) {
                    $dataset = array();
                    foreach ($fields as $idx => $fieldname) {
                        // #14811
                        $row[$idx] = $this->_convertCharset($row[$idx]);

                        if (count($existingcolumns)) {
                            if (array_key_exists($idx, $existingcolumns)) {
                                $dataset[$fieldname] = $row[$idx];
                            }
                        } else {
                            $dataset[$fieldname] = $row[$idx];
                        }
                    }
                    if ($dataset['email'] !== '') {
                        $founddata[] = $dataset;
                        $this->addCodeForExternal(
                            $dataset['email'],
                            $dataset['lastname'] ?? "",
                            $dataset['firstname'] ?? ""
                        );
                    }
                }
            }
            $reader->close();

            if (count($founddata)) {
                $this->tpl->setOnScreenMessage('success', $this->lng->txt('external_recipients_imported'), true);
            }
        }

        $this->ctrl->redirect($this, 'codes');
    }

    public function importExternalMailRecipientsFromFileFormObject(): void
    {
        $ilAccess = $this->access;

        $this->handleWriteAccess();
        $this->setParticipantSubTabs("mail_survey_codes");

        $form_import_file = new ilPropertyFormGUI();
        $form_import_file->setFormAction($this->ctrl->getFormAction($this));
        $form_import_file->setTableWidth("100%");
        $form_import_file->setId("codes_import_file");

        $headerfile = new ilFormSectionHeaderGUI();
        $headerfile->setTitle($this->lng->txt("import_from_file"));
        $form_import_file->addItem($headerfile);

        $externalmails = new ilFileInputGUI($this->lng->txt("externalmails"), "externalmails");
        $externalmails->setInfo($this->lng->txt('externalmails_info'));
        $externalmails->setRequired(true);
        $form_import_file->addItem($externalmails);
        if ($ilAccess->checkAccess("write", "", $this->edit_request->getRefId())) {
            $form_import_file->addCommandButton("importExternalRecipientsFromFile", $this->lng->txt("import"));
        }
        if ($ilAccess->checkAccess("write", "", $this->edit_request->getRefId())) {
            $form_import_file->addCommandButton("codes", $this->lng->txt("cancel"));
        }

        $this->tpl->setContent($form_import_file->getHTML());
    }

    public function importExternalMailRecipientsFromTextFormObject(): void
    {
        $ilAccess = $this->access;

        $this->handleWriteAccess();
        $this->setParticipantSubTabs("mail_survey_codes");

        $form_import_text = new ilPropertyFormGUI();
        $form_import_text->setFormAction($this->ctrl->getFormAction($this));
        $form_import_text->setTableWidth("100%");
        $form_import_text->setId("codes_import_text");

        $headertext = new ilFormSectionHeaderGUI();
        $headertext->setTitle($this->lng->txt("import_from_text"));
        $form_import_text->addItem($headertext);

        $inp = new ilTextAreaInputGUI($this->lng->txt('externaltext'), 'externaltext');
        $external_text = $this->edit_manager->getExternalText();
        if ($external_text !== "") {
            $inp->setValue($external_text);
        } else {
            // $this->lng->txt('mail_import_example1') #14897
            $inp->setValue("email;firstname;lastname\n" . $this->lng->txt('mail_import_example2') . "\n" . $this->lng->txt('mail_import_example3') . "\n");
        }
        $inp->setRequired(true);
        $inp->setCols(80);
        $inp->setRows(10);
        $inp->setInfo($this->lng->txt('externaltext_info'));
        $form_import_text->addItem($inp);
        $this->edit_manager->setExternalText("");

        if ($ilAccess->checkAccess("write", "", $this->edit_request->getRefId())) {
            $form_import_text->addCommandButton("importExternalRecipientsFromText", $this->lng->txt("import"));
        }
        if ($ilAccess->checkAccess("write", "", $this->edit_request->getRefId())) {
            $form_import_text->addCommandButton("codes", $this->lng->txt("cancel"));
        }

        $this->tpl->setContent($form_import_text->getHTML());
    }

    //
    // 360°
    //

    public function listAppraiseesObject(): void
    {
        $ilToolbar = $this->toolbar;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $this->handleWriteAccess();

        $this->ctrl->setParameter($this, "appr360", 1);

        ilRepositorySearchGUI::fillAutoCompleteToolbar(
            $this,
            $ilToolbar,
            array(
                'auto_complete_name' => $this->lng->txt('user'),
                'submit_name' => $this->lng->txt('add'),
                'add_search' => true,
                'add_from_container' => $this->ref_id
            )
        );

        // competence calculations
        $skmg_set = new ilSkillManagementSettings();
        if ($this->object->getSkillService() && $skmg_set->isActivated()) {
            $ilToolbar->addSeparator();
            $ilToolbar->addButton(
                $lng->txt("survey_calc_skills"),
                $ilCtrl->getLinkTargetByClass("ilsurveyskilldeterminationgui"),
                ""
            );
        }

        $ilToolbar->addSeparator();
        $ilToolbar->addButton(
            $this->lng->txt('svy_delete_all_user_data'),
            $this->ctrl->getLinkTarget($this, 'deleteAllUserData')
        );

        $this->ctrl->setParameter($this, "appr360", "");

        $tbl = new ilSurveyAppraiseesTableGUI($this, "listAppraisees");
        $tbl->setData($this->object->getAppraiseesData());
        $this->tpl->setContent($tbl->getHTML());
    }

    public function addAppraisee(
        array $a_user_ids
    ): void {
        if (count($a_user_ids)) {
            // #13319
            foreach (array_unique($a_user_ids) as $user_id) {
                $this->object->addAppraisee($user_id);
            }

            $this->tpl->setOnScreenMessage('success', $this->lng->txt("settings_saved"), true);
        }
        $this->ctrl->redirect($this, "listAppraisees");
    }

    public function confirmDeleteAppraiseesObject(): void
    {
        $ilTabs = $this->tabs;

        $appr_ids = $this->edit_request->getAppraiseeIds();
        if (count($appr_ids) === 0) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("select_one"), true);
            $this->ctrl->redirect($this, "listAppraisees");
        }

        $ilTabs->clearTargets();
        $ilTabs->setBackTarget(
            $this->lng->txt("btn_back"),
            $this->ctrl->getLinkTarget($this, "listAppraisees")
        );

        $cgui = new ilConfirmationGUI();
        $cgui->setHeaderText($this->lng->txt("survey_360_sure_delete_appraises"));

        $cgui->setFormAction($this->ctrl->getFormAction($this, "deleteAppraisees"));
        $cgui->setCancel($this->lng->txt("cancel"), "listAppraisees");
        $cgui->setConfirm($this->lng->txt("confirm"), "deleteAppraisees");

        $data = $this->object->getAppraiseesData();

        $count = 0;
        foreach ($appr_ids as $id) {
            if (isset($data[$id]) && !$data[$id]["closed"]) {
                $cgui->addItem("appr_id[]", $id, ilUserUtil::getNamePresentation($id));
                $count++;
            }
        }

        if (!$count) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("select_one"), true);
            $this->ctrl->redirect($this, "listAppraisees");
        }

        $this->tpl->setContent($cgui->getHTML());
    }

    public function deleteAppraiseesObject(): void
    {
        $appr_ids = $this->edit_request->getAppraiseeIds();
        if (count($appr_ids) > 0) {
            $data = $this->object->getAppraiseesData();

            foreach ($appr_ids as $id) {
                // #11285
                if (isset($data[$id]) && !$data[$id]["closed"]) {
                    $this->object->deleteAppraisee($id);
                }
            }

            $this->tpl->setOnScreenMessage('success', $this->lng->txt("settings_saved"), true);
        }

        $this->ctrl->redirect($this, "listAppraisees");
    }

    public function handleRatersAccess(): ?int
    {
        $ilAccess = $this->access;
        $ilUser = $this->user;

        if ($ilAccess->checkAccess("write", "", $this->ref_id)) {
            $appr_id = $this->edit_request->getAppraiseeId();
            if (!$appr_id) {
                $this->ctrl->redirect($this, "listAppraisees");
            }
            return $appr_id;
        } elseif ($this->feature_config->usesAppraisees() &&
            $this->object->get360SelfRaters() &&
            $this->object->isAppraisee($ilUser->getId()) &&
            !$this->object->isAppraiseeClosed($ilUser->getId())) {
            return $ilUser->getId();
        }
        $this->ctrl->redirect($this->parent_gui, "infoScreen");
        return null;
    }

    protected function storeMailSent(): void
    {
        $appr_id = $this->handleRatersAccess();
        $all_data = $this->object->getRatersData($appr_id);

        $recs = json_decode(base64_decode($this->edit_request->getRecipients()));
        foreach ($all_data as $rec_id => $rater) {
            $sent = false;
            if (($rater["login"] != "" && in_array($rater["login"], $recs, true)) ||
                ($rater["email"] != "" && in_array($rater["email"], $recs, true))) {
                $sent = true;
            }
            if ($sent) {
                $this->object->set360RaterSent(
                    $appr_id,
                    strpos($rec_id, "a") === 0 ? 0 : (int) substr($rec_id, 1),
                    strpos($rec_id, "u") === 0 ? 0 : (int) substr($rec_id, 1)
                );
            }
        }
        $this->ctrl->setParameter($this, "appr_id", $appr_id);
        $this->ctrl->redirect($this, "editRaters");
    }

    public function editRatersObject(): void
    {
        if ($this->edit_request->getReturnedFromMail() === 1) {
            $this->storeMailSent();
        }

        $ilTabs = $this->tabs;
        $ilToolbar = $this->toolbar;
        $ilAccess = $this->access;
        $ilTabs->activateTab("survey_360_edit_raters");
        $appr_id = $this->handleRatersAccess();

        $has_write = $ilAccess->checkAccess("write", "", $this->ref_id);
        if ($has_write) {
            $ilTabs->clearTargets();
            $ilTabs->setBackTarget(
                $this->lng->txt("btn_back"),
                $this->ctrl->getLinkTarget($this, "listAppraisees")
            );
        }

        $this->ctrl->setParameter($this, "appr_id", $appr_id);
        $this->ctrl->setParameter($this, "rate360", 1);

        $ilToolbar->addButton(
            $this->lng->txt("svy_add_rater"),
            $this->ctrl->getLinkTargetByClass("ilSurveyRaterGUI", "add")
        );

        // #13320
        $url = ilLink::_getStaticLink($this->object->getRefId());

        $tbl = new ilSurveyAppraiseesTableGUI($this, "editRaters", true, !$this->object->isAppraiseeClosed($appr_id), $url); // #11285
        $tbl->setData($this->object->getRatersData($appr_id));
        $this->tpl->setContent($tbl->getHTML());
    }

    public function addExternalRaterFormObject(
        ilPropertyFormGUI $a_form = null
    ): void {
        $ilTabs = $this->tabs;
        $ilAccess = $this->access;

        $appr_id = $this->handleRatersAccess();
        $this->ctrl->setParameter($this, "appr_id", $appr_id);

        $has_write = $ilAccess->checkAccess("write", "", $this->ref_id);
        if ($has_write) {
            $ilTabs->clearTargets();
            $ilTabs->setBackTarget(
                $this->lng->txt("btn_back"),
                $this->ctrl->getLinkTarget($this, "editRaters")
            );
        }

        if (!$a_form) {
            $a_form = $this->initExternalRaterForm($appr_id);
        }

        $this->tpl->setContent($a_form->getHTML());
    }

    protected function initExternalRaterForm(
        int $appr_id
    ): ilPropertyFormGUI {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, "addExternalRater"));
        $form->setTitle($this->lng->txt("survey_360_add_external_rater") .
            ": " . ilUserUtil::getNamePresentation($appr_id));

        $email = new ilEmailInputGUI($this->lng->txt("email"), "email");
        $email->setRequired(true);
        $form->addItem($email);

        $lname = new ilTextInputGUI($this->lng->txt("lastname"), "lname");
        $lname->setSize(30);
        $form->addItem($lname);

        $fname = new ilTextInputGUI($this->lng->txt("firstname"), "fname");
        $fname->setSize(30);
        $form->addItem($fname);

        $form->addCommandButton("addExternalRater", $this->lng->txt("save"));
        $form->addCommandButton("editRaters", $this->lng->txt("cancel"));

        return $form;
    }

    public function addExternalRaterObject(): void
    {
        $appr_id = $this->edit_request->getAppraiseeId();
        if (!$appr_id) {
            $this->ctrl->redirect($this, "listAppraisees");
        }

        $this->ctrl->setParameter($this, "appr_id", $appr_id);

        $form = $this->initExternalRaterForm($appr_id);
        if ($form->checkInput()) {
            $code_id = $this->addCodeForExternal(
                $form->getInput("email"),
                $form->getInput("lname"),
                $form->getInput("fname")
            );

            $this->object->addRater($appr_id, 0, $code_id);

            $this->tpl->setOnScreenMessage('success', $this->lng->txt("settings_saved"), true);
            $this->ctrl->setParameter($this, "appr_id", $appr_id);
            $this->ctrl->redirect($this, "editRaters");
        }

        $form->setValuesByPost();
        $this->addExternalRaterFormObject($form);
    }

    public function addRater(
        array $a_user_ids
    ): void {
        $ilAccess = $this->access;
        $ilUser = $this->user;

        $appr_id = $this->handleRatersAccess();

        if (count($a_user_ids)) {
            // #13319
            foreach (array_unique($a_user_ids) as $user_id) {
                if ($ilAccess->checkAccess("write", "", $this->ref_id) ||
                    $this->object->get360SelfEvaluation() ||
                    $user_id != $ilUser->getId()) {
                    if ($appr_id != $user_id) {
                        $this->object->addRater($appr_id, $user_id);
                        $this->tpl->setOnScreenMessage('success', $this->lng->txt("settings_saved"), true);
                    } else {
                        $this->tpl->setOnScreenMessage('failure', $this->lng->txt("svy_appraisses_cannot_be_raters"), true);
                    }
                }
            }
        }

        $this->ctrl->setParameter($this, "appr_id", $appr_id);
        $this->ctrl->redirect($this, "editRaters");
    }

    public function confirmDeleteRatersObject(): void
    {
        $ilTabs = $this->tabs;

        $rater_ids = $this->edit_request->getRaterIds();
        $appr_id = $this->handleRatersAccess();
        $this->ctrl->setParameter($this, "appr_id", $appr_id);
        if (count($rater_ids) === 0) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("select_one"), true);
            $this->ctrl->redirect($this, "editRaters");
        }

        $ilTabs->clearTargets();
        $ilTabs->setBackTarget(
            $this->lng->txt("btn_back"),
            $this->ctrl->getLinkTarget($this, "editRaters")
        );

        $cgui = new ilConfirmationGUI();
        $cgui->setHeaderText(sprintf(
            $this->lng->txt("survey_360_sure_delete_raters"),
            ilUserUtil::getNamePresentation($appr_id)
        ));

        $cgui->setFormAction($this->ctrl->getFormAction($this, "deleteRaters"));
        $cgui->setCancel($this->lng->txt("cancel"), "editRaters");
        $cgui->setConfirm($this->lng->txt("confirm"), "deleteRaters");

        $data = $this->object->getRatersData($appr_id);

        foreach ($rater_ids as $id) {
            if (isset($data[$id])) {
                $cgui->addItem("rtr_id[]", $id, $data[$id]["lastname"] . ", " .
                    $data[$id]["firstname"] . " (" . $data[$id]["email"] . ")");
            }
        }

        $this->tpl->setContent($cgui->getHTML());
    }

    public function deleteRatersObject(): void
    {
        $appr_id = $this->handleRatersAccess();
        $this->ctrl->setParameter($this, "appr_id", $appr_id);

        $rater_ids = $this->edit_request->getRaterIds();
        if (count($rater_ids) > 0) {
            $data = $this->object->getRatersData($appr_id);

            foreach ($rater_ids as $id) {
                if (isset($data[$id])) {
                    if (strpos($id, "u") === 0) {
                        $this->object->deleteRater($appr_id, substr($id, 1));
                    } else {
                        $this->object->deleteRater($appr_id, 0, substr($id, 1));
                    }
                }
            }

            $this->tpl->setOnScreenMessage('success', $this->lng->txt("settings_saved"), true);
        }

        $this->ctrl->redirect($this, "editRaters");
    }

    public function addSelfAppraiseeObject(): void
    {
        $ilUser = $this->user;

        if ($this->object->get360SelfAppraisee() &&
            !$this->object->isAppraisee($ilUser->getId())) {
            $this->object->addAppraisee($ilUser->getId());
        }

        $this->ctrl->redirect($this->parent_gui, "infoScreen");
    }

    public function initMailRatersForm(
        int $appr_id,
        array $rec_ids
    ): ilPropertyFormGUI {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, "mailRatersAction"));
        $form->setTitle($this->lng->txt('compose'));

        $all_data = $this->object->getRatersData($appr_id);
        $rec_data = array();
        foreach ($rec_ids as $rec_id) {
            if (isset($all_data[$rec_id])) {
                $rec_data[] = $all_data[$rec_id]["lastname"] . ", " .
                    $all_data[$rec_id]["firstname"] .
                    " (" . $all_data[$rec_id]["email"] . ")";
            }
        }
        sort($rec_data);
        $rec = new ilCustomInputGUI($this->lng->txt('recipients'));
        $rec->setHtml(implode("<br />", $rec_data));
        $form->addItem($rec);

        $subject = new ilTextInputGUI($this->lng->txt('subject'), 'subject');
        $subject->setSize(50);
        $subject->setRequired(true);
        $form->addItem($subject);

        $existingdata = $this->object->getExternalCodeRecipients();
        $existingcolumns = array();
        if (count($existingdata)) {
            $first = array_shift($existingdata);
            foreach ($first as $key => $value) {
                if (strcmp($key, 'code') !== 0 && strcmp($key, 'email') !== 0 && strcmp($key, 'sent') !== 0) {
                    $existingcolumns[] = '[' . $key . ']';
                }
            }
        }

        $mailmessage_u = new ilTextAreaInputGUI($this->lng->txt('survey_360_rater_message_content_registered'), 'message_u');
        $mailmessage_u->setRequired(true);
        $mailmessage_u->setCols(80);
        $mailmessage_u->setRows(10);
        $form->addItem($mailmessage_u);

        $mailmessage_a = new ilTextAreaInputGUI($this->lng->txt('survey_360_rater_message_content_anonymous'), 'message_a');
        $mailmessage_a->setRequired(true);
        $mailmessage_a->setCols(80);
        $mailmessage_a->setRows(10);
        $mailmessage_a->setInfo(sprintf($this->lng->txt('message_content_info'), implode(', ', $existingcolumns)));
        $form->addItem($mailmessage_a);

        $recf = new ilHiddenInputGUI("rater_id");
        $recf->setValue(implode(";", $rec_ids));
        $form->addItem($recf);

        $form->addCommandButton("mailRatersAction", $this->lng->txt("send"));
        $form->addCommandButton("editRaters", $this->lng->txt("cancel"));

        $subject->setValue(sprintf($this->lng->txt('survey_360_rater_subject_default'), $this->object->getTitle()));
        $mailmessage_u->setValue($this->lng->txt('survey_360_rater_message_content_registered_default'));
        $mailmessage_a->setValue($this->lng->txt('survey_360_rater_message_content_anonymous_default'));

        return $form;
    }

    public function mailRatersObject(): void
    {
        $appr_id = $this->handleRatersAccess();
        $all_data = $this->object->getRatersData($appr_id);
        $this->ctrl->setParameter($this, "appr_id", $appr_id);

        $raters = $this->edit_request->getRaterIds();

        $rec = [];
        $external_rater = false;
        $rater_id = "";
        foreach ($raters as $id) {
            if (isset($all_data[$id])) {
                if ($all_data[$id]["login"] != "") {
                    $rec[] = $all_data[$id]["login"];
                } elseif ($all_data[$id]["email"] != "") {
                    $rec[] = $all_data[$id]["email"];
                    $external_rater = true;
                    $rater_id = $all_data[$id]["user_id"];
                }
            }
        }
        if ($external_rater && count($rec) > 1) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("svy_only_max_one_external_rater"), true);
            $this->ctrl->redirect($this, "editRaters");
        }

        // $_POST["rtr_id"]
        ilMailFormCall::setRecipients($rec);

        $contextParameters = [
            'ref_id' => $this->object->getRefId(),
            'ts' => time(),
            'appr_id' => $appr_id,
            'rater_id' => $rater_id,
            ilMailFormCall::CONTEXT_KEY => "svy_rater_inv"
        ];

        $this->ctrl->redirectToURL(ilMailFormCall::getRedirectTarget(
            $this,
            'editRaters',
            [
                'recipients' => base64_encode(json_encode($rec, JSON_THROW_ON_ERROR)),
            ],
            [
                'type' => 'new',
                'sig' => rawurlencode(base64_encode("\n\n" . $this->lng->txt("svy_link_to_svy") . ": [SURVEY_LINK]"))
            ],
            $contextParameters
        ));
    }

    public function mailRatersObjectOld(
        ilPropertyFormGUI $a_form = null
    ): void {
        $ilTabs = $this->tabs;
        $rater_ids = $this->edit_request->getRaterIds();
        if (!$a_form) {
            $appr_id = $this->handleRatersAccess();
            $this->ctrl->setParameter($this, "appr_id", $appr_id);

            if (count($rater_ids) === 0) {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt("select_one"), true);
                $this->ctrl->redirect($this, "editRaters");
            }

            $a_form = $this->initMailRatersForm($appr_id, $rater_ids);
        }

        $ilTabs->clearTargets();
        $ilTabs->setBackTarget(
            $this->lng->txt("btn_back"),
            $this->ctrl->getLinkTarget($this, "editRaters")
        );

        $this->tpl->setContent($a_form->getHTML());
    }

    public function mailRatersActionObject(): void
    {
        $ilUser = $this->user;
        $appr_id = $this->handleRatersAccess();
        $this->ctrl->setParameter($this, "appr_id", $appr_id);

        $rec_ids = $this->edit_request->getRaterIds();
        if (count($rec_ids) === 0) {
            $this->ctrl->redirect($this, "editRaters");
        }

        $form = $this->initMailRatersForm($appr_id, $rec_ids);
        if ($form->checkInput()) {
            $txt_u = $form->getInput("message_u");
            $txt_a = $form->getInput("message_a");
            $subj = $form->getInput("subject");

            // #12743
            $sender_id = (trim($ilUser->getEmail()))
                ? $ilUser->getId()
                : ANONYMOUS_USER_ID;

            $all_data = $this->object->getRatersData($appr_id);
            foreach ($rec_ids as $rec_id) {
                if (isset($all_data[$rec_id])) {
                    $user = $all_data[$rec_id];

                    // anonymous
                    if (strpos($rec_id, "a") === 0) {
                        $mytxt = $txt_a;
                        $url = $user["href"];
                        $rcp = $user["email"];
                    }
                    // reg
                    else {
                        $mytxt = $txt_u;
                        $user["code"] = $this->lng->txt("survey_code_mail_on_demand");
                        $url = ilLink::_getStaticLink($this->object->getRefId());
                        $rcp = $user["login"]; // #15141
                    }

                    $mytxt = str_replace(
                        ["[lastname]", "[firstname]", "[url]", "[code]"],
                        [$user["lastname"], $user["firstname"], $url, $user["code"]],
                        $mytxt
                    );

                    $mail = new ilMail($sender_id);
                    $mail->enqueue(
                        $rcp, // to
                        "", // cc
                        "", // bcc
                        $subj, // subject
                        $mytxt, // message
                        array() // attachments
                    );

                    $this->object->set360RaterSent(
                        $appr_id,
                        (strpos($rec_id, "a") === 0) ? 0 : (int) substr($rec_id, 1),
                        (strpos($rec_id, "u") === 0) ? 0 : (int) substr($rec_id, 1)
                    );
                }
            }

            $this->tpl->setOnScreenMessage('success', $this->lng->txt("mail_sent"), true);
            $this->ctrl->redirect($this, "editRaters");
        }

        $form->setValuesByPost();
        $this->mailRatersObject();
    }

    public function confirmAppraiseeCloseObject(): void
    {
        $ilUser = $this->user;
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;

        $ilTabs->clearTargets();
        $ilTabs->setBackTarget(
            $this->lng->txt("menuback"),
            $this->ctrl->getLinkTarget($this->parent_gui, "infoScreen")
        );

        if (!$this->object->isAppraisee($ilUser->getId())) {
            $this->ctrl->redirect($this->parent_gui, "infoScreen");
        }

        $cgui = new ilConfirmationGUI();
        $cgui->setHeaderText($this->lng->txt("survey_360_sure_appraisee_close"));

        $cgui->setFormAction($this->ctrl->getFormAction($this, "appraiseeClose"));
        $cgui->setCancel($this->lng->txt("cancel"), "confirmAppraiseeCloseCancel");
        $cgui->setConfirm($this->lng->txt("confirm"), "appraiseeClose");

        $tpl->setContent($cgui->getHTML());
    }

    public function confirmAppraiseeCloseCancelObject(): void
    {
        $this->ctrl->redirect($this->parent_gui, "infoScreen");
    }

    public function appraiseeCloseObject(): void
    {
        $ilUser = $this->user;

        if (!$this->object->isAppraisee($ilUser->getId())) {
            $this->ctrl->redirect($this->parent_gui, "infoScreen");
        }

        $this->object->closeAppraisee($ilUser->getId());
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("survey_360_appraisee_close_action_success"), true);
        $this->ctrl->redirect($this->parent_gui, "infoScreen");
    }

    public function confirmAdminAppraiseesCloseObject(): void
    {
        $tpl = $this->tpl;

        $this->handleWriteAccess();

        $appr_ids = $this->edit_request->getAppraiseeIds();

        if (count($appr_ids) === 0) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("select_one"), true);
            $this->ctrl->redirect($this, "listAppraisees");
        }

        $cgui = new ilConfirmationGUI();
        $cgui->setHeaderText($this->lng->txt("survey_360_sure_appraisee_close_admin"));

        $cgui->setFormAction($this->ctrl->getFormAction($this, "adminAppraiseesClose"));
        $cgui->setCancel($this->lng->txt("cancel"), "listAppraisees");
        $cgui->setConfirm($this->lng->txt("confirm"), "adminAppraiseesClose");

        foreach ($appr_ids as $appr_id) {
            $cgui->addItem("appr_id[]", $appr_id, ilUserUtil::getNamePresentation($appr_id));
        }

        $tpl->setContent($cgui->getHTML());
    }

    public function adminAppraiseesCloseObject(): void
    {
        $this->handleWriteAccess();

        $appr_ids = $this->edit_request->getAppraiseeIds();

        if (count($appr_ids) === 0) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("select_one"), true);
            $this->ctrl->redirect($this, "listAppraisees");
        }

        $appr_data = $this->object->getAppraiseesData();
        foreach ($appr_ids as $appr_id) {
            if (isset($appr_data[$appr_id]) && !$appr_data[$appr_id]["closed"]) {
                $this->object->closeAppraisee($appr_id);
            }
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("survey_360_appraisee_close_action_success_admin"), true);
        $this->ctrl->redirect($this, "listAppraisees");
    }

    protected function listParticipantsObject(): void
    {
        $ilToolbar = $this->toolbar;

        if (!$this->isAnonymousListActive()) {
            $this->ctrl->redirect($this, "maintenance");
        }

        $this->handleWriteAccess();
        $this->setParticipantSubTabs("anon_participants");

        $button = ilLinkButton::getInstance();
        $button->setCaption("print");
        $button->setOnClick("window.print(); return false;");
        $button->setOmitPreventDoubleSubmission(true);
        $ilToolbar->addButtonInstance($button);

        $tbl = new ilSurveyParticipantsTableGUI($this, "listParticipants", $this->object);
        $this->tpl->setContent($tbl->getHTML());
    }

    /**
     * @param int[] $user_ids
     * @throws ilCtrlException
     */
    public function inviteUsers(array $user_ids): void
    {
        $lng = $this->lng;
        $ctrl = $this->ctrl;

        foreach ($user_ids as $user_id) {
            $this->invitation_manager->add($this->object->getSurveyId(), $user_id);
        }
        $this->tpl->setOnScreenMessage('success', $lng->txt("svy_users_invited"), true);
        $ctrl->redirect($this, "maintenance");
    }
}

<?php declare(strict_types = 1);

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

namespace ILIAS\Survey\Settings;

use ILIAS\Survey\InternalGUIService;
use ILIAS\Survey\Mode\UIModifier;
use ILIAS\Survey\InternalDomainService;

/**
 * Settings form
 * @author Alexander Killing <killing@leifos.de>
 */
class SettingsFormGUI
{
    protected InternalGUIService $ui_service;
    protected \ilObjectServiceInterface $object_service;
    protected \ilObjSurvey $survey;
    protected UIModifier $modifier;
    protected InternalDomainService $domain_service;
    protected \ILIAS\Survey\Mode\FeatureConfig $feature_config;
    protected \ilRbacSystem $rbacsystem;
    private \ilGlobalTemplateInterface $main_tpl;

    public function __construct(
        InternalGUIService $ui_service,
        InternalDomainService $domain_service,
        \ilObjectServiceInterface $object_service,
        \ilObjSurvey $survey,
        UIModifier $modifier
    ) {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();

        $this->rbacsystem = $DIC->rbac()->system();

        $this->survey = $survey;
        $this->object_service = $object_service;
        $this->ui_service = $ui_service;
        $this->domain_service = $domain_service;
        $this->modifier = $modifier;
        $this->feature_config = $this->domain_service->modeFeatureConfig($survey->getMode());
    }

    public function checkForm(\ilPropertyFormGUI $form) : bool
    {
        $feature_config = $this->feature_config;
        $lng = $this->ui_service->lng();

        $valid = false;

        if ($form->checkInput()) {
            $valid = true;

            if ($feature_config->supportsTutorNotification()) {
                if ($form->getInput("tut")) {
                    $tut_ids = $this->getTutorIdsFromForm($form);
                    // check if given "tutors" have write permission
                    if (!$tut_ids) {
                        $tut_ids = $form->getItemByPostVar("tut_ids");
                        $tut_ids->setAlert($lng->txt("survey_notification_tutor_recipients_invalid"));
                        $valid = false;
                    }
                }
                if ($form->getInput("tut_res")) {
                    $end = $form->getItemByPostVar("end_date");
                    if (!$end->getDate()) {
                        $tut_res = $form->getItemByPostVar("tut_res");
                        $tut_res->setAlert($lng->txt("svy_notification_tutor_results_alert"));
                        $valid = false;
                    }

                    // check if given "tutors" have write permission
                    $tut_res_ids = $this->getTutorResIdsFromForm($form);
                    if (!$tut_res_ids) {
                        $tut_res_ids = $form->getItemByPostVar("tut_res_ids");
                        $tut_res_ids->setAlert($lng->txt("survey_notification_tutor_recipients_invalid"));
                        $valid = false;
                    }
                }
            }
        }

        return $valid;
    }

    /**
     * @param \ilPropertyFormGUI $form
     * @return array
     */
    protected function getTutorIdsFromForm(\ilPropertyFormGUI $form) : array
    {
        $rbacsystem = $this->rbacsystem;
        $survey = $this->survey;

        $tut_ids = array();
        $tut_logins = $form->getInput("tut_ids");
        foreach ($tut_logins as $tut_login) {
            $tut_id = \ilObjUser::_lookupId($tut_login);
            if ($tut_id && $rbacsystem->checkAccessOfUser($tut_id, "write", $survey->getRefId())) {
                $tut_ids[] = $tut_id;
            }
        }
        return $tut_ids;
    }

    /**
     * @param \ilPropertyFormGUI $form
     * @return array
     */
    protected function getTutorResIdsFromForm(\ilPropertyFormGUI $form) : array
    {
        $rbacsystem = $this->rbacsystem;
        $survey = $this->survey;

        $tut_res_ids = array();
        $tut_logins = $form->getInput("tut_res_ids");
        foreach ($tut_logins as $tut_login) {
            $tut_id = \ilObjUser::_lookupId($tut_login);
            if ($tut_id && $rbacsystem->checkAccessOfUser($tut_id, "write", $survey->getRefId())) {
                $tut_res_ids[] = $tut_id;
            }
        }
        return $tut_res_ids;
    }

    /**
     * @throws \ilCtrlException
     * @throws \ilDateTimeException
     */
    public function getForm(
        string $target_class
    ) : \ilPropertyFormGUI {
        $ctrl = $this->ui_service->ctrl();
        $lng = $this->ui_service->lng();

        $form = new \ilPropertyFormGUI();
        $form->setFormAction($ctrl->getFormActionByClass(
            $target_class
        ));
        $form->setId("survey_properties");

        $form = $this->withGeneral($form);
        $form = $this->withActivation($form);
        $form = $this->withPresentation($form);
        $form = $this->withBeforeStart($form);
        $form = $this->withAccess($form);
        $form = $this->withQuestionBehaviour($form);
        $form = $this->withAfterEnd($form, $target_class);
        $form = $this->withReminders($form);
        $form = $this->withResults($form);
        $form = $this->withOther($form);

        $form->addCommandButton("saveProperties", $lng->txt("save"));

        return $form;
    }

    /**
     * add general section
     */
    public function withGeneral(
        \ilPropertyFormGUI $form
    ) : \ilPropertyFormGUI {
        $survey = $this->survey;
        $lng = $this->ui_service->lng();
        $feature_config = $this->feature_config;

        // general properties
        $header = new \ilFormSectionHeaderGUI();
        $header->setTitle($lng->txt("settings"));
        $form->addItem($header);

        // title & description (meta data)
        //$md_obj = new ilMD($this->object->getId(), 0, "svy");
        //$md_section = $md_obj->getGeneral();

        $title = new \ilTextInputGUI($lng->txt("title"), "title");
        $title->setRequired(true);
        $title->setValue($survey->getTitle());
        $form->addItem($title);

        //$ids = $md_section->getDescriptionIds();
        //if ($ids) {
        //    $desc_obj = $md_section->getDescription(array_pop($ids));

        $desc = new \ilTextAreaInputGUI($lng->txt("description"), "description");
        $desc->setRows(4);
        $desc->setValue($survey->getLongDescription());
        $form->addItem($desc);
        //}

        // pool usage
        $pool_usage = new \ilRadioGroupInputGUI($lng->txt("survey_question_pool_usage"), "use_pool");
        $opt = new \ilRadioOption($lng->txt("survey_question_pool_usage_active"), "1");
        $opt->setInfo($lng->txt("survey_question_pool_usage_active_info"));
        $pool_usage->addOption($opt);
        $opt = new \ilRadioOption($lng->txt("survey_question_pool_usage_inactive"), "0");
        $opt->setInfo($lng->txt("survey_question_pool_usage_inactive_info"));
        $pool_usage->addOption($opt);
        $pool_usage->setValue((string) (int) $survey->getPoolUsage());
        $form->addItem($pool_usage);

        if ($feature_config->usesAppraisees()) {
            $self_rate = new \ilCheckboxInputGUI($lng->txt("survey_360_self_raters"), "self_rate");
            $self_rate->setInfo($lng->txt("survey_360_self_raters_info"));
            $self_rate->setChecked($survey->get360SelfRaters());
            $form->addItem($self_rate);

            $self_appr = new \ilCheckboxInputGUI($lng->txt("survey_360_self_appraisee"), "self_appr");
            $self_appr->setInfo($lng->txt("survey_360_self_appraisee_info"));
            $self_appr->setChecked($survey->get360SelfAppraisee());
            $form->addItem($self_appr);
        }

        foreach ($this->modifier->getSurveySettingsGeneral($survey) as $item) {
            $form->addItem($item);
        }
        return $form;
    }

    /**
     * add activation section
     * @throws \ilDateTimeException
     */
    public function withActivation(
        \ilPropertyFormGUI $form
    ) : \ilPropertyFormGUI {
        $lng = $this->ui_service->lng();
        $survey = $this->survey;

        // activation
        $lng->loadLanguageModule('rep');

        $section = new \ilFormSectionHeaderGUI();
        $section->setTitle($lng->txt('rep_activation_availability'));
        $form->addItem($section);

        // additional info only with multiple references
        $act_obj_info = $act_ref_info = "";
        if (count(\ilObject::_getAllReferences($survey->getId())) > 1) {
            $act_obj_info = ' ' . $lng->txt('rep_activation_online_object_info');
            $act_ref_info = $lng->txt('rep_activation_access_ref_info');
        }

        $online = new \ilCheckboxInputGUI($lng->txt('rep_activation_online'), 'online');
        $online->setInfo($lng->txt('svy_activation_online_info') . $act_obj_info);
        $online->setChecked(!$survey->getOfflineStatus());
        $form->addItem($online);

        $dur = new \ilDateDurationInputGUI($lng->txt('rep_visibility_until'), "access_period");
        $dur->setShowTime(true);
        $date = $survey->getActivationStartDate();
        $dur->setStart($date
            ? new \ilDateTime($date, IL_CAL_UNIX)
            : null);
        $date = $survey->getActivationEndDate();
        $dur->setEnd($date
            ? new \ilDateTime($date, IL_CAL_UNIX)
            : null);
        $form->addItem($dur);

        $visible = new \ilCheckboxInputGUI($lng->txt('rep_activation_limited_visibility'), 'access_visiblity');
        $visible->setInfo($lng->txt('svy_activation_limited_visibility_info'));
        $visible->setChecked($survey->getActivationVisibility());
        $dur->addSubItem($visible);

        return $form;
    }

    /**
     * add presentation section
     */
    public function withPresentation(
        \ilPropertyFormGUI $form
    ) : \ilPropertyFormGUI {
        $obj_service = $this->object_service;
        $survey = $this->survey;
        $lng = $this->ui_service->lng();

        // presentation
        $section = new \ilFormSectionHeaderGUI();
        $section->setTitle($lng->txt('obj_presentation'));
        $form->addItem($section);

        // tile image
        $obj_service->commonSettings()->legacyForm($form, $survey)->addTileImage();

        return $form;
    }

    /**
     * add "before start" section
     */
    public function withBeforeStart(
        \ilPropertyFormGUI $form
    ) : \ilPropertyFormGUI {
        $survey = $this->survey;
        $lng = $this->ui_service->lng();

        $section = new \ilFormSectionHeaderGUI();
        $section->setTitle($lng->txt('svy_settings_section_before_start'));
        $form->addItem($section);

        // introduction
        $intro = new \ilTextAreaInputGUI($lng->txt("introduction"), "introduction");
        $intro->setValue($survey->prepareTextareaOutput($survey->getIntroduction()));
        $intro->setRows(10);
        $intro->setCols(80);
        $intro->setUseRte(true);
        $intro->setInfo($lng->txt("survey_introduction_info"));
        $intro->setRteTags(\ilObjAdvancedEditing::_getUsedHTMLTags("survey"));
        $intro->addPlugin("latex");
        $intro->addButton("latex");
        $intro->addButton("pastelatex");
        $intro->setRTESupport($survey->getId(), "svy", "survey", null);
        $form->addItem($intro);

        return $form;
    }

    /**
     * add access section
     */
    public function withAccess(
        \ilPropertyFormGUI $form
    ) : \ilPropertyFormGUI {
        $survey = $this->survey;
        $lng = $this->ui_service->lng();
        $feature_config = $this->feature_config;

        // access

        $section = new \ilFormSectionHeaderGUI();
        $section->setTitle($lng->txt('svy_settings_section_access'));
        $form->addItem($section);

        // enable start date
        $start = $survey->getStartDate();
        // start date
        $startingtime = new \ilDateTimeInputGUI($lng->txt("start_date"), 'start_date');
        $startingtime->setShowTime(true);
        if ($start) {
            $startingtime->setDate(new \ilDate($start, IL_CAL_TIMESTAMP));
        }
        $form->addItem($startingtime);

        // enable end date
        $end = $survey->getEndDate();
        // end date
        $endingtime = new \ilDateTimeInputGUI($lng->txt("end_date"), 'end_date');
        $endingtime->setShowTime(true);
        if ($end) {
            $endingtime->setDate(new \ilDate($end, IL_CAL_TIMESTAMP));
        }
        $form->addItem($endingtime);

        // anonymization
        if ($feature_config->supportsAccessCodes()) {
            $codes = new \ilCheckboxInputGUI($lng->txt("survey_access_codes"), "acc_codes");
            $codes->setInfo($lng->txt("survey_access_codes_info"));
            $codes->setChecked(!$survey->isAccessibleWithoutCode());
            $form->addItem($codes);

            if (\ilObjSurvey::_hasDatasets($survey->getSurveyId())) {
                $codes->setDisabled(true);
            }
        }

        return $form;
    }

    /**
     * add question behaviour section
     */
    public function withQuestionBehaviour(
        \ilPropertyFormGUI $form
    ) : \ilPropertyFormGUI {
        $survey = $this->survey;
        $lng = $this->ui_service->lng();

        // question behaviour

        $section = new \ilFormSectionHeaderGUI();
        $section->setTitle($lng->txt('svy_settings_section_question_behaviour'));
        $form->addItem($section);

        // show question titles
        $show_question_titles = new \ilCheckboxInputGUI($lng->txt("svy_show_questiontitles"), "show_question_titles");
        $show_question_titles->setValue("1");
        $show_question_titles->setChecked($survey->getShowQuestionTitles());
        $form->addItem($show_question_titles);

        return $form;
    }

    /**
     * add "after ending" section
     */
    public function withAfterEnd(
        \ilPropertyFormGUI $form,
        string $target_class
    ) : \ilPropertyFormGUI {
        $survey = $this->survey;
        $lng = $this->ui_service->lng();
        $feature_config = $this->feature_config;
        $ctrl = $this->ui_service->ctrl();
        $invitation_manager = $this->domain_service->participants()->invitations();

        // finishing

        $info = new \ilFormSectionHeaderGUI();
        $info->setTitle($lng->txt("svy_settings_section_finishing"));
        $form->addItem($info);

        $view_own = new \ilCheckboxInputGUI($lng->txt("svy_results_view_own"), "view_own");
        $view_own->setInfo($lng->txt("svy_results_view_own_info"));
        $view_own->setChecked($survey->hasViewOwnResults());
        $form->addItem($view_own);

        $mail_confirm = new \ilCheckboxInputGUI($lng->txt("svy_results_mail_confirm"), "mail_confirm");
        $mail_confirm->setInfo($lng->txt("svy_results_mail_confirm_info"));
        $mail_confirm->setChecked($survey->hasMailConfirmation());
        $form->addItem($mail_confirm);

        $mail_own = new \ilCheckboxInputGUI($lng->txt("svy_results_mail_own"), "mail_own");
        $mail_own->setInfo($lng->txt("svy_results_mail_own_info"));
        $mail_own->setChecked($survey->hasMailOwnResults());
        $mail_confirm->addSubItem($mail_own);

        // final statement
        $finalstatement = new \ilTextAreaInputGUI($lng->txt("outro"), "outro");
        $finalstatement->setValue($survey->prepareTextareaOutput($survey->getOutro()));
        $finalstatement->setRows(10);
        $finalstatement->setCols(80);
        $finalstatement->setUseRte(true);
        $finalstatement->setRteTags(\ilObjAdvancedEditing::_getUsedHTMLTags("survey"));
        $finalstatement->addPlugin("latex");
        $finalstatement->addButton("latex");
        $finalstatement->addButton("pastelatex");
        $finalstatement->setRTESupport($survey->getId(), "svy", "survey", null);
        $form->addItem($finalstatement);

        // mail notification
        $mailnotification = new \ilCheckboxInputGUI($lng->txt("mailnotification"), "mailnotification");
        // $mailnotification->setOptionTitle($lng->txt("activate"));
        $mailnotification->setInfo($lng->txt("svy_result_mail_notification_info")); // #11762
        $mailnotification->setValue("1");
        $mailnotification->setChecked($survey->getMailNotification());

        // addresses
        $mailaddresses = new \ilTextInputGUI($lng->txt("mailaddresses"), "mailaddresses");
        $mailaddresses->setValue($survey->getMailAddresses());
        $mailaddresses->setSize(80);
        $mailaddresses->setInfo($lng->txt('mailaddresses_info'));
        $mailaddresses->setRequired(true);

        // participant data
        $participantdata = new \ilTextAreaInputGUI($lng->txt("mailparticipantdata"), "mailparticipantdata");
        $participantdata->setValue($survey->getMailParticipantData());
        $participantdata->setRows(6);
        $participantdata->setCols(80);
        $participantdata->setUseRte(false);
        $participantdata->setInfo($lng->txt("mailparticipantdata_info"));

        // #12755 - because of privacy concerns we restrict user data to a minimum
        $placeholders = array(
            "FIRST_NAME" => "firstname",
            "LAST_NAME" => "lastname",
            "LOGIN" => "login"
        );
        $txt = array();
        foreach ($placeholders as $placeholder => $caption) {
            $txt[] = "[" . strtoupper($placeholder) . "]: " . $lng->txt($caption);
        }
        $txt = implode("<br />", $txt);
        $participantdatainfo = new \ilNonEditableValueGUI($lng->txt("mailparticipantdata_placeholder"), "", true);
        $participantdatainfo->setValue($txt);

        $mailnotification->addSubItem($mailaddresses);
        $mailnotification->addSubItem($participantdata);
        $mailnotification->addSubItem($participantdatainfo);
        $form->addItem($mailnotification);

        // tutor notification - currently not available for 360°
        if ($feature_config->supportsTutorNotification()) {
            $num_inv = count($invitation_manager->getAllForSurvey($survey->getSurveyId()));

            // notification
            $tut = new \ilCheckboxInputGUI($lng->txt("survey_notification_tutor_setting"), "tut");
            $tut->setChecked($survey->getTutorNotificationStatus());
            $form->addItem($tut);

            $tut_logins = array();
            $tuts = $survey->getTutorNotificationRecipients();
            if ($tuts) {
                foreach ($tuts as $tut_id) {
                    $tmp = \ilObjUser::_lookupName((int) $tut_id);
                    if ($tmp["login"]) {
                        $tut_logins[] = $tmp["login"];
                    }
                }
            }
            $tut_ids = new \ilTextInputGUI($lng->txt("survey_notification_tutor_recipients"), "tut_ids");
            $tut_ids->setDataSource($ctrl->getLinkTargetByClass($target_class, "doAutoComplete", "", true));
            $tut_ids->setRequired(true);
            $tut_ids->setMulti(true);
            $tut_ids->setMultiValues($tut_logins);
            $tut_ids->setValue(array_shift($tut_logins));
            $tut->addSubItem($tut_ids);

            $tut_grp = new \ilRadioGroupInputGUI($lng->txt("survey_notification_target_group"), "tut_grp");
            $tut_grp->setRequired(true);
            $tut_grp->setValue((string) $survey->getTutorNotificationTarget());
            $tut->addSubItem($tut_grp);

            $tut_grp_crs = new \ilRadioOption(
                $lng->txt("survey_notification_target_group_parent_course"),
                (string) \ilObjSurvey::NOTIFICATION_PARENT_COURSE
            );
            if (!$this->hasGroupCourseParent()) {
                $tut_grp_crs->setInfo($lng->txt("survey_notification_target_group_parent_course_inactive"));
            } else {
                $tut_grp_crs->setInfo(sprintf(
                    $lng->txt("survey_notification_target_group_invited_info"),
                    count($survey->getNotificationTargetUserIds(false))
                ));
            }
            $tut_grp->addOption($tut_grp_crs);

            $tut_grp_inv = new \ilRadioOption(
                $lng->txt("survey_notification_target_group_invited"),
                (string) \ilObjSurvey::NOTIFICATION_INVITED_USERS
            );
            $tut_grp_inv->setInfo(sprintf($lng->txt("survey_notification_target_group_invited_info"), $num_inv));
            $tut_grp->addOption($tut_grp_inv);

            /*
            $tut_res = new \ilCheckboxInputGUI($lng->txt("svy_notification_tutor_results"), "tut_res");
            $tut_res->setInfo($lng->txt("svy_notification_tutor_results_info"));
            $tut_res->setChecked($survey->getTutorResultsStatus());
            $form->addItem($tut_res);

            $tut_res_logins = array();
            $tuts = $survey->getTutorResultsRecipients();
            if ($tuts) {
                foreach ($tuts as $tut_id) {
                    $tmp = \ilObjUser::_lookupName((int) $tut_id);
                    if ($tmp["login"]) {
                        $tut_res_logins[] = $tmp["login"];
                    }
                }
            }
            $tut_res_ids = new \ilTextInputGUI($lng->txt("survey_notification_tutor_recipients"), "tut_res_ids");
            $tut_res_ids->setDataSource(
                $ctrl->getLinkTargetByClass(
                    $target_class,
                    "doAutoComplete",
                    "",
                    true
                )
            );
            $tut_res_ids->setRequired(true);
            $tut_res_ids->setMulti(true);
            $tut_res_ids->setMultiValues($tut_res_logins);
            $tut_res_ids->setValue(array_shift($tut_res_logins));
            $tut_res->addSubItem($tut_res_ids);*/
        }

        return $form;
    }

    /**
     * Check for group course parent
     */
    protected function hasGroupCourseParent() : bool
    {
        $survey = $this->survey;

        $tree = $this->domain_service->repositoryTree();
        $has_parent = $tree->checkForParentType($survey->getRefId(), "grp");
        if (!$has_parent) {
            $has_parent = $tree->checkForParentType($survey->getRefId(), "crs");
        }
        return (bool) $has_parent;
    }

    /**
     * add reminders section
     */
    public function withReminders(
        \ilPropertyFormGUI $form
    ) : \ilPropertyFormGUI {
        $survey = $this->survey;
        $lng = $this->ui_service->lng();
        $feature_config = $this->feature_config;
        $invitation_manager = $this->domain_service->participants()->invitations();

        // reminders

        $info = new \ilFormSectionHeaderGUI();
        $info->setTitle($lng->txt("svy_settings_section_reminders"));
        $form->addItem($info);

        $rmd = new \ilCheckboxInputGUI($lng->txt("survey_reminder_setting"), "rmd");
        $rmd->setChecked($survey->getReminderStatus());
        $form->addItem($rmd);

        $rmd_start = new \ilDateTimeInputGUI($lng->txt("survey_reminder_start"), "rmd_start");
        $rmd_start->setRequired(true);
        $start = $survey->getReminderStart();
        if ($start) {
            $rmd_start->setDate($start);
        }
        $rmd->addSubItem($rmd_start);

        $end = $survey->getReminderEnd();
        $rmd_end = new \ilDateTimeInputGUI($lng->txt("survey_reminder_end"), "rmd_end");
        if ($end) {
            $rmd_end->setDate($end);
        }
        $rmd->addSubItem($rmd_end);

        $rmd_freq = new \ilNumberInputGUI($lng->txt("survey_reminder_frequency"), "rmd_freq");
        $rmd_freq->setRequired(true);
        $rmd_freq->setSize(3);
        $rmd_freq->setSuffix($lng->txt("survey_reminder_frequency_days"));
        $rmd_freq->setValue((string) $survey->getReminderFrequency());
        $rmd_freq->setMinValue(1);
        $rmd->addSubItem($rmd_freq);

        if ($feature_config->supportsMemberReminder()) {
            $rmd_grp = new \ilRadioGroupInputGUI($lng->txt("survey_notification_target_group"), "rmd_grp");
            $rmd_grp->setRequired(true);
            $rmd_grp->setValue((string) $survey->getReminderTarget());
            $rmd->addSubItem($rmd_grp);

            $rmd_grp_crs = new \ilRadioOption(
                $lng->txt("survey_notification_target_group_parent_course"),
                (string) \ilObjSurvey::NOTIFICATION_PARENT_COURSE
            );
            if (!$this->hasGroupCourseParent()) {
                $rmd_grp_crs->setInfo($lng->txt("survey_notification_target_group_parent_course_inactive"));
            } else {
                $rmd_grp_crs->setInfo(sprintf(
                    $lng->txt("survey_notification_target_group_invited_info"),
                    count($survey->getNotificationTargetUserIds(false))
                ));
            }
            $rmd_grp->addOption($rmd_grp_crs);

            $rmd_grp_inv = new \ilRadioOption(
                $lng->txt("survey_notification_target_group_invited"),
                (string) \ilObjSurvey::NOTIFICATION_INVITED_USERS
            );
            $num_inv = count($invitation_manager->getAllForSurvey($survey->getSurveyId()));
            $rmd_grp_inv->setInfo(sprintf($lng->txt("survey_notification_target_group_invited_info"), $num_inv));
            $rmd_grp->addOption($rmd_grp_inv);

            $mtmpl = $survey->getReminderMailTemplates();
            if ($mtmpl) {
                $rmdt = new \ilRadioGroupInputGUI($lng->txt("svy_reminder_mail_template"), "rmdt");
                $rmdt->setRequired(true);
                $rmdt->addOption(new \ilRadioOption($lng->txt("svy_reminder_mail_template_none"), "-1"));
                foreach ($mtmpl as $mtmpl_id => $mtmpl_caption) {
                    $option = new \ilRadioOption($mtmpl_caption, (string) $mtmpl_id);
                    $rmdt->addOption($option);
                }

                $reminderTemplateValue = -1;
                if ($survey->getReminderTemplate()) {
                    $reminderTemplateValue = $survey->getReminderTemplate();
                }
                $rmdt->setValue((string) $reminderTemplateValue);
                $rmd->addSubItem($rmdt);
            }
        }

        foreach ($this->modifier->getSurveySettingsReminderTargets(
            $survey,
            $this->ui_service
        ) as $item) {
            $rmd->addSubItem($item);
        }

        return $form;
    }

    /**
     * add results section
     */
    public function withResults(
        \ilPropertyFormGUI $form
    ) : \ilPropertyFormGUI {
        $lng = $this->ui_service->lng();
        $survey = $this->survey;
        $feature_config = $this->feature_config;

        // results

        $results = new \ilFormSectionHeaderGUI();
        $results->setTitle($lng->txt("results"));
        $form->addItem($results);

        if ($feature_config->supportsSumScore()) {
            // calculate sum score
            $sum_score = new \ilCheckboxInputGUI($lng->txt("survey_calculate_sum_score"), "calculate_sum_score");
            $sum_score->setInfo($lng->txt("survey_calculate_sum_score_info"));
            $sum_score->setValue("1");
            $sum_score->setChecked($survey->getCalculateSumScore());
            $form->addItem($sum_score);
        }

        foreach ($this->modifier->getSurveySettingsResults(
            $survey,
            $this->ui_service
        ) as $item) {
            $form->addItem($item);
        }

        return $form;
    }

    /**
     * add "other" section
     */
    public function withOther(
        \ilPropertyFormGUI $form
    ) : \ilPropertyFormGUI {
        $lng = $this->ui_service->lng();
        $survey = $this->survey;
        $feature_config = $this->feature_config;

        $other_items = [];

        // competence service activation for 360 mode

        $skmg_set = new \ilSkillManagementSettings();

        if ($feature_config->supportsCompetences() && $skmg_set->isActivated()) {
            $skill_service = new \ilCheckboxInputGUI($lng->txt("survey_activate_skill_service"), "skill_service");
            $skill_service->setInfo($lng->txt("survey_activate_skill_service_info"));
            $skill_service->setChecked($survey->getSkillService());
            $other_items[] = $skill_service;
        }

        $position_settings = \ilOrgUnitGlobalSettings::getInstance()
            ->getObjectPositionSettingsByType($survey->getType());

        if (count($other_items) > 0 ||
            $position_settings->isActive()
        ) {
            $feat = new \ilFormSectionHeaderGUI();
            $feat->setTitle($lng->txt('obj_features'));
            $form->addItem($feat);

            foreach ($other_items as $item) {
                $form->addItem($item);
            }
        }

        if ($position_settings->isActive()) {
            // add orgunit settings
            \ilObjectServiceSettingsGUI::initServiceSettingsForm(
                $survey->getId(),
                $form,
                array(
                    \ilObjectServiceSettingsGUI::ORGU_POSITION_ACCESS
                )
            );
        }

        return $form;
    }

    public function saveForm(
        \ilPropertyFormGUI $form
    ) : void {
        $survey = $this->survey;
        $feature_config = $this->feature_config;
        $obj_service = $this->object_service;
        $lng = $this->ui_service->lng();

        if ($form->getInput("rmd")) {
            $rmd_start = $form->getItemByPostVar("rmd_start")->getDate();
            $rmd_end = $form->getItemByPostVar("rmd_end")->getDate();
            if ($rmd_end && $rmd_start->get(IL_CAL_UNIX) > $rmd_end->get(IL_CAL_UNIX)) {
                $tmp = $rmd_start;
                $rmd_start = $rmd_end;
                $rmd_end = $tmp;
            }
            $survey->setReminderStatus(true);
            $survey->setReminderStart($rmd_start);
            $survey->setReminderEnd($rmd_end);
            $survey->setReminderFrequency((int) $form->getInput("rmd_freq"));
            if ($feature_config->supportsMemberReminder()) {
                $survey->setReminderTarget((int) $form->getInput("rmd_grp"));
                $survey->setReminderTemplate(($form->getInput("rmdt") > 0)
                    ? (int) $form->getInput("rmdt")
                    : null);
            }
        } else {
            $survey->setReminderStatus(false);
        }

        if (!$feature_config->supportsTutorNotification()) {

            // "one mail after all participants finished"
            if ($form->getInput("tut")) {
                $tut_ids = $this->getTutorIdsFromForm($form);
                $survey->setTutorNotificationStatus(true);
                $survey->setTutorNotificationRecipients($tut_ids); // see above
                $survey->setTutorNotificationTarget($form->getInput("tut_grp"));
            } else {
                $survey->setTutorNotificationStatus(false);
            }

            /*
            if ($form->getInput("tut_res")) {
                $tut_res_ids = $this->getTutorResIdsFromForm($form);
                $survey->setTutorResultsStatus(true);
                $survey->setTutorResultsRecipients($tut_res_ids); // see above
            } else {
                $survey->setTutorResultsStatus(false);
            }*/
        }

        // #10055
        if ($form->getInput('online') && count($survey->questions) === 0) {
            $this->main_tpl->setOnScreenMessage('failure', $lng->txt("cannot_switch_to_online_no_questions"), true);
        } else {
            $survey->setOfflineStatus(!$form->getInput('online'));
        }

        $survey->setViewOwnResults((bool) $form->getInput("view_own"));
        $survey->setMailOwnResults((bool) $form->getInput("mail_own"));
        $survey->setMailConfirmation((bool) $form->getInput("mail_confirm"));

        // both are saved in object, too
        $survey->setTitle($form->getInput('title'));
        $survey->setDescription($form->getInput('description'));
        $survey->update();

        // activation
        $period = $form->getItemByPostVar("access_period");
        if ($period->getStart() && $period->getEnd()) {
            $survey->setActivationLimited(true);
            $survey->setActivationVisibility((bool) $form->getInput("access_visiblity"));
            $survey->setActivationStartDate($period->getStart()->get(IL_CAL_UNIX));
            $survey->setActivationEndDate($period->getEnd()->get(IL_CAL_UNIX));
        } else {
            $survey->setActivationLimited(false);
        }

        // tile image
        $obj_service->commonSettings()->legacyForm($form, $survey)->saveTileImage();

        $start = $form->getItemByPostVar("start_date");
        if ($start->getDate()) {
            $datetime = explode(" ", $start->getDate()->get(IL_CAL_DATETIME));
            $survey->setStartDateAndTime($datetime[0], $datetime[1]);
        } else {
            $survey->setStartDate("");
        }

        $end = $form->getItemByPostVar("end_date");
        if ($end->getDate()) {
            $datetime = explode(" ", $end->getDate()->get(IL_CAL_DATETIME));
            $survey->setEndDateAndTime($datetime[0], $datetime[1]);
        } else {
            $survey->setEndDate("");
        }
        $survey->setIntroduction($form->getInput("introduction"));
        $survey->setOutro($form->getInput("outro"));
        $survey->setShowQuestionTitles((bool) $form->getInput("show_question_titles"));
        $survey->setPoolUsage((bool) $form->getInput("use_pool"));

        // "separate mail for each participant finished"
        $survey->setMailNotification((bool) $form->getInput('mailnotification'));
        $survey->setMailAddresses($form->getInput('mailaddresses'));
        $survey->setMailParticipantData($form->getInput('mailparticipantdata'));

        if ($feature_config->usesAppraisees()) {
            $survey->set360SelfAppraisee((bool) $form->getInput("self_appr"));
            $survey->set360SelfRaters((bool) $form->getInput("self_rate"));
        }

        if ($feature_config->supportsCompetences()) {
            $survey->setSkillService((bool) $form->getInput("skill_service"));
        }

        foreach ($this->modifier->getSurveySettingsResults(
            $survey,
            $this->ui_service
        ) as $item) {
            $this->modifier->setValuesFromForm($survey, $form);
        }

        $survey->saveToDb();

        \ilObjectServiceSettingsGUI::updateServiceSettingsForm(
            $survey->getId(),
            $form,
            array(
                \ilObjectServiceSettingsGUI::ORGU_POSITION_ACCESS
            )
        );
    }
}

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

namespace ILIAS\Survey\InfoScreen;

use ILIAS\Survey\Participants;
use ILIAS\Survey\Execution;
use ILIAS\Survey\InternalDomainService;
use ILIAS\Survey\Access;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Info screen wrapper for the survey. The screen currently acts
 * as a start screen, too.
 * @author Alexander Killing <killing@leifos.de>
 */
class InfoScreenGUI
{
    protected \ILIAS\DI\UIServices $ui;
    protected \ilObjSurvey $survey;
    protected \ilObjUser $user;
    protected \ilToolbarGUI $toolbar;
    protected \ilObjSurveyGUI $survey_gui;
    protected Participants\StatusManager $status_manager;
    protected Access\AccessManager $access_manager;
    protected Execution\RunManager $run_manager;
    protected ServerRequestInterface $request;
    protected string $requested_code;
    protected \ILIAS\Survey\Mode\FeatureConfig $feature_config;
    protected \ilLanguage $lng;
    protected \ilCtrl $ctrl;
    private \ilGlobalTemplateInterface $main_tpl;

    public function __construct(
        \ilObjSurveyGUI $survey_gui,
        \ilToolbarGUI $toolbar,
        \ilObjUser $user,
        \ilLanguage $lng,
        \ilCtrl $ctrl,
        ServerRequestInterface $request,
        InternalDomainService $domain_service
    ) {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->user = $user;
        $this->toolbar = $toolbar;
        $this->survey_gui = $survey_gui;
        $this->ui = $DIC->ui();
        /** @var \ilObjSurvey $survey */
        $survey = $survey_gui->getObject();
        $this->survey = $survey;
        $this->status_manager = $domain_service->participants()->status($this->survey, $user->getId());
        $this->access_manager = $domain_service->access($this->survey->getRefId(), $user->getId());
        $this->run_manager = $domain_service->execution()->run($this->survey, $user->getId());
        $this->feature_config = $domain_service->modeFeatureConfig($this->survey->getMode());

        $this->lng = $lng;
        $this->ctrl = $ctrl;

        $body = $request->getParsedBody();
        $this->requested_code = (string) ($body["anonymous_id"] ?? "");
    }

    public function getInfoScreenGUI(): \ilInfoScreenGUI
    {
        $user = $this->user;
        $toolbar = $this->toolbar;
        $status_manager = $this->status_manager;
        $survey = $this->survey;

        $external_rater = $status_manager->isExternalRater();

        $output_gui = new \ilSurveyExecutionGUI($survey);

        $info = new \ilInfoScreenGUI($this->survey_gui);
        $info->enablePrivateNotes();

        // appraisee infos
        $this->addAppraiseeInfo($info);

        // handle (anonymous) code

        $this->run_manager->initSession($this->requested_code);
        $anonymous_code = $this->run_manager->getCode();

        // completed message
        if ($this->status_manager->cantStartAgain()) {
            $this->main_tpl->setOnScreenMessage('info', $this->lng->txt("already_completed_survey"));
        }

        $separator = false;

        // view results button
        if ($this->status_manager->canViewUserResults()) {
            $button = \ilLinkButton::getInstance();
            $button->setCaption("svy_view_own_results");
            $button->setUrl($this->ctrl->getLinkTarget($this->survey_gui, "viewUserResults"));
            $toolbar->addButtonInstance($button);
            $separator = true;
        }

        // confirmation mail button / input
        if ($this->status_manager->canMailUserResults()) {
            if ($separator) {
                $toolbar->addSeparator();
            }

            if (!$user->getEmail()) {
                $mail = new \ilTextInputGUI($this->lng->txt("email"), "mail");
                $mail->setSize(25);
                $mail->setValue($user->getEmail());
                $toolbar->addInputItem($mail, true);
            }

            $toolbar->setFormAction($this->ctrl->getFormAction($this->survey_gui, "mailUserResults"));

            $button = \ilSubmitButton::getInstance();
            $button->setCaption("svy_mail_send_confirmation");
            $button->setCommand("mailUserResults");
            $toolbar->addButtonInstance($button);
        }

        $this->displayNotStartableReasons($info);

        if ($status_manager->mustEnterCode($anonymous_code)) {
            $info->setFormAction($this->ctrl->getFormAction($this->survey_gui, "infoScreen"));
            $info->addSection($this->lng->txt("anonymization"));
            $info->addProperty("", $this->lng->txt("anonymize_anonymous_introduction"));
            $info->addPropertyTextinput($this->lng->txt("enter_anonymous_id"), "anonymous_id", "", "8", "infoScreen", $this->lng->txt("submit"), true);
        }

        // display start / resume links/buttons
        $this->addStartResumeSection($info, $anonymous_code, $output_gui);

        if ($status_manager->canAddItselfAsAppraisee()) { // #14968
            $link = $this->ctrl->getLinkTargetByClass("ilsurveyparticipantsgui", "addSelfAppraisee");
            $toolbar->addButton(
                $this->lng->txt("survey_360_add_self_appraisee"),
                $link
            );
        }

        //if ($big_button) {
        /*
        $toolbar->setFormAction($this->ctrl->getFormAction($output_gui, "infoScreen"));

        $toolbar->setCloseFormTag(false);
        $info->setOpenFormTag(false);*/
        //}
        /* #12016
        else
        {
            $info->setFormAction($this->ctrl->getFormAction($output_gui, "infoScreen"));
        }
        */
        $toolbar->setFormAction($this->ctrl->getFormAction($output_gui, "infoScreen"));

        // introduction
        if ($survey->getIntroduction() !== '') {
            $introduction = $survey->getIntroduction();
            $info->addSection($this->lng->txt("introduction"));
            $info->addProperty("", $survey->prepareTextareaOutput($introduction) .
                "<br />" . $info->getHiddenToggleButton());
        } else {
            $info->addSection($this->lng->txt("show_details"));
            $info->addProperty("", $info->getHiddenToggleButton());
        }

        $info->hideFurtherSections(false);

        if (!$this->feature_config->usesAppraisees()) {
            $info->addSection($this->lng->txt("svy_general_properties"));

            $info->addProperty(
                $this->lng->txt("survey_results_anonymization"),
                !$survey->hasAnonymizedResults()
                    ? $this->lng->txt("survey_results_personalized_info")
                    : $this->lng->txt("survey_results_anonymized_info")
            );

            if ($this->access_manager->canAccessEvaluation()) {
                $info->addProperty($this->lng->txt("evaluation_access"), $this->lng->txt("evaluation_access_info"));
            }
        }

        $info->addMetaDataSections($survey->getId(), 0, $survey->getType());

        return $info;
    }

    /**
     * Add start/resume buttons or appraisee list to info screen
     * @param object|string $output_gui
     * @throws \ilCtrlException
     */
    protected function addStartResumeSection(
        \ilInfoScreenGUI $info,
        string $anonymous_code,
        $output_gui
    ): void {
        $survey = $this->survey;

        $status_manager = $this->status_manager;
        if ($this->access_manager->canStartSurvey() &&
            !$status_manager->mustEnterCode($anonymous_code)) {
            if (!$this->feature_config->usesAppraisees()) {
                if ($anonymous_code) {
                    $info->addHiddenElement("anonymous_id", $anonymous_code);
                }
                $big_button = false;
                if ($this->run_manager->hasStarted() &&
                    !$this->run_manager->hasFinished()) {
                    $big_button = array("resume", $this->lng->txt("resume_survey"));
                } elseif (!$this->run_manager->hasStarted()) {
                    $big_button = array("start", $this->lng->txt("start_survey"));
                }
                if ($big_button) {
                    $button = \ilSubmitButton::getInstance();
                    $button->setCaption($big_button[1], false);
                    $button->setCommand($big_button[0]);
                    $button->setPrimary(true);
                    $this->toolbar->addButtonInstance($button);
                }
            } else {

                // list appraisees
                $appr_ids = array();

                // use given code (if proper external one)
                if ($anonymous_code) {
                    $anonymous_id = $survey->getAnonymousIdByCode($anonymous_code);
                    if ($anonymous_id) {
                        $appr_ids = $survey->getAppraiseesToRate(0, $anonymous_id);
                    }
                }

                // registered user
                // if an auto-code was generated, we still have to check for the original user id
                if (!$appr_ids && $this->user->getId() !== ANONYMOUS_USER_ID) {
                    $appr_ids = $survey->getAppraiseesToRate($this->user->getId());
                }

                if (count($appr_ids)) {
                    // map existing runs to appraisees
                    $active_appraisees = array();
                    foreach ($this->run_manager
                                 ->getRunsForUser($this->user->getId(), $anonymous_code) as $item) {
                        $active_appraisees[$item->getAppraiseeId()] = $item->getFinished();
                    }

                    $list = array();
                    foreach ($appr_ids as $appr_id) {
                        if ($survey->isAppraiseeClosed($appr_id)) {
                            // closed
                            $list[$appr_id] = $this->lng->txt("survey_360_appraisee_is_closed");
                        } elseif (array_key_exists($appr_id, $active_appraisees)) {
                            // already done
                            if ($active_appraisees[$appr_id]) {
                                $list[$appr_id] = $this->lng->txt("already_completed_survey");
                            }
                            // resume
                            else {
                                $list[$appr_id] = array("resume", $this->lng->txt("resume_survey"));
                            }
                        } else {
                            // start
                            $list[$appr_id] = array("start", $this->lng->txt("start_survey"));
                        }
                    }

                    $info->addSection($this->lng->txt("survey_360_rate_other_appraisees"));

                    foreach ($list as $appr_id => $item) {
                        $appr_name = \ilUserUtil::getNamePresentation($appr_id, false, false, "", true);

                        if (!is_array($item)) {
                            $info->addProperty($appr_name, $item);
                        } else {
                            $this->ctrl->setParameter($output_gui, "appr_id", $appr_id);
                            $href = $this->ctrl->getLinkTarget($output_gui, $item[0]);
                            $this->ctrl->setParameter($output_gui, "appr_id", "");

                            $button = \ilLinkButton::getInstance();
                            $button->setCaption($item[1], false);
                            $button->setUrl($href);
                            $big_button_360 = '<div>' . $button->render() . '</div>';

                            $info->addProperty($appr_name, $big_button_360);
                        }
                    }
                } elseif (!$status_manager->isAppraisee()) {
                    $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt("survey_360_no_appraisees"));
                }
            }
        }
    }

    protected function addAppraiseeInfo(
        \ilInfoScreenGUI $info
    ): void {
        $survey = $this->survey;
        if ($this->status_manager->isAppraisee()) {
            $info->addSection($this->lng->txt("survey_360_appraisee_info"));

            $privacy_info = $this->lng->txt("svy_rater_see_app_info");
            if (in_array($survey->get360Results(), [\ilObjSurvey::RESULTS_360_OWN, \ilObjSurvey::RESULTS_360_ALL], true)) {
                $privacy_info .= " " . $this->lng->txt("svy_app_see_rater_info");
            }
            $info->addProperty($this->lng->txt("svy_privacy_info"), $privacy_info);

            $appr_data = $survey->getAppraiseesData();
            $appr_data = $appr_data[$this->user->getId()];
            $info->addProperty($this->lng->txt("survey_360_raters_status_info"), $appr_data["finished"]);

            if ($survey->get360Mode()) {
                if (!$appr_data["closed"]) {
                    $button = \ilLinkButton::getInstance();
                    $button->setCaption("survey_360_appraisee_close_action");
                    $button->setUrl($this->ctrl->getLinkTargetByClass(
                        "ilsurveyparticipantsgui",
                        "confirmappraiseeclose"
                    ));
                    $close_button_360 = '<div>' . $button->render() . '</div>';

                    $txt = "survey_360_appraisee_close_action_info";
                    if ($survey->getSkillService()) {
                        $txt .= "_skill";
                    }
                    $info->addProperty(
                        $this->lng->txt("status"),
                        $close_button_360 . $this->lng->txt($txt)
                    );
                } else {
                    \ilDatePresentation::setUseRelativeDates(false);

                    $dt = new \ilDateTime($appr_data["closed"], IL_CAL_UNIX);
                    $info->addProperty(
                        $this->lng->txt("status"),
                        sprintf(
                            $this->lng->txt("survey_360_appraisee_close_action_status"),
                            \ilDatePresentation::formatDate($dt)
                        )
                    );
                }
            }
        }
    }

    protected function displayNotStartableReasons(\ilInfoScreenGUI $info): void
    {
        $survey = $this->survey;

        $links = [];

        if (!$this->access_manager->canStartSurvey() &&
            $this->access_manager->canEditSettings()) {
            $messages = [];

            if (!$survey->hasStarted()) {
                $messages[] = $this->lng->txt('start_date_not_reached') . ' (' .
                    \ilDatePresentation::formatDate(new \ilDateTime(
                        $survey->getStartDate(),
                        IL_CAL_TIMESTAMP
                    )) . ")";
            }

            if ($survey->hasEnded()) {
                $messages[] = $this->lng->txt('end_date_reached') . ' (' .
                    \ilDatePresentation::formatDate(new \ilDateTime($survey->getEndDate(), IL_CAL_TIMESTAMP)) . ")";
            }

            if ($survey->getOfflineStatus()) {
                $messages[] = $this->lng->txt("survey_is_offline");
            }

            if (count($messages) > 0) {
                $links[] = $this->ui->factory()->link()->standard(
                    $this->lng->txt("survey_edit_settings"),
                    $this->ctrl->getLinkTarget($this->survey_gui, "properties")
                );
                $mbox = $this->ui->factory()->messageBox()->info(implode("<br />", $messages));
                if (count($links) > 0) {
                    $mbox = $mbox->withLinks($links);
                }
                $info->setMessageBox($mbox);
            }
        }
    }
}

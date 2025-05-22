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

namespace ILIAS\Survey\Execution;

use ILIAS\Survey\InternalGUIService;
use ILIAS\Survey\InternalDomainService;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\Survey\Access\AccessManager;
use ILIAS\Survey\Participants\StatusManager;
use ILIAS\Survey\Mode\FeatureConfig;
use ILIAS\UI\Component\MessageBox\MessageBox;
use ILIAS\UI\Component\Launcher\Inline;
use ILIAS\Data\Result;
use ILIAS\UI\Component\Panel\Standard;

class LaunchGUI
{
    protected string $requested_code;
    protected FeatureConfig $feature_config;
    protected RunManager $run_manager;
    protected int $user_id;
    protected AccessManager $access_manager;
    protected StatusManager $status_manager;
    protected string $launch_title = "";
    protected string $launch_target = "";
    protected array $launch_inputs = [];
    protected array $launch_messages = [];

    protected array $launch_information = [];
    protected array $launch_message_buttons = [];
    protected array $launch_message_links = [];

    public function __construct(
        protected InternalDomainService $domain,
        protected InternalGUIService $gui,
        protected \ilObjSurvey $survey
    ) {
        $this->user_id = $domain->user()->getId();
        $this->status_manager = $domain->participants()->status($this->survey, $this->user_id);
        $this->access_manager = $domain->access($this->survey->getRefId(), $this->user_id);
        $this->run_manager = $domain->execution()->run($this->survey, $this->user_id);
        $this->feature_config = $domain->modeFeatureConfig($this->survey->getMode());
        $exec_request = $gui->execution()->request();
        $this->requested_code = $exec_request->getAnonymousId();
    }

    public function executeCommand(): void
    {
        $ctrl = $this->gui->ctrl();

        $next_class = $ctrl->getNextClass($this);
        $cmd = $ctrl->getCmd("launch");

        switch ($next_class) {
            default:
                if (in_array($cmd, array("launch", "start", "resume"))) {
                    $this->$cmd();
                }
        }
    }

    public function initSession(): void
    {
        $ctrl = $this->gui->ctrl();
        $main_tpl = $this->gui->ui()->mainTemplate();
        try {
            $this->run_manager->initSession($this->requested_code);
        } catch (\ilWrongSurveyCodeException $e) {
            $main_tpl->setOnScreenMessage("failure", $e->getMessage(), true);
            $ctrl->redirectByClass(self::class, "launch");
        }
    }

    protected function launch(): void
    {
        $f = $this->gui->ui()->factory();
        $r = $this->gui->ui()->renderer();
        $ctrl = $this->gui->ctrl();
        $main_tpl = $this->gui->ui()->mainTemplate();
        $lng = $this->domain->lng();

        // init session
        $this->initSession();

        // completed message
        if ($this->status_manager->cantStartAgain()) {
            $main_tpl->setOnScreenMessage('info', $lng->txt("already_completed_survey"));
        }

        // view results link
        /*
        if ($this->status_manager->canViewUserResults()) {
            $this->launch_message_links[] = $f->link()->standard(
                $lng->txt("svy_view_own_results"),
                $ctrl->getLinkTargetByClass(\ilObjSurveyGUI::class, "viewUserResults")
            );
        }*/

        // confirmation mail button / input (omitted, since abandoned)

        $items = [];

        $this->collectData();

        // message box
        if ($mbox = $this->getMessageBox()) {
            $items[] = $mbox;
        }

        // panel
        if ($panel = $this->getPanel()) {
            $items[] = $panel;
        }

        // launcher
        if ($launcher = $this->getLauncher()) {
            $items[] = $launcher;
        }

        $mt = $this->gui->mainTemplate();
        $mt->setContent($r->render($items));
    }

    protected function forwardInputsToParameters(): void
    {
        $ctrl = $this->gui->ctrl();
        $request = $this->gui->http()->request();
        $this->collectData();
        $launcher = $this->getLauncher();
        if ($launcher) {
            $launcher = $launcher->withRequest($request);
            $result = $launcher->getResult();
            if ($result && $result->isOK()) {
                foreach ($result->value() as $key => $value) {
                    if ($key === "appraisee_id") {
                        $ctrl->setParameterByClass(\ilSurveyExecutionGUI::class, "appr_id", $value);
                    }
                    if ($key === "anonymous_id") {
                        $this->requested_code = $value;
                        $ctrl->setParameterByClass(\ilSurveyExecutionGUI::class, "anonymous_id", $value);
                    }
                }
            }
        }
    }

    protected function start(): void
    {
        $ctrl = $this->gui->ctrl();
        $this->forwardInputsToParameters();
        $this->initSession();
        $ctrl->redirectByClass(\ilSurveyExecutionGUI::class, "start");
    }

    protected function resume(): void
    {
        $ctrl = $this->gui->ctrl();
        $this->forwardInputsToParameters();
        $this->initSession();
        $ctrl->redirectByClass(\ilSurveyExecutionGUI::class, "resume");
    }

    protected function collectData(): void
    {
        $ctrl = $this->gui->ctrl();
        $lng = $this->domain->lng();
        $f = $this->gui->ui()->factory();

        $anonymous_code = $this->run_manager->getCode();

        // appraisee info
        $this->determineAppraiseeInfo();

        $this->determineNotStartableReasons();

        // enter code?
        if ($this->status_manager->mustEnterCode($anonymous_code)) {
            $this->launch_information[$lng->txt("svy_code")] =
                $lng->txt("anonymize_anonymous_introduction");
            $this->launch_inputs["anonymous_id"] = $f->input()->field()->text(
                $lng->txt("enter_anonymous_id")
            );
            $this->launch_target = $ctrl->getLinkTargetByClass(
                self::class,
                "start"
            );
            $this->launch_title = $lng->txt("start_survey");
        }

        $this->determineMainLink($anonymous_code);

        // add as appraisee
        if ($this->status_manager->canAddItselfAsAppraisee() &&
            $this->access_manager->canStartSurvey()) { // #14968
            $this->launch_message_buttons[] = $f->button()->standard(
                $lng->txt("survey_360_add_self_appraisee"),
                $ctrl->getLinkTargetByClass(\ilSurveyParticipantsGUI::class, "addSelfAppraisee")
            );
        }

        // introduction
        if ($this->survey->getIntroduction() !== '') {
            $introduction = $this->survey->getIntroduction();
            $this->launch_information[] = $this->survey->prepareTextareaOutput($introduction);
        }

        // access information
        if (!$this->feature_config->usesAppraisees()) {
            $this->launch_information[$lng->txt("survey_results_anonymization")] =
            !$this->survey->hasAnonymizedResults()
                ? $lng->txt("survey_results_personalized_info")
                : $lng->txt("survey_results_anonymized_info");
            if ($this->access_manager->canAccessEvaluation()) {
                $this->launch_messages[$lng->txt("evaluation_access")] =
                    $lng->txt("evaluation_access_info");
            }
        }
    }


    protected function getLauncher(): ?Inline
    {
        $f = $this->gui->ui()->factory();

        if ($this->launch_target !== "") {
            $data_factory = new \ILIAS\Data\Factory();
            $uri = $data_factory->uri(ILIAS_HTTP_PATH . '/' . $this->launch_target);
            $link = $data_factory->link($this->launch_title, $uri);

            $launcher = $f->launcher()->inline(
                $link
            );

            if (count($this->launch_inputs) > 0) {
                $launcher = $launcher->withInputs(
                    $f->input()->field()->group($this->launch_inputs),
                    function () {
                    },
                    null
                )->withModalSubmitLabel($this->launch_title);
            }

            return $launcher;
        }
        return null;
    }

    protected function getPanel(): ?Standard
    {
        $f = $this->gui->ui()->factory();
        $lng = $this->domain->lng();

        if (count($this->launch_information) > 0) {

            $items = [];
            $key_value = [];

            foreach ($this->launch_information as $key => $value) {
                if (is_numeric($key)) {
                    $items[] = $f->legacy()->content($value);
                } else {
                    $key_value[$key] = $value;
                }
            }

            if (count($key_value) > 0) {
                if (count($items) > 0) {
                    $items[] = $f->divider()->horizontal();
                }
                $items[] = $f->listing()->descriptive($key_value);
            }

            $panel = $f->panel()->standard(
                $lng->txt("svy_information"),
                $items
            );

            return $panel;
        }
        return null;
    }

    protected function getMessageBox(): ?MessageBox
    {
        $f = $this->gui->ui()->factory();
        if (count($this->launch_messages) > 0 || count($this->launch_message_buttons) > 0
            || count($this->launch_message_links) > 0) {
            $mess = "";
            foreach ($this->launch_messages as $m) {
                $mess .= "<p>$m</p>";
            }
            $mbox = $f->messageBox()->info($mess);
            if (count($this->launch_message_buttons) > 0) {
                $mbox = $mbox->withButtons($this->launch_message_buttons);
            }
            if (count($this->launch_message_links) > 0) {
                $mbox = $mbox->withLinks($this->launch_message_links);
            }
            return $mbox;
        }
        return null;
    }

    protected function determinePrivacyInfo(
    ): void {
        $survey = $this->survey;
        $lng = $this->domain->lng();

        $privacy_info = $lng->txt("svy_rater_see_app_info");
        if (in_array($survey->get360Results(), [\ilObjSurvey::RESULTS_360_OWN, \ilObjSurvey::RESULTS_360_ALL], true)) {
            $privacy_info .= " " . $lng->txt("svy_app_see_rater_info");
        }
        //$this->launch_messages[] = $lng->txt("svy_privacy_info") . ": " . $privacy_info;
        $this->launch_information[$lng->txt("svy_privacy_info")] = $privacy_info;
    }

    protected function determineAppraiseeInfo(): void
    {
        $survey = $this->survey;
        $lng = $this->domain->lng();
        $ctrl = $this->gui->ctrl();
        $f = $this->gui->ui()->factory();

        if ($this->status_manager->isAppraisee()) {
            $this->determinePrivacyInfo();

            $appr_data = $survey->getAppraiseesData();
            $appr_data = $appr_data[$this->user_id];

            $this->launch_information[$lng->txt("svy_your_raters")] =
                sprintf($lng->txt("svy_your_raters_finished"), $appr_data["finished"]);

            if ($survey->get360Mode()) {
                if (!$appr_data["closed"]) {
                    $button = $f->button()->standard(
                        $lng->txt("survey_360_appraisee_close_action"),
                        $ctrl->getLinkTargetByClass(
                            \ilSurveyParticipantsGUI::class,
                            "confirmappraiseeclose"
                        )
                    );
                    $this->launch_message_buttons[] = $button;

                    $txt = "survey_360_appraisee_close_action_info";
                    if ($survey->getSkillService()) {
                        $txt .= "_skill";
                    }
                    $this->launch_messages[] = $lng->txt($txt);
                } else {
                    \ilDatePresentation::setUseRelativeDates(false);
                    $dt = new \ilDateTime($appr_data["closed"], IL_CAL_UNIX);
                    $this->launch_information[$lng->txt("status")] = sprintf(
                        $lng->txt("survey_360_appraisee_close_action_status"),
                        \ilDatePresentation::formatDate($dt)
                    );
                }
            }
        }
    }

    protected function determineMainLink(
        string $anonymous_code,
    ): void {
        $ctrl = $this->gui->ctrl();
        $f = $this->gui->ui()->factory();
        $lng = $this->domain->lng();
        $survey = $this->survey;
        $status_manager = $this->status_manager;
        if ($this->access_manager->canStartSurvey() &&
            !$status_manager->mustEnterCode($anonymous_code)) {
            if (!$this->feature_config->usesAppraisees()) {
                if ($anonymous_code) {
                    $ctrl->setParameterByClass(\ilObjSurvey::class, "anonymous_id", $anonymous_code);
                    // $info->addHiddenElement("anonymous_id", $anonymous_code);
                }
                if ($this->run_manager->hasStarted() &&
                    !$this->run_manager->hasFinished()) {
                    $this->launch_target = $ctrl->getLinkTargetByClass(
                        self::class,
                        "resume"
                    );
                    $this->launch_title = $lng->txt("resume_survey");
                } elseif (!$this->run_manager->hasStarted()) {
                    $this->launch_target = $ctrl->getLinkTargetByClass(
                        self::class,
                        "start"
                    );
                    $this->launch_title = $lng->txt("start_survey");
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
                if (!$appr_ids && $this->user_id !== ANONYMOUS_USER_ID) {
                    $appr_ids = $survey->getAppraiseesToRate($this->user_id);
                }

                if (count($appr_ids)) {
                    // map existing runs to appraisees
                    $active_appraisees = array();
                    foreach ($this->run_manager
                                 ->getRunsForUser($this->user_id, $anonymous_code) as $item) {
                        $active_appraisees[$item->getAppraiseeId()] = $item->getFinished();
                    }

                    $list = array();
                    $appraisee_options = [];
                    $closed = 0;
                    $open = 0;
                    $finished = 0;
                    foreach ($appr_ids as $appr_id) {
                        if ($survey->isAppraiseeClosed($appr_id)) {
                            // closed
                            $list[$appr_id] = $lng->txt("survey_360_appraisee_is_closed");
                            $closed++;
                            if ($active_appraisees[$appr_id] ?? false) {
                                $finished++;
                            }
                        } elseif (array_key_exists($appr_id, $active_appraisees)) {
                            // already done
                            if ($active_appraisees[$appr_id]) {
                                $list[$appr_id] = $lng->txt("already_completed_survey");
                                $finished++;
                            }
                            // resume
                            else {
                                $list[$appr_id] = array("resume", $lng->txt("resume_survey"));
                                $open++;
                            }
                        } else {
                            // start
                            $list[$appr_id] = array("start", $lng->txt("start_survey"));
                            $open++;
                        }
                    }

                    foreach ($list as $appr_id => $item) {
                        $appr_name = \ilUserUtil::getNamePresentation($appr_id, false, false, "", true);
                        if (is_array($item)) {
                            $appraisee_options[$appr_id] = $appr_name;
                        }
                    }
                    if (count($appraisee_options) > 0) {
                        $this->launch_inputs["appraisee_id"] = $f->input()->field()->select(
                            $lng->txt("survey_360_appraisee"),
                            $appraisee_options
                        )->withRequired(true)->withValue(key($appraisee_options));
                        $this->launch_target = $ctrl->getLinkTargetByClass(
                            self::class,
                            "start"
                        );
                        $this->launch_title = $lng->txt("survey_360_rate_other_appraisee");
                    }

                    $status_txt = ($open === 0)
                        ? $lng->txt("svy_0_open_appraisees")
                        : sprintf($lng->txt("svy_x_open_appraisees"), $open);
                    if ($finished > 0) {
                        $status_txt .= " " . sprintf($lng->txt("svy_finished_x_appraisees"), $finished);
                    }
                    if ($closed > 0) {
                        $status_txt .= " " . sprintf($lng->txt("svy_x_appraisees_closed_for_raters"), $closed);
                    }
                    $this->launch_information[$lng->txt("svy_your_appraisees")] = $status_txt;

                } elseif (!$status_manager->isAppraisee()) {
                    $this->launch_messages[] = $lng->txt("survey_360_no_appraisees");
                    //$this->main_tpl->setOnScreenMessage('failure', $this->lng->txt("survey_360_no_appraisees"));
                }
            }
        }
    }

    protected function determineNotStartableReasons(): void
    {
        $survey = $this->survey;
        $lng = $this->domain->lng();
        $ctrl = $this->gui->ctrl();
        $f = $this->gui->ui()->factory();

        if (!$this->access_manager->canStartSurvey() &&
            $this->access_manager->canEditSettings()) {

            if (!$survey->hasStarted()) {
                $this->launch_messages[] = $lng->txt('start_date_not_reached') . ' (' .
                    \ilDatePresentation::formatDate(new \ilDateTime(
                        $survey->getStartDate(),
                        IL_CAL_TIMESTAMP
                    )) . ")";
            }

            if ($survey->hasEnded()) {
                $this->launch_messages[] = $lng->txt('end_date_reached') . ' (' .
                    \ilDatePresentation::formatDate(new \ilDateTime($survey->getEndDate(), IL_CAL_TIMESTAMP)) . ")";
            }

            if ($survey->getOfflineStatus()) {
                $this->launch_messages[] = $lng->txt("survey_is_offline");
            }

            if ($this->access_manager->canEditSettings()) {
                $this->launch_message_links[] = $f->link()->standard(
                    $lng->txt("survey_edit_settings"),
                    $ctrl->getLinkTargetByClass(\ilObjSurveyGUI::class, "properties")
                );
            }
        }
    }
}

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

use ILIAS\Survey\Mode;

/**
 * Survey execution graphical output
 *
 * The ilSurveyExecutionGUI class creates the execution output for the ilObjSurveyGUI
 * class. This saves some heap space because the ilObjSurveyGUI class will be
 * smaller.
 *
 * @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
 */
class ilSurveyExecutionGUI
{
    /**
     * @var array|object|null
     */
    protected array $raw_post_data;
    protected \ILIAS\Survey\Execution\ExecutionGUIRequest $request;
    protected ilRbacSystem $rbacsystem;
    protected ilObjUser $user;
    protected ilHelpGUI $help;
    protected ilToolbarGUI $toolbar;
    protected ilObjSurvey $object;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;
    protected ilCtrl $ctrl;
    protected ilTree $tree;
    protected bool $preview;
    protected ilLogger $log;
    protected \ILIAS\Survey\Execution\RunManager $run_manager;
    protected \ILIAS\Survey\Participants\StatusManager $participant_manager;
    protected \ILIAS\Survey\Access\AccessManager $access_manager;
    protected int $requested_appr_id;
    protected Mode\FeatureConfig $feature_config;

    public function __construct(ilObjSurvey $a_object)
    {
        global $DIC;

        $this->rbacsystem = $DIC->rbac()->system();
        $this->user = $DIC->user();
        $this->help = $DIC["ilHelp"];
        $this->toolbar = $DIC->toolbar();
        $lng = $DIC->language();
        $tpl = $DIC["tpl"];
        $ilCtrl = $DIC->ctrl();
        $tree = $DIC->repositoryTree();

        $this->lng = $lng;
        $this->tpl = $tpl;
        $this->ctrl = $ilCtrl;
        $this->object = $a_object;
        $this->tree = $tree;
        $this->user = $DIC->user();

        $this->request = $DIC->survey()
                             ->internal()
                             ->gui()
                             ->execution()
                             ->request();

        // stay in preview mode
        $this->preview = (bool) $this->request->getPreview();
        $this->requested_appr_id = $this->request->getAppraiseeId();
        $this->ctrl->saveParameter($this, "prvw");
        $this->ctrl->saveParameter($this, "appr_id");
        $this->ctrl->saveParameter($this, "pgov");

        $this->log = ilLoggerFactory::getLogger("svy");

        $domain_service = $DIC->survey()->internal();
        $this->run_manager = $domain_service->domain()->execution()->run(
            $a_object,
            $this->user->getId(),
            $this->requested_appr_id
        );
        $this->participant_manager = $domain_service->domain()->participants()->status(
            $a_object,
            $this->user->getId()
        );
        $this->access_manager = $domain_service->domain()->access(
            $a_object->getRefId(),
            $this->user->getId()
        );

        $this->feature_config = $domain_service->domain()->modeFeatureConfig($a_object->getMode());


        // @todo this is used to store answers in the session, but should
        // be refactored somehow to avoid the use of the complete post body
        $this->raw_post_data = $DIC->http()->request()->getParsedBody() ?? [];
    }

    public function executeCommand(): string
    {
        // record read event for lp
        ilChangeEvent::_recordReadEvent(
            'svy',
            $this->object->getRefId(),
            $this->object->getId(),
            $GLOBALS['DIC']->user()->getId()
        );

        $cmd = $this->ctrl->getCmd();
        $next_class = $this->ctrl->getNextClass($this);

        $this->log->debug("- cmd= " . $cmd);

        if ($cmd === null || $cmd === '') {
            $this->ctrl->setParameter(
                $this,
                "qid",
                $this->request->getQuestionId()
            );
            $this->ctrl->redirect($this, "gotoPage");
        }
        switch ($next_class) {
            default:
                $ret = $this->$cmd();
                break;
        }
        return (string) $ret;
    }

    protected function checkAuth(
        bool $a_may_start = false,
        bool $a_ignore_status = false
    ): void {
        $rbacsystem = $this->rbacsystem;
        $ilUser = $this->user;

        if ($this->preview) {
            if (!$rbacsystem->checkAccess("write", $this->object->getRefId())) {
                // only with write access it is possible to preview the survey
                throw new ilSurveyException($this->lng->txt("survey_cannot_preview_survey"));
            }
            return;
        }


        if (!$this->access_manager->canStartSurvey()) {
            // only with read access it is possible to run the test
            throw new ilSurveyException($this->lng->txt("cannot_read_survey"));
        }

        $user_id = $ilUser->getId();

        // check existing code
        // see ilObjSurveyGUI::infoScreen()
        $anonymous_id = null;
        $anonymous_code = "";
        if ($this->access_manager->isCodeInputAllowed()) {
            $anonymous_code = $this->run_manager->getCode();
            $anonymous_id = $this->object->getAnonymousIdByCode($anonymous_code);
            if (!$anonymous_id) {
                $this->tpl->setOnScreenMessage('failure', sprintf($this->lng->txt("error_retrieving_anonymous_survey"), $anonymous_code, true));
                $this->ctrl->redirectByClass("ilobjsurveygui", "infoScreen");
            }
        }

        // appraisee validation
        $appr_id = 0;
        $appraisees = [];
        if ($this->feature_config->usesAppraisees()) {
            $appr_id = $this->requested_appr_id;
            //if (!$appr_id) {
            //    $appr_id = $_SESSION["appr_id"][$this->object->getId()];
            //}
            // check if appraisee is valid
            if ($anonymous_id) {
                $appraisees = $this->object->getAppraiseesToRate(0, $anonymous_id);
            }
            if (!$appraisees && $user_id !== ANONYMOUS_USER_ID) {
                $appraisees = $this->object->getAppraiseesToRate($user_id);
            }
            if (!in_array($appr_id, $appraisees)) {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt("survey_360_execution_invalid_appraisee"), true);
                $this->ctrl->redirectByClass("ilobjsurveygui", "infoScreen");
            }
        }
        //Self evaluation mode
        #23575 in self eval the appraisee is the current user.
        if ($this->object->getMode() === ilObjSurvey::MODE_SELF_EVAL) {
            $appr_id = $ilUser->getId();
        }

        //$_SESSION["appr_id"][$this->object->getId()] = $appr_id;

        if (!$a_ignore_status) {
            // completed
            if ($this->run_manager->hasFinished()) {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt("already_completed_survey"), true);
                $this->ctrl->redirectByClass("ilobjsurveygui", "infoScreen");
            }
            // starting
            elseif (!$this->run_manager->hasStarted()) {
                if ($a_may_start) {
                    //$_SESSION["finished_id"][$this->object->getId()] =
                    //    $this->object->startSurvey($user_id, $anonymous_code, $appr_id);
                    $this->run_manager->start($appr_id);
                } else {
                    $this->tpl->setOnScreenMessage('failure', $this->lng->txt("survey_use_start_button"), true);
                    $this->ctrl->redirectByClass("ilobjsurveygui", "infoScreen");
                }
            }
            // resuming
            else {
                // nothing todo
            }
        }

        // validate finished id
        if ($this->object->getActiveID($user_id, $anonymous_code, $appr_id) !==
            $this->run_manager->getCurrentRunId()) {
            throw new ilSurveyException("Run ID mismatch");
        }
    }

    public function resume(): void
    {
        $this->start(true);
    }

    public function start(bool $resume = false): void
    {
        if ($this->preview) {
            $this->run_manager->clearAllPreviewData();
        }
        $this->run_manager->clearErrors();

        $this->checkAuth(!$resume);

        $activepage = "";
        if ($resume) {
            $activepage = $this->object->getLastActivePage(
                $this->getCurrentRunId()
            );
        }

        if ((string) $activepage !== '') {
            $this->ctrl->setParameter($this, "qid", (string) $activepage);
        }
        $this->ctrl->setParameter($this, "activecommand", "default");
        $this->ctrl->redirect($this, "redirectQuestion");
    }

    /**
     * Called when a user answered a page to perform a redirect after POST.
     * This is called for security reasons to prevent users sending a form twice.
     */
    public function redirectQuestion(): void
    {
        switch ($this->request->getActiveCommand()) {
            case "previous":
            case "gotoPage":
            case "next":
                $this->outSurveyPage(
                    $this->request->getQuestionId(),
                    $this->request->getDirection()
                );
                break;
            case "default":
                $this->outSurveyPage($this->request->getQuestionId());
                break;
            default:
                // don't save input, go to the first page
                $this->outSurveyPage();
                break;
        }
    }

    public function previousNoSave(): void
    {
        $this->previous(false);
    }

    public function previous(
        bool $a_save_input = true
    ): void {
        $has_error = "";
        if ($a_save_input) {
            // #16209
            $has_error = $this->saveUserInput("previous");
        }
        $this->ctrl->setParameter($this, "activecommand", "previous");
        $this->ctrl->setParameter($this, "qid", $this->request->getQuestionId());
        if ($has_error > 0) {
            $this->ctrl->setParameter($this, "direction", "0");
        } else {
            $this->ctrl->setParameter($this, "direction", "-1");
        }
        $this->ctrl->redirect($this, "redirectQuestion");
    }

    /**
     * Navigates to the next page
     */
    public function next(): void
    {
        $result = $this->saveUserInput("next");
        $this->ctrl->setParameter($this, "activecommand", "next");
        $this->ctrl->setParameter($this, "qid", $this->request->getQuestionId());
        if ($result > 0) {
            $this->ctrl->setParameter($this, "direction", "0");
        } else {
            $this->ctrl->setParameter($this, "direction", "1");
        }
        $this->ctrl->redirect($this, "redirectQuestion");
    }

    /**
     * Go to a specific page without saving
     */
    public function gotoPage(): void
    {
        $this->ctrl->setParameter($this, "activecommand", "gotoPage");
        $this->ctrl->setParameter($this, "qid", $this->request->getQuestionId());
        $this->ctrl->setParameter($this, "direction", "0");
        $this->ctrl->redirect($this, "redirectQuestion");
    }

    /**
     * Output of the active survey question to the screen
     * @throws ilCtrlException
     * @throws ilSurveyException
     */
    public function outSurveyPage(
        int $activepage = 0,
        int $direction = 0
    ): void {
        $ilUser = $this->user;

        $this->checkAuth();
        $page = $this->object->getNextPage($activepage, $direction);
        $constraint_true = 0;

        // check for constraints
        if (!is_null($page) && is_array($page[0]["constraints"]) && count($page[0]["constraints"])) {
            $this->log->debug("Page constraints= ", $page[0]["constraints"]);

            while (!is_null($page) and ($constraint_true == 0) and (count($page[0]["constraints"]))) {
                $constraint_true = $page[0]['constraints'][0]['conjunction'] == 0;
                foreach ($page[0]["constraints"] as $constraint) {
                    if (!$this->preview) {
                        $working_data = $this->object->loadWorkingData($constraint["question"], $this->getCurrentRunId());
                    } else {
                        $working_data = $this->run_manager->getPreviewData($constraint["question"]);
                    }
                    if ($constraint['conjunction'] == 0) {
                        // and
                        $constraint_true &= $this->object->checkConstraint($constraint, $working_data);
                    } else {
                        // or
                        $constraint_true |= $this->object->checkConstraint($constraint, $working_data);
                    }
                }
                if ($constraint_true == 0) {
                    // #11047 - we are skipping the page, so we have to get rid of existing answers for that question(s)
                    foreach ($page as $page_question) {
                        $qid = $page_question["question_id"];

                        // see saveActiveQuestionData()
                        if (!$this->preview) {
                            $this->object->deleteWorkingData($qid, $this->getCurrentRunId());
                        } else {
                            $this->run_manager->clearPreviewData($qid);
                        }
                    }

                    $page = $this->object->getNextPage($page[0]["question_id"], $direction);
                }
            }
        }
        $first_question = -1;
        if (is_null($page) && $direction === -1) {
            $this->ctrl->redirectByClass("ilobjsurveygui", "infoScreen");
        } elseif (is_null($page) && $direction === 1) {
            $state = $this->object->getUserSurveyExecutionStatus();
            if ($this->preview ||
                !($state["runs"][$this->getCurrentRunId()]["finished"] ?? false)) {
                $this->showFinishConfirmation();
            } else {
                $this->runShowFinishedPage();
            }
            return;
        } else {
            $ilHelp = $this->help;
            $ilHelp->setScreenIdComponent("svy");
            $ilHelp->setScreenId("quest_presentation");

            if ($ilUser->getId() !== ANONYMOUS_USER_ID) {
                ilLearningProgress::_tracProgress($ilUser->getId(), $this->object->getId(), $this->object->getRefId(), "svy");
            }

            $required = false;
            //$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_svy_content.html", "Modules/Survey");
            $stpl = new ilTemplate("tpl.il_svy_svy_content.html", true, true, "Modules/Survey");

            // title / appraisee
            if ($this->feature_config->usesAppraisees()) {
                $appr_id = $this->requested_appr_id;

                $this->tpl->setTitle($this->object->getTitle() . " (" .
                    $this->lng->txt("survey_360_appraisee") . ": " .
                    ilUserUtil::getNamePresentation($appr_id) . ")");
            }

            // top / bottom nav
            if (!($this->object->getAnonymize() && $this->object->isAccessibleWithoutCode() && ($ilUser->getId() === ANONYMOUS_USER_ID))) {
                $stpl->setCurrentBlock("suspend_survey");

                if (!$this->preview) {
                    $stpl->setVariable("TEXT_SUSPEND", $this->lng->txt("cancel_survey"));
                    $stpl->setVariable("HREF_SUSPEND", $this->ctrl->getLinkTargetByClass("ilObjSurveyGUI", "infoScreen"));
                } else {
                    $this->ctrl->setParameterByClass("ilObjSurveyGUI", "pgov", $this->request->getTargetPosition());
                    $stpl->setVariable("TEXT_SUSPEND", $this->lng->txt("survey_cancel_preview"));
                    $stpl->setVariable("HREF_SUSPEND", $this->ctrl->getLinkTargetByClass(array("ilObjSurveyGUI", "ilSurveyEditorGUI"), "questions"));
                }

                $stpl->setVariable("ALT_IMG_SUSPEND", $this->lng->txt("cancel_survey"));
                $stpl->setVariable("TITLE_IMG_SUSPEND", $this->lng->txt("cancel_survey"));
                $stpl->parseCurrentBlock();
            }
            $this->outNavigationButtons("top", $page, $stpl);
            $this->outNavigationButtons("bottom", $page, $stpl);

            // progress
            $stpl->setCurrentBlock("percentage");
            $percentage = (int) (($page[0]["position"]) * 100);
            $pbar = ilProgressBar::getInstance();
            $pbar->setCurrent($percentage);
            $stpl->setVariable("NEW_PBAR", $pbar->render());
            $stpl->parseCurrentBlock();

            // questions
            $working_data = [];
            $errors = $this->run_manager->getErrors();
            foreach ($page as $data) {
                if ($first_question === -1) {
                    $first_question = $data["question_id"];
                }
                $question_gui = $this->object->getQuestionGUI($data["type_tag"], $data["question_id"]);

                if (count($errors) > 0) {
                    $working_data[$data["question_id"]] = $question_gui->object->getWorkingDataFromUserInput(
                        $this->run_manager->getPostData()
                    );
                } else {
                    if (!$this->preview) {
                        $working_data[$data["question_id"]] = $this->object->loadWorkingData(
                            $data["question_id"],
                            $this->run_manager->getCurrentRunId()
                        );
                    } else {
                        $working_data[$data["question_id"]] =
                            $this->run_manager->getPreviewData($data["question_id"]);
                    }
                }
            }

            $page_renderer = new \ILIAS\Survey\Page\PageRenderer(
                $this->object,
                $page,
                $working_data,
                $errors
            );

            $stpl->setVariable("PAGE", $page_renderer->render());

            $this->ctrl->setParameter($this, "qid", $first_question);
            $stpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this, "redirectQuestion"));
            $this->tpl->setContent($stpl->get());
        }

        if (!$this->preview) {
            $this->object->setPage($this->getCurrentRunId(), $page[0]['question_id']);
            $this->run_manager->setStartTime($first_question);
        }
    }

    protected function getCurrentRunId(): int
    {
        return $this->run_manager->getCurrentRunId();
    }

    /**
     * Save the user's input
     */
    public function saveUserInput(
        string $navigationDirection = "next"
    ): int {
        if (!$this->preview) {
            $this->run_manager->setEndTime();
        }

        // check users input when it is a metric question
        $this->run_manager->clearErrors();
        $this->run_manager->setPostData($this->raw_post_data);

        $page_error = 0;
        $page = $this->object->getNextPage($this->request->getQuestionId(), 0);
        foreach ($page as $data) {
            $page_error += $this->saveActiveQuestionData($data);
        }
        if ($page_error && (strcmp($navigationDirection, "previous") !== 0)) {
            if ($page_error === 1) {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt("svy_page_error"), true);
            } else {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt("svy_page_errors"), true);
            }
        } else {
            $page_error = 0;
            $this->run_manager->clearErrors();
        }
        return $page_error;
    }

    /**
     * Saves the users input of the active page
     */
    public function saveActiveQuestionData(array $data): int
    {
        $question = SurveyQuestion::_instanciateQuestion($data["question_id"]);
        $error = $question->checkUserInput($this->raw_post_data, $this->object->getSurveyId());
        if (strlen($error) === 0) {
            if (!$this->preview) {
                // delete old answers
                $this->object->deleteWorkingData($data["question_id"], $this->getCurrentRunId());

                $question->saveUserInput($this->raw_post_data, $this->getCurrentRunId());
            } else {
                $this->run_manager->setPreviewData(
                    $data["question_id"],
                    $question->saveUserInput($this->raw_post_data, $this->getCurrentRunId(), true)
                );
            }
            return 0;
        } else {
            $errors = $this->run_manager->getErrors();
            $errors[$question->getId()] = $error;
            $this->run_manager->setErrors($errors);
            return 1;
        }
    }

    public function cancel(): void
    {
        $this->ctrl->redirectByClass("ilobjsurveygui", "infoScreen");
    }

    /**
     * Show finish page
     */
    public function runShowFinishedPage(): void
    {
        $ilToolbar = $this->toolbar;
        $ilUser = $this->user;

        $has_button = false;


        if (!$this->preview) {
            if ($this->object->hasViewOwnResults()) {
                $button = ilLinkButton::getInstance();
                $button->setCaption("svy_view_own_results");
                $button->setUrl($this->ctrl->getLinkTarget($this, "viewUserResults"));
                $ilToolbar->addButtonInstance($button);

                $has_button = true;
            }

            if ($this->object->hasMailConfirmation()) {
                if ($has_button) {
                    $ilToolbar->addSeparator();
                }

                if ($ilUser->getId() === ANONYMOUS_USER_ID ||
                    !$ilUser->getEmail()) {
                    $mail = new ilTextInputGUI($this->lng->txt("email"), "mail");
                    $mail->setSize(25);
                    $ilToolbar->addInputItem($mail, true);
                }

                $ilToolbar->setFormAction($this->ctrl->getFormAction($this, "mailUserResults"));

                $button = ilSubmitButton::getInstance();
                $button->setCaption("svy_mail_send_confirmation");
                $button->setCommand("mailUserResults");
                $ilToolbar->addButtonInstance($button);

                $has_button = true;
            }

            // #6307
            if (ilObjSurveyAccess::_hasEvaluationAccess($this->object->getId(), $ilUser->getId())) {
                $button = ilLinkButton::getInstance();
                $button->setCaption("svy_results");
                $button->setUrl($this->ctrl->getLinkTargetByClass("ilObjSurveyGUI", "evaluation"));
                $ilToolbar->addButtonInstance($button);

                $has_button = true;
            }
        }

        if (!$has_button &&
            $this->object->getOutro() === '') {
            $this->exitSurvey();
        } else {
            if ($has_button) {
                $ilToolbar->addSeparator();
            }

            $button = ilLinkButton::getInstance();
            $button->setCaption("survey_execution_exit");
            $button->setUrl($this->ctrl->getLinkTarget($this, "exitSurvey"));
            $ilToolbar->addButtonInstance($button);

            if ($this->object->getOutro() !== '') {
                $panel = ilPanelGUI::getInstance();
                $panel->setBody($this->object->prepareTextareaOutput($this->object->getOutro()));
                $this->tpl->setContent($panel->getHTML());
            }
        }
    }

    public function backToRepository(): void
    {
        $tree = $this->tree;

        // #14971
        if ($this->feature_config->usesAppraisees()) {
            $target_ref_id = $this->object->getRefId();
        } else {
            // #11534
            $target_ref_id = $tree->getParentId($this->object->getRefId());
        }

        ilUtil::redirect(ilLink::_getLink($target_ref_id));
    }

    /**
     * Exits the survey after finishing it
     */
    public function exitSurvey(): void
    {
        if (!$this->preview) {
            $this->backToRepository();
        } else {
            // #12841
            $this->ctrl->setParameterByClass("ilsurveyeditorgui", "pgov", $this->request->getTargetPosition());
            $this->ctrl->redirectByClass(array("ilobjsurveygui", "ilsurveyeditorgui"), "questions");
        }
    }

    public function outNavigationButtons(
        string $navigationblock,
        array $page,
        ilTemplate $stpl
    ): void {
        $prevpage = $this->object->getNextPage($page[0]["question_id"], -1);
        $stpl->setCurrentBlock($navigationblock . "_prev");
        if (is_null($prevpage)) {
            $stpl->setVariable("BTN_PREV", $this->lng->txt("survey_start"));
        } else {
            $stpl->setVariable("BTN_PREV", $this->lng->txt("survey_previous"));
        }
        $stpl->parseCurrentBlock();
        $nextpage = $this->object->getNextPage($page[0]["question_id"], 1);
        $stpl->setCurrentBlock($navigationblock . "_next");
        if (is_null($nextpage)) {
            $stpl->setVariable("BTN_NEXT", $this->lng->txt("survey_finish"));
        } else {
            $stpl->setVariable("BTN_NEXT", $this->lng->txt("survey_next"));
        }
        $stpl->parseCurrentBlock();
    }

    public function preview(): void
    {
        $this->outSurveyPage();
    }

    public function viewUserResults(): void
    {
        $ilToolbar = $this->toolbar;

        if (!$this->object->hasViewOwnResults()) {
            $this->backToRepository();
        }

        $this->checkAuth(false, true);

        $button = ilLinkButton::getInstance();
        $button->setCaption("btn_back");
        $button->setUrl($this->ctrl->getLinkTarget($this, "runShowFinishedPage"));
        $ilToolbar->addButtonInstance($button);

        $survey_gui = new ilObjSurveyGUI();
        $html = $survey_gui->getUserResultsTable($this->getCurrentRunId());
        $this->tpl->setContent($html);
    }

    public function mailUserResults(): void
    {
        $ilUser = $this->user;

        if (!$this->object->hasMailConfirmation()) {
            $this->backToRepository();
        }

        $this->checkAuth(false, true);

        $recipient = $this->request->getMail();
        if (!$recipient) {
            $recipient = $ilUser->getEmail();
        }
        if (!ilUtil::is_email($recipient)) {
            $this->ctrl->redirect($this, "runShowFinishedPage");
        }

        $survey_gui = new ilObjSurveyGUI();
        $survey_gui->sendUserResultsMail(
            $this->getCurrentRunId(),
            $recipient
        );

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("mail_sent"), true);
        $this->ctrl->redirect($this, "runShowFinishedPage");
    }

    public function showFinishConfirmation(): void
    {
        $tpl = $this->tpl;

        $cgui = new ilConfirmationGUI();
        $cgui->setHeaderText($this->lng->txt("survey_execution_sure_finish"));

        $cgui->setFormAction($this->ctrl->getFormAction($this, "confirmedFinish"));
        $cgui->setCancel($this->lng->txt("cancel"), "previousNoSave");
        $cgui->setConfirm($this->lng->txt("confirm"), "confirmedFinish");

        $tpl->setContent($cgui->getHTML());
    }

    public function confirmedFinish(): void
    {
        $ilUser = $this->user;

        if (!$this->preview) {
            $this->object->finishSurvey($this->getCurrentRunId(), $this->requested_appr_id);

            if ($ilUser->getId() !== ANONYMOUS_USER_ID) {
                ilLPStatusWrapper::_updateStatus($this->object->getId(), $ilUser->getId());
            }

            // send "single participant has finished" mail to tutors
            if ($this->object->getMailNotification()) {
                $this->object->sendNotificationMail(
                    $ilUser->getId(),
                    $this->run_manager->getCode(),
                    $this->requested_appr_id
                );
            }
        }

        $this->ctrl->redirect($this, "runShowFinishedPage");
    }
}

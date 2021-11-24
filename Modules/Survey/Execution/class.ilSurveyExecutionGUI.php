<?php
 /*
   +----------------------------------------------------------------------------+
   | ILIAS open source                                                          |
   +----------------------------------------------------------------------------+
   | Copyright (c) 1998-2001 ILIAS open source, University of Cologne           |
   |                                                                            |
   | This program is free software; you can redistribute it and/or              |
   | modify it under the terms of the GNU General Public License                |
   | as published by the Free Software Foundation; either version 2             |
   | of the License, or (at your option) any later version.                     |
   |                                                                            |
   | This program is distributed in the hope that it will be useful,            |
   | but WITHOUT ANY WARRANTY; without even the implied warranty of             |
   | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the              |
   | GNU General Public License for more details.                               |
   |                                                                            |
   | You should have received a copy of the GNU General Public License          |
   | along with this program; if not, write to the Free Software                |
   | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA. |
   +----------------------------------------------------------------------------+
*/

/**
* Survey execution graphical output
*
* The ilSurveyExecutionGUI class creates the execution output for the ilObjSurveyGUI
* class. This saves some heap space because the ilObjSurveyGUI class will be
* smaller.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesSurvey
*/
class ilSurveyExecutionGUI
{
    /**
     * @var ilRbacSystem
     */
    protected $rbacsystem;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilHelpGUI
     */
    protected $help;

    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;

    public $object;
    public $lng;
    public $tpl;
    public $ctrl;
    public $tree;
    public $preview;

    /**
    * @var ilLogger
    */
    protected $log;
    
    /**
    * ilSurveyExecutionGUI constructor
    *
    * The constructor takes possible arguments an creates an instance of the ilSurveyExecutionGUI object.
    *
    * @param object $a_object Associated ilObjSurvey class
    * @access public
    */
    public function __construct($a_object)
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
                
        $this->external_rater_360 = false;
        if ($this->object->get360Mode() &&
            $_SESSION["anonymous_id"][$this->object->getId()] &&
            ilObjSurvey::validateExternalRaterCode(
                $this->object->getRefId(),
                $_SESSION["anonymous_id"][$this->object->getId()]
            )) {
            $this->external_rater_360 = true;
        }

        // stay in preview mode
        $this->preview = (bool) $_REQUEST["prvw"];
        $this->ctrl->saveParameter($this, "prvw");
        $this->ctrl->saveParameter($this, "pgov");

        $this->log = ilLoggerFactory::getLogger("svy");
    }
    
    /**
    * execute command
    */
    public function executeCommand()
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

        $cmd = $this->getCommand($cmd);

        $this->log->debug("- cmd= " . $cmd);

        if (strlen($cmd) == 0) {
            $this->ctrl->setParameter($this, "qid", $_GET["qid"]);
            $this->ctrl->redirect($this, "gotoPage");
        }
        switch ($next_class) {
            default:
                $ret = &$this->$cmd();
                break;
        }
        return $ret;
    }
    
    protected function checkAuth($a_may_start = false, $a_ignore_status = false)
    {
        $rbacsystem = $this->rbacsystem;
        $ilUser = $this->user;
        
        if ($this->preview) {
            if (!$rbacsystem->checkAccess("write", $this->object->ref_id)) {
                // only with write access it is possible to preview the survey
                throw new ilSurveyException($this->lng->txt("survey_cannot_preview_survey"));
            }
            
            return true;
        }
                        
        if (!$this->external_rater_360 &&
            !$rbacsystem->checkAccess("read", $this->object->ref_id)) {
            // only with read access it is possible to run the test
            throw new ilSurveyException($this->lng->txt("cannot_read_survey"));
        }
        
        $user_id = $ilUser->getId();

        // check existing code
        // see ilObjSurveyGUI::infoScreen()
        $anonymous_id = $anonymous_code = null;
        if ($this->object->getAnonymize() || !$this->object->isAccessibleWithoutCode()) {
            $anonymous_code = $_SESSION["anonymous_id"][$this->object->getId()];
            $anonymous_id = $this->object->getAnonymousIdByCode($anonymous_code);
            if (!$anonymous_id) {
                ilUtil::sendFailure(sprintf($this->lng->txt("error_retrieving_anonymous_survey"), $anonymous_code, true));
                $this->ctrl->redirectByClass("ilobjsurveygui", "infoScreen");
            }
        }
        
        // appraisee validation
        $appr_id = 0;
        if ($this->object->get360Mode()) {
            $appr_id = $_REQUEST["appr_id"];
            if (!$appr_id) {
                $appr_id = $_SESSION["appr_id"][$this->object->getId()];
            }
            // check if appraisee is valid
            if ($anonymous_id) {
                $appraisees = $this->object->getAppraiseesToRate(0, $anonymous_id);
            }
            if (!$appraisees && $user_id != ANONYMOUS_USER_ID) {
                $appraisees = $this->object->getAppraiseesToRate($user_id);
            }
            if (!in_array($appr_id, $appraisees)) {
                ilUtil::sendFailure($this->lng->txt("survey_360_execution_invalid_appraisee"), true);
                $this->ctrl->redirectByClass("ilobjsurveygui", "infoScreen");
            }
        }
        //Self evaluation mode
        #23575 in self eval the appraisee is the current user.
        if ($this->object->getMode() == ilObjSurvey::MODE_SELF_EVAL) {
            $appr_id = $ilUser->getId();
        }
        
        $_SESSION["appr_id"][$this->object->getId()] = $appr_id;
                    
        if (!$a_ignore_status) {
            $status = $this->object->isSurveyStarted($user_id, $anonymous_code, $appr_id);
            // completed
            if ($status === 1) {
                ilUtil::sendFailure($this->lng->txt("already_completed_survey"), true);
                $this->ctrl->redirectByClass("ilobjsurveygui", "infoScreen");
            }
            // starting
            elseif ($status === false) {
                if ($a_may_start) {
                    $_SESSION["finished_id"][$this->object->getId()] =
                        $this->object->startSurvey($user_id, $anonymous_code, $appr_id);
                } else {
                    ilUtil::sendFailure($this->lng->txt("survey_use_start_button"), true);
                    $this->ctrl->redirectByClass("ilobjsurveygui", "infoScreen");
                }
            }
            // resuming
            else {
                // nothing todo
            }
        }
        
        // validate finished id
        if ($this->object->getActiveID($user_id, $anonymous_code, $appr_id) !=
            $_SESSION["finished_id"][$this->object->getId()]) {
            ilUtil::sendFailure($this->lng->txt("cannot_read_survey"), true);
            $this->ctrl->redirectByClass("ilobjsurveygui", "infoScreen");
        }
    }

    /**
    * Retrieves the ilCtrl command
    *
    * Retrieves the ilCtrl command
    *
    * @access public
    */
    public function getCommand($cmd)
    {
        return $cmd;
    }

    /**
    * Resumes the survey
    *
    * Resumes the survey
    *
    * @access private
    */
    public function resume()
    {
        $this->start(true);
    }
    
    /**
    * Starts the survey
    *
    * Starts the survey
    *
    * @access private
    */
    public function start($resume = false)
    {
        if ($this->preview) {
            unset($_SESSION["preview_data"]);
        }
        unset($_SESSION["svy_errors"]);
        
        $this->checkAuth(!$resume);
                
        $activepage = "";
        if ($resume) {
            $activepage = $this->object->getLastActivePage($_SESSION["finished_id"][$this->object->getId()]);
        }
        
        if (strlen($activepage)) {
            $this->ctrl->setParameter($this, "qid", $activepage);
        }
        $this->ctrl->setParameter($this, "activecommand", "default");
        $this->ctrl->redirect($this, "redirectQuestion");
    }

    /**
    * Called when a user answered a page to perform a redirect after POST.
    * This is called for security reasons to prevent users sending a form twice.
    *
    * @access public
    */
    public function redirectQuestion()
    {
        switch ($_GET["activecommand"]) {
            case "next":
                $this->outSurveyPage($_GET["qid"], $_GET["direction"]);
                break;
            case "previous":
                $this->outSurveyPage($_GET["qid"], $_GET["direction"]);
                break;
            case "gotoPage":
                $this->outSurveyPage($_GET["qid"], $_GET["direction"]);
                break;
            case "default":
                $this->outSurveyPage($_GET["qid"]);
                break;
            default:
                // don't save input, go to the first page
                $this->outSurveyPage();
                break;
        }
    }
    
    public function previousNoSave()
    {
        $this->previous(false);
    }

    /**
    * Navigates to the previous pages
    *
    * Navigates to the previous pages
    *
    * @access private
    */
    public function previous($a_save_input = true)
    {
        if ($a_save_input) {
            // #16209
            $has_error = $this->saveUserInput("previous");
        }
        $this->ctrl->setParameter($this, "activecommand", "previous");
        $this->ctrl->setParameter($this, "qid", $_GET["qid"]);
        if (strlen($has_error)) {
            $this->ctrl->setParameter($this, "direction", "0");
        } else {
            $this->ctrl->setParameter($this, "direction", "-1");
        }
        $this->ctrl->redirect($this, "redirectQuestion");
    }
    
    /**
    * Navigates to the next pages
    *
    * @access private
    */
    public function next()
    {
        $result = $this->saveUserInput("next");
        $this->ctrl->setParameter($this, "activecommand", "next");
        $this->ctrl->setParameter($this, "qid", $_GET["qid"]);
        if (strlen($result)) {
            $this->ctrl->setParameter($this, "direction", "0");
        } else {
            $this->ctrl->setParameter($this, "direction", "1");
        }
        $this->ctrl->redirect($this, "redirectQuestion");
    }
    
    /**
    * Go to a specific page without saving
    *
    * @access private
    */
    public function gotoPage()
    {
        $this->ctrl->setParameter($this, "activecommand", "gotoPage");
        $this->ctrl->setParameter($this, "qid", $_GET["qid"]);
        $this->ctrl->setParameter($this, "direction", "0");
        $this->ctrl->redirect($this, "redirectQuestion");
    }

    /**
    * Output of the active survey question to the screen
    *
    * Output of the active survey question to the screen
    *
    * @access private
    */
    public function outSurveyPage($activepage = null, $direction = null)
    {
        $ilUser = $this->user;
        
        $this->checkAuth();
        
        $page = $this->object->getNextPage($activepage, $direction);
        $constraint_true = 0;
        
        // check for constraints
        if (is_array($page[0]["constraints"]) && count($page[0]["constraints"])) {
            $this->log->debug("Page constraints= ", $page[0]["constraints"]);

            while (is_array($page) and ($constraint_true == 0) and (count($page[0]["constraints"]))) {
                $constraint_true = ($page[0]['constraints'][0]['conjunction'] == 0) ? true : false;
                foreach ($page[0]["constraints"] as $constraint) {
                    if (!$this->preview) {
                        $working_data = $this->object->loadWorkingData($constraint["question"], $_SESSION["finished_id"][$this->object->getId()]);
                    } else {
                        $working_data = $_SESSION["preview_data"][$this->object->getId()][$constraint["question"]];
                    }
                    if ($constraint['conjunction'] == 0) {
                        // and
                        $constraint_true = $constraint_true & $this->object->checkConstraint($constraint, $working_data);
                    } else {
                        // or
                        $constraint_true = $constraint_true | $this->object->checkConstraint($constraint, $working_data);
                    }
                }
                if ($constraint_true == 0) {
                    // #11047 - we are skipping the page, so we have to get rid of existing answers for that question(s)
                    foreach ($page as $page_question) {
                        $qid = $page_question["question_id"];
                                                
                        // see saveActiveQuestionData()
                        if (!$this->preview) {
                            $this->object->deleteWorkingData($qid, $_SESSION["finished_id"][$this->object->getId()]);
                        } else {
                            $_SESSION["preview_data"][$this->object->getId()][$qid] = null;
                        }
                    }
                    
                    $page = $this->object->getNextPage($page[0]["question_id"], $direction);
                }
            }
        }
        
        $first_question = -1;
        if ($page === 0) {
            $this->ctrl->redirectByClass("ilobjsurveygui", "infoScreen");
        } elseif ($page === 1) {
            $state = $this->object->getUserSurveyExecutionStatus();
            if ($this->preview ||
                !$state["runs"][$_SESSION["finished_id"][$this->object->getId()]]["finished"]) {
                $this->showFinishConfirmation();
            } else {
                $this->runShowFinishedPage();
            }
            return;
        } else {
            $ilHelp = $this->help;
            $ilHelp->setScreenIdComponent("svy");
            $ilHelp->setScreenId("quest_presentation");
            
            if ($ilUser->getId() != ANONYMOUS_USER_ID) {
                ilLearningProgress::_tracProgress($ilUser->getId(), $this->object->getId(), $this->object->ref_id, "svy");
            }

            $required = false;
            //$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_svy_content.html", "Modules/Survey");
            $stpl = new ilTemplate("tpl.il_svy_svy_content.html", true, true, "Modules/Survey");
            
            if ($this->object->get360Mode()) {
                $appr_id = $_SESSION["appr_id"][$this->object->getId()];
                
                $this->tpl->setTitle($this->object->getTitle() . " (" .
                    $this->lng->txt("survey_360_appraisee") . ": " .
                    ilUserUtil::getNamePresentation($appr_id) . ")");
            }

            if (!($this->object->getAnonymize() && $this->object->isAccessibleWithoutCode() && ($ilUser->getId() == ANONYMOUS_USER_ID))) {
                $stpl->setCurrentBlock("suspend_survey");

                if (!$this->preview) {
                    $stpl->setVariable("TEXT_SUSPEND", $this->lng->txt("cancel_survey"));
                    $stpl->setVariable("HREF_SUSPEND", $this->ctrl->getLinkTargetByClass("ilObjSurveyGUI", "infoScreen"));
                } else {
                    $this->ctrl->setParameterByClass("ilObjSurveyGUI", "pgov", $_REQUEST["pgov"]);
                    $stpl->setVariable("TEXT_SUSPEND", $this->lng->txt("survey_cancel_preview"));
                    $stpl->setVariable("HREF_SUSPEND", $this->ctrl->getLinkTargetByClass(array("ilObjSurveyGUI", "ilSurveyEditorGUI"), "questions"));
                }

                $stpl->setVariable("ALT_IMG_SUSPEND", $this->lng->txt("cancel_survey"));
                $stpl->setVariable("TITLE_IMG_SUSPEND", $this->lng->txt("cancel_survey"));
                $stpl->parseCurrentBlock();
            }
            $this->outNavigationButtons("top", $page, $stpl);


            $stpl->setCurrentBlock("percentage");
            
            $percentage = (int) (($page[0]["position"]) * 100);
            
            $pbar = ilProgressBar::getInstance();
            $pbar->setCurrent($percentage);
            $stpl->setVariable("NEW_PBAR", $pbar->render());

            $stpl->parseCurrentBlock();
            
            
            if (count($page) > 1 && $page[0]["questionblock_show_blocktitle"]) {
                $stpl->setCurrentBlock("questionblock_title");
                $stpl->setVariable("TEXT_QUESTIONBLOCK_TITLE", $page[0]["questionblock_title"]);
                $stpl->parseCurrentBlock();
            }
            $compress_view = false;
            if (count($page) > 1) {
                $compress_view = $page[0]["questionblock_compress_view"];
            }
            $previous_page = null;

            // set compress view flags
            $previous_key = null;
            foreach ($page as $k => $data) {
                $page[$k]["compressed"] = false;
                $page[$k]["compressed_first"] = false;
                if ($compress_view && $this->compressQuestion($previous_page, $data)) {
                    $page[$k]["compressed"] = true;
                    if ($previous_key !== null && $page[$previous_key]["compressed"] == false) {
                        $page[$previous_key]["compressed_first"] = true;
                    }
                }
                $previous_key = $k;
                $previous_page = $data;
            }
            foreach ($page as $data) {
                if ($data["heading"]) {
                    $stpl->setCurrentBlock("heading");
                    $stpl->setVariable("QUESTION_HEADING", $data["heading"]);
                    $stpl->parseCurrentBlock();
                }
                $stpl->setCurrentBlock("survey_content");
                if ($first_question == -1) {
                    $first_question = $data["question_id"];
                }
                $question_gui = $this->object->getQuestionGUI($data["type_tag"], $data["question_id"]);
                if (is_array($_SESSION["svy_errors"])) {
                    $working_data = &$question_gui->object->getWorkingDataFromUserInput($_SESSION["postdata"]);
                } else {
                    $working_data = $this->object->loadWorkingData($data["question_id"], $_SESSION["finished_id"][$this->object->getId()]);
                }
                $question_gui->object->setObligatory($data["obligatory"]);
                $error_messages = array();
                if (is_array($_SESSION["svy_errors"])) {
                    $error_messages = $_SESSION["svy_errors"];
                }
                $show_questiontext = ($data["questionblock_show_questiontext"]) ? 1 : 0;
                $show_title = ($this->object->getShowQuestionTitles() && !$data["compressed_first"]);
                $question_output = $question_gui->getWorkingForm($working_data, $show_title, $show_questiontext, $error_messages[$data["question_id"]], $this->object->getSurveyId(), $compress_view);
                if ($data["compressed"]) {
                    $question_output = '<div class="il-svy-qst-compressed">' . $question_output . '</div>';
                }
                $stpl->setVariable("QUESTION_OUTPUT", $question_output);
                $this->ctrl->setParameter($this, "qid", $data["question_id"]);
                //$this->tpl->parse("survey_content");
                if ($data["obligatory"]) {
                    $required = true;
                }
                $stpl->parseCurrentBlock();
            }
            if ($required) {
                $stpl->setCurrentBlock("required");
                $stpl->setVariable("TEXT_REQUIRED", $this->lng->txt("required_field"));
                $stpl->parseCurrentBlock();
            }

            $this->outNavigationButtons("bottom", $page, $stpl);

            $stpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this, "redirectQuestion"));
        }
        $this->tpl->setContent($stpl->get());

        if (!$this->preview) {
            $this->object->setPage($_SESSION["finished_id"][$this->object->getId()], $page[0]['question_id']);
            $this->object->setStartTime($_SESSION["finished_id"][$this->object->getId()], $first_question);
        }
    }

    /**
     *
     * @param array $previous_page
     * @param array $page
     * @return bool
     */
    protected function compressQuestion($previous_page, $page)
    {
        if (!$previous_page) {
            return false;
        }

        if ($previous_page["type_tag"] === $page["type_tag"] &&
            $page["type_tag"] === "SurveySingleChoiceQuestion") {
            if (SurveySingleChoiceQuestion::compressable($previous_page["question_id"], $page["question_id"])) {
                return true;
            }
        }

        return false;
    }

    /**
    * Save the user's input
    *
    * @access private
    */
    public function saveUserInput($navigationDirection = "next")
    {
        if (!$this->preview) {
            $this->object->setEndTime($_SESSION["finished_id"][$this->object->getId()]);
        }
        
        // check users input when it is a metric question
        unset($_SESSION["svy_errors"]);
        $_SESSION["postdata"] = $_POST;
        $page_error = 0;
        $page = $this->object->getNextPage($_GET["qid"], 0);
        foreach ($page as $data) {
            $page_error += $this->saveActiveQuestionData($data);
        }
        if ($page_error && (strcmp($navigationDirection, "previous") != 0)) {
            if ($page_error == 1) {
                ilUtil::sendFailure($this->lng->txt("svy_page_error"), true);
            } else {
                ilUtil::sendFailure($this->lng->txt("svy_page_errors"), true);
            }
        } else {
            $page_error = "";
            unset($_SESSION["svy_errors"]);
        }
        return $page_error;
    }

    /**
    * Survey navigation
    *
    * Survey navigation
    *
    * @access private
    */
    /*
    function navigate($navigationDirection = "next")
    {
        // check users input when it is a metric question
        unset($_SESSION["svy_errors"]);
        $page_error = 0;
        $page = $this->object->getNextPage($_GET["qid"], 0);
        foreach ($page as $data)
        {
            $page_error += $this->saveActiveQuestionData($data);
        }
        if ($page_error && (strcmp($navigationDirection, "previous") != 0))
        {
            if ($page_error == 1)
            {
                ilUtil::sendFailure($this->lng->txt("svy_page_error"));
            }
            else
            {
                ilUtil::sendFailure($this->lng->txt("svy_page_errors"));
            }
        }
        else
        {
            $page_error = "";
            unset($_SESSION["svy_errors"]);
        }

        $direction = 0;
        switch ($navigationDirection)
        {
            case "next":
            default:
                $activepage = $_GET["qid"];
                if (!$page_error)
                {
                    $direction = 1;
                }
                break;
            case "previous":
                $activepage = $_GET["qid"];
                if (!$page_error)
                {
                    $direction = -1;
                }
                break;
        }
        $this->outSurveyPage($activepage, $direction);
    }
*/
    
    /**
    * Saves the users input of the active page
    *
    * Saves the users input of the active page
    *
    * @access private
    */
    public function saveActiveQuestionData(&$data)
    {
        $ilUser = $this->user;
        
        $question = &SurveyQuestion::_instanciateQuestion($data["question_id"]);
        $error = $question->checkUserInput($_POST, $this->object->getSurveyId());
        if (strlen($error) == 0) {
            if (!$this->preview) {
                // delete old answers
                $this->object->deleteWorkingData($data["question_id"], $_SESSION["finished_id"][$this->object->getId()]);
        
                $question->saveUserInput($_POST, $_SESSION["finished_id"][$this->object->getId()]);
            } else {
                $_SESSION["preview_data"][$this->object->getId()][$data["question_id"]] =
                    $question->saveUserInput($_POST, $_SESSION["finished_id"][$this->object->getId()], true);
            }
            return 0;
        } else {
            $_SESSION["svy_errors"][$question->getId()] = $error;
            return 1;
        }
    }
    
    /**
    * Called on cancel
    *
    * Called on cancel
    *
    * @access private
    */
    public function cancel()
    {
        $this->ctrl->redirectByClass("ilobjsurveygui", "infoScreen");
    }
    
    /**
    * Creates the finished page for a running survey
    *
    * Creates the finished page for a running survey
    *
    * @access public
    */
    public function runShowFinishedPage()
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

                if ($ilUser->getId() == ANONYMOUS_USER_ID ||
                    !$ilUser->getEmail()) {
                    require_once "Services/Form/classes/class.ilTextInputGUI.php";
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
            strlen($this->object->getOutro()) == 0) {
            $this->exitSurvey();
        } else {
            if ($has_button) {
                $ilToolbar->addSeparator();
            }
            
            $button = ilLinkButton::getInstance();
            $button->setCaption("survey_execution_exit");
            $button->setUrl($this->ctrl->getLinkTarget($this, "exitSurvey"));
            $ilToolbar->addButtonInstance($button);
        
            if (strlen($this->object->getOutro())) {
                $panel = ilPanelGUI::getInstance();
                $panel->setBody($this->object->prepareTextareaOutput($this->object->getOutro()));
                $this->tpl->setContent($panel->getHTML());
            }
        }
    }
    
    public function backToRepository()
    {
        $tree = $this->tree;
        
        // #14971
        if ($this->object->get360Mode()) {
            $target_ref_id = $this->object->getRefId();
        } else {
            // #11534
            $target_ref_id = $tree->getParentId($this->object->getRefId());
        }
                
        ilUtil::redirect(ilLink::_getLink($target_ref_id));
    }

    /**
    * Exits the survey after finishing it
    *
    * Exits the survey after finishing it
    *
    * @access public
    */
    public function exitSurvey()
    {
        if (!$this->preview) {
            $this->backToRepository();
        } else {
            // #12841
            $this->ctrl->setParameterByClass("ilsurveyeditorgui", "pgov", $_REQUEST["pgov"]);
            $this->ctrl->redirectByClass(array("ilobjsurveygui", "ilsurveyeditorgui"), "questions");
        }
    }
    
    /**
    * Creates the navigation buttons for a survey
    *
    * Creates the navigation buttons for a survey.
    * Runs twice to generate a top and a bottom navigation to
    * ease the use of long forms.
    *
    * @access public
    */
    public function outNavigationButtons($navigationblock = "top", $page, $stpl)
    {
        $prevpage = $this->object->getNextPage($page[0]["question_id"], -1);
        $stpl->setCurrentBlock($navigationblock . "_prev");
        if ($prevpage === 0) {
            $stpl->setVariable("BTN_PREV", $this->lng->txt("survey_start"));
        } else {
            $stpl->setVariable("BTN_PREV", $this->lng->txt("survey_previous"));
        }
        $stpl->parseCurrentBlock();
        $nextpage = $this->object->getNextPage($page[0]["question_id"], 1);
        $stpl->setCurrentBlock($navigationblock . "_next");
        if ($nextpage === 1) {
            $stpl->setVariable("BTN_NEXT", $this->lng->txt("survey_finish"));
        } else {
            $stpl->setVariable("BTN_NEXT", $this->lng->txt("survey_next"));
        }
        $stpl->parseCurrentBlock();
    }

    public function preview()
    {
        $this->outSurveyPage();
    }
    
    public function viewUserResults()
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
        $html = $survey_gui->getUserResultsTable($_SESSION["finished_id"][$this->object->getId()]);
        $this->tpl->setContent($html);
    }
    
    public function mailUserResults()
    {
        $ilUser = $this->user;

        if (!$this->object->hasMailConfirmation()) {
            $this->backToRepository();
        }
        
        $this->checkAuth(false, true);
        
        $recipient = $_POST["mail"];
        if (!$recipient) {
            $recipient = $ilUser->getEmail();
        }
        if (!ilUtil::is_email($recipient)) {
            $this->ctrl->redirect($this, "runShowFinishedPage");
        }
        
        $survey_gui = new ilObjSurveyGUI();
        $survey_gui->sendUserResultsMail(
            $_SESSION["finished_id"][$this->object->getId()],
            $recipient
        );
        
        ilUtil::sendSuccess($this->lng->txt("mail_sent"), true);
        $this->ctrl->redirect($this, "runShowFinishedPage");
    }
    
    public function showFinishConfirmation()
    {
        $tpl = $this->tpl;
        
        $cgui = new ilConfirmationGUI();
        $cgui->setHeaderText($this->lng->txt("survey_execution_sure_finish"));

        $cgui->setFormAction($this->ctrl->getFormAction($this, "confirmedFinish"));
        $cgui->setCancel($this->lng->txt("cancel"), "previousNoSave");
        $cgui->setConfirm($this->lng->txt("confirm"), "confirmedFinish");
        
        $tpl->setContent($cgui->getHTML());
    }
    
    public function confirmedFinish()
    {
        $ilUser = $this->user;
        
        if (!$this->preview) {
            $this->object->finishSurvey($_SESSION["finished_id"][$this->object->getId()]);
                        
            if ($ilUser->getId() != ANONYMOUS_USER_ID) {
                ilLPStatusWrapper::_updateStatus($this->object->getId(), $ilUser->getId());
            }
                                    
            if ($this->object->getMailNotification()) {
                $this->object->sendNotificationMail(
                    $ilUser->getId(),
                    $_SESSION["anonymous_id"][$this->object->getId()],
                    $_SESSION["appr_id"][$this->object->getId()]
                );
            }
        }

        $this->ctrl->redirect($this, "runShowFinishedPage");
    }
}

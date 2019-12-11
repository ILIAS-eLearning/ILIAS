<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Modules/TestQuestionPool/classes/class.assQuestionGUI.php';
require_once './Modules/TestQuestionPool/interfaces/interface.ilGuiQuestionScoringAdjustable.php';
include_once './Modules/Test/classes/inc.AssessmentConstants.php';

/**
 * The assFlashQuestionGUI class encapsulates the GUI representation
 * for flash questions.
 *
 * @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @author		Björn Heyser <bheyser@databay.de>
 * @author		Maximilian Becker <mbecker@databay.de>
 *
 * @version	$Id$
 *
 * @ingroup ModulesTestQuestionPool
 *
 * @ilctrl_iscalledby assFlashQuestionGUI: ilObjQuestionPoolGUI
 * @ilCtrl_Calls assFlashQuestionGUI: ilFormPropertyDispatchGUI
 */
class assFlashQuestionGUI extends assQuestionGUI implements ilGuiQuestionScoringAdjustable
{
    private $newUnitId;
    
    /**
    * assFlashQuestionGUI constructor
    *
    * The constructor takes possible arguments an creates an instance of the assFlashQuestionGUI object.
    *
    * @param integer $id The database id of a single choice question object
    * @access public
    */
    public function __construct($id = -1)
    {
        parent::__construct();
        include_once "./Modules/TestQuestionPool/classes/class.assFlashQuestion.php";
        $this->object = new assFlashQuestion();
        $this->newUnitId = null;
        if ($id >= 0) {
            $this->object->loadFromDb($id);
        }
    }

    public function getCommand($cmd)
    {
        if (preg_match("/suggestrange_(.*?)/", $cmd, $matches)) {
            $cmd = "suggestRange";
        }
        return $cmd;
    }

    /**
    * Suggest a range for a result
    *
    * @access public
    */
    public function suggestRange()
    {
        if ($this->writePostData()) {
            ilUtil::sendInfo($this->getErrorMessage());
        }
        $this->editQuestion();
    }

    /**
     * {@inheritdoc}
     */
    protected function writePostData($always = false)
    {
        $hasErrors = (!$always) ? $this->editQuestion(true) : false;
        if (!$hasErrors) {
            require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
            $this->writeQuestionGenericPostData();
            $this->writeQuestionSpecificPostData(new ilPropertyFormGUI());

            $this->saveTaxonomyAssignments();
            return 0;
        }
        return 1;
    }

    public function writeQuestionSpecificPostData(ilPropertyFormGUI $form)
    {
        $this->setErrorMessage("");
        if ($_POST['flash']['delete'] == 1) {
            $this->object->deleteApplet();
        } else {
            $this->object->setApplet($_POST['flash']['filename']);
        }
        if ($_FILES["flash"]["tmp_name"]) {
            $this->object->deleteApplet();
            $filename = $this->object->moveUploadedFile($_FILES["flash"]["tmp_name"], $_FILES["flash"]["name"]);
            $this->object->setApplet($filename);
        }
        $this->object->clearParameters();
        if (is_array($_POST["flash"]["flash_param_name"])) {
            foreach ($_POST['flash']['flash_param_name'] as $idx => $val) {
                $this->object->addParameter($val, $_POST['flash']['flash_param_value'][$idx]);
            }
        }
        if (is_array($_POST['flash']['flash_param_delete'])) {
            foreach ($_POST['flash']['flash_param_delete'] as $key => $value) {
                $this->object->removeParameter($_POST['flash']['flash_param_name'][$key]);
            }
        }

        $this->object->setWidth($_POST["flash"]["width"]);
        $this->object->setHeight($_POST["flash"]["height"]);
        $this->object->setPoints($_POST["points"]);
    }

    /**
    * Creates an output of the edit form for the question
    *
    * @access public
    */
    public function editQuestion($checkonly = false)
    {
        //$save = ((strcmp($this->ctrl->getCmd(), "save") == 0) || (strcmp($this->ctrl->getCmd(), "saveEdit") == 0)) ? TRUE : FALSE;
        $save = $this->isSaveCommand();
        $this->getQuestionTemplate();

        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $this->editForm = $form;

        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->outQuestionType());
        $form->setMultipart(true);
        $form->setTableWidth("100%");
        $form->setId("flash");

        $this->addBasicQuestionFormProperties($form);
        $this->populateQuestionSpecificFormPart($form);

        $this->populateTaxonomyFormSection($form);

        $this->addQuestionFormCommandButtons($form);
        
        $errors = false;
    
        if ($save) {
            $form->setValuesByPost();
            $errors = !$form->checkInput();
            $form->setValuesByPost(); // again, because checkInput now performs the whole stripSlashes handling and we need this if we don't want to have duplication of backslashes
            if ($errors) {
                $checkonly = false;
            }
        }

        if (!$checkonly) {
            $this->tpl->setVariable("QUESTION_DATA", $form->getHTML());
        }
        return $errors;
    }

    public function populateQuestionSpecificFormPart(ilPropertyFormGUI $form)
    {
        // flash file
        $flash = new ilFlashFileInputGUI($this->lng->txt("flashfile"), "flash");
        $flash->setRequired(true);
        if (strlen($this->object->getApplet())) {
            $flash->setApplet($this->object->getApplet());
            $flash->setAppletPathWeb($this->object->getFlashPathWeb());
        }
        $flash->setWidth($this->object->getWidth());
        $flash->setHeight($this->object->getHeight());
        $flash->setParameters($this->object->getParameters());
        $form->addItem($flash);
        if ($this->object->getId()) {
            $hidden = new ilHiddenInputGUI("", "ID");
            $hidden->setValue($this->object->getId());
            $form->addItem($hidden);
        }
        // points
        $points = new ilNumberInputGUI($this->lng->txt("points"), "points");
        $points->setValue($this->object->getPoints());
        $points->setRequired(true);
        $points->setSize(3);
        $points->setMinValue(0.0);
        $form->addItem($points);

        return $form;
    }

    public function flashAddParam()
    {
        $this->writePostData();
        $this->object->addParameter("", "");
        $this->editQuestion();
    }

    /**
    * Get the question solution output
    *
    * @param integer $active_id The active user id
    * @param integer $pass The test pass
    * @param boolean $graphicalOutput Show visual feedback for right/wrong answers
    * @param boolean $result_output Show the reached points for parts of the question
    * @param boolean $show_question_only Show the question without the ILIAS content around
    * @param boolean $show_feedback Show the question feedback
    * @param boolean $show_correct_solution Show the correct solution instead of the user solution
    * @param boolean $show_manual_scoring Show specific information for the manual scoring output
    * @return The solution output of the question as HTML code
    */
    public function getSolutionOutput(
        $active_id,
        $pass = null,
        $graphicalOutput = false,
        $result_output = false,
        $show_question_only = true,
        $show_feedback = false,
        $show_correct_solution = false,
        $show_manual_scoring = false,
        $show_question_text = true
    ) {
        // get the solution of the user for the active pass or from the last pass if allowed
        $template = new ilTemplate("tpl.il_as_qpl_flash_question_output_solution.html", true, true, "Modules/TestQuestionPool");

        $params = array();
        if (is_array($this->object->getParameters())) {
            foreach ($this->object->getParameters() as $name => $value) {
                array_push($params, urlencode($name) . "=" . urlencode($value));
            }
        }

        array_push($params, "session_id=" . urlencode($_COOKIE[session_name()]));
        array_push($params, "client=" . urlencode(CLIENT_ID));
        array_push($params, "points_max=" . urlencode($this->object->getPoints()));
        array_push($params, "server=" . urlencode(ilUtil::removeTrailingPathSeparators(ILIAS_HTTP_PATH) . "/webservice/soap/server.php?wsdl"));
        if (!is_null($pass)) {
            array_push($params, "pass=" . $pass);
        } else {
            include_once "./Modules/Test/classes/class.ilObjTest.php";
            array_push($params, "pass=" . ilObjTest::_getPass($active_id));
        }
        if ($active_id) {
            array_push($params, "active_id=" . $active_id);
        }
        array_push($params, "question_id=" . $this->object->getId());

        if ($show_correct_solution) {
            array_push($params, "solution=correct");
        } else {
            array_push($params, "solution=user");
        }

        if (($active_id > 0) && (!$show_correct_solution)) {
            if ($graphicalOutput) {
                // output of ok/not ok icons for user entered solutions
                $reached_points = $this->object->getReachedPoints($active_id, $pass);
                if ($reached_points == $this->object->getMaximumPoints()) {
                    $template->setCurrentBlock("icon_ok");
                    $template->setVariable("ICON_OK", ilUtil::getImagePath("icon_ok.svg"));
                    $template->setVariable("TEXT_OK", $this->lng->txt("answer_is_right"));
                    $template->parseCurrentBlock();
                } else {
                    $template->setCurrentBlock("icon_ok");
                    if ($reached_points > 0) {
                        $template->setVariable("ICON_NOT_OK", ilUtil::getImagePath("icon_mostly_ok.svg"));
                        $template->setVariable("TEXT_NOT_OK", $this->lng->txt("answer_is_not_correct_but_positive"));
                    } else {
                        $template->setVariable("ICON_NOT_OK", ilUtil::getImagePath("icon_not_ok.svg"));
                        $template->setVariable("TEXT_NOT_OK", $this->lng->txt("answer_is_wrong"));
                    }
                    $template->parseCurrentBlock();
                }
            }
        }

        if (count($params)) {
            $template->setCurrentBlock("flash_vars");
            $template->setVariable("FLASH_VARS", join($params, "&"));
            $template->parseCurrentBlock();
            $template->setCurrentBlock("applet_parameters");
            $template->setVariable("PARAM_VALUE", join($params, "&"));
            $template->parseCurrentBlock();
        }
        if ($show_question_text==true) {
            $template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($this->object->getQuestion(), true));
        }
        $template->setVariable("APPLET_WIDTH", $this->object->getWidth());
        $template->setVariable("APPLET_HEIGHT", $this->object->getHeight());
        $template->setVariable("ID", $this->object->getId());
        $template->setVariable("APPLET_PATH", $this->object->getFlashPathWeb() . $this->object->getApplet());
        $template->setVariable("APPLET_FILE", $this->object->getApplet());

        $questionoutput = $template->get();
        $solutiontemplate = new ilTemplate("tpl.il_as_tst_solution_output.html", true, true, "Modules/TestQuestionPool");
        $solutiontemplate->setVariable("SOLUTION_OUTPUT", $questionoutput);
        $solutionoutput = $solutiontemplate->get();
        if (!$show_question_only) {
            // get page object output
            $solutionoutput = $this->getILIASPage($solutionoutput);
        }
        return $solutionoutput;
    }
    
    public function getPreview($show_question_only = false, $showInlineFeedback = false)
    {
        $template = new ilTemplate("tpl.il_as_qpl_flash_question_output.html", true, true, "Modules/TestQuestionPool");
        $params = array();
        if (is_array($this->object->getParameters())) {
            foreach ($this->object->getParameters() as $name => $value) {
                array_push($params, urlencode($name) . "=" . urlencode($value));
            }
        }
        if (count($params)) {
            $template->setCurrentBlock("flash_vars");
            $template->setVariable("FLASH_VARS", join($params, "&"));
            $template->parseCurrentBlock();
            $template->setCurrentBlock("applet_parameters");
            $template->setVariable("PARAM_VALUE", join($params, "&"));
            $template->parseCurrentBlock();
        }
        $template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($this->object->getQuestion(), true));
        $template->setVariable("APPLET_WIDTH", $this->object->getWidth());
        $template->setVariable("APPLET_HEIGHT", $this->object->getHeight());
        $template->setVariable("ID", $this->object->getId());
        $template->setVariable("APPLET_PATH", $this->object->getFlashPathWeb() . $this->object->getApplet());
        $template->setVariable("APPLET_FILE", $this->object->getApplet());
        $questionoutput = $template->get();
        if (!$show_question_only) {
            // get page object output
            $questionoutput = $this->getILIASPage($questionoutput);
        }
        return $questionoutput;
    }

    // hey: prevPassSolutions - pass will be always available from now on
    public function getTestOutput($active_id, $pass, $is_postponed = false, $use_post_solutions = false, $show_feedback = false)
    // hey.
    {
        // generate the question output
        $template = new ilTemplate("tpl.il_as_qpl_flash_question_output.html", true, true, "Modules/TestQuestionPool");
        $params = array();
        if (is_array($this->object->getParameters())) {
            foreach ($this->object->getParameters() as $name => $value) {
                array_push($params, urlencode($name) . "=" . urlencode($value));
            }
        }

        array_push($params, "session_id=" . urlencode($_COOKIE[session_name()]));
        array_push($params, "client=" . urlencode(CLIENT_ID));
        array_push($params, "points_max=" . urlencode($this->object->getPoints()));
        array_push($params, "server=" . urlencode(ilUtil::removeTrailingPathSeparators(ILIAS_HTTP_PATH) . "/webservice/soap/server.php?wsdl"));
        if (strlen($pass)) {
            array_push($params, "pass=" . $pass);
        } else {
            include_once "./Modules/Test/classes/class.ilObjTest.php";
            array_push($params, "pass=" . ilObjTest::_getPass($active_id));
        }
        if ($active_id) {
            array_push($params, "active_id=" . $active_id);
        }
        array_push($params, "question_id=" . $this->object->getId());

        if (count($params)) {
            $template->setCurrentBlock("flash_vars");
            $template->setVariable("FLASH_VARS", join($params, "&"));
            $template->parseCurrentBlock();
            $template->setCurrentBlock("applet_parameters");
            $template->setVariable("PARAM_VALUE", join($params, "&"));
            $template->parseCurrentBlock();
        }
        $template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($this->object->getQuestion(), true));
        $template->setVariable("APPLET_WIDTH", $this->object->getWidth());
        $template->setVariable("APPLET_HEIGHT", $this->object->getHeight());
        $template->setVariable("ID", $this->object->getId());
        $template->setVariable("APPLET_PATH", $this->object->getFlashPathWeb() . $this->object->getApplet());
        $template->setVariable("APPLET_FILE", $this->object->getFlashPathWeb() . $this->object->getApplet());
        $questionoutput = $template->get();
        
        $pageoutput = $this->outQuestionPage("", $is_postponed, $active_id, $questionoutput);
        return $pageoutput;
    }

    /**
     * Sets the ILIAS tabs for this question type
     *
     * @access public
     *
     * @todo:	MOVE THIS STEPS TO COMMON QUESTION CLASS assQuestionGUI
     */
    public function setQuestionTabs()
    {
        global $DIC;
        $rbacsystem = $DIC['rbacsystem'];
        $ilTabs = $DIC['ilTabs'];

        $ilTabs->clearTargets();
        
        $this->ctrl->setParameterByClass("ilAssQuestionPageGUI", "q_id", $_GET["q_id"]);
        include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
        $q_type = $this->object->getQuestionType();

        if (strlen($q_type)) {
            $classname = $q_type . "GUI";
            $this->ctrl->setParameterByClass(strtolower($classname), "sel_question_types", $q_type);
            $this->ctrl->setParameterByClass(strtolower($classname), "q_id", $_GET["q_id"]);
        }

        if ($_GET["q_id"]) {
            if ($rbacsystem->checkAccess('write', $_GET["ref_id"])) {
                // edit page
                $ilTabs->addTarget(
                    "edit_page",
                    $this->ctrl->getLinkTargetByClass("ilAssQuestionPageGUI", "edit"),
                    array("edit", "insert", "exec_pg"),
                    "",
                    "",
                    $force_active
                );
            }

            $this->addTab_QuestionPreview($ilTabs);
        }

        $force_active = false;
        if ($rbacsystem->checkAccess('write', $_GET["ref_id"])) {
            $url = "";
            if ($classname) {
                $url = $this->ctrl->getLinkTargetByClass($classname, "editQuestion");
            }
            $commands = $_POST["cmd"];
            if (is_array($commands)) {
                foreach ($commands as $key => $value) {
                    if (preg_match("/^suggestrange_.*/", $key, $matches)) {
                        $force_active = true;
                    }
                }
            }
            // edit question properties
            $ilTabs->addTarget(
                "edit_question",
                $url,
                array("editQuestion", "save", "flashAddParam", "saveEdit", "originalSyncForm"),
                $classname,
                "",
                $force_active
            );
        }

        // add tab for question feedback within common class assQuestionGUI
        $this->addTab_QuestionFeedback($ilTabs);

        // add tab for question hint within common class assQuestionGUI
        $this->addTab_QuestionHints($ilTabs);

        // add tab for question's suggested solution within common class assQuestionGUI
        $this->addTab_SuggestedSolution($ilTabs, $classname);

        // Assessment of questions sub menu entry
        if ($_GET["q_id"]) {
            $ilTabs->addTarget(
                "statistics",
                $this->ctrl->getLinkTargetByClass($classname, "assessment"),
                array("assessment"),
                $classname,
                ""
            );
        }

        $this->addBackTab($ilTabs);
    }
    
    public function getSpecificFeedbackOutput($userSolution)
    {
        $output = "";
        return $this->object->prepareTextareaOutput($output, true);
    }

    /**
     * Returns a list of postvars which will be suppressed in the form output when used in scoring adjustment.
     * The form elements will be shown disabled, so the users see the usual form but can only edit the settings, which
     * make sense in the given context.
     *
     * E.g. array('cloze_type', 'image_filename')
     *
     * @return string[]
     */
    public function getAfterParticipationSuppressionQuestionPostVars()
    {
        return array();
    }

    /**
     * Returns an html string containing a question specific representation of the answers so far
     * given in the test for use in the right column in the scoring adjustment user interface.
     *
     * @param array $relevant_answers
     *
     * @return string
     */
    public function getAggregatedAnswersView($relevant_answers)
    {
        // Empty implementation here since a feasible way to aggregate answer is not known.
        return ''; //print_r($relevant_answers,true);
    }
}

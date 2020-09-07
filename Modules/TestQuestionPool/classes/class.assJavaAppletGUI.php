<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Modules/TestQuestionPool/classes/class.assQuestionGUI.php';
require_once './Modules/TestQuestionPool/interfaces/interface.ilGuiQuestionScoringAdjustable.php';
include_once './Modules/Test/classes/inc.AssessmentConstants.php';

/**
 * Java applet question GUI representation
 *
 * The assJavaAppletGUI class encapsulates the GUI representation
 * for java applet questions.
 *
 * @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @author		Björn Heyser <bheyser@databay.de>
 * @author		Maximilian Becker <mbecker@databay.de>
 *
 * @version	$Id$
 *
 * @ingroup ModulesTestQuestionPool
 * @ilCtrl_Calls assJavaAppletGUI: ilFormPropertyDispatchGUI
 */
class assJavaAppletGUI extends assQuestionGUI implements ilGuiQuestionScoringAdjustable
{
    /**
     * assJavaAppletGUI constructor
     *
     * The constructor takes possible arguments an creates an instance of the assJavaAppletGUI object.
     *
     * @param integer $id The database id of a image map question object
     *
     * @return \assJavaAppletGUI
     */
    public function __construct($id = -1)
    {
        parent::__construct();
        include_once './Modules/TestQuestionPool/classes/class.assJavaApplet.php';
        $this->object = new assJavaApplet();
        if ($id >= 0) {
            $this->object->loadFromDb($id);
        }
    }

    public function getCommand($cmd)
    {
        if (substr($cmd, 0, 6) == "delete") {
            $cmd = "delete";
        }
        return $cmd;
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
            
            $this->object->setPoints($_POST["points"]);
            $this->writeQuestionSpecificPostData(new ilPropertyFormGUI());

            $this->saveTaxonomyAssignments();
            
            return 0;
        }
        return 1;
    }

    public function writeQuestionSpecificPostData(ilPropertyFormGUI $form)
    {
        if ($_POST['delete_applet']) {
            // delete the applet file
            $this->object->deleteJavaAppletFilename();
        } else {
            $this->object->setJavaAppletFilename($_POST['uploaded_javaapplet']);
        }

        //setting java applet
        if (!empty($_FILES['javaappletName']['tmp_name'])) {
            $this->object->setJavaAppletFilename(
                $_FILES['javaappletName']['name'],
                $_FILES['javaappletName']['tmp_name']
            );
        }
        $this->object->setJavaCode($_POST["java_code"]);
        $this->object->setJavaCodebase($_POST["java_codebase"]);
        $this->object->setJavaArchive($_POST["java_archive"]);
        $this->object->setJavaWidth($_POST["java_width"]);
        $this->object->setJavaHeight($_POST["java_height"]);

        $this->object->flushParams();
        if (is_array($_POST['kvp']['key'])) {
            foreach ($_POST['kvp']['key'] as $idx => $val) {
                if (strlen($val) && strlen($_POST['kvp']['value'][$idx])) {
                    $this->object->addParameter($val, $_POST['kvp']['value'][$idx]);
                }
            }
        }
    }

    /**
    * Creates an output of the edit form for the question
    *
    */
    public function editQuestion($checkonly = false)
    {
        $save = $this->isSaveCommand();
        $this->getQuestionTemplate();

        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $this->editForm = $form;

        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->outQuestionType());
        $form->setMultipart(true);
        $form->setTableWidth("100%");
        $form->setId("assjavaapplet");

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

    public function populateQuestionSpecificFormPart(\ilPropertyFormGUI $form)
    {
        // points
        $points = new ilNumberInputGUI($this->lng->txt("points"), "points");
        $points->setValue($this->object->getPoints());
        $points->setRequired(true);
        $points->setSize(3);
        $points->setMinValue(0.0);
        $form->addItem($points);

        $header = new ilFormSectionHeaderGUI();
        $header->setTitle($this->lng->txt("applet_attributes"));
        $form->addItem($header);

        // java applet
        $javaapplet = $this->object->getJavaAppletFilename();
        $applet = new ilFileInputGUI($this->lng->txt('javaapplet'), 'javaappletName');
        $applet->setSuffixes(array( 'jar', 'class' ));
        $applet->setRequired(false);

        if (strlen($javaapplet)) {
            $filename = new ilNonEditableValueGUI($this->lng->txt('filename'), 'uploaded_javaapplet');
            $filename->setValue($javaapplet);
            $applet->addSubItem($filename);

            $delete = new ilCheckboxInputGUI('', 'delete_applet');
            $delete->setOptionTitle($this->lng->txt('delete'));
            $delete->setValue(1);
            $applet->addSubItem($delete);
        }
        $form->addItem($applet);

        // Code
        $code = new ilTextInputGUI($this->lng->txt("code"), "java_code");
        $code->setValue($this->object->getJavaCode());
        $code->setRequired(true);
        $form->addItem($code);

        if (!strlen($javaapplet)) {
            // Archive
            $archive = new ilTextInputGUI($this->lng->txt("archive"), "java_archive");
            $archive->setValue($this->object->getJavaArchive());
            $archive->setRequired(false);
            $form->addItem($archive);

            // Codebase
            $codebase = new ilTextInputGUI($this->lng->txt("codebase"), "java_codebase");
            $codebase->setValue($this->object->getJavaCodebase());
            $codebase->setRequired(false);
            $form->addItem($codebase);
        }

        // Width
        $width = new ilNumberInputGUI($this->lng->txt("width"), "java_width");
        $width->setDecimals(0);
        $width->setSize(6);
        $width->setMinValue(50);
        $width->setMaxLength(6);
        $width->setValue($this->object->getJavaWidth());
        $width->setRequired(true);
        $form->addItem($width);

        // Height
        $height = new ilNumberInputGUI($this->lng->txt("height"), "java_height");
        $height->setDecimals(0);
        $height->setSize(6);
        $height->setMinValue(50);
        $height->setMaxLength(6);
        $height->setValue($this->object->getJavaHeight());
        $height->setRequired(true);
        $form->addItem($height);

        $header = new ilFormSectionHeaderGUI();
        $header->setTitle($this->lng->txt("applet_parameters"));
        $form->addItem($header);

        include_once "./Modules/TestQuestionPool/classes/class.ilKVPWizardInputGUI.php";
        $kvp = new ilKVPWizardInputGUI($this->lng->txt("applet_parameters"), "kvp");
        $values = array();
        for ($i = 0; $i < $this->object->getParameterCount(); $i++) {
            $param = $this->object->getParameter($i);
            array_push($values, array( $param['name'], $param['value'] ));
        }
        if (count($values) == 0) {
            array_push($values, array( "", "" ));
        }
        $kvp->setKeyName($this->lng->txt('name'));
        $kvp->setValueName($this->lng->txt('value'));
        $kvp->setValues($values);
        $form->addItem($kvp);
    }

    /**
    * Add a new answer
    */
    public function addkvp()
    {
        $this->writePostData(true);
        $position = key($_POST['cmd']['addkvp']);
        $this->object->addParameterAtIndex($position + 1, "", "");
        $this->editQuestion();
    }

    /**
    * Remove an answer
    */
    public function removekvp()
    {
        $this->writePostData(true);
        $position = key($_POST['cmd']['removekvp']);
        $this->object->removeParameter($position);
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
        $userdata = $this->object->getActiveUserData($active_id);

        // generate the question output
        include_once "./Services/UICore/classes/class.ilTemplate.php";
        include_once "./Modules/Test/classes/class.ilObjTest.php";
        $template = new ilTemplate("tpl.il_as_qpl_javaapplet_question_output_solution.html", true, true, "Modules/TestQuestionPool");
        $solutiontemplate = new ilTemplate("tpl.il_as_tst_solution_output.html", true, true, "Modules/TestQuestionPool");
        if (strlen($userdata["test_id"])) {
            $template->setCurrentBlock("appletparam");
            $template->setVariable("PARAM_NAME", "test_type");
            if (ilObjTest::_lookupAnonymity(ilObjTest::_getObjectIDFromTestID($userdata["test_id"]))) {
                $template->setVariable("PARAM_VALUE", "0");
            } else {
                $template->setVariable("PARAM_VALUE", "1");
            }
            $template->parseCurrentBlock();
        }
        if (strlen($userdata["test_id"])) {
            $template->setCurrentBlock("appletparam");
            $template->setVariable("PARAM_NAME", "test_id");
            $template->setVariable("PARAM_VALUE", $userdata["test_id"]);
            $template->parseCurrentBlock();
        }
        $template->setCurrentBlock("appletparam");
        $template->setVariable("PARAM_NAME", "active_id");
        $template->setVariable("PARAM_VALUE", $active_id);
        $template->parseCurrentBlock();
        $template->setCurrentBlock("appletparam");
        $template->setVariable("PARAM_NAME", "question_id");
        $template->setVariable("PARAM_VALUE", $this->object->getId());
        $template->parseCurrentBlock();
        if (strlen($userdata["user_id"])) {
            $template->setCurrentBlock("appletparam");
            $template->setVariable("PARAM_NAME", "user_id");
            $template->setVariable("PARAM_VALUE", $userdata["user_id"]);
            $template->parseCurrentBlock();
        }
        $template->setCurrentBlock("appletparam");
        $template->setVariable("PARAM_NAME", "points_max");
        $template->setVariable("PARAM_VALUE", $this->object->getPoints());
        $template->parseCurrentBlock();
        $template->setCurrentBlock("appletparam");
        $template->setVariable("PARAM_NAME", "session_id");
        $template->setVariable("PARAM_VALUE", $_COOKIE[session_name()]);
        $template->parseCurrentBlock();
        $template->setCurrentBlock("appletparam");
        $template->setVariable("PARAM_NAME", "client");
        $template->setVariable("PARAM_VALUE", CLIENT_ID);
        $template->parseCurrentBlock();
        $template->setCurrentBlock("appletparam");
        $template->setVariable("PARAM_NAME", "pass");
        $actualpass = ilObjTest::_getPass($active_id);
        $template->setVariable("PARAM_VALUE", $actualpass);
        $template->parseCurrentBlock();
        // additional parameters
        for ($i = 0; $i < $this->object->getParameterCount(); $i++) {
            $parameter = $this->object->getParameter($i);
            $template->setCurrentBlock("appletparam");
            $template->setVariable("PARAM_NAME", $parameter["name"]);
            $template->setVariable("PARAM_VALUE", $parameter["value"]);
            $template->parseCurrentBlock();
        }

        if (($active_id > 0) && (!$show_correct_solution)) {
            $solutions = null;
            include_once "./Modules/Test/classes/class.ilObjTest.php";
            $info = $this->object->getSolutionValues($active_id, $pass);
            foreach ($info as $kk => $infodata) {
                $template->setCurrentBlock("appletparam");
                $template->setVariable("PARAM_NAME", "value_" . $infodata["order"] . "_1");
                $template->setVariable("PARAM_VALUE", $infodata["value1"]);
                $template->parseCurrentBlock();
                $template->setCurrentBlock("appletparam");
                $template->setVariable("PARAM_NAME", "value_" . $infodata["order"] . "_2");
                $template->setVariable("PARAM_VALUE", $infodata["value2"]);
                $template->parseCurrentBlock();
            }
        }
        
        $questiontext = $this->object->getQuestion();
        if ($show_question_text == true) {
            $template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($questiontext, true));
        }
        $template->setVariable("APPLET_WIDTH", $this->object->getJavaWidth());
        $template->setVariable("APPLET_HEIGHT", $this->object->getJavaHeight());
        $template->setVariable("APPLET_CODE", $this->object->getJavaCode());
        if (strlen($this->object->getJavaArchive()) > 0) {
            $template->setVariable("APPLET_ARCHIVE", " archive=\"" . $this->object->getJavaArchive() . "\"");
        } else {
            if (strpos($this->object->getJavaAppletFilename(), ".jar") !== false) {
                $template->setVariable("APPLET_ARCHIVE", " archive=\"" . $this->object->getJavaPathWeb() . $this->object->getJavaAppletFilename() . "\"");
            }
        }
        if (strlen($this->object->getJavaCodebase()) > 0) {
            $template->setVariable("APPLET_CODEBASE", " codebase=\"" . $this->object->getJavaCodebase() . "\"");
        } else {
            if (strpos($this->object->getJavaAppletFilename(), ".class") !== false) {
                $template->setVariable("APPLET_CODEBASE", " codebase=\"" . $this->object->getJavaPathWeb() . "\"");
            }
        }
        if (($active_id > 0) && (!$show_correct_solution)) {
            if ($graphicalOutput) {
                // output of ok/not ok icons for user entered solutions
                $reached_points = $this->object->getReachedPoints($active_id, $pass);
                if ($reached_points == $this->object->getPoints()) {
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
        $questionoutput = $template->get();
        $feedback = ($show_feedback && !$this->isTestPresentationContext()) ? $this->getAnswerFeedbackOutput($active_id, $pass) : "";
        if (strlen($feedback)) {
            $cssClass = (
                $this->hasCorrectSolution($active_id, $pass) ?
                ilAssQuestionFeedback::CSS_CLASS_FEEDBACK_CORRECT : ilAssQuestionFeedback::CSS_CLASS_FEEDBACK_WRONG
            );
            
            $solutiontemplate->setVariable("ILC_FB_CSS_CLASS", $cssClass);
            $solutiontemplate->setVariable("FEEDBACK", $this->object->prepareTextareaOutput($feedback, true));
        }
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
        // generate the question output
        include_once "./Services/UICore/classes/class.ilTemplate.php";
        $template = new ilTemplate("tpl.il_as_qpl_javaapplet_question_output.html", true, true, "Modules/TestQuestionPool");
        $template->setCurrentBlock("appletparam");
        $template->setVariable("PARAM_NAME", "question_id");
        $template->setVariable("PARAM_VALUE", $this->object->getId());
        $template->parseCurrentBlock();
        $template->setCurrentBlock("appletparam");
        $template->setVariable("PARAM_NAME", "points_max");
        $template->setVariable("PARAM_VALUE", $this->object->getPoints());
        $template->parseCurrentBlock();
        $template->setCurrentBlock("appletparam");
        $template->setVariable("PARAM_NAME", "session_id");
        $template->setVariable("PARAM_VALUE", $_COOKIE[session_name()]);
        $template->parseCurrentBlock();
        $template->setCurrentBlock("appletparam");
        $template->setVariable("PARAM_NAME", "client");
        $template->setVariable("PARAM_VALUE", CLIENT_ID);
        $template->parseCurrentBlock();
        // additional parameters
        for ($i = 0; $i < $this->object->getParameterCount(); $i++) {
            $parameter = $this->object->getParameter($i);
            $template->setCurrentBlock("appletparam");
            $template->setVariable("PARAM_NAME", $parameter["name"]);
            $template->setVariable("PARAM_VALUE", $parameter["value"]);
            $template->parseCurrentBlock();
        }

        $questiontext = $this->object->getQuestion();
        $template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($questiontext, true));
        $template->setVariable("APPLET_WIDTH", $this->object->getJavaWidth());
        $template->setVariable("APPLET_HEIGHT", $this->object->getJavaHeight());
        $template->setVariable("APPLET_CODE", $this->object->getJavaCode());
        if (strlen($this->object->getJavaArchive()) > 0) {
            $template->setVariable("APPLET_ARCHIVE", " archive=\"" . $this->object->getJavaArchive() . "\"");
        } else {
            if (strpos($this->object->getJavaAppletFilename(), ".jar") !== false) {
                $template->setVariable("APPLET_ARCHIVE", " archive=\"" . $this->object->getJavaPathWeb() . $this->object->getJavaAppletFilename() . "\"");
            }
        }
        if (strlen($this->object->getJavaCodebase()) > 0) {
            $template->setVariable("APPLET_CODEBASE", " codebase=\"" . $this->object->getJavaCodebase() . "\"");
        } else {
            if (strpos($this->object->getJavaAppletFilename(), ".class") !== false) {
                $template->setVariable("APPLET_CODEBASE", " codebase=\"" . $this->object->getJavaPathWeb() . "\"");
            }
        }
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
        $userdata = $this->object->getActiveUserData($active_id);
        // generate the question output
        include_once "./Services/UICore/classes/class.ilTemplate.php";
        $template = new ilTemplate("tpl.il_as_qpl_javaapplet_question_output.html", true, true, "Modules/TestQuestionPool");
        $template->setCurrentBlock("appletparam");
        $template->setVariable("PARAM_NAME", "test_type");
        include_once "./Modules/Test/classes/class.ilObjTest.php";
        if (ilObjTest::_lookupAnonymity(ilObjTest::_getObjectIDFromTestID($userdata["test_id"]))) {
            $template->setVariable("PARAM_VALUE", "0");
        } else {
            $template->setVariable("PARAM_VALUE", "1");
        }
        $template->parseCurrentBlock();
        $template->setCurrentBlock("appletparam");
        $template->setVariable("PARAM_NAME", "active_id");
        $template->setVariable("PARAM_VALUE", $active_id);
        $template->parseCurrentBlock();
        $template->setCurrentBlock("appletparam");
        $template->setVariable("PARAM_NAME", "test_id");
        $template->setVariable("PARAM_VALUE", $userdata["test_id"]);
        $template->parseCurrentBlock();
        $template->setCurrentBlock("appletparam");
        $template->setVariable("PARAM_NAME", "question_id");
        $template->setVariable("PARAM_VALUE", $this->object->getId());
        $template->parseCurrentBlock();
        $template->setCurrentBlock("appletparam");
        $template->setVariable("PARAM_NAME", "user_id");
        $template->setVariable("PARAM_VALUE", $userdata["user_id"]);
        $template->parseCurrentBlock();
        $template->setCurrentBlock("appletparam");
        $template->setVariable("PARAM_NAME", "points_max");
        $template->setVariable("PARAM_VALUE", $this->object->getPoints());
        $template->parseCurrentBlock();
        $template->setCurrentBlock("appletparam");
        $template->setVariable("PARAM_NAME", "session_id");
        $template->setVariable("PARAM_VALUE", $_COOKIE[session_name()]);
        $template->parseCurrentBlock();
        $template->setCurrentBlock("appletparam");
        $template->setVariable("PARAM_NAME", "client");
        $template->setVariable("PARAM_VALUE", CLIENT_ID);
        $template->parseCurrentBlock();
        $template->setCurrentBlock("appletparam");
        $template->setVariable("PARAM_NAME", "pass");
        $actualpass = ilObjTest::_getPass($active_id);
        $template->setVariable("PARAM_VALUE", $actualpass);
        $template->parseCurrentBlock();
        $template->setCurrentBlock("appletparam");
        $template->setVariable("PARAM_NAME", "post_url");
        $template->setVariable("PARAM_VALUE", ilUtil::removeTrailingPathSeparators(ILIAS_HTTP_PATH) . "/Modules/TestQuestionPool/save_question_post_data.php");
        $template->parseCurrentBlock();
        // additional parameters
        for ($i = 0; $i < $this->object->getParameterCount(); $i++) {
            $parameter = $this->object->getParameter($i);
            $template->setCurrentBlock("appletparam");
            $template->setVariable("PARAM_NAME", $parameter["name"]);
            $template->setVariable("PARAM_VALUE", $parameter["value"]);
            $template->parseCurrentBlock();
        }

        if ($active_id) {
            // hey: prevPassSolutions - obsolete due to central check
            #$solutions = NULL;
            #include_once "./Modules/Test/classes/class.ilObjTest.php";
            #if (!ilObjTest::_getUsePreviousAnswers($active_id, true))
            #{
            #	if (is_null($pass)) $pass = ilObjTest::_getPass($active_id);
            #}
            $info = $this->object->getTestOutputSolutions($active_id, $pass);
            // hey.
            foreach ($info as $kk => $infodata) {
                $template->setCurrentBlock("appletparam");
                $template->setVariable("PARAM_NAME", "value_" . $infodata["order"] . "_1");
                $template->setVariable("PARAM_VALUE", $infodata["value1"]);
                $template->parseCurrentBlock();
                $template->setCurrentBlock("appletparam");
                $template->setVariable("PARAM_NAME", "value_" . $infodata["order"] . "_2");
                $template->setVariable("PARAM_VALUE", $infodata["value2"]);
                $template->parseCurrentBlock();
            }
            include_once './Services/Administration/classes/class.ilSetting.php';
            $soapSetting = new ilSetting();
            if ($soapSetting->get("soap_user_administration") == 1) {
                $template->setCurrentBlock("appletparam");
                $template->setVariable("PARAM_NAME", "server");
                $template->setVariable("PARAM_VALUE", ilUtil::removeTrailingPathSeparators(ILIAS_HTTP_PATH) . "/webservice/soap/server.php");
            }
        }
        
        $questiontext = $this->object->getQuestion();
        $template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($questiontext, true));
        $template->setVariable("APPLET_WIDTH", $this->object->getJavaWidth());
        $template->setVariable("APPLET_HEIGHT", $this->object->getJavaHeight());
        $template->setVariable("APPLET_CODE", $this->object->getJavaCode());
        if (strlen($this->object->getJavaArchive()) > 0) {
            $template->setVariable("APPLET_ARCHIVE", " archive=\"" . $this->object->getJavaArchive() . "\"");
        } else {
            if (strpos($this->object->getJavaAppletFilename(), ".jar") !== false) {
                $template->setVariable("APPLET_ARCHIVE", " archive=\"" . $this->object->getJavaPathWeb() . $this->object->getJavaAppletFilename() . "\"");
            }
        }
        if (strlen($this->object->getJavaCodebase()) > 0) {
            $template->setVariable("APPLET_CODEBASE", " codebase=\"" . $this->object->getJavaCodebase() . "\"");
        } else {
            if (strpos($this->object->getJavaAppletFilename(), ".class") !== false) {
                $template->setVariable("APPLET_CODEBASE", " codebase=\"" . $this->object->getJavaPathWeb() . "\"");
            }
        }
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
                    if (preg_match("/^delete_.*/", $key, $matches)) {
                        $force_active = true;
                    }
                }
            }
            // edit question properties
            $ilTabs->addTarget(
                "edit_question",
                $url,
                array("editQuestion", "save", "saveEdit", "addkvp", "removekvp", "originalSyncForm"),
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

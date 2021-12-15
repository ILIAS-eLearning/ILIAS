<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * @author Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @ilCtrl_Calls ilSurveyConstraintsGUI:
 */
class ilSurveyConstraintsGUI
{
    protected ilObjSurvey $object;
    protected ilObjSurveyGUI $parent_gui;
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;
    protected ilAccessHandler $access;

    public function __construct(
        ilObjSurveyGUI $a_parent_gui
    ) {
        global $DIC;

        $this->access = $DIC->access();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        $tpl = $DIC["tpl"];
        
        $this->parent_gui = $a_parent_gui;

        /** @var ilObjSurvey $survey */
        $survey = $this->parent_gui->object;
        $this->object = $survey;
        
        $this->ctrl = $ilCtrl;
        $this->lng = $lng;
        $this->tpl = $tpl;
    }
    
    public function executeCommand() : void
    {
        $ilCtrl = $this->ctrl;
        
        $cmd = $ilCtrl->getCmd("constraints");
        $cmd .= "Object";
        
        $this->$cmd();
    }
    
    /**
     * Administration page for survey constraints
     */
    public function constraintsObject() : void
    {
        $step = 0;
        if (array_key_exists("step", $_GET)) {
            $step = (int) $_GET["step"];
        }
        switch ($step) {
            case 1:
                $this->constraintStep1Object();
                return;
            case 3:
            case 2:
                return;
        }
        
        $hasDatasets = ilObjSurvey::_hasDatasets($this->object->getSurveyId());
        
        $tbl = new SurveyConstraintsTableGUI($this, "constraints", $this->object, $hasDatasets);

        $mess = "";
        if ($hasDatasets) {
            $mbox = new ilSurveyContainsDataMessageBoxGUI();
            $mess = $mbox->getHTML();
        } else {
            $_SESSION["constraintstructure"] = $tbl->getStructure();
        }

        $this->tpl->setContent($mess . $tbl->getHTML());
    }
    
    /**
     * Add a precondition for a survey question or question block
     */
    public function constraintsAddObject() : void
    {
        if (strlen($_POST["v"]) == 0) {
            ilUtil::sendFailure($this->lng->txt("msg_enter_value_for_valid_constraint"));
            $this->constraintStep3Object();
            return;
        }
        $survey_questions = $this->object->getSurveyQuestions();
        $structure = &$_SESSION["constraintstructure"];
        $include_elements = $_SESSION["includeElements"];
        foreach ($include_elements as $elementCounter) {
            if (is_array($structure[$elementCounter])) {
                if (strlen($_GET["precondition"])) {
                    $this->object->updateConstraint($_GET['precondition'], $_POST["q"], $_POST["r"], $_POST["v"], $_POST['c']);
                } else {
                    $constraint_id = $this->object->addConstraint($_POST["q"], $_POST["r"], $_POST["v"], $_POST['c']);
                    foreach ($structure[$elementCounter] as $key => $question_id) {
                        $this->object->addConstraintToQuestion($question_id, $constraint_id);
                    }
                }
                if (count($structure[$elementCounter]) > 1) {
                    $this->object->updateConjunctionForQuestions($structure[$elementCounter], $_POST['c']);
                }
            }
        }
        unset($_SESSION["includeElements"]);
        unset($_SESSION["constraintstructure"]);
        $this->ctrl->redirect($this, "constraints");
    }

    /**
     * Handles the first step of the precondition add action
     */
    public function constraintStep1Object() : void
    {
        $survey_questions = $this->object->getSurveyQuestions();
        $structure = &$_SESSION["constraintstructure"];
        $start = $_GET["start"];
        $option_questions = array();
        for ($i = 1; $i < $start; $i++) {
            if (is_array($structure[$i])) {
                foreach ($structure[$i] as $key => $question_id) {
                    if ($survey_questions[$question_id]["usableForPrecondition"]) {
                        array_push($option_questions, array("question_id" => $survey_questions[$question_id]["question_id"], "title" => $survey_questions[$question_id]["title"], "type_tag" => $survey_questions[$question_id]["type_tag"]));
                    }
                }
            }
        }
        if (count($option_questions) == 0) {
            unset($_SESSION["includeElements"]);
            unset($_SESSION["constraintstructure"]);
            ilUtil::sendInfo($this->lng->txt("constraints_no_nonessay_available"), true);
            $this->ctrl->redirect($this, "constraints");
        }
        $this->constraintForm(1, $_POST, $survey_questions, $option_questions);
    }
    
    /**
     * Handles the second step of the precondition add action
     */
    public function constraintStep2Object() : void
    {
        $survey_questions = $this->object->getSurveyQuestions();
        $option_questions = array();
        array_push($option_questions, array("question_id" => $_POST["q"], "title" => $survey_questions[$_POST["q"]]["title"], "type_tag" => $survey_questions[$_POST["q"]]["type_tag"]));
        $this->constraintForm(2, $_POST, $survey_questions, $option_questions);
    }
    
    /**
     * Handles the third step of the precondition add action
     */
    public function constraintStep3Object() : void
    {
        $survey_questions = $this->object->getSurveyQuestions();
        $option_questions = array();
        if (strlen($_GET["precondition"])) {
            if (!$this->validateConstraintForEdit($_GET["precondition"])) {
                $this->ctrl->redirect($this, "constraints");
            }
            
            $pc = $this->object->getPrecondition($_GET["precondition"]);
            $postvalues = array(
                "c" => $pc["conjunction"],
                "q" => $pc["question_fi"],
                "r" => $pc["relation_id"],
                "v" => $pc["value"]
            );
            array_push($option_questions, array("question_id" => $pc["question_fi"], "title" => $survey_questions[$pc["question_fi"]]["title"], "type_tag" => $survey_questions[$pc["question_fi"]]["type_tag"]));
            $this->constraintForm(3, $postvalues, $survey_questions, $option_questions);
        } else {
            array_push($option_questions, array("question_id" => $_POST["q"], "title" => $survey_questions[$_POST["q"]]["title"], "type_tag" => $survey_questions[$_POST["q"]]["type_tag"]));
            $this->constraintForm(3, $_POST, $survey_questions, $option_questions);
        }
    }

    // output constraint editing form
    public function constraintForm(
        int $step,
        array $postvalues,
        array $survey_questions,
        ?array $questions = null
    ) : void {
        if (strlen($_GET["start"])) {
            $this->ctrl->setParameter($this, "start", $_GET["start"]);
        }
        $this->ctrl->saveParameter($this, "precondition");
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTableWidth("100%");
        $form->setId("constraintsForm");
                
        // #9366
        $title = array();
        $title_ids = $_SESSION["includeElements"];
        if (!$title_ids) {
            $title_ids = array($_GET["start"]);
        }
        foreach ($title_ids as $title_id) {
            // question block
            if ($survey_questions[$_SESSION["constraintstructure"][$title_id][0]]["questionblock_id"] > 0) {
                $title[] = $this->lng->txt("questionblock") . ": " . $survey_questions[$_SESSION["constraintstructure"][$title_id][0]]["questionblock_title"];
            }
            // question
            else {
                $title[] = $this->lng->txt($survey_questions[$_SESSION["constraintstructure"][$title_id][0]]["type_tag"]) . ": " .
                    $survey_questions[$_SESSION["constraintstructure"][$title_id][0]]["title"];
            }
        }
        $header = new ilFormSectionHeaderGUI();
        $header->setTitle(implode("<br/>", $title));
        $form->addItem($header);
        
        $fulfilled = new ilRadioGroupInputGUI($this->lng->txt("constraint_fulfilled"), "c");
        $fulfilled->addOption(new ilRadioOption($this->lng->txt("conjunction_and"), '0', ''));
        $fulfilled->addOption(new ilRadioOption($this->lng->txt("conjunction_or"), '1', ''));
        $fulfilled->setValue((strlen($postvalues['c'])) ? $postvalues['c'] : 0);
        $form->addItem($fulfilled);

        $step1 = new ilSelectInputGUI($this->lng->txt("step") . " 1: " . $this->lng->txt("select_prior_question"), "q");
        $options = array();
        if (is_array($questions)) {
            foreach ($questions as $question) {
                $options[$question["question_id"]] = $question["title"] . " (" . SurveyQuestion::_getQuestionTypeName($question["type_tag"]) . ")";
            }
        }
        $step1->setOptions($options);
        $step1->setValue($postvalues["q"]);
        $form->addItem($step1);

        if ($step > 1) {
            $relations = $this->object->getAllRelations();
            $step2 = new ilSelectInputGUI($this->lng->txt("step") . " 2: " . $this->lng->txt("select_relation"), "r");
            $options = array();
            foreach ($relations as $rel_id => $relation) {
                if (in_array($relation["short"], $survey_questions[$postvalues["q"]]["availableRelations"])) {
                    $options[$rel_id] = $relation['short'];
                }
            }
            $step2->setOptions($options);
            $step2->setValue($postvalues["r"]);
            $form->addItem($step2);
        }
        
        if ($step > 2) {
            $variables = $this->object->getVariables($postvalues["q"]);
            $question_type = $survey_questions[$postvalues["q"]]["type_tag"];
            SurveyQuestion::_includeClass($question_type);
            $question = new $question_type();
            $question->loadFromDb($postvalues["q"]);

            $step3 = $question->getPreconditionSelectValue($postvalues["v"], $this->lng->txt("step") . " 3: " . $this->lng->txt("select_value"), "v");
            $form->addItem($step3);
        }

        switch ($step) {
            case 1:
                $cmd_continue = "constraintStep2";
                $cmd_back = "constraints";
                break;
            case 2:
                $cmd_continue = "constraintStep3";
                $cmd_back = "constraintStep1";
                break;
            case 3:
                $cmd_continue = "constraintsAdd";
                $cmd_back = "constraintStep2";
                break;
        }
        $form->addCommandButton($cmd_back, $this->lng->txt("back"));
        $form->addCommandButton($cmd_continue, $this->lng->txt("continue"));

        $this->tpl->setVariable("ADM_CONTENT", $form->getHTML());
    }
    
    /**
     * Validate if given constraint id is part of current survey and
     * there are sufficient permissions to edit.
     * @todo actually the ID is not checked against the survey
     */
    protected function validateConstraintForEdit(
        int $a_id
    ) : bool {
        $ilAccess = $this->access;
        
        if (ilObjSurvey::_hasDatasets($this->object->getSurveyId())) {
            return false;
        }
        if (!$ilAccess->checkAccess("write", "", $this->object->getRefId())) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Delete constraint confirmation
     */
    public function confirmDeleteConstraintsObject() : void
    {
        $id = (int) $_REQUEST["precondition"];
        if (!$this->validateConstraintForEdit($id)) {
            $this->ctrl->redirect($this, "constraints");
        }
        
        $constraint = $this->object->getPrecondition($id);
        $questions = $this->object->getSurveyQuestions();
        $question = $questions[$constraint["question_fi"]];
        $relation = $questions[$constraint["ref_question_fi"]];
        $relation = $relation["title"];
        
        // see ilSurveyConstraintsTableGUI
        $question_type = SurveyQuestion::_getQuestionType($constraint["question_fi"]);
        SurveyQuestion::_includeClass($question_type);
        $question_obj = new $question_type();
        $question_obj->loadFromDb($constraint["question_fi"]);
        $valueoutput = $question_obj->getPreconditionValueOutput($constraint["value"]);
        
        $title = $question["title"] . " " . $constraint["shortname"] . " " . $valueoutput;
        
        $this->ctrl->saveParameter($this, "precondition");
        
        $cgui = new ilConfirmationGUI();
        $cgui->setHeaderText(sprintf($this->lng->txt("survey_sure_delete_constraint"), $title, $relation));

        $cgui->setFormAction($this->ctrl->getFormAction($this, "deleteConstraints"));
        $cgui->setCancel($this->lng->txt("cancel"), "constraints");
        $cgui->setConfirm($this->lng->txt("confirm"), "deleteConstraints");
        
        $this->tpl->setContent($cgui->getHTML());
    }

    public function deleteConstraintsObject() : void
    {
        $id = (int) $_REQUEST["precondition"];
        if ($this->validateConstraintForEdit($id)) {
            ilUtil::sendSuccess($this->lng->txt("survey_constraint_deleted"), true);
            $this->object->deleteConstraint($id);
        }

        $this->ctrl->redirect($this, "constraints");
    }
    
    public function createConstraintsObject() : void
    {
        $include_elements = $_POST["includeElements"];
        if ((!is_array($include_elements)) || (count($include_elements) == 0)) {
            ilUtil::sendInfo($this->lng->txt("constraints_no_questions_or_questionblocks_selected"), true);
            $this->ctrl->redirect($this, "constraints");
        } elseif (count($include_elements) >= 1) {
            $_SESSION["includeElements"] = $include_elements;
            sort($include_elements, SORT_NUMERIC);
            $_GET["start"] = $include_elements[0];
            $this->constraintStep1Object();
        }
    }

    /**
     * @throws ilCtrlException
     */
    public function editPreconditionObject() : void
    {
        if (!$this->validateConstraintForEdit($_GET["precondition"])) {
            $this->ctrl->redirect($this, "constraints");
        }
        
        $_SESSION["includeElements"] = array($_GET["start"]);
        $this->ctrl->setParameter($this, "precondition", $_GET["precondition"]);
        $this->ctrl->setParameter($this, "start", $_GET["start"]);
        $this->ctrl->redirect($this, "constraintStep3");
    }
}

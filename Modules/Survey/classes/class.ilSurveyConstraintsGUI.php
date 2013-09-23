<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilSurveyConstraintsGUI
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version  $Id: class.ilObjSurveyGUI.php 43670 2013-07-26 08:41:31Z jluetzen $
*
* @ilCtrl_Calls ilSurveyConstraintsGUI:
*
* @ingroup ModulesSurvey
*/
class ilSurveyConstraintsGUI
{
	public function __construct(ilObjSurveyGUI $a_parent_gui)
	{		
		global $ilCtrl, $lng, $tpl;
		
		$this->parent_gui = $a_parent_gui;
		$this->object = $this->parent_gui->object;
		
		$this->ctrl = $ilCtrl;
		$this->lng = $lng;
		$this->tpl = $tpl;
	}
	
	public function executeCommand()
	{
		global $ilCtrl;
		
		$cmd = $ilCtrl->getCmd("constraints");
		$cmd .= "Object";
		
		$this->$cmd();		
	}
	
	/**
	* Administration page for survey constraints
	*/
	public function constraintsObject()
	{		
		global $rbacsystem;
		
		$hasDatasets = $this->object->_hasDatasets($this->object->getSurveyId());
		$step = 0;
		if (array_key_exists("step", $_GET))	$step = $_GET["step"];
		switch ($step)
		{
			case 1:
				$this->constraintStep1Object();
				return;
				break;
			case 2:
				return;
				break;
			case 3:
				return;
				break;
		}
		
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_svy_constraints_list.html", "Modules/Survey");
		$survey_questions =& $this->object->getSurveyQuestions();
		$last_questionblock_id = 0;
		$counter = 1;
		$hasPreconditions = FALSE;
		$structure = array();
		$colors = array("tblrow1", "tblrow2");
		foreach ($survey_questions as $question_id => $data)
		{
			$title = $data["title"];
			$show = true;
			if ($data["questionblock_id"] > 0)
			{
				$title = $data["questionblock_title"];
				$type = $this->lng->txt("questionblock");
				if ($data["questionblock_id"] != $last_questionblock_id) 
				{
					$last_questionblock_id = $data["questionblock_id"];
					$structure[$counter] = array();
					array_push($structure[$counter], $data["question_id"]);
				}
				else
				{
					array_push($structure[$counter-1], $data["question_id"]);
					$show = false;
				}
			}
			else
			{
				$structure[$counter] = array($data["question_id"]);
				$type = $this->lng->txt("question");
			}
			if ($show)
			{
				if ($counter == 1)
				{
					$this->tpl->setCurrentBlock("description");
					$this->tpl->setVariable("DESCRIPTION", $this->lng->txt("constraints_first_question_description"));
					$this->tpl->parseCurrentBlock();
				}
				else
				{
					$constraints =& $this->object->getConstraints($data["question_id"]);
					$rowcount = 0;
					if (count($constraints))
					{
						$hasPreconditions = TRUE;
						foreach ($constraints as $constraint)
						{
							$this->tpl->setCurrentBlock("constraint");
							$this->tpl->setVariable("SEQUENCE_ID", $counter);
							$this->tpl->setVariable("CONSTRAINT_ID", $constraint["id"]);
							$this->tpl->setVariable("CONSTRAINT_TEXT", $survey_questions[$constraint["question"]]["title"] . " " . $constraint["short"] . " " . $constraint["valueoutput"]);
							$this->tpl->setVariable("TEXT_EDIT_PRECONDITION", $this->lng->txt("edit"));
							$this->ctrl->setParameter($this, "precondition", $constraint["id"]);
							$this->ctrl->setParameter($this, "start", $counter);
							$this->tpl->setVariable("EDIT_PRECONDITION", $this->ctrl->getLinkTarget($this, "editPrecondition"));
							$this->ctrl->setParameter($this, "precondition", "");
							$this->ctrl->setParameter($this, "start", "");
							$this->tpl->parseCurrentBlock();
						}
						if (count($constraints) > 1)
						{
							$this->tpl->setCurrentBlock("conjunction");
							$this->tpl->setVariable("TEXT_CONJUNCTION", ($constraints[0]['conjunction']) ? $this->lng->txt('conjunction_or_title') : $this->lng->txt('conjunction_and_title'));
							$this->tpl->parseCurrentBlock();
						}
					}
				}
				if ($counter != 1)
				{
					$this->tpl->setCurrentBlock("include_elements");
					$this->tpl->setVariable("QUESTION_NR", "$counter");
					$this->tpl->parseCurrentBlock();
				}
				$this->tpl->setCurrentBlock("constraint_section");
				$this->tpl->setVariable("COLOR_CLASS", $colors[$counter % 2]);
				$this->tpl->setVariable("QUESTION_NR", "$counter");
				$this->tpl->setVariable("TITLE", "$title");
				$icontype = "question.png";
				if ($data["questionblock_id"] > 0)
				{
					$icontype = "questionblock.png";
				}
				$this->tpl->setVariable("TYPE", "$type: ");
				include_once "./Services/Utilities/classes/class.ilUtil.php";
				$this->tpl->setVariable("ICON_HREF", ilUtil::getImagePath($icontype, "Modules/Survey"));
				$this->tpl->setVariable("ICON_ALT", $type);
				$this->tpl->parseCurrentBlock();
				$counter++;
			}
		}
		if (!$hasDatasets)
		{
			if ($hasPreconditions)
			{
				$this->tpl->setCurrentBlock("selectall_preconditions");
				$this->tpl->setVariable("SELECT_ALL_PRECONDITIONS", $this->lng->txt("select_all"));
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setCurrentBlock("selectall");
			$counter++;
			$this->tpl->setVariable("SELECT_ALL", $this->lng->txt("select_all"));
			$this->tpl->setVariable("COLOR_CLASS", $colors[$counter % 2]);
			$this->tpl->parseCurrentBlock();

			if ($hasPreconditions)
			{
				$this->tpl->setCurrentBlock("delete_button");
				$this->tpl->setVariable("BTN_DELETE", $this->lng->txt("delete"));
				include_once "./Services/Utilities/classes/class.ilUtil.php";
				$this->tpl->setVariable("ARROW", "<img src=\"" . ilUtil::getImagePath("arrow_downright.png") . "\" alt=\"".$this->lng->txt("arrow_downright")."\">");
				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setCurrentBlock("buttons");
			$this->tpl->setVariable("ARROW", "<img src=\"" . ilUtil::getImagePath("arrow_downright.png") . "\" alt=\"".$this->lng->txt("arrow_downright")."\">");
			$this->tpl->setVariable("BTN_CREATE_CONSTRAINTS", $this->lng->txt("constraint_add"));
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("CONSTRAINTS_INTRODUCTION", $this->lng->txt("constraints_introduction"));
		$this->tpl->setVariable("DEFINED_PRECONDITIONS", $this->lng->txt("existing_constraints"));
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this, "constraints"));
		$this->tpl->setVariable("CONSTRAINTS_HEADER", $this->lng->txt("constraints_list_of_entities"));
		$this->tpl->parseCurrentBlock();
		$_SESSION["constraintstructure"] = $structure;
		if ($hasDatasets)
		{
			// ilUtil::sendInfo($this->lng->txt("survey_has_datasets_warning"));
			$link = $this->ctrl->getLinkTarget($this, "maintenance");
			$link = "<a href=\"".$link."\">".$this->lng->txt("survey_has_datasets_warning_page_view_link")."</a>";
			ilUtil::sendInfo($this->lng->txt("survey_has_datasets_warning_page_view")." ".$link);
		}
	}
	
	/**
	* Add a precondition for a survey question or question block
	*/
	public function constraintsAddObject()
	{
		if (strlen($_POST["v"]) == 0)
		{
			ilUtil::sendFailure($this->lng->txt("msg_enter_value_for_valid_constraint"));
			return $this->constraintStep3Object();
		}
		$survey_questions =& $this->object->getSurveyQuestions();
		$structure =& $_SESSION["constraintstructure"];
		$include_elements = $_SESSION["includeElements"];
		foreach ($include_elements as $elementCounter)
		{
			if (is_array($structure[$elementCounter]))
			{
				if (strlen($_GET["precondition"]))
				{
					$this->object->updateConstraint($_GET['precondition'], $_POST["q"], $_POST["r"], $_POST["v"], $_POST['c']);
				}
				else
				{
					$constraint_id = $this->object->addConstraint($_POST["q"], $_POST["r"], $_POST["v"], $_POST['c']);
					foreach ($structure[$elementCounter] as $key => $question_id)
					{
						$this->object->addConstraintToQuestion($question_id, $constraint_id);
					}
				}
				if (count($structure[$elementCounter]) > 1)
				{
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
	public function constraintStep1Object()
	{
		$survey_questions =& $this->object->getSurveyQuestions();
		$structure =& $_SESSION["constraintstructure"];
		$start = $_GET["start"];
		$option_questions = array();
		for ($i = 1; $i < $start; $i++)
		{
			if (is_array($structure[$i]))
			{
				foreach ($structure[$i] as $key => $question_id)
				{
					if ($survey_questions[$question_id]["usableForPrecondition"])
					{
						array_push($option_questions, array("question_id" => $survey_questions[$question_id]["question_id"], "title" => $survey_questions[$question_id]["title"], "type_tag" => $survey_questions[$question_id]["type_tag"]));
					}
				}
			}
		}
		if (count($option_questions) == 0)
		{
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
	public function constraintStep2Object()
	{
		$survey_questions =& $this->object->getSurveyQuestions();
		$option_questions = array();
		array_push($option_questions, array("question_id" => $_POST["q"], "title" => $survey_questions[$_POST["q"]]["title"], "type_tag" => $survey_questions[$_POST["q"]]["type_tag"]));
		$this->constraintForm(2, $_POST, $survey_questions, $option_questions);
	}
	
	/**
	* Handles the third step of the precondition add action
	*/
	public function constraintStep3Object()
	{
		$survey_questions =& $this->object->getSurveyQuestions();
		$option_questions = array();
		if (strlen($_GET["precondition"]))
		{
			$pc = $this->object->getPrecondition($_GET["precondition"]);
			$postvalues = array(
				"c" => $pc["conjunction"],
				"q" => $pc["question_fi"],
				"r" => $pc["relation_id"],
				"v" => $pc["value"]
			);
			array_push($option_questions, array("question_id" => $pc["question_fi"], "title" => $survey_questions[$pc["question_fi"]]["title"], "type_tag" => $survey_questions[$pc["question_fi"]]["type_tag"]));
			$this->constraintForm(3, $postvalues, $survey_questions, $option_questions);
		}
		else
		{
			array_push($option_questions, array("question_id" => $_POST["q"], "title" => $survey_questions[$_POST["q"]]["title"], "type_tag" => $survey_questions[$_POST["q"]]["type_tag"]));
			$this->constraintForm(3, $_POST, $survey_questions, $option_questions);
		}
	}
	
	public function constraintForm($step, $postvalues, &$survey_questions, $questions = FALSE)
	{
		if (strlen($_GET["start"])) $this->ctrl->setParameter($this, "start", $_GET["start"]);
		$this->ctrl->saveParameter($this, "precondition");
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTableWidth("100%");
		$form->setId("constraintsForm");
				
		// #9366
		$title = array();		
		$title_ids = $_SESSION["includeElements"];
		if(!$title_ids)
		{
			$title_ids = array($_GET["start"]);
		}
		foreach($title_ids as $title_id)
		{
			// question block
			if ($survey_questions[$_SESSION["constraintstructure"][$title_id][0]]["questionblock_id"] > 0)
			{
				$title[] = $this->lng->txt("questionblock") . ": " . $survey_questions[$_SESSION["constraintstructure"][$title_id][0]]["questionblock_title"];
			}
			// question
			else
			{
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
		if (is_array($questions))
		{
			foreach ($questions as $question)
			{
				$options[$question["question_id"]] = $question["title"] . " (" . SurveyQuestion::_getQuestionTypeName($question["type_tag"]) . ")";
			}
		}
		$step1->setOptions($options);
		$step1->setValue($postvalues["q"]);
		$form->addItem($step1);

		if ($step > 1)
		{
			$relations = $this->object->getAllRelations();
			$step2 = new ilSelectInputGUI($this->lng->txt("step") . " 2: " . $this->lng->txt("select_relation"), "r");
			$options = array();
			foreach ($relations as $rel_id => $relation)
			{
				if (in_array($relation["short"], $survey_questions[$postvalues["q"]]["availableRelations"]))
				{
					$options[$rel_id] = $relation['short'];
				}
			}
			$step2->setOptions($options);
			$step2->setValue($postvalues["r"]);
			$form->addItem($step2);
		}
		
		if ($step > 2)
		{
			$variables =& $this->object->getVariables($postvalues["q"]);
			$question_type = $survey_questions[$postvalues["q"]]["type_tag"];
			include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestion.php";
			SurveyQuestion::_includeClass($question_type);
			$question = new $question_type();
			$question->loadFromDb($postvalues["q"]);

			$step3 = $question->getPreconditionSelectValue($postvalues["v"], $this->lng->txt("step") . " 3: " . $this->lng->txt("select_value"), "v");
			$form->addItem($step3);
		}

		switch ($step)
		{
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
	* Delete constraints of a survey
	*/
	public function deleteConstraintsObject()
	{
		$survey_questions =& $this->object->getSurveyQuestions();
		$structure =& $_SESSION["constraintstructure"];
		foreach ($_POST as $key => $value)
		{
			if (preg_match("/^constraint_(\d+)_(\d+)/", $key, $matches)) 
			{
				$this->object->deleteConstraint($matches[2]);
			}
		}

		$this->ctrl->redirect($this, "constraints");
	}
	
	function createConstraintsObject()
	{
		$include_elements = $_POST["includeElements"];
		if ((!is_array($include_elements)) || (count($include_elements) == 0))
		{
			ilUtil::sendInfo($this->lng->txt("constraints_no_questions_or_questionblocks_selected"), true);
			$this->ctrl->redirect($this, "constraints");
		}
		else if (count($include_elements) >= 1)
		{
			$_SESSION["includeElements"] = $include_elements;
			sort($include_elements, SORT_NUMERIC);
			$_GET["start"] = $include_elements[0];
			$this->constraintStep1Object();
		}
	}
	
	function editPreconditionObject()
	{
		$_SESSION["includeElements"] = array($_GET["start"]);
		$this->ctrl->setParameter($this, "precondition", $_GET["precondition"]);
		$this->ctrl->setParameter($this, "start", $_GET["start"]);
		$this->ctrl->redirect($this, "constraintStep3");
	}
}

?>
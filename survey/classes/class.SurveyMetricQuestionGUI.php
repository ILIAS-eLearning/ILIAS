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

include_once "./survey/classes/class.SurveyMetricQuestion.php";
include_once "./survey/classes/class.SurveyQuestionGUI.php";

/**
* Metric survey question GUI representation
*
* The SurveyMetricQuestionGUI class encapsulates the GUI representation
* for metric survey question types.
*
* @author		Helmut Schottmüller <hschottm@tzi.de>
* @version	$Id$
* @module   class.SurveyMetricQuestionGUI.php
* @modulegroup   Survey
*/
class SurveyMetricQuestionGUI extends SurveyQuestionGUI {
/**
* SurveyMetricQuestionGUI constructor
*
* The constructor takes possible arguments an creates an instance of the SurveyMetricQuestionGUI object.
*
* @param integer $id The database id of a metric question object
* @access public
*/
  function SurveyMetricQuestionGUI(
		$id = -1
  )

  {
		$this->SurveyQuestionGUI();
		$this->object = new SurveyMetricQuestion();
		if ($id >= 0)
		{
			$this->object->loadFromDb($id);
		}
	}

/**
* Returns the question type string
*
* Returns the question type string
*
* @result string The question type string
* @access public
*/
	function getQuestionType()
	{
		return "qt_metric";
	}

/**
* Creates an output of the edit form for the question
*
* Creates an output of the edit form for the question
*
* @access public
*/
  function editQuestion() {
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_qpl_metric.html", true);
	  $this->tpl->addBlockFile("OTHER_QUESTION_DATA", "other_question_data", "tpl.il_svy_qpl_other_question_data.html", true);
		$internallinks = array(
			"lm" => $this->lng->txt("obj_lm"),
			"st" => $this->lng->txt("obj_st"),
			"pg" => $this->lng->txt("obj_pg"),
			"glo" => $this->lng->txt("glossary_term")
		);
		foreach ($internallinks as $key => $value)
		{
			$this->tpl->setCurrentBlock("internallink");
			$this->tpl->setVariable("TYPE_INTERNAL_LINK", $key);
			$this->tpl->setVariable("TEXT_INTERNAL_LINK", $value);
			$this->tpl->parseCurrentBlock();
		}
		
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("TEXT_MATERIAL", $this->lng->txt("material"));
		if (count($this->object->material))
		{
			$href = SurveyQuestion::_getInternalLinkHref($this->object->material["internal_link"]);
			$this->tpl->setVariable("TEXT_VALUE_MATERIAL", " <a href=\"$href\" target=\"content\">" . $this->lng->txt("material"). "</a> ");
			$this->tpl->setVariable("BUTTON_REMOVE_MATERIAL", $this->lng->txt("remove"));
			$this->tpl->setVariable("BUTTON_ADD_MATERIAL", $this->lng->txt("change"));
			$this->tpl->setVariable("VALUE_MATERIAL", $this->object->material["internal_link"]);
			$this->tpl->setVariable("VALUE_MATERIAL_TITLE", $this->object->material["title"]);
			$this->tpl->setVariable("TEXT_TITLE", $this->lng->txt("title"));
		}
		else
		{
			$this->tpl->setVariable("BUTTON_ADD_MATERIAL", $this->lng->txt("add"));
		}
		$this->tpl->setVariable("QUESTION_ID", $this->object->getId());
		$this->tpl->setVariable("VALUE_TITLE", $this->object->getTitle());
		$this->tpl->setVariable("VALUE_DESCRIPTION", $this->object->getDescription());
		$this->tpl->setVariable("VALUE_AUTHOR", $this->object->getAuthor());
		$questiontext = $this->object->getQuestiontext();
		$questiontext = str_replace("<br />", "\n", $questiontext);
		$this->tpl->setVariable("VALUE_QUESTION", htmlspecialchars($questiontext));
		$this->tpl->setVariable("VALUE_MINIMUM", $this->object->getMinimum());
		$this->tpl->setVariable("VALUE_MAXIMUM", $this->object->getMaximum());
		$this->tpl->setVariable("TEXT_MINIMUM", $this->lng->txt("minimum"));
		$this->tpl->setVariable("TEXT_MAXIMUM", $this->lng->txt("maximum"));
		$this->tpl->setVariable("TEXT_NONRATIO", $this->lng->txt("non_ratio"));
		$this->tpl->setVariable("TEXT_RATIONONABSOLUTE", $this->lng->txt("ratio_non_absolute"));
		$this->tpl->setVariable("TEXT_RATIOABSOLUTE", $this->lng->txt("ratio_absolute"));
		if ($this->object->getSubtype() == SUBTYPE_NON_RATIO)
		{
			$this->tpl->setVariable("SELECTED_NONRATIO", " selected=\"selected\"");
		}
		else if ($this->object->getSubtype() == SUBTYPE_RATIO_NON_ABSOLUTE)
		{
			$this->tpl->setVariable("SELECTED_RATIONONABSOLUTE", " selected=\"selected\"");
		}
		else if ($this->object->getSubtype() == SUBTYPE_RATIO_ABSOLUTE)
		{
			$this->tpl->setVariable("SELECTED_RATIOABSOLUTE", " selected=\"selected\"");
		}
		$this->tpl->setVariable("TEXT_TITLE", $this->lng->txt("title"));
		$this->tpl->setVariable("TEXT_AUTHOR", $this->lng->txt("author"));
		$this->tpl->setVariable("TEXT_DESCRIPTION", $this->lng->txt("description"));
		$this->tpl->setVariable("TEXT_QUESTION", $this->lng->txt("question"));
		$this->tpl->setVariable("TEXT_OBLIGATORY", $this->lng->txt("obligatory"));
		if ($this->object->getObligatory())
		{
			$this->tpl->setVariable("CHECKED_OBLIGATORY", " checked=\"checked\"");
		}
		$this->tpl->setVariable("TEXT_QUESTION_TYPE", $this->lng->txt("questiontype"));
		if (($_GET["calling_survey"] > 0) || ($_GET["new_for_survey"] > 0))
		{
			$this->tpl->setVariable("SAVE",$this->lng->txt("save_back_to_survey"));
		}
		else
		{
			$this->tpl->setVariable("SAVE",$this->lng->txt("save"));
		}
		$this->tpl->setVariable("CANCEL",$this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
		$this->tpl->parseCurrentBlock();
  }

/**
* Evaluates a posted edit form and writes the form data in the question object
*
* Evaluates a posted edit form and writes the form data in the question object
*
* @return integer A positive value, if one of the required fields wasn't set, else 0
* @access private
*/
  function writePostData() {
    $result = 0;
    if ((!$_POST["title"]) or (!$_POST["author"]) or (!$_POST["question"]))
      $result = 1;

    // Set the question id from a hidden form parameter
    if ($_POST["id"] > 0)
      $this->object->setId($_POST["id"]);

    $this->object->setTitle(ilUtil::stripSlashes($_POST["title"]));
    $this->object->setAuthor(ilUtil::stripSlashes($_POST["author"]));
    $this->object->setDescription(ilUtil::stripSlashes($_POST["description"]));
		if (strlen($_POST["material"]))
		{
			$this->object->setMaterial($_POST["material"], 0, ilUtil::stripSlashes($_POST["material_title"]));
		}
		$questiontext = ilUtil::stripSlashes($_POST["question"]);
		$questiontext = str_replace("\n", "<br />", $questiontext);
    $this->object->setQuestiontext($questiontext);
		if ($_POST["obligatory"])
		{
			$this->object->setObligatory(1);
		}
		else
		{
			$this->object->setObligatory(0);
		}
		$this->object->setSubtype($_POST["type"]);
		$this->object->setMinimum($_POST["minimum"]);
		$this->object->setMaximum($_POST["maximum"]);

		if ($saved) {
			// If the question was saved automatically before an upload, we have to make
			// sure, that the state after the upload is saved. Otherwise the user could be
			// irritated, if he presses cancel, because he only has the question state before
			// the upload process.
			$this->object->saveToDb();
		}
    return $result;
  }

/**
* Creates the question output form for the learner
*
* Creates the question output form for the learner
*
* @access public
*/
	function outWorkingForm($working_data = "", $question_title = 1, $error_message = "")
	{
		if (count($this->object->material))
		{
			$this->tpl->setCurrentBlock("material_metric");
			$href = SurveyQuestion::_getInternalLinkHref($this->object->material["internal_link"]);
			$this->tpl->setVariable("TEXT_MATERIAL", $this->lng->txt("material") . ": <a href=\"$href\" target=\"content\">" . $this->object->material["title"]. "</a> ");
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setCurrentBlock("question_data_metric");
		$this->tpl->setVariable("QUESTIONTEXT", $this->object->getQuestiontext());
		if (! $this->object->getObligatory())
		{
			$this->tpl->setVariable("OBLIGATORY_TEXT", $this->lng->txt("survey_question_optional"));
		}
		if ($question_title)
		{
			$this->tpl->setVariable("QUESTION_TITLE", $this->object->getTitle());
		}
		$this->tpl->setVariable("TEXT_ANSWER", $this->lng->txt("answer"));
		$this->tpl->setVariable("TEXT_MINIMUM", $this->lng->txt("minimum"));
		$this->tpl->setVariable("VALUE_MINIMUM", $this->object->getMinimum());
		$this->tpl->setVariable("TEXT_MAXIMUM", $this->lng->txt("maximum"));
		$this->tpl->setVariable("VALUE_MAXIMUM", $this->object->getMaximum());
		$this->tpl->setVariable("QUESTION_ID", $this->object->getId());
		if (is_array($working_data))
		{
			$this->tpl->setVariable("VALUE_METRIC", $working_data[0]["value"]);
		}

		if (strlen($this->object->getMaximum())>1) {
			$len = strlen($this->object->getMaximum()) + 2;
			$this->tpl->setVariable("INPUT_SIZE", $len);
		}
		else {
			$this->tpl->setVariable("INPUT_SIZE", 10);
		}

		if (strcmp($error_message, "") != 0)
		{
			$this->tpl->setVariable("ERROR_MESSAGE", "<p class=\"warning\">$error_message</p>");
		}
		$this->tpl->parseCurrentBlock();
	}

/**
* Creates a preview of the question
*
* Creates a preview of the question
*
* @access private
*/
	function preview()
	{
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_qpl_preview.html", true);
		$this->tpl->addBlockFile("METRIC", "metric", "tpl.il_svy_out_metric.html", true);
		$this->outWorkingForm();
		$this->tpl->parseCurrentBlock();
	}

	function setQuestionTabs()
	{
		$this->setQuestionTabsForClass("surveymetricquestiongui");
	}
}
?>

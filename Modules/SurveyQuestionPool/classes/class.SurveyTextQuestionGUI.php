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

include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestionGUI.php";
include_once "./Modules/Survey/classes/inc.SurveyConstants.php";

/**
* Text survey question GUI representation
*
* The SurveyTextQuestionGUI class encapsulates the GUI representation
* for text survey question types.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @extends SurveyQuestionGUI
* @ingroup ModulesSurveyQuestionPool
*/
class SurveyTextQuestionGUI extends SurveyQuestionGUI 
{

/**
* SurveyTextQuestionGUI constructor
*
* The constructor takes possible arguments an creates an instance of the SurveyTextQuestionGUI object.
*
* @param integer $id The database id of a text question object
* @access public
*/
  function SurveyTextQuestionGUI(
		$id = -1
  )

  {
		$this->SurveyQuestionGUI();
		include_once "./Modules/SurveyQuestionPool/classes/class.SurveyTextQuestion.php";
		$this->object = new SurveyTextQuestion();
		if ($id >= 0)
		{
			$this->object->loadFromDb($id);
		}
	}

/**
* Creates an output of the edit form for the question
*
* Creates an output of the edit form for the question
*
* @access public
*/
  function editQuestion() 
	{
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_qpl_text.html", "Modules/SurveyQuestionPool");
	  $this->tpl->addBlockFile("OTHER_QUESTION_DATA", "other_question_data", "tpl.il_svy_qpl_other_question_data.html", "Modules/SurveyQuestionPool");

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
			include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestion.php";
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
		if ($this->object->getMaxChars() > 0)
		{
			$this->tpl->setVariable("VALUE_MAXCHARS", $this->object->getMaxChars());
		}
		$questiontext = $this->object->getQuestiontext();
		$this->tpl->setVariable("VALUE_QUESTION", ilUtil::prepareFormOutput($this->object->prepareTextareaOutput($questiontext)));
		$this->tpl->setVariable("TEXT_TITLE", $this->lng->txt("title"));
		$this->tpl->setVariable("TEXT_AUTHOR", $this->lng->txt("author"));
		$this->tpl->setVariable("TEXT_DESCRIPTION", $this->lng->txt("description"));
		$this->tpl->setVariable("TEXT_MAXCHARS", $this->lng->txt("maxchars"));
		$this->tpl->setVariable("TEXT_WIDTH", $this->lng->txt("width"));
		$this->tpl->setVariable("TEXT_HEIGHT", $this->lng->txt("height"));
		$this->tpl->setVariable("DESCRIPTION_TEXTWIDTH", $this->lng->txt("survey_text_textwidth_desc"));
		$this->tpl->setVariable("DESCRIPTION_TEXTHEIGHT", $this->lng->txt("survey_text_textheight_desc"));
		if ($this->object->getTextWidth())
		{
			$this->tpl->setVariable("VALUE_TEXTWIDTH", " value=\"" . $this->object->getTextWidth() . "\"");
		}
		if ($this->object->getTextHeight())
		{
			$this->tpl->setVariable("VALUE_TEXTHEIGHT", " value=\"" . $this->object->getTextHeight() . "\"");
		}
		$this->tpl->setVariable("DESCRIPTION_MAXCHARS", $this->lng->txt("description_maxchars"));
		$this->tpl->setVariable("TEXT_QUESTION", $this->lng->txt("question"));
		$this->tpl->setVariable("TEXT_OBLIGATORY", $this->lng->txt("obligatory"));
		if ($this->object->getObligatory())
		{
			$this->tpl->setVariable("CHECKED_OBLIGATORY", " checked=\"checked\"");
		}
		$this->tpl->setVariable("SAVE",$this->lng->txt("save"));
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TEXT_QUESTION_TYPE", $this->lng->txt($this->getQuestionType()));
		$this->tpl->parseCurrentBlock();
		include_once "./Services/RTE/classes/class.ilRTE.php";
		$rtestring = ilRTE::_getRTEClassname();
		include_once "./Services/RTE/classes/class.$rtestring.php";
		$rte = new $rtestring();
		$rte->addPlugin("latex");
		$rte->addButton("latex"); $rte->addButton("pastelatex");
		$rte->removePlugin("ibrowser");
		include_once "./classes/class.ilObject.php";
		$obj_id = $_GET["q_id"];
		$obj_type = ilObject::_lookupType($_GET["ref_id"], TRUE);
		$rte->addRTESupport($obj_id, $obj_type, "survey");
		
		parent::editQuestion();
	}

/**
* Creates the question output form for the learner
*
* Creates the question output form for the learner
*
* @access public
*/
	function getWorkingForm($working_data = "", $question_title = 1, $show_questiontext = 1, $error_message = "", $survey_id = null)
	{
		$template = new ilTemplate("tpl.il_svy_out_text.html", TRUE, TRUE, "Modules/SurveyQuestionPool");
		$template->setCurrentBlock("material_text");
		$template->setVariable("TEXT_MATERIAL", $this->getMaterialOutput());
		$template->parseCurrentBlock();

		if ($this->object->getTextHeight() == 1)
		{
			$template->setCurrentBlock("textinput");
			if (is_array($working_data))
			{
				if (strlen($working_data[0]["textanswer"]))
				{
					$template->setVariable("VALUE_ANSWER", " value=\"" . ilUtil::prepareFormOutput($working_data[0]["textanswer"]) . "\"");
				}
			}
			$template->setVariable("QUESTION_ID", $this->object->getId());
			$template->setVariable("WIDTH", $this->object->getTextWidth());
			if ($this->object->getMaxChars())
			{
				$template->setVariable("MAXLENGTH", " maxlength=\"" . $this->object->getMaxChars() . "\"");
			}
			$template->parseCurrentBlock();
		}
		else
		{
			$template->setCurrentBlock("textarea");
			if (is_array($working_data))
			{
				$template->setVariable("VALUE_ANSWER", ilUtil::prepareFormOutput($working_data[0]["textanswer"]));
			}
			$template->setVariable("QUESTION_ID", $this->object->getId());
			$template->setVariable("WIDTH", $this->object->getTextWidth());
			$template->setVariable("HEIGHT", $this->object->getTextHeight());
			$template->parseCurrentBlock();
		}
		$template->setCurrentBlock("question_data_text");
		if ($show_questiontext)
		{
			$questiontext = $this->object->getQuestiontext();
			$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($questiontext, TRUE));
		}
		if (! $this->object->getObligatory($survey_id))
		{
			$template->setVariable("OBLIGATORY_TEXT", $this->lng->txt("survey_question_optional"));
		}
		if ($question_title)
		{
			$template->setVariable("QUESTION_TITLE", $this->object->getTitle());
		}
		$template->setVariable("TEXT_ANSWER", $this->lng->txt("answer"));
		$template->setVariable("LABEL_QUESTION_ID", $this->object->getId());
		if (strcmp($error_message, "") != 0)
		{
			$template->setVariable("ERROR_MESSAGE", "<p class=\"warning\">$error_message</p>");
		}
		if ($this->object->getMaxChars())
		{
			$template->setVariable("TEXT_MAXCHARS", sprintf($this->lng->txt("text_maximum_chars_allowed"), $this->object->getMaxChars()));
		}
		$template->parseCurrentBlock();
		return $template->get();
	}

	/**
	* Creates a HTML representation of the question
	*
	* Creates a HTML representation of the question
	*
	* @access private
	*/
	function getPrintView($question_title = 1, $show_questiontext = 1, $survey_id = null)
	{
		$template = new ilTemplate("tpl.il_svy_qpl_text_printview.html", TRUE, TRUE, "Modules/SurveyQuestionPool");
		if ($show_questiontext)
		{
			$questiontext = $this->object->getQuestiontext();
			$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($questiontext, TRUE));
		}
		if (! $this->object->getObligatory($survey_id))
		{
			$template->setVariable("OBLIGATORY_TEXT", $this->lng->txt("survey_question_optional"));
		}
		if ($question_title)
		{
			$template->setVariable("QUESTION_TITLE", $this->object->getTitle());
		}
		$template->setVariable("TEXT_ANSWER", $this->lng->txt("answer"));
		$template->setVariable("TEXTBOX_IMAGE", ilUtil::getHtmlPath(ilUtil::getImagePath("textbox.png")));
		$template->setVariable("TEXTBOX", $this->lng->txt("textbox"));
		$template->setVariable("TEXTBOX_WIDTH", $this->object->getTextWidth()*16);
		$template->setVariable("TEXTBOX_HEIGHT", $this->object->getTextHeight()*16);
		$template->setVariable("QUESTION_ID", $this->object->getId());
		if ($this->object->getMaxChars())
		{
			$template->setVariable("TEXT_MAXCHARS", sprintf($this->lng->txt("text_maximum_chars_allowed"), $this->object->getMaxChars()));
		}
		return $template->get();
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
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_qpl_preview.html", "Modules/SurveyQuestionPool");
		$question_output = $this->getWorkingForm();
		$this->tpl->setVariable("QUESTION_OUTPUT", $question_output);
	}

/**
* Evaluates a posted edit form and writes the form data in the question object
*
* Evaluates a posted edit form and writes the form data in the question object
*
* @return integer A positive value, if one of the required fields wasn't set, else 0
* @access private
*/
	function writePostData() 
	{
		$result = 0;
		if ((!$_POST["title"]) or (!$_POST["author"]) or (!$_POST["question"])) $result = 1;
		if ($result == 1) $this->addErrorMessage($this->lng->txt("fill_out_all_required_fields"));

		// Set the question id from a hidden form parameter
		if ($_POST["id"] > 0) $this->object->setId($_POST["id"]);
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		$this->object->setTitle(ilUtil::stripSlashes($_POST["title"]));
		$this->object->setAuthor(ilUtil::stripSlashes($_POST["author"]));
		$this->object->setDescription(ilUtil::stripSlashes($_POST["description"]));
		$this->object->setMaxChars(ilUtil::stripSlashes($_POST["maxchars"]));
		$this->object->setTextWidth(ilUtil::stripSlashes($_POST["textwidth"]));
		$this->object->setTextHeight(ilUtil::stripSlashes($_POST["textheight"]));
		if (strlen($_POST["material"]))
		{
			$this->object->setMaterial($_POST["material"], 0, ilUtil::stripSlashes($_POST["material_title"]));
		}
		include_once "./Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php";
		$questiontext = ilUtil::stripSlashes($_POST["question"], true, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("survey"));
		$this->object->setQuestiontext($questiontext);
		if ($_POST["obligatory"])
		{
			$this->object->setObligatory(1);
		}
		else
		{
			$this->object->setObligatory(0);
		}

		if ($saved) 
		{
			// If the question was saved automatically before an upload, we have to make
			// sure, that the state after the upload is saved. Otherwise the user could be
			// irritated, if he presses cancel, because he only has the question state before
			// the upload process.
			$this->object->saveToDb();
		}
    return $result;
  }

	function setQuestionTabs()
	{
		$this->setQuestionTabsForClass("surveytextquestiongui");
	}

/**
* Creates a the cumulated results row for the question
*
* Creates a the cumulated results row for the question
*
* @return string HTML text with the cumulated results
* @access private
*/
	function getCumulatedResultRow($counter, $css_class, $survey_id)
	{
		include_once "./classes/class.ilTemplate.php";
		if (count($this->cumulated) == 0)
		{
			include_once "./Modules/Survey/classes/class.ilObjSurvey.php";
			$nr_of_users = ilObjSurvey::_getNrOfParticipants($survey_id);
			$this->cumulated =& $this->object->getCumulatedResults($survey_id, $nr_of_users);
		}
		$template = new ilTemplate("tpl.il_svy_svy_cumulated_results_row.html", TRUE, TRUE, "Modules/Survey");
		$template->setVariable("QUESTION_TITLE", ($counter+1) . ". ".$this->object->getTitle());
		$maxlen = 37;
		$questiontext = preg_replace("/\<[^>]+?>/ims", "", $this->object->getQuestiontext());
		if (strlen($questiontext) > $maxlen + 3)
		{
			$questiontext = substr($questiontext, 0, $maxlen) . "...";
		}
		$template->setVariable("QUESTION_TEXT", $questiontext);
		$template->setVariable("USERS_ANSWERED", $this->cumulated["USERS_ANSWERED"]);
		$template->setVariable("USERS_SKIPPED", $this->cumulated["USERS_SKIPPED"]);
		$template->setVariable("QUESTION_TYPE", $this->lng->txt($this->cumulated["QUESTION_TYPE"]));
		$template->setVariable("MODE", $this->cumulated["MODE"]);
		$template->setVariable("MODE_NR_OF_SELECTIONS", $this->cumulated["MODE_NR_OF_SELECTIONS"]);
		$template->setVariable("MEDIAN", $this->cumulated["MEDIAN"]);
		$template->setVariable("ARITHMETIC_MEAN", $this->cumulated["ARITHMETIC_MEAN"]);
		$template->setVariable("COLOR_CLASS", $css_class);
		return $template->get();
	}

/**
* Creates the detailed output of the cumulated results for the question
*
* Creates the detailed output of the cumulated results for the question
*
* @param integer $survey_id The database ID of the survey
* @param integer $counter The counter of the question position in the survey
* @return string HTML text with the cumulated results
* @access private
*/
	function getCumulatedResultsDetails($survey_id, $counter)
	{
		if (count($this->cumulated) == 0)
		{
			include_once "./Modules/Survey/classes/class.ilObjSurvey.php";
			$nr_of_users = ilObjSurvey::_getNrOfParticipants($survey_id);
			$this->cumulated =& $this->object->getCumulatedResults($survey_id, $nr_of_users);
		}
		
		$output = "";
		include_once "./classes/class.ilTemplate.php";
		$template = new ilTemplate("tpl.il_svy_svy_cumulated_results_detail.html", TRUE, TRUE, "Modules/Survey");

		$template->setCurrentBlock("detail_row");
		$template->setVariable("TEXT_OPTION", $this->lng->txt("question"));
		$questiontext = $this->object->getQuestiontext();
		$template->setVariable("TEXT_OPTION_VALUE", $this->object->prepareTextareaOutput($questiontext, TRUE));
		$template->parseCurrentBlock();
		$template->setCurrentBlock("detail_row");
		$template->setVariable("TEXT_OPTION", $this->lng->txt("question_type"));
		$template->setVariable("TEXT_OPTION_VALUE", $this->lng->txt($this->getQuestionType()));
		$template->parseCurrentBlock();
		$template->setCurrentBlock("detail_row");
		$template->setVariable("TEXT_OPTION", $this->lng->txt("users_answered"));
		$template->setVariable("TEXT_OPTION_VALUE", $this->cumulated["USERS_ANSWERED"]);
		$template->parseCurrentBlock();
		$template->setCurrentBlock("detail_row");
		$template->setVariable("TEXT_OPTION", $this->lng->txt("users_skipped"));
		$template->setVariable("TEXT_OPTION_VALUE", $this->cumulated["USERS_SKIPPED"]);
		$template->parseCurrentBlock();
		
		$template->setCurrentBlock("detail_row");
		$template->setVariable("TEXT_OPTION", $this->lng->txt("given_answers"));
		$textvalues = "";
		if (is_array($this->cumulated["textvalues"]))
		{
			foreach ($this->cumulated["textvalues"] as $textvalue)
			{
				$textvalues .= "<li>" . preg_replace("/\n/", "<br>", $textvalue) . "</li>";
			}
		}
		$textvalues = "<ul>$textvalues</ul>";
		$template->setVariable("TEXT_OPTION_VALUE", $textvalues);
		$template->parseCurrentBlock();

		$template->setVariable("QUESTION_TITLE", "$counter. ".$this->object->getTitle());
		return $template->get();
	}
}
?>

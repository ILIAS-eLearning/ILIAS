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

include_once "./assessment/classes/class.assQuestionGUI.php";
include_once "./assessment/classes/inc.AssessmentConstants.php";

/**
* Text question GUI representation
*
* The assTextQuestionGUI class encapsulates the GUI representation
* for text questions.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @module   class.assTextQuestionGUI.php
* @modulegroup   assessment
*/
class assTextQuestionGUI extends assQuestionGUI
{
	/**
	* assTextQuestionGUI constructor
	*
	* The constructor takes possible arguments an creates an instance of the assTextQuestionGUI object.
	*
	* @param integer $id The database id of a text question object
	* @access public
	*/
	function assTextQuestionGUI(
			$id = -1
	)
	{
		$this->assQuestionGUI();
		include_once "./assessment/classes/class.assTextQuestion.php";
		$this->object = new assTextQuestion();
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
		return "assTextQuestion";
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
		$javascript = "<script type=\"text/javascript\">function initialSelect() {\n%s\n}</script>";
		// single response
		$this->getQuestionTemplate("qt_text");
		$this->tpl->addBlockFile("QUESTION_DATA", "question_data", "tpl.il_as_qpl_text_question.html", true);
		// call to other question data i.e. estimated working time block
		$this->outOtherQuestionData();

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

		// Add text rating options
		$text_options = array(
			array("ci", $this->lng->txt("cloze_textgap_case_insensitive")),
			array("cs", $this->lng->txt("cloze_textgap_case_sensitive")),
			array("l1", sprintf($this->lng->txt("cloze_textgap_levenshtein_of"), "1")),
			array("l2", sprintf($this->lng->txt("cloze_textgap_levenshtein_of"), "2")),
			array("l3", sprintf($this->lng->txt("cloze_textgap_levenshtein_of"), "3")),
			array("l4", sprintf($this->lng->txt("cloze_textgap_levenshtein_of"), "4")),
			array("l5", sprintf($this->lng->txt("cloze_textgap_levenshtein_of"), "5"))
		);
		$text_rating = $this->object->getTextRating();
		foreach ($text_options as $text_option)
		{
			$this->tpl->setCurrentBlock("text_rating");
			$this->tpl->setVariable("RATING_VALUE", $text_option[0]);
			$this->tpl->setVariable("RATING_TEXT", $text_option[1]);
			if (strcmp($text_rating, $text_option[0]) == 0)
			{
				$this->tpl->setVariable("SELECTED_RATING_VALUE", " selected=\"selected\"");
			}
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setCurrentBlock("HeadContent");
		$this->tpl->setVariable("CONTENT_BLOCK", sprintf($javascript, "document.frm_text_question.title.focus();"));
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("question_data");
		$this->tpl->setVariable("TEXT_QUESTION_ID", $this->object->getId());
		$this->tpl->setVariable("VALUE_TEXT_QUESTION_TITLE", ilUtil::prepareFormOutput($this->object->getTitle()));
		$this->tpl->setVariable("VALUE_TEXT_QUESTION_COMMENT", ilUtil::prepareFormOutput($this->object->getComment()));
		$this->tpl->setVariable("VALUE_TEXT_QUESTION_AUTHOR", ilUtil::prepareFormOutput($this->object->getAuthor()));
		$questiontext = $this->object->getQuestion();
		$questiontext = preg_replace("/<br \/>/", "\n", $questiontext);
		$this->tpl->setVariable("VALUE_QUESTION", ilUtil::prepareFormOutput($questiontext));
		$keywords = $this->object->getKeywords();
		$keywords = preg_replace("/<br \/>/", "\n", $keywords);
		$this->tpl->setVariable("VALUE_KEYWORDS", ilUtil::prepareFormOutput($keywords));
		$this->tpl->setVariable("VALUE_POINTS", ilUtil::prepareFormOutput($this->object->getPoints()));
		if ($this->object->getMaxNumOfChars())
		{
			$this->tpl->setVariable("VALUE_MAXCHARS", ilUtil::prepareFormOutput($this->object->getMaxNumOfChars()));
		}
		$this->tpl->setVariable("TEXT_TITLE", $this->lng->txt("title"));
		$this->tpl->setVariable("TEXT_AUTHOR", $this->lng->txt("author"));
		$this->tpl->setVariable("TEXT_COMMENT", $this->lng->txt("description"));
		$this->tpl->setVariable("TEXT_RATING", $this->lng->txt("text_rating"));
		$this->tpl->setVariable("TEXT_QUESTION", $this->lng->txt("question"));
		$this->tpl->setVariable("TEXT_MAXCHARS", $this->lng->txt("maxchars"));
		$this->tpl->setVariable("TEXT_POINTS", $this->lng->txt("points"));
		$this->tpl->setVariable("OPTIONAL_KEYWORDS", $this->lng->txt("optional_keywords"));
		$this->tpl->setVariable("TEXT_KEYWORDS", $this->lng->txt("keywords"));
		$this->tpl->setVariable("DESCRIPTION_MAXCHARS", $this->lng->txt("description_maxchars"));
		$this->tpl->setVariable("TEXT_SOLUTION_HINT", $this->lng->txt("solution_hint"));
		$this->tpl->setVariable("TEXT_KEYWORDS_HINT", $this->lng->txt("keywords_hint"));
		if (count($this->object->suggested_solutions))
		{
			$solution_array = $this->object->getSuggestedSolution(0);
			include_once "./assessment/classes/class.assQuestion.php";
			$href = assQuestion::_getInternalLinkHref($solution_array["internal_link"]);
			$this->tpl->setVariable("TEXT_VALUE_SOLUTION_HINT", " <a href=\"$href\" target=\"content\">" . $this->lng->txt("solution_hint"). "</a> ");
			$this->tpl->setVariable("BUTTON_REMOVE_SOLUTION", $this->lng->txt("remove"));
			$this->tpl->setVariable("BUTTON_ADD_SOLUTION", $this->lng->txt("change"));
			$this->tpl->setVariable("VALUE_SOLUTION_HINT", $solution_array["internal_link"]);
		}
		else
		{
			$this->tpl->setVariable("BUTTON_ADD_SOLUTION", $this->lng->txt("add"));
		}
		$this->tpl->setVariable("SAVE",$this->lng->txt("save"));
		$this->tpl->setVariable("SAVE_EDIT", $this->lng->txt("save_edit"));
		$this->tpl->setVariable("CANCEL",$this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		$this->ctrl->setParameter($this, "sel_question_types", "assTextQuestion");
		$this->tpl->setVariable("TEXT_QUESTION_TYPE", $this->lng->txt("assTextQuestion"));
		$this->tpl->setVariable("ACTION_TEXT_QUESTION", $this->ctrl->getFormAction($this));

		$this->tpl->parseCurrentBlock();
		include_once "./Services/RTE/classes/class.ilRTE.php";
		$rtestring = ilRTE::_getRTEClassname();
		include_once "./Services/RTE/classes/class.$rtestring.php";
		$rte = new $rtestring();
		$rte->addPlugin("latex");
		$rte->addRTESupport();

		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("BODY_ATTRIBUTES", " onload=\"initialSelect();\""); 
		$this->tpl->parseCurrentBlock();
	}

	/**
	* check input fields
	*/
	function checkInput()
	{
		$cmd = $this->ctrl->getCmd();

		if ((!$_POST["title"]) or (!$_POST["author"]) or (!$_POST["question"]) or (!$_POST["points"]))
		{
			return false;
		}
		return true;
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
		if ((!$_POST["title"]) or (!$_POST["author"]) or (!$_POST["question"]) or (!$_POST["points"]))
		{
			$result = 1;
		}

		$this->object->setTitle(ilUtil::stripSlashes($_POST["title"]));
		$this->object->setAuthor(ilUtil::stripSlashes($_POST["author"]));
		$this->object->setComment(ilUtil::stripSlashes($_POST["comment"]));
		include_once "./classes/class.ilObjAdvancedEditing.php";
		$questiontext = ilUtil::stripSlashes($_POST["question"], true, ilObjAdvancedEditing::_getUsedHTMLTagsAsString());
		$questiontext = preg_replace("/\n/", "<br />", $questiontext);
		$this->object->setQuestion($questiontext);
		$this->object->setPoints($_POST["points"]);
		if ($_POST["points"] < 0)
		{
			$result = 1;
			$this->setErrorMessage($this->lng->txt("negative_points_not_allowed"));
		}
		$this->object->setSuggestedSolution($_POST["solution_hint"], 0);
		$this->object->setMaxNumOfChars($_POST["maxchars"]);
		$this->object->setKeywords(ilUtil::stripSlashes($_POST["keywords"], true, ilObjAdvancedEditing::_getUsedHTMLTagsAsString()));
		$this->object->setTextRating($_POST["text_rating"]);

		$saved = $this->writeOtherPostData($result);

		// Set the question id from a hidden form parameter
		if ($_POST["text_question_id"] > 0)
		{
			$this->object->setId($_POST["text_question_id"]);
		}
		
		return $result;
	}

	function outAdditionalOutput()
	{
		if ($this->object->getMaxNumOfChars() > 0)
		{
			$this->tpl->addBlockFile("CONTENT_BLOCK", "charcounter", "tpl.charcounter.html", true);
			$this->tpl->setCurrentBlock("charcounter");
			$this->tpl->setVariable("CHARACTERS", $this->lng->txt("characters"));
			$this->tpl->parseCurrentBlock();
		}
	}

	function outQuestionForTest($formaction, $active_id, $pass = NULL, $is_postponed = FALSE, $use_post_solutions = FALSE)
	{
		$test_output = $this->getTestOutput($active_id, $pass, $is_postponed, $use_post_solutions); 
		$this->tpl->setVariable("QUESTION_OUTPUT", $test_output);
		$this->tpl->setVariable("FORMACTION", $formaction);
		$this->outAdditionalOutput();
	}

	function getSolutionOutput($active_id, $pass = NULL)
	{
		// get page object output
		$pageoutput = $this->outQuestionPage("", $is_postponed, $active_id);

		// get the solution of the user for the active pass or from the last pass if allowed
		$user_solution = "";
		if ($active_id)
		{
			$solutions =& $this->object->getSolutionValues($active_id, $pass);
			foreach ($solutions as $idx => $solution_value)
			{
				$user_solution = $solution_value["value1"];
			}
		}
		else
		{
			$keywords = $this->object->getKeywordList();
			if (count($keywords))
			{
				$user_solution = $this->lng->txt("solution_may_contain_keywords") . ": " . join(",", $keywords);
			}
		}
		
		// generate the question output
		include_once "./classes/class.ilTemplate.php";
		$template = new ilTemplate("tpl.il_as_qpl_text_question_output_solution.html", TRUE, TRUE, TRUE);
		$template->setVariable("ESSAY", $user_solution);
		$questiontext = $this->object->getQuestion();
		$questiontext = ilUtil::insertLatexImages($questiontext, "\<span class\=\"latex\">", "\<\/span>", $this->getLatexCGI());
		$template->setVariable("QUESTIONTEXT", $questiontext);
		$questionoutput = $template->get();
		$questionoutput = str_replace("<div xmlns:xhtml=\"http://www.w3.org/1999/xhtml\" class=\"ilc_Question\"></div>", $questionoutput, $pageoutput);
		$questionoutput = preg_replace("/<div class\=\"ilc_PageTitle\"\>.*?\<\/div\>/", "", $questionoutput);
		return $questionoutput;
	}
	
	function getPreview()
	{
		// generate the question output
		include_once "./classes/class.ilTemplate.php";
		$template = new ilTemplate("tpl.il_as_qpl_text_question_output.html", TRUE, TRUE, TRUE);
		if ($this->object->getMaxNumOfChars())
		{
			$template->setCurrentBlock("maximum_char_hint");
			$template->setVariable("MAXIMUM_CHAR_HINT", sprintf($this->lng->txt("text_maximum_chars_allowed"), $this->object->getMaxNumOfChars()));
			$template->parseCurrentBlock();
			$template->setCurrentBlock("has_maxchars");
			$template->setVariable("MAXCHARS", $this->object->getMaxNumOfChars());
			$template->parseCurrentBlock();
			$template->setCurrentBlock("maxchars_counter");
			$template->setVariable("MAXCHARS", $this->object->getMaxNumOfChars());
			$template->parseCurrentBlock();
		}
		$questiontext = $this->object->getQuestion();
		$questiontext = ilUtil::insertLatexImages($questiontext, "\<span class\=\"latex\">", "\<\/span>", $this->getLatexCGI());
		$template->setVariable("QUESTIONTEXT", $questiontext);
		$questionoutput = $template->get();
		$questionoutput = preg_replace("/\<div[^>]*?>(.*)\<\/div>/is", "\\1", $questionoutput);
		return $questionoutput;
	}

	function getTestOutput($active_id, $pass = NULL, $is_postponed = FALSE, $use_post_solutions = FALSE)
	{
		// get page object output
		$pageoutput = $this->outQuestionPage("", $is_postponed, $active_id);

		// get the solution of the user for the active pass or from the last pass if allowed
		$user_solution = "";
		if ($active_id)
		{
			$solutions = NULL;
			include_once "./assessment/classes/class.ilObjTest.php";
			if (ilObjTest::_getHidePreviousResults($active_id, true))
			{
				if (is_null($pass)) $pass = ilObjTest::_getPass($active_id);
			}
			$solutions =& $this->object->getSolutionValues($active_id, $pass);
			foreach ($solutions as $idx => $solution_value)
			{
				$user_solution = $solution_value["value1"];
			}
		}
		
		// generate the question output
		include_once "./classes/class.ilTemplate.php";
		$template = new ilTemplate("tpl.il_as_qpl_text_question_output.html", TRUE, TRUE, TRUE);
		if ($this->object->getMaxNumOfChars())
		{
			$template->setCurrentBlock("maximum_char_hint");
			$template->setVariable("MAXIMUM_CHAR_HINT", sprintf($this->lng->txt("text_maximum_chars_allowed"), $this->object->getMaxNumOfChars()));
			$template->parseCurrentBlock();
			$template->setCurrentBlock("has_maxchars");
			$template->setVariable("MAXCHARS", $this->object->getMaxNumOfChars());
			$template->parseCurrentBlock();
			$template->setCurrentBlock("maxchars_counter");
			$template->setVariable("MAXCHARS", $this->object->getMaxNumOfChars());
			$template->parseCurrentBlock();
		}
		$template->setVariable("ESSAY", $user_solution);
		$questiontext = $this->object->getQuestion();
		$questiontext = ilUtil::insertLatexImages($questiontext, "\<span class\=\"latex\">", "\<\/span>", $this->getLatexCGI());
		$template->setVariable("QUESTIONTEXT", $questiontext);
		$questionoutput = $template->get();
		$questionoutput = str_replace("<div xmlns:xhtml=\"http://www.w3.org/1999/xhtml\" class=\"ilc_Question\"></div>", $questionoutput, $pageoutput);
		return $questionoutput;
	}

	function addSuggestedSolution()
	{
		$_SESSION["subquestion_index"] = 0;
		if ($_POST["cmd"]["addSuggestedSolution"])
		{
			if ($this->writePostData())
			{
				sendInfo($this->getErrorMessage());
				$this->editQuestion();
				return;
			}
			if (!$this->checkInput())
			{
				sendInfo($this->lng->txt("fill_out_all_required_fields_add_answer"));
				$this->editQuestion();
				return;
			}
		}
		$this->object->saveToDb();
		$_GET["q_id"] = $this->object->getId();
		$this->tpl->setVariable("HEADER", $this->object->getTitle());
		$this->getQuestionTemplate("qt_text");
		parent::addSuggestedSolution();
	}
}
?>

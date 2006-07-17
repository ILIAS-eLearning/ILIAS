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

include_once "./survey/classes/class.SurveyQuestionGUI.php";
include_once "./survey/classes/inc.SurveyConstants.php";

/**
* Nominal survey question GUI representation
*
* The SurveyNominalQuestionGUI class encapsulates the GUI representation
* for nominal survey question types.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @module   class.SurveyNominalQuestionGUI.php
* @modulegroup   Survey
*/
class SurveyNominalQuestionGUI extends SurveyQuestionGUI 
{

/**
* SurveyNominalQuestionGUI constructor
*
* The constructor takes possible arguments an creates an instance of the SurveyNominalQuestionGUI object.
*
* @param integer $id The database id of a nominal question object
* @access public
*/
  function SurveyNominalQuestionGUI(
		$id = -1
  )

  {
		$this->SurveyQuestionGUI();
		include_once "./survey/classes/class.SurveyNominalQuestion.php";
		$this->object = new SurveyNominalQuestion();
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
		return "SurveyNominalQuestion";
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
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_qpl_nominal.html", true);
	  $this->tpl->addBlockFile("OTHER_QUESTION_DATA", "other_question_data", "tpl.il_svy_qpl_other_question_data.html", true);
		$this->tpl->setVariable("TEXT_ORIENTATION", $this->lng->txt("orientation"));
		switch ($this->object->getOrientation())
		{
			case 0:
				$this->tpl->setVariable("SELECTED_VERTICAL", " selected=\"selected\"");
				break;
			case 1:
				$this->tpl->setVariable("SELECTED_HORIZONTAL", " selected=\"selected\"");
				break;
			case 2:
				$this->tpl->setVariable("SELECTED_COMBOBOX", " selected=\"selected\"");
				break;
		}
		$this->tpl->setVariable("TXT_VERTICAL", $this->lng->txt("vertical"));
		$this->tpl->setVariable("TXT_HORIZONTAL", $this->lng->txt("horizontal"));
		if ($this->object->getSubtype() == SUBTYPE_MCSR)
		{
			$this->tpl->setVariable("TXT_COMBOBOX", $this->lng->txt("combobox"));
		}

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
			include_once "./survey/classes/class.SurveyQuestion.php";
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
		$this->tpl->setVariable("VALUE_QUESTION", htmlspecialchars($questiontext));
		$this->tpl->setVariable("TXT_SR", $this->lng->txt("multiple_choice_single_response"));
		$this->tpl->setVariable("TXT_MR", $this->lng->txt("multiple_choice_multiple_response"));
		if ($this->object->getSubtype() == SUBTYPE_MCSR)
		{
			$this->tpl->setVariable("SELECTED_SR", " selected=\"selected\"");
		}
		else
		{
			$this->tpl->setVariable("SELECTED_MR", " selected=\"selected\"");
		}
		$this->tpl->setVariable("TEXT_TITLE", $this->lng->txt("title"));
		$this->tpl->setVariable("TEXT_AUTHOR", $this->lng->txt("author"));
		$this->tpl->setVariable("TEXT_DESCRIPTION", $this->lng->txt("description"));
		$this->tpl->setVariable("TEXT_QUESTION", $this->lng->txt("question"));
		$this->tpl->setVariable("TEXT_QUESTION_TYPE", $this->lng->txt("questiontype"));
		$this->tpl->setVariable("TEXT_OBLIGATORY", $this->lng->txt("obligatory"));
		if ($this->object->getObligatory())
		{
			$this->tpl->setVariable("CHECKED_OBLIGATORY", " checked=\"checked\"");
		}
		$this->tpl->setVariable("SAVE",$this->lng->txt("save"));
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
		$this->tpl->parseCurrentBlock();
		include_once "./Services/RTE/classes/class.ilRTE.php";
		$rtestring = ilRTE::_getRTEClassname();
		include_once "./Services/RTE/classes/class.$rtestring.php";
		$rte = new $rtestring();
		$rte->addPlugin("latex");
		$rte->addRTESupport("survey");
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
			$this->tpl->setCurrentBlock("material_nominal");
			include_once "./survey/classes/class.SurveyQuestion.php";
			$href = SurveyQuestion::_getInternalLinkHref($this->object->material["internal_link"]);
			$this->tpl->setVariable("TEXT_MATERIAL", $this->lng->txt("material") . ": <a href=\"$href\" target=\"content\">" . $this->object->material["title"]. "</a> ");
			$this->tpl->parseCurrentBlock();
		}
		switch ($this->object->getOrientation())
		{
			case 0:
				// vertical orientation
				for ($i = 0; $i < $this->object->categories->getCategoryCount(); $i++) {
					$category = $this->object->categories->getCategory($i);
					if ($this->object->getSubtype() == SUBTYPE_MCSR)
					{
						$this->tpl->setCurrentBlock("nominal_row_sr");
						$this->tpl->setVariable("TEXT_NOMINAL", $category);
						$this->tpl->setVariable("VALUE_NOMINAL", $i);
						$this->tpl->setVariable("QUESTION_ID", $this->object->getId());
						if (is_array($working_data))
						{
							foreach ($working_data as $value)
							{
								if (strlen($value["value"]))
								{
									if ($value["value"] == $i)
									{
										$this->tpl->setVariable("CHECKED_NOMINAL", " checked=\"checked\"");
									}
								}
							}
						}
						$this->tpl->parseCurrentBlock();
					}
					else
					{
						$this->tpl->setCurrentBlock("nominal_row_mr");
						$this->tpl->setVariable("TEXT_NOMINAL", $category);
						$this->tpl->setVariable("VALUE_NOMINAL", $i);
						$this->tpl->setVariable("QUESTION_ID", $this->object->getId());
						if (is_array($working_data))
						{
							foreach ($working_data as $value)
							{
								if (strlen($value["value"]))
								{
									if ($value["value"] == $i)
									{
										$this->tpl->setVariable("CHECKED_NOMINAL", " checked=\"checked\"");
									}
								}
							}
						}
						$this->tpl->parseCurrentBlock();
					}
				}
				break;
			case 1:
				// horizontal orientation
				if ($this->object->getSubtype() == SUBTYPE_MCSR)
				{
					for ($i = 0; $i < $this->object->categories->getCategoryCount(); $i++) 
					{
						$category = $this->object->categories->getCategory($i);
						$this->tpl->setCurrentBlock("radio_col_nominal");
						$this->tpl->setVariable("VALUE_NOMINAL", $i);
						$this->tpl->setVariable("QUESTION_ID", $this->object->getId());
						if (is_array($working_data))
						{
							foreach ($working_data as $value)
							{
								if (strlen($value["value"]))
								{
									if ($value["value"] == $i)
									{
										$this->tpl->setVariable("CHECKED_NOMINAL", " checked=\"checked\"");
									}
								}
							}
						}
						$this->tpl->parseCurrentBlock();
					}
					for ($i = 0; $i < $this->object->categories->getCategoryCount(); $i++) 
					{
						$category = $this->object->categories->getCategory($i);
						$this->tpl->setCurrentBlock("text_col_nominal");
						$this->tpl->setVariable("VALUE_NOMINAL", $i);
						$this->tpl->setVariable("TEXT_NOMINAL", $category);
						$this->tpl->setVariable("QUESTION_ID", $this->object->getId());
						$this->tpl->parseCurrentBlock();
					}
				}
				else
				{
					for ($i = 0; $i < $this->object->categories->getCategoryCount(); $i++) 
					{
						$category = $this->object->categories->getCategory($i);
						$this->tpl->setCurrentBlock("checkbox_col_nominal");
						$this->tpl->setVariable("VALUE_NOMINAL", $i);
						$this->tpl->setVariable("QUESTION_ID", $this->object->getId());
						if (is_array($working_data))
						{
							foreach ($working_data as $value)
							{
								if (strlen($value["value"]))
								{
									if ($value["value"] == $i)
									{
										$this->tpl->setVariable("CHECKED_NOMINAL", " checked=\"checked\"");
									}
								}
							}
						}
						$this->tpl->parseCurrentBlock();
					}
					for ($i = 0; $i < $this->object->categories->getCategoryCount(); $i++) 
					{
						$category = $this->object->categories->getCategory($i);
						$this->tpl->setCurrentBlock("text_col_nominal_mr");
						$this->tpl->setVariable("VALUE_NOMINAL", $i);
						$this->tpl->setVariable("TEXT_NOMINAL", $category);
						$this->tpl->setVariable("QUESTION_ID", $this->object->getId());
						$this->tpl->parseCurrentBlock();
					}
				}
				break;
			case 2:
				// combobox output
				for ($i = 0; $i < $this->object->categories->getCategoryCount(); $i++) 
				{
					$category = $this->object->categories->getCategory($i);
					$this->tpl->setCurrentBlock("comborow_nominal");
					$this->tpl->setVariable("TEXT_NOMINAL", $category);
					$this->tpl->setVariable("VALUE_NOMINAL", $i);
					if (is_array($working_data))
					{
						foreach ($working_data as $value)
						{
							if (strlen($value["value"]))
							{
								if ($value["value"] == $i)
								{
									$this->tpl->setVariable("SELECTED_NOMINAL", " selected=\"selected\"");
								}
							}
						}
					}
					$this->tpl->parseCurrentBlock();
				}
				$this->tpl->setCurrentBlock("combooutput_nominal");
				$this->tpl->setVariable("QUESTION_ID", $this->object->getId());
				$this->tpl->setVariable("SELECT_OPTION", $this->lng->txt("select_option"));
				$this->tpl->setVariable("TEXT_SELECTION", $this->lng->txt("selection"));
				$this->tpl->parseCurrentBlock();
				break;
		}
		
		$this->tpl->setCurrentBlock("question_data_nominal");
		if (strcmp($error_message, "") != 0)
		{
			$this->tpl->setVariable("ERROR_MESSAGE", "<p class=\"warning\">$error_message</p>");
		}
		$questiontext = $this->object->getQuestiontext();
		$questiontext = ilUtil::insertLatexImages($questiontext, "\<span class\=\"latex\">", "\<\/span>", URL_TO_LATEX);
		$this->tpl->setVariable("QUESTIONTEXT", $questiontext);
		if (! $this->object->getObligatory())
		{
			$this->tpl->setVariable("OBLIGATORY_TEXT", $this->lng->txt("survey_question_optional"));
		}
		if ($question_title)
		{
			$this->tpl->setVariable("QUESTION_TITLE", $this->object->getTitle());
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
		$this->tpl->addBlockFile("NOMINAL", "nominal", "tpl.il_svy_out_nominal.html", true);
		$this->outWorkingForm();
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
  function writePostData() 
	{
    $result = 0;
    if ((!$_POST["title"]) or (!$_POST["author"]) or (!$_POST["question"]))
      $result = 1;

    // Set the question id from a hidden form parameter
    if ($_POST["id"] > 0)
      $this->object->setId($_POST["id"]);
		include_once "./classes/class.ilUtil.php";
    $this->object->setTitle(ilUtil::stripSlashes($_POST["title"]));
    $this->object->setAuthor(ilUtil::stripSlashes($_POST["author"]));
    $this->object->setDescription(ilUtil::stripSlashes($_POST["description"]));
		if (strlen($_POST["material"]))
		{
			$this->object->setMaterial($_POST["material"], 0, ilUtil::stripSlashes($_POST["material_title"]));
		}
		$this->object->setSubtype($_POST["type"]);
		$this->object->setOrientation($_POST["orientation"]);
		include_once "./classes/class.ilObjAdvancedEditing.php";
		$questiontext = ilUtil::stripSlashes($_POST["question"], true, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("survey"));
		$questiontext = preg_replace("/[\n\r]+/", "<br />", $questiontext);
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
			$this->object->saveToDb("", false);
		}
    return $result;
  }
	
/**
* Creates the form to edit the question categories
*
* Creates the form to edit the question categories
*
* @access private
*/
	function categories($add = false)
	{
		if ($this->object->getId() < 1) 
		{
			sendInfo($this->lng->txt("fill_out_all_required_fields_add_category"), true);
			$this->ctrl->redirect($this, "editQuestion");
		}
		if (strcmp($this->ctrl->getCmd(), "categories") == 0) $_SESSION["spl_modified"] = false;
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_qpl_nominal_answers.html", true);
    // output of existing single response answers
		for ($i = 0; $i < $this->object->categories->getCategoryCount(); $i++) 
		{
			$this->tpl->setCurrentBlock("cat_selector");
			$this->tpl->setVariable("CATEGORY_ORDER", $i);
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("categories");
			$category = $this->object->categories->getCategory($i);
			$this->tpl->setVariable("CATEGORY_ORDER", $i);
			$this->tpl->setVariable("CATEGORY_NUMBER", $i+1);
			$this->tpl->setVariable("VALUE_CATEGORY", $category);
			$this->tpl->setVariable("TEXT_CATEGORY", $this->lng->txt("category"));
			$this->tpl->parseCurrentBlock();
		}
		
		if ($add)
		{
			$nrOfCategories = $_POST["nrOfCategories"];
			if ($nrOfCategories < 1) $nrOfCategories = 1;
			// Create template for a new category
			for ($i = 1; $i <= $nrOfCategories; $i++)
			{
				$this->tpl->setCurrentBlock("categories");
				$this->tpl->setVariable("CATEGORY_ORDER", $this->object->categories->getCategoryCount() + $i - 1);
				$this->tpl->setVariable("TEXT_CATEGORY", $this->lng->txt("category"));
				$this->tpl->parseCurrentBlock();
			}
		}

		if (is_array($_SESSION["spl_move"]))
		{
			if (count($_SESSION["spl_move"]))
			{
				$this->tpl->setCurrentBlock("move_buttons");
				$this->tpl->setVariable("INSERT_BEFORE", $this->lng->txt("insert_before"));
				$this->tpl->setVariable("INSERT_AFTER", $this->lng->txt("insert_after"));
				$this->tpl->parseCurrentBlock();
			}
		}
		
		include_once "./classes/class.ilUtil.php";
		if ($this->object->categories->getCategoryCount() == 0)
		{
			if (!$add)
			{
				$this->tpl->setCurrentBlock("nocategories");
				$this->tpl->setVariable("NO_CATEGORIES", $this->lng->txt("question_contains_no_categories"));
				$this->tpl->parseCurrentBlock();
			}
		}
		else
		{
			$this->tpl->setCurrentBlock("selectall");
			$this->tpl->setVariable("SELECT_ALL", $this->lng->txt("select_all"));
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("existingcategories");
			$this->tpl->setVariable("DELETE", $this->lng->txt("delete"));
			$this->tpl->setVariable("MOVE", $this->lng->txt("move"));
			$this->tpl->setVariable("ARROW", "<img src=\"" . ilUtil::getImagePath("arrow_downright.gif") . "\" alt=\"".$this->lng->txt("arrow_downright")."\">");
			$this->tpl->parseCurrentBlock();
		}

		for ($i = 1; $i < 10; $i++)
		{
			$this->tpl->setCurrentBlock("numbers");
			$this->tpl->setVariable("VALUE_NUMBER", $i);
			if ($i == 1)
			{
				$this->tpl->setVariable("TEXT_NUMBER", $i . " " . $this->lng->txt("category"));
			}
			else
			{
				$this->tpl->setVariable("TEXT_NUMBER", $i . " " . $this->lng->txt("categories"));
			}
			$this->tpl->parseCurrentBlock();
		}
		
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("VALUE_ADD_CATEGORY", $this->lng->txt("add"));
		$this->tpl->setVariable("SAVE", $this->lng->txt("save"));
		if ($_SESSION["spl_modified"])
		{
			$this->tpl->setVariable("FORM_DATA_MODIFIED_PRESS_SAVE", $this->lng->txt("form_data_modified_press_save"));
		}
		$this->tpl->setVariable("QUESTION_TEXT", ilUtil::prepareFormOutput($this->object->getQuestiontext()));
		$this->tpl->parseCurrentBlock();
	}
	
	function setQuestionTabs()
	{
		$this->setQuestionTabsForClass("surveynominalquestiongui");
	}

	function outCumulatedResultsDetails(&$cumulated_results, $counter)
	{
		$this->tpl->setCurrentBlock("detail_row");
		$this->tpl->setVariable("TEXT_OPTION", $this->lng->txt("question"));
		$this->tpl->setVariable("TEXT_OPTION_VALUE", $this->object->getQuestiontext());
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("detail_row");
		$this->tpl->setVariable("TEXT_OPTION", $this->lng->txt("question_type"));
		$this->tpl->setVariable("TEXT_OPTION_VALUE", $this->lng->txt($this->getQuestionType()));
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("detail_row");
		$this->tpl->setVariable("TEXT_OPTION", $this->lng->txt("users_answered"));
		$this->tpl->setVariable("TEXT_OPTION_VALUE", $cumulated_results["USERS_ANSWERED"]);
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("detail_row");
		$this->tpl->setVariable("TEXT_OPTION", $this->lng->txt("users_skipped"));
		$this->tpl->setVariable("TEXT_OPTION_VALUE", $cumulated_results["USERS_SKIPPED"]);
		$this->tpl->parseCurrentBlock();
		
		$this->tpl->setCurrentBlock("detail_row");
		$this->tpl->setVariable("TEXT_OPTION", $this->lng->txt("subtype"));
		switch ($this->object->getSubType())
		{
			case SUBTYPE_MCSR:
				$this->tpl->setVariable("TEXT_OPTION_VALUE", $this->lng->txt("multiple_choice_single_response"));
				break;
			case SUBTYPE_MCMR:
				$this->tpl->setVariable("TEXT_OPTION_VALUE", $this->lng->txt("multiple_choice_multiple_response"));
				break;
		}
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("detail_row");
		$this->tpl->setVariable("TEXT_OPTION", $this->lng->txt("mode"));
		$this->tpl->setVariable("TEXT_OPTION_VALUE", $cumulated_results["MODE"]);
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("detail_row");
		$this->tpl->setVariable("TEXT_OPTION", $this->lng->txt("mode_nr_of_selections"));
		$this->tpl->setVariable("TEXT_OPTION_VALUE", $cumulated_results["MODE_NR_OF_SELECTIONS"]);
		$this->tpl->parseCurrentBlock();
		
		$this->tpl->setCurrentBlock("detail_row");
		$this->tpl->setVariable("TEXT_OPTION", $this->lng->txt("categories"));
		$categories = "";
		foreach ($cumulated_results["variables"] as $key => $value)
		{
			$categories .= "<li>" . $this->lng->txt("title") . ":" . "<span class=\"bold\">" . $value["title"] . "</span><br />" .
				$this->lng->txt("category_nr_selected") . ": " . "<span class=\"bold\">" . $value["selected"] . "</span><br />" .
				$this->lng->txt("percentage_of_selections") . ": " . "<span class=\"bold\">" . sprintf("%.2f", 100*$value["percentage"]) . "</span></li>";
		}
		$categories = "<ol>$categories</ol>";
		$this->tpl->setVariable("TEXT_OPTION_VALUE", $categories);
		$this->tpl->parseCurrentBlock();
		
		// display chart for nominal question for array $eval["variables"]
		$this->tpl->setVariable("TEXT_CHART", $this->lng->txt("chart"));
		$this->tpl->setVariable("ALT_CHART", $data["title"] . "( " . $this->lng->txt("chart") . ")");
		$this->tpl->setVariable("CHART","./survey/displaychart.php?grName=" . urlencode($this->object->getTitle()) .
			"&type=bars" . 
			"&x=" . urlencode($this->lng->txt("answers")) . 
			"&y=" . urlencode($this->lng->txt("users_answered")) . 
			"&arr=".base64_encode(serialize($cumulated_results["variables"])));
		
		$this->tpl->setCurrentBlock("detail");
		$this->tpl->setVariable("QUESTION_TITLE", "$counter. ".$this->object->getTitle());
		$this->tpl->parseCurrentBlock();
	}
}
?>

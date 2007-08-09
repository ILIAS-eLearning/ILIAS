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
* Nominal survey question GUI representation
*
* The SurveyNominalQuestionGUI class encapsulates the GUI representation
* for nominal survey question types.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @extends SurveyQuestionGUI
* @ingroup ModulesSurveyQuestionPool
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
		include_once "./Modules/SurveyQuestionPool/classes/class.SurveyNominalQuestion.php";
		$this->object = new SurveyNominalQuestion();
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
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_qpl_nominal.html", "Modules/SurveyQuestionPool");
	  $this->tpl->addBlockFile("OTHER_QUESTION_DATA", "other_question_data", "tpl.il_svy_qpl_other_question_data.html", "Modules/SurveyQuestionPool");
		$this->tpl->setVariable("TEXT_ORIENTATION", $this->lng->txt("orientation"));
		switch ($this->object->getOrientation())
		{
			case 0:
				$this->tpl->setVariable("CHECKED_VERTICAL", " checked=\"checked\"");
				break;
			case 1:
				$this->tpl->setVariable("CHECKED_HORIZONTAL", " checked=\"checked\"");
				break;
			case 2:
				$this->tpl->setVariable("CHECKED_COMBOBOX", " checked=\"checked\"");
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
		$questiontext = $this->object->getQuestiontext();
		$this->tpl->setVariable("VALUE_QUESTION", ilUtil::prepareFormOutput($this->object->prepareTextareaOutput($questiontext)));
		$this->tpl->setVariable("TXT_SR", $this->lng->txt("multiple_choice_single_response"));
		$this->tpl->setVariable("TXT_MR", $this->lng->txt("multiple_choice_multiple_response"));
		$this->tpl->setVariable("TEXT_SUBTYPE", $this->lng->txt("subtype"));
		$this->tpl->setVariable("DESCRIPTION_QUESTION_TYPE", $this->lng->txt("multiple_choice_subtype_description"));
		if ($this->object->getSubtype() == SUBTYPE_MCSR)
		{
			$this->tpl->setVariable("CHECKED_SR", " checked=\"checked\"");
		}
		else
		{
			$this->tpl->setVariable("CHECKED_MR", " checked=\"checked\"");
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
		$this->tpl->setVariable("TEXT_QUESTION_TYPE", $this->lng->txt($this->getQuestionType()));
		$this->tpl->parseCurrentBlock();
		include_once "./Services/RTE/classes/class.ilRTE.php";
		$rtestring = ilRTE::_getRTEClassname();
		include_once "./Services/RTE/classes/class.$rtestring.php";
		$rte = new $rtestring();
		$rte->addPlugin("latex");
		$rte->addButton("latex");
		$rte->removePlugin("ibrowser");
		include_once "./classes/class.ilObject.php";
		$obj_id = $_GET["q_id"];
		$obj_type = ilObject::_lookupType($_GET["ref_id"], TRUE);
		$rte->addRTESupport($obj_id, $obj_type, "survey");
  }

/**
* Creates the question output form for the learner
* 
* Creates the question output form for the learner
*
* @access public
*/
	function getWorkingForm($working_data = "", $question_title = 1, $show_questiontext = 1, $error_message = "")
	{
		$template = new ilTemplate("tpl.il_svy_out_nominal.html", TRUE, TRUE, "Modules/SurveyQuestionPool");
		if (count($this->object->material))
		{
			$template->setCurrentBlock("material_nominal");
			include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestion.php";
			$href = SurveyQuestion::_getInternalLinkHref($this->object->material["internal_link"]);
			$template->setVariable("TEXT_MATERIAL", $this->lng->txt("material") . ": <a href=\"$href\" target=\"content\">" . $this->object->material["title"]. "</a> ");
			$template->parseCurrentBlock();
		}
		switch ($this->object->getOrientation())
		{
			case 0:
				// vertical orientation
				for ($i = 0; $i < $this->object->categories->getCategoryCount(); $i++) {
					$category = $this->object->categories->getCategory($i);
					if ($this->object->getSubtype() == SUBTYPE_MCSR)
					{
						$template->setCurrentBlock("nominal_row_sr");
						$template->setVariable("TEXT_NOMINAL", $category);
						$template->setVariable("VALUE_NOMINAL", $i);
						$template->setVariable("QUESTION_ID", $this->object->getId());
						if (is_array($working_data))
						{
							foreach ($working_data as $value)
							{
								if (strlen($value["value"]))
								{
									if ($value["value"] == $i)
									{
										$template->setVariable("CHECKED_NOMINAL", " checked=\"checked\"");
									}
								}
							}
						}
						$template->parseCurrentBlock();
					}
					else
					{
						$template->setCurrentBlock("nominal_row_mr");
						$template->setVariable("TEXT_NOMINAL", $category);
						$template->setVariable("VALUE_NOMINAL", $i);
						$template->setVariable("QUESTION_ID", $this->object->getId());
						if (is_array($working_data))
						{
							foreach ($working_data as $value)
							{
								if (strlen($value["value"]))
								{
									if ($value["value"] == $i)
									{
										$template->setVariable("CHECKED_NOMINAL", " checked=\"checked\"");
									}
								}
							}
						}
						$template->parseCurrentBlock();
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
						$template->setCurrentBlock("radio_col_nominal");
						$template->setVariable("VALUE_NOMINAL", $i);
						$template->setVariable("QUESTION_ID", $this->object->getId());
						if (is_array($working_data))
						{
							foreach ($working_data as $value)
							{
								if (strlen($value["value"]))
								{
									if ($value["value"] == $i)
									{
										$template->setVariable("CHECKED_NOMINAL", " checked=\"checked\"");
									}
								}
							}
						}
						$template->parseCurrentBlock();
					}
					for ($i = 0; $i < $this->object->categories->getCategoryCount(); $i++) 
					{
						$category = $this->object->categories->getCategory($i);
						$template->setCurrentBlock("text_col_nominal");
						$template->setVariable("VALUE_NOMINAL", $i);
						$template->setVariable("TEXT_NOMINAL", $category);
						$template->setVariable("QUESTION_ID", $this->object->getId());
						$template->parseCurrentBlock();
					}
				}
				else
				{
					for ($i = 0; $i < $this->object->categories->getCategoryCount(); $i++) 
					{
						$category = $this->object->categories->getCategory($i);
						$template->setCurrentBlock("checkbox_col_nominal");
						$template->setVariable("VALUE_NOMINAL", $i);
						$template->setVariable("QUESTION_ID", $this->object->getId());
						if (is_array($working_data))
						{
							foreach ($working_data as $value)
							{
								if (strlen($value["value"]))
								{
									if ($value["value"] == $i)
									{
										$template->setVariable("CHECKED_NOMINAL", " checked=\"checked\"");
									}
								}
							}
						}
						$template->parseCurrentBlock();
					}
					for ($i = 0; $i < $this->object->categories->getCategoryCount(); $i++) 
					{
						$category = $this->object->categories->getCategory($i);
						$template->setCurrentBlock("text_col_nominal_mr");
						$template->setVariable("VALUE_NOMINAL", $i);
						$template->setVariable("TEXT_NOMINAL", $category);
						$template->setVariable("QUESTION_ID", $this->object->getId());
						$template->parseCurrentBlock();
					}
				}
				break;
			case 2:
				// combobox output
				for ($i = 0; $i < $this->object->categories->getCategoryCount(); $i++) 
				{
					$category = $this->object->categories->getCategory($i);
					$template->setCurrentBlock("comborow_nominal");
					$template->setVariable("TEXT_NOMINAL", $category);
					$template->setVariable("VALUE_NOMINAL", $i);
					if (is_array($working_data))
					{
						foreach ($working_data as $value)
						{
							if (strlen($value["value"]))
							{
								if ($value["value"] == $i)
								{
									$template->setVariable("SELECTED_NOMINAL", " selected=\"selected\"");
								}
							}
						}
					}
					$template->parseCurrentBlock();
				}
				$template->setCurrentBlock("combooutput_nominal");
				$template->setVariable("QUESTION_ID", $this->object->getId());
				$template->setVariable("SELECT_OPTION", $this->lng->txt("select_option"));
				$template->setVariable("TEXT_SELECTION", $this->lng->txt("selection"));
				$template->parseCurrentBlock();
				break;
		}
		
		$template->setCurrentBlock("question_data_nominal");
		if (strcmp($error_message, "") != 0)
		{
			$template->setVariable("ERROR_MESSAGE", "<p class=\"warning\">$error_message</p>");
		}
		if ($show_questiontext)
		{
			$questiontext = $this->object->getQuestiontext();
			$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($questiontext, TRUE));
		}
		if (! $this->object->getObligatory())
		{
			$template->setVariable("OBLIGATORY_TEXT", $this->lng->txt("survey_question_optional"));
		}
		if ($question_title)
		{
			$template->setVariable("QUESTION_TITLE", $this->object->getTitle());
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
	function getPrintView($question_title = 1, $show_questiontext = 1)
	{
		$template = new ilTemplate("tpl.il_svy_qpl_nominal_printview.html", TRUE, TRUE, "Modules/SurveyQuestionPool");
		switch ($this->object->getOrientation())
		{
			case 0:
				// vertical orientation
				for ($i = 0; $i < $this->object->categories->getCategoryCount(); $i++) {
					$category = $this->object->categories->getCategory($i);
					if ($this->object->getSubtype() == SUBTYPE_MCSR)
					{
						$template->setCurrentBlock("nominal_row_sr");
						$template->setVariable("IMAGE_RADIO", ilUtil::getHtmlPath(ilUtil::getImagePath("radiobutton_unchecked.gif")));
						$template->setVariable("ALT_RADIO", $this->lng->txt("unchecked"));
						$template->setVariable("TITLE_RADIO", $this->lng->txt("unchecked"));
						$template->setVariable("TEXT_NOMINAL", $category);
						$template->parseCurrentBlock();
					}
					else
					{
						$template->setCurrentBlock("nominal_row_mr");
						$template->setVariable("IMAGE_CHECKBOX", ilUtil::getHtmlPath(ilUtil::getImagePath("checkbox_unchecked.gif")));
						$template->setVariable("ALT_CHECKBOX", $this->lng->txt("unchecked"));
						$template->setVariable("TITLE_CHECKBOX", $this->lng->txt("unchecked"));
						$template->setVariable("TEXT_NOMINAL", $category);
						$template->parseCurrentBlock();
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
						$template->setCurrentBlock("radio_col_nominal");
						$template->setVariable("IMAGE_RADIO", ilUtil::getHtmlPath(ilUtil::getImagePath("radiobutton_unchecked.gif")));
						$template->setVariable("ALT_RADIO", $this->lng->txt("unchecked"));
						$template->setVariable("TITLE_RADIO", $this->lng->txt("unchecked"));
						$template->parseCurrentBlock();
					}
					for ($i = 0; $i < $this->object->categories->getCategoryCount(); $i++) 
					{
						$category = $this->object->categories->getCategory($i);
						$template->setCurrentBlock("text_col_nominal");
						$template->setVariable("TEXT_NOMINAL", $category);
						$template->parseCurrentBlock();
					}
				}
				else
				{
					for ($i = 0; $i < $this->object->categories->getCategoryCount(); $i++) 
					{
						$category = $this->object->categories->getCategory($i);
						$template->setCurrentBlock("checkbox_col_nominal");
						$template->setVariable("IMAGE_CHECKBOX", ilUtil::getHtmlPath(ilUtil::getImagePath("checkbox_unchecked.gif")));
						$template->setVariable("ALT_CHECKBOX", $this->lng->txt("unchecked"));
						$template->setVariable("TITLE_CHECKBOX", $this->lng->txt("unchecked"));
						$template->parseCurrentBlock();
					}
					for ($i = 0; $i < $this->object->categories->getCategoryCount(); $i++) 
					{
						$category = $this->object->categories->getCategory($i);
						$template->setCurrentBlock("text_col_nominal_mr");
						$template->setVariable("TEXT_NOMINAL", $category);
						$template->parseCurrentBlock();
					}
				}
				break;
			case 2:
				// combobox output
				for ($i = 0; $i < $this->object->categories->getCategoryCount(); $i++) 
				{
					$category = $this->object->categories->getCategory($i);
					$template->setCurrentBlock("comborow_nominal");
					$template->setVariable("TEXT_NOMINAL", $category);
					$template->setVariable("VALUE_NOMINAL", $i);
					$template->parseCurrentBlock();
				}
				$template->setCurrentBlock("combooutput_nominal");
				$template->setVariable("QUESTION_ID", $this->object->getId());
				$template->setVariable("SELECT_OPTION", $this->lng->txt("select_option"));
				$template->setVariable("TEXT_SELECTION", $this->lng->txt("selection"));
				$template->parseCurrentBlock();
				break;
		}
		
		if ($show_questiontext)
		{
			$questiontext = $this->object->getQuestiontext();
			$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($questiontext, TRUE));
		}
		if (! $this->object->getObligatory())
		{
			$template->setVariable("OBLIGATORY_TEXT", $this->lng->txt("survey_question_optional"));
		}
		if ($question_title)
		{
			$template->setVariable("QUESTION_TITLE", $this->object->getTitle());
		}
		$template->parseCurrentBlock();
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
		include_once "./Services/Utilities/classes/class.ilUtil.php";
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
			ilUtil::sendInfo($this->lng->txt("fill_out_all_required_fields_add_category"), true);
			$this->ctrl->redirect($this, "editQuestion");
		}
		if (strcmp($this->ctrl->getCmd(), "categories") == 0) $_SESSION["spl_modified"] = false;
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_qpl_nominal_answers.html", "Modules/SurveyQuestionPool");
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
		
		include_once "./Services/Utilities/classes/class.ilUtil.php";
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
		$questiontext = $this->object->getQuestiontext();
		$this->tpl->setVariable("QUESTION_TEXT", $this->object->prepareTextareaOutput($questiontext, TRUE));
		$this->tpl->parseCurrentBlock();
	}
	
	function setQuestionTabs()
	{
		$this->setQuestionTabsForClass("surveynominalquestiongui");
	}

/**
* Saves the categories
*
* Saves the categories
*
* @access private
*/
	function saveCategories()
	{
		global $ilUser;
		
		$this->writeCategoryData(true);
		$_SESSION["spl_modified"] = false;
		ilUtil::sendInfo($this->lng->txt("saved_successfully"), true);
		$originalexists = $this->object->_questionExists($this->object->original_id);
		$this->ctrl->setParameter($this, "q_id", $this->object->getId());
		include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestion.php";
		if ($_GET["calling_survey"] && $originalexists && SurveyQuestion::_isWriteable($this->object->original_id, $ilUser->getId()))
		{
			$this->originalSyncForm();
			return;
		}
		else
		{
			$this->ctrl->redirect($this, "categories");
		}
	}

/**
* Adds a category to the question
*
* Adds a category to the question
*
* @access private
*/
	function addCategory()
	{
		$result = $this->writeCategoryData();
		if ($result == false)
		{
			ilUtil::sendInfo($this->lng->txt("fill_out_all_category_fields"));
		}
		$_SESSION["spl_modified"] = true;
		$this->categories($result);
	}
	
/**
* Recreates the categories from the POST data
*
* Recreates the categories from the POST data and
* saves it (optionally) to the database.
*
* @param boolean $save If set to true the POST data will be saved to the database
* @access private
*/
	function writeCategoryData($save = false)
	{
    // Delete all existing categories and create new categories from the form data
    $this->object->categories->flushCategories();
		$complete = true;
		$array1 = array();
    // Add all categories from the form into the object
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		foreach ($_POST as $key => $value) 
		{
			if (preg_match("/^category_(\d+)/", $key, $matches)) 
			{
				$array1[$matches[1]] = ilUtil::stripSlashes($value);
				if (strlen($array1[$matches[1]]) == 0) $complete = false;
			}
		}
		$this->object->categories->addCategoryArray($array1);
		if ($save)
		{	
			$this->object->saveCategoriesToDb();
		}
		return $complete;
	}
	
/**
* Removes one or more categories
*
* Removes one or more categories
*
* @access private
*/
	function deleteCategory()
	{
		$this->writeCategoryData();
		$nothing_selected = true;
		if (array_key_exists("chb_category", $_POST))
		{
			if (count($_POST["chb_category"]))
			{
				$nothing_selected = false;
				$this->object->categories->removeCategories($_POST["chb_category"]);
			}
		}
		if ($nothing_selected) ilUtil::sendInfo($this->lng->txt("category_delete_select_none"));
		$_SESSION["spl_modified"] = true;
		$this->categories();
	}
	
/**
* Selects one or more categories for moving
*
* Selects one or more categories for moving
*
* @access private
*/
	function moveCategory()
	{
		$this->writeCategoryData();
		$nothing_selected = true;
		if (array_key_exists("chb_category", $_POST))
		{
			if (count($_POST["chb_category"]))
			{
				$nothing_selected = false;
				ilUtil::sendInfo($this->lng->txt("select_target_position_for_move"));
				$_SESSION["spl_move"] = $_POST["chb_category"];
			}
		}
		if ($nothing_selected) ilUtil::sendInfo($this->lng->txt("no_category_selected_for_move"));
		$this->categories();
	}
	
/**
* Inserts categories which are selected for moving before the selected category
*
* Inserts categories which are selected for moving before the selected category
*
* @access private
*/
	function insertBeforeCategory()
	{
		$result = $this->writeCategoryData();
		if (array_key_exists("chb_category", $_POST))
		{
			if (count($_POST["chb_category"]) == 1)
			{
				// one entry is selected, moving is allowed
				$this->object->categories->removeCategories($_SESSION["spl_move"]);
				$newinsertindex = $this->object->categories->getCategoryIndex($_POST["category_".$_POST["chb_category"][0]]);
				if ($newinsertindex === false) $newinsertindex = 0;
				$move_categories = $_SESSION["spl_move"];
				natsort($move_categories);
				foreach (array_reverse($move_categories) as $index)
				{
					$this->object->categories->addCategoryAtPosition($_POST["category_$index"], $newinsertindex);
				}
				$_SESSION["spl_modified"] = true;
				unset($_SESSION["spl_move"]);
			}
			else
			{
				ilUtil::sendInfo("wrong_categories_selected_for_insert");
			}
		}
		$this->categories();
	}
	
/**
* Inserts categories which are selected for moving before the selected category
*
* Inserts categories which are selected for moving before the selected category
*
* @access private
*/
	function insertAfterCategory()
	{
		$result = $this->writeCategoryData();
		if (array_key_exists("chb_category", $_POST))
		{
			if (count($_POST["chb_category"]) == 1)
			{
				// one entry is selected, moving is allowed
				$this->object->categories->removeCategories($_SESSION["spl_move"]);
				$newinsertindex = $this->object->categories->getCategoryIndex($_POST["category_".$_POST["chb_category"][0]]);
				if ($newinsertindex === false) $newinsertindex = 0;
				$move_categories = $_SESSION["spl_move"];
				natsort($move_categories);
				foreach (array_reverse($move_categories) as $index)
				{
					$this->object->categories->addCategoryAtPosition($_POST["category_$index"], $newinsertindex+1);
				}
				$_SESSION["spl_modified"] = true;
				unset($_SESSION["spl_move"]);
			}
			else
			{
				ilUtil::sendInfo("wrong_categories_selected_for_insert");
			}
		}
		$this->categories();
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
		$template->setVariable("TEXT_OPTION", $this->lng->txt("subtype"));
		switch ($this->object->getSubType())
		{
			case SUBTYPE_MCSR:
				$template->setVariable("TEXT_OPTION_VALUE", $this->lng->txt("multiple_choice_single_response"));
				break;
			case SUBTYPE_MCMR:
				$template->setVariable("TEXT_OPTION_VALUE", $this->lng->txt("multiple_choice_multiple_response"));
				break;
		}
		$template->parseCurrentBlock();

		$template->setCurrentBlock("detail_row");
		$template->setVariable("TEXT_OPTION", $this->lng->txt("mode"));
		$template->setVariable("TEXT_OPTION_VALUE", $this->cumulated["MODE"]);
		$template->parseCurrentBlock();
		$template->setCurrentBlock("detail_row");
		$template->setVariable("TEXT_OPTION", $this->lng->txt("mode_nr_of_selections"));
		$template->setVariable("TEXT_OPTION_VALUE", $this->cumulated["MODE_NR_OF_SELECTIONS"]);
		$template->parseCurrentBlock();
		
		$template->setCurrentBlock("detail_row");
		$template->setVariable("TEXT_OPTION", $this->lng->txt("categories"));
		$categories = "";
		if (is_array($this->cumulated["variables"]))
		{
			foreach ($this->cumulated["variables"] as $key => $value)
			{
				$categories .= "<li>" . $this->lng->txt("title") . ":" . "<span class=\"bold\">" . $value["title"] . "</span><br />" .
					$this->lng->txt("category_nr_selected") . ": " . "<span class=\"bold\">" . $value["selected"] . "</span><br />" .
					$this->lng->txt("percentage_of_selections") . ": " . "<span class=\"bold\">" . sprintf("%.2f", 100*$value["percentage"]) . "</span></li>";
			}
		}
		$categories = "<ol>$categories</ol>";
		$template->setVariable("TEXT_OPTION_VALUE", $categories);
		$template->parseCurrentBlock();
		
		// display chart for nominal question for array $eval["variables"]
		$template->setCurrentBlock("chart");
		$template->setVariable("TEXT_CHART", $this->lng->txt("chart"));
		$template->setVariable("ALT_CHART", $data["title"] . "( " . $this->lng->txt("chart") . ")");
		$this->ctrl->setParameterByClass("ilsurveyevaluationgui", "survey", $survey_id);
		$this->ctrl->setParameterByClass("ilsurveyevaluationgui", "question", $this->object->getId());
		$template->setVariable("CHART", $this->ctrl->getLinkTargetByClass("ilsurveyevaluationgui", "outChart"));
		$template->parseCurrentBlock();
		
		$template->setVariable("QUESTION_TITLE", "$counter. ".$this->object->getTitle());
		return $template->get();
	}

		
}
?>

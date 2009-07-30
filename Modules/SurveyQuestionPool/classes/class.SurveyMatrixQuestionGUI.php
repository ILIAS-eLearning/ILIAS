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
* Matrix question GUI representation
*
* The SurveyMatrixQuestionGUI class encapsulates the GUI representation
* for matrix question types.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @extends SurveyQuestionGUI
* @ingroup ModulesSurveyQuestionPool
*/
class SurveyMatrixQuestionGUI extends SurveyQuestionGUI 
{
	var $show_layout_row;
	
/**
* SurveyMatrixQuestionGUI constructor
*
* The constructor takes possible arguments an creates an instance of the SurveyMatrixQuestionGUI object.
*
* @param integer $id The database id of a matrix question object
* @access public
*/
  function SurveyMatrixQuestionGUI(
		$id = -1
  )

  {
		$this->SurveyQuestionGUI();
		include_once "./Modules/SurveyQuestionPool/classes/class.SurveyMatrixQuestion.php";
		$this->object = new SurveyMatrixQuestion();
		$this->show_layout_row = FALSE;
		if ($id >= 0)
		{
			$this->object->loadFromDb($id);
		}
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		$cmd = $this->ctrl->getCmd();
		$next_class = $this->ctrl->getNextClass($this);

		$cmd = $this->getCommand($cmd);
		switch($next_class)
		{
			default:
				$ret =& $this->$cmd();
				break;
		}
		return $ret;
	}

	/**
	* Evaluates a posted edit form and writes the form data in the question object
	*
	* @return integer A positive value, if one of the required fields wasn't set, else 0
	* @access private
	*/
	function writePostData($always = false)
	{
		$hasErrors = (!$always) ? $this->editQuestion(true) : false;
		if (!$hasErrors)
		{
			$this->object->setTitle(ilUtil::stripSlashes($_POST["title"]));
			$this->object->setAuthor(ilUtil::stripSlashes($_POST["author"]));
			$this->object->setDescription(ilUtil::stripSlashes($_POST["description"]));
			include_once "./Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php";
			$questiontext = ilUtil::stripSlashes($_POST["question"], false, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("survey"));
			$this->object->setQuestiontext($questiontext);
			$this->object->setObligatory(($_POST["obligatory"]) ? 1 : 0);
			$this->object->setSubtype($_POST["type"]);
			$this->object->setRowSeparators(($_POST["row_separators"]) ? 1 : 0);
			$this->object->setColumnSeparators(($_POST["column_separators"]) ? 1 : 0);
			$this->object->setNeutralColumnSeparator(($_POST["neutral_column_separator"]) ? 1 : 0);
			// Set bipolar adjectives
			$this->object->setBipolarAdjective(0, ilUtil::stripSlashes($_POST["bipolar1"]));
			$this->object->setBipolarAdjective(1, ilUtil::stripSlashes($_POST["bipolar2"]));
			// set columns
			$this->object->flushColumns();
			foreach ($_POST['columns']['answer'] as $key => $value)
			{
				if (strlen($value)) $this->object->addColumn(ilUtil::stripSlashes($value));
			}
			// Set neutral column
			$this->object->setNeutralColumn(ilUtil::stripSlashes($_POST["columns_neutral"]));
			// set rows
			$this->object->flushRows();
			foreach ($_POST['rows'] as $key => $value)
			{
				$this->object->addRow(ilUtil::stripSlashes($value));
			}
			return 0;
		}
		else
		{
			return 1;
		}
	}

	/**
	* Creates an output of the edit form for the question
	*
	* @access public
	*/
	public function editQuestion($checkonly = FALSE)
	{
		$save = ((strcmp($this->ctrl->getCmd(), "save") == 0) || 
			(strcmp($this->ctrl->getCmd(), "wizardcolumns") == 0) ||
			(strcmp($this->ctrl->getCmd(), "savePhrasecolumns") == 0)
		) ? TRUE : FALSE;

		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->lng->txt($this->getQuestionType()));
		$form->setMultipart(FALSE);
		$form->setTableWidth("100%");
		$form->setId("multiplechoice");

		// title
		$title = new ilTextInputGUI($this->lng->txt("title"), "title");
		$title->setValue($this->object->getTitle());
		$title->setRequired(TRUE);
		$form->addItem($title);
		
		// author
		$author = new ilTextInputGUI($this->lng->txt("author"), "author");
		$author->setValue($this->object->getAuthor());
		$author->setRequired(TRUE);
		$form->addItem($author);
		
		// description
		$description = new ilTextInputGUI($this->lng->txt("description"), "description");
		$description->setValue($this->object->getDescription());
		$description->setRequired(FALSE);
		$form->addItem($description);
		
		// questiontext
		$question = new ilTextAreaInputGUI($this->lng->txt("question"), "question");
		$question->setValue($this->object->prepareTextareaOutput($this->object->getQuestiontext()));
		$question->setRequired(TRUE);
		$question->setRows(10);
		$question->setCols(80);
		$question->setUseRte(TRUE);
		$question->addPlugin("latex");
		$question->removePlugin("ibrowser");
		$question->addButton("latex");
		$question->addButton("pastelatex");
		$question->setRTESupport($this->object->getId(), "spl", "survey");
		$form->addItem($question);
		
		// subtype
		$subtype = new ilRadioGroupInputGUI($this->lng->txt("subtype"), "type");
		$subtype->setRequired(false);
		$subtype->setValue($this->object->getSubtype());
		$subtypes = array(
			"0" => "matrix_subtype_sr",
			"1" => "matrix_subtype_mr",
			//"2" => "matrix_subtype_text",
			//"3" => "matrix_subtype_integer",
			//"4" => "matrix_subtype_double",
			//"5" => "matrix_subtype_date",
			//"6" => "matrix_subtype_time"
		);
		foreach ($subtypes as $idx => $st)
		{
			$subtype->addOption(new ilRadioOption($this->lng->txt($st), $idx));
		}
		$form->addItem($subtype);

		// obligatory
		$shuffle = new ilCheckboxInputGUI($this->lng->txt("obligatory"), "obligatory");
		$shuffle->setValue(1);
		$shuffle->setChecked($this->object->getObligatory());
		$shuffle->setRequired(FALSE);
		$form->addItem($shuffle);

		$header = new ilFormSectionHeaderGUI();
		$header->setTitle($this->lng->txt("matrix_appearance"));
		$form->addItem($header);
		
		// column separators
		$column_separators = new ilCheckboxInputGUI($this->lng->txt("matrix_column_separators"), "column_separators");
		$column_separators->setValue(1);
		$column_separators->setInfo($this->lng->txt("matrix_column_separators_description"));
		$column_separators->setChecked($this->object->getColumnSeparators());
		$column_separators->setRequired(false);
		$form->addItem($column_separators);

		// row separators
		$row_separators = new ilCheckboxInputGUI($this->lng->txt("matrix_row_separators"), "row_separators");
		$row_separators->setValue(1);
		$row_separators->setInfo($this->lng->txt("matrix_row_separators_description"));
		$row_separators->setChecked($this->object->getRowSeparators());
		$row_separators->setRequired(false);
		$form->addItem($row_separators);

		// neutral column separators
		$neutral_column_separator = new ilCheckboxInputGUI($this->lng->txt("matrix_neutral_column_separator"), "neutral_column_separator");
		$neutral_column_separator->setValue(1);
		$neutral_column_separator->setInfo($this->lng->txt("matrix_neutral_column_separator_description"));
		$neutral_column_separator->setChecked($this->object->getNeutralColumnSeparator());
		$neutral_column_separator->setRequired(false);
		$form->addItem($neutral_column_separator);

		$header = new ilFormSectionHeaderGUI();
		$header->setTitle($this->lng->txt("matrix_columns"));
		$form->addItem($header);
		
		// Answers
		include_once "./Modules/SurveyQuestionPool/classes/class.ilCategoryWizardInputGUI.php";
		$columns = new ilCategoryWizardInputGUI("", "columns");
		$columns->setRequired(false);
		$columns->setAllowMove(true);
		$columns->setShowWizard(true);
		$columns->setShowNeutralCategory(true);
		$columns->setNeutralCategory($this->object->getNeutralColumn());
		$columns->setNeutralCategoryTitle($this->lng->txt('matrix_neutral_answer'));
		$columns->setCategoryText($this->lng->txt('matrix_standard_answers'));
		$columns->setShowSavePhrase(true);
		if (!$this->object->getColumnCount())
		{
			$this->object->addColumn("");
		}
		$columns->setValues($this->object->getColumns());
		$form->addItem($columns);
		
		$header = new ilFormSectionHeaderGUI();
		$header->setTitle($this->lng->txt("matrix_column_settings"));
		$form->addItem($header);
		
		// bipolar adjectives
		$bipolar = new ilCustomInputGUI($this->lng->txt("matrix_bipolar_adjectives"));
		$bipolar->setInfo($this->lng->txt("matrix_bipolar_adjectives_description"));
		
		// left pole
		$bipolar1 = new ilTextInputGUI($this->lng->txt("matrix_left_pole"), "bipolar1");
		$bipolar1->setValue($this->object->getBipolarAdjective(0));
		$bipolar1->setRequired(false);
		$bipolar->addSubItem($bipolar1);
		
		// right pole
		$bipolar2 = new ilTextInputGUI($this->lng->txt("matrix_right_pole"), "bipolar2");
		$bipolar2->setValue($this->object->getBipolarAdjective(1));
		$bipolar2->setRequired(false);
		$bipolar->addSubItem($bipolar2);

		$form->addItem($bipolar);

		$header = new ilFormSectionHeaderGUI();
		$header->setTitle($this->lng->txt("matrix_rows"));
		$form->addItem($header);

		// matrix rows
		$rows = new ilTextWizardInputGUI($this->lng->txt('row_text'), "rows");
		$rows->setRequired(true);
		$rows->setAllowMove(true);
		if (count($this->object->getRows()) == 0)
		{
			$this->object->addRow("");
		}
		$rows->setValues($this->object->getRows());
		$form->addItem($rows);

		$form->addCommandButton("save", $this->lng->txt("save"));
	
		$errors = false;
	
		if ($save)
		{
			$form->setValuesByPost();
			$errors = !$form->checkInput();
			if ($errors) $checkonly = false;
		}

		if (!$checkonly) $this->tpl->setVariable("ADM_CONTENT", $form->getHTML());
		return $errors;
	}

	/**
	* Add a new row
	*/
	public function addrows()
	{
		$this->writePostData(true);
		$position = key($_POST['cmd']['addrows']);
		$this->object->addRowAtPosition("", $position+1);
		$this->editQuestion();
	}

	/**
	* Remove a row
	*/
	public function removerows()
	{
		$this->writePostData(true);
		$position = key($_POST['cmd']['removerows']);
		$this->object->removeRow($position);
		$this->editQuestion();
	}

	/**
	* Move a row up
	*/
	public function uprows()
	{
		$this->writePostData(true);
		$position = key($_POST['cmd']['uprows']);
		$this->object->moveRowUp($position);
		$this->editQuestion();
	}

	/**
	* Move a row down
	*/
	public function downrows()
	{
		$this->writePostData(true);
		$position = key($_POST['cmd']['downrows']);
		$this->object->moveRowDown($position);
		$this->editQuestion();
	}

	/**
	* Add a new column
	*/
	public function addcolumns()
	{
		$this->writePostData(true);
		$position = key($_POST['cmd']['addcolumns']);
		$this->object->getColumns()->addCategoryAtPosition("", $position+1);
		$this->editQuestion();
	}

	/**
	* Remove a column
	*/
	public function removecolumns()
	{
		$this->writePostData(true);
		$position = key($_POST['cmd']['removecolumns']);
		$this->object->getColumns()->removeCategory($position);
		$this->editQuestion();
	}

	/**
	* Move a column up
	*/
	public function upcolumns()
	{
		$this->writePostData(true);
		$position = key($_POST['cmd']['upcolumns']);
		$this->object->getColumns()->moveCategoryUp($position);
		$this->editQuestion();
	}

	/**
	* Move a column down
	*/
	public function downcolumns()
	{
		$this->writePostData(true);
		$position = key($_POST['cmd']['downcolumns']);
		$this->object->getColumns()->moveCategoryDown($position);
		$this->editQuestion();
	}

	/**
	* Creates an output to save the current answers as a phrase
	*
	* @access public
	*/
	function savePhrasecolumns($haserror = false) 
	{
		if (!$haserror) $result = $this->writePostData();
		if ($result == 0 || $haserror)
		{
			if (!$haserror) $this->object->saveToDb();
			$nothing_selected = true;
			$nothing_selected = false;
			$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_qpl_savephrase.html", "Modules/SurveyQuestionPool");
			$rowclass = array("tblrow1", "tblrow2");
			$counter = 0;
			foreach ($_POST['columns']['answer'] as $key => $value) 
			{
				if (strlen($value))
				{
					$this->tpl->setCurrentBlock("row");
					$this->tpl->setVariable("TXT_TITLE", ilUtil::stripSlashes($value));
					$this->tpl->setVariable("COLOR_CLASS", $rowclass[$counter % 2]);
					$this->tpl->parseCurrentBlock();
					$this->tpl->setCurrentBlock("hidden");
					$this->tpl->setVariable("HIDDEN_NAME", "columns[answer][]");
					$this->tpl->setVariable("HIDDEN_VALUE", ilUtil::stripSlashes($value));
					$this->tpl->parseCurrentBlock();
					$counter++;
				}
			}
			if ($counter == 0)
			{
				ilUtil::sendFailure($this->lng->txt("check_category_to_save_phrase"), true);
				$this->ctrl->redirect($this, "editQuestion");
			}
			$this->tpl->setCurrentBlock("adm_content");
			$this->tpl->setVariable("SAVE_PHRASE_INTRODUCTION", $this->lng->txt("save_phrase_introduction"));
			$this->tpl->setVariable("TEXT_PHRASE_TITLE", $this->lng->txt("enter_phrase_title"));
			$this->tpl->setVariable("TXT_TITLE", $this->lng->txt("category"));
			$this->tpl->setVariable("VALUE_PHRASE_TITLE", $_POST["phrase_title"]);
			$this->tpl->setVariable("BTN_CANCEL",$this->lng->txt("cancel"));
			$this->tpl->setVariable("BTN_CONFIRM",$this->lng->txt("confirm"));
			$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
			$this->tpl->parseCurrentBlock();
		}
	}

	/**
	* Cancels the form saving a phrase
	*
	* @access public
	*/
	function cancelSavePhrase() 
	{
		ilUtil::sendInfo($this->lng->txt("msg_cancel"), true);
		$this->ctrl->redirect($this, "editQuestion");
	}

	/**
	* Save a new phrase to the database
	*
	* @access public
	*/
	function confirmSavePhrase() 
	{
		if (!$_POST["phrase_title"])
		{
			ilUtil::sendInfo($this->lng->txt("qpl_savephrase_empty"));
			$this->savePhrasecolumns(true);
			return;
		}

		if ($this->object->phraseExists($_POST["phrase_title"]))
		{
			ilUtil::sendInfo($this->lng->txt("qpl_savephrase_exists"));
			$this->savePhrasecolumns(true);
			return;
		}

		$this->object->savePhrase($_POST['columns']['answer'], $_POST["phrase_title"]);
		ilUtil::sendSuccess($this->lng->txt("phrase_saved"), true);
		$this->ctrl->redirect($this, "editQuestion");
	}

	/**
	* Creates an output for the addition of phrases
	*
	* @access public
	*/
  function wizardcolumns($save_post_data = true) 
	{
		if ($save_post_data) $result = $this->writePostData();
		if ($result == 0)
		{
			if ($save_post_data) $this->object->saveToDb();
			$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_qpl_addphrase.html", "Modules/SurveyQuestionPool");

			// set the id to return to the selected question
			$this->tpl->setCurrentBlock("hidden");
			$this->tpl->setVariable("HIDDEN_NAME", "id");
			$this->tpl->setVariable("HIDDEN_VALUE", $this->object->getId());
			$this->tpl->parseCurrentBlock();

			include_once "./Modules/SurveyQuestionPool/classes/class.ilSurveyPhrases.php";
			$phrases =& ilSurveyPhrases::_getAvailablePhrases();
			$colors = array("tblrow1", "tblrow2");
			$counter = 0;
			foreach ($phrases as $phrase_id => $phrase_array)
			{
				$this->tpl->setCurrentBlock("phraserow");
				$this->tpl->setVariable("COLOR_CLASS", $colors[$counter++ % 2]);
				$this->tpl->setVariable("PHRASE_VALUE", $phrase_id);
				$this->tpl->setVariable("PHRASE_NAME", $phrase_array["title"]);
				$categories =& ilSurveyPhrases::_getCategoriesForPhrase($phrase_id);
				$this->tpl->setVariable("PHRASE_CONTENT", join($categories, ","));
				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setCurrentBlock("adm_content");
			$this->tpl->setVariable("TEXT_CANCEL", $this->lng->txt("cancel"));
			$this->tpl->setVariable("TEXT_PHRASE", $this->lng->txt("phrase"));
			$this->tpl->setVariable("TEXT_CONTENT", $this->lng->txt("categories"));
			$this->tpl->setVariable("TEXT_ADD_PHRASE", $this->lng->txt("add_phrase"));
			$this->tpl->setVariable("TEXT_INTRODUCTION",$this->lng->txt("add_phrase_introduction"));
			$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
			$this->tpl->parseCurrentBlock();
		}
	}

	/**
	* Cancels the form adding a phrase
	*
	* @access public
	*/
	function cancelViewPhrase() 
	{
		ilUtil::sendInfo($this->lng->txt('msg_cancel'), true);
		$this->ctrl->redirect($this, 'editQuestion');
	}

	/**
	* Adds a selected phrase
	*
	* @access public
	*/
	function addSelectedPhrase() 
	{
		if (strcmp($_POST["phrases"], "") == 0)
		{
			ilUtil::sendInfo($this->lng->txt("select_phrase_to_add"));
			$this->wizardcolumns(false);
		}
		else
		{
			if (strcmp($this->object->getPhrase($_POST["phrases"]), "dp_standard_numbers") != 0)
			{
				$this->object->addPhrase($_POST["phrases"]);
				$this->object->saveToDb();
			}
			else
			{
				$this->addStandardNumbers();
				return;
			}
			ilUtil::sendSuccess($this->lng->txt('phrase_added'), true);
			$this->ctrl->redirect($this, 'editQuestion');
		}
	}

	/**
	* Creates an output for the addition of standard numbers
	*
	* @access public
	*/
	  function addStandardNumbers() 
		{
			$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_qpl_addphrase_standard_numbers.html", "Modules/SurveyQuestionPool");

			// set the id to return to the selected question
			$this->tpl->setCurrentBlock("hidden");
			$this->tpl->setVariable("HIDDEN_NAME", "id");
			$this->tpl->setVariable("HIDDEN_VALUE", $this->object->getId());
			$this->tpl->parseCurrentBlock();

			$this->tpl->setCurrentBlock("adm_content");
			$this->tpl->setVariable("ADD_STANDARD_NUMBERS", $this->lng->txt("add_standard_numbers"));
			$this->tpl->setVariable("TEXT_ADD_LIMITS", $this->lng->txt("add_limits_for_standard_numbers"));
			$this->tpl->setVariable("TEXT_LOWER_LIMIT",$this->lng->txt("lower_limit"));
			$this->tpl->setVariable("TEXT_UPPER_LIMIT",$this->lng->txt("upper_limit"));
			$this->tpl->setVariable("VALUE_LOWER_LIMIT", $_POST["lower_limit"]);
			$this->tpl->setVariable("VALUE_UPPER_LIMIT", $_POST["upper_limit"]);
			$this->tpl->setVariable("BTN_ADD",$this->lng->txt("add_phrase"));
			$this->tpl->setVariable("BTN_CANCEL",$this->lng->txt("cancel"));
			$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
			$this->tpl->parseCurrentBlock();
		}

	/**
	* Cancels the form adding standard numbers
	*
	* @access public
	*/
	function cancelStandardNumbers() 
	{
		ilUtil::sendInfo($this->lng->txt("msg_cancel"), true);
		$this->ctrl->redirect($this, "editQuestion");
	}

	/**
	* Insert standard numbers to the question
	*
	* @access public
	*/
	function insertStandardNumbers() 
	{
		if ((strcmp($_POST["lower_limit"], "") == 0) or (strcmp($_POST["upper_limit"], "") == 0))
		{
			ilUtil::sendInfo($this->lng->txt("missing_upper_or_lower_limit"));
			$this->addStandardNumbers();
		}
		else if ((int)$_POST["upper_limit"] <= (int)$_POST["lower_limit"])
		{
			ilUtil::sendInfo($this->lng->txt("upper_limit_must_be_greater"));
			$this->addStandardNumbers();
		}
		else
		{
			$this->object->addStandardNumbers($_POST["lower_limit"], $_POST["upper_limit"]);
			$this->object->saveToDb();
			ilUtil::sendSuccess($this->lng->txt('phrase_added'), true);
			$this->ctrl->redirect($this, "editQuestion");
		}
	}

/**
* Creates the question output form for the learner
*
* @access public
*/
	function getWorkingForm($working_data = "", $question_title = 1, $show_questiontext = 1, $error_message = "")
	{
		$layout = $this->object->getLayout();
		$neutralstyle = "3px solid #808080";
		$bordercolor = "#808080";
		$template = new ilTemplate("tpl.il_svy_out_matrix.html", TRUE, TRUE, "Modules/SurveyQuestionPool");
		$template->setCurrentBlock("material_matrix");
		$template->setVariable("TEXT_MATERIAL", $this->getMaterialOutput());
		$template->parseCurrentBlock();
		
		if ($this->show_layout_row)
		{
			$layout_row = $this->getLayoutRow();
			$template->setCurrentBlock("matrix_row");
			$template->setVariable("ROW", $layout_row);
			$template->parseCurrentBlock();
		}
		
		$tplheaders = new ilTemplate("tpl.il_svy_out_matrix_columnheaders.html", TRUE, TRUE, "Modules/SurveyQuestionPool");
		if ((strlen($this->object->getBipolarAdjective(0))) && (strlen($this->object->getBipolarAdjective(1))))
		{
			$tplheaders->setCurrentBlock("bipolar_start");
			$style = array();
			array_push($style, sprintf("width: %.2f%s!important", $layout["percent_bipolar_adjective1"], "%"));
			if (count($style) > 0)
			{
				$tplheaders->setVariable("STYLE", " style=\"" . implode(";", $style) . "\"");
			}
			$tplheaders->parseCurrentBlock();
		}
		// column headers
		for ($i = 0; $i < $this->object->getColumnCount(); $i++)
		{
			$style = array();
			if ($this->object->getColumnSeparators() == 1)
			{
				if (($i < $this->object->getColumnCount() - 1))
				{
					array_push($style, "border-right: 1px solid $bordercolor!important");
				}
			}
			array_push($style, sprintf("width: %.2f%s!important", $layout["percent_columns"] / $this->object->getColumnCount(), "%"));
			$tplheaders->setCurrentBlock("column_header");
			$tplheaders->setVariable("TEXT", ilUtil::prepareFormOutput($this->object->getColumn($i)));
			$tplheaders->setVariable("CLASS", "center");
			if (count($style) > 0)
			{
				$tplheaders->setVariable("STYLE", " style=\"" . implode(";", $style) . "\"");
			}
			$tplheaders->parseCurrentBlock();
		}
		if (strlen($this->object->getNeutralColumn()))
		{
			$tplheaders->setCurrentBlock("neutral_column_header");
			$tplheaders->setVariable("TEXT", ilUtil::prepareFormOutput($this->object->getNeutralColumn()));
			$tplheaders->setVariable("CLASS", "rsep");
			$style = array();
			array_push($style, sprintf("width: %.2f%s!important", $layout["percent_neutral"], "%"));
			if ($this->object->getNeutralColumnSeparator())
			{
				array_push($style, "border-left: $neutralstyle!important;");
			}
			if (count($style) > 0)
			{
				$tplheaders->setVariable("STYLE", " style=\"" . implode(";", $style) . "\"");
			}
			$tplheaders->parseCurrentBlock();
		}
		if ((strlen($this->object->getBipolarAdjective(0))) && (strlen($this->object->getBipolarAdjective(1))))
		{
			$tplheaders->setCurrentBlock("bipolar_end");
			$style = array();
			array_push($style, sprintf("width: %.2f%s!important", $layout["percent_bipolar_adjective2"], "%"));
			if (count($style) > 0)
			{
				$tplheaders->setVariable("STYLE", " style=\"" . implode(";", $style) . "\"");
			}
			$tplheaders->parseCurrentBlock();
		}

		$style = array();
		array_push($style, sprintf("width: %.2f%s!important", $layout["percent_row"], "%"));
		if (count($style) > 0)
		{
			$tplheaders->setVariable("STYLE", " style=\"" . implode(";", $style) . "\"");
		}
		
		$template->setCurrentBlock("matrix_row");
		$template->setVariable("ROW", $tplheaders->get());
		$template->parseCurrentBlock();

		$rowclass = array("tblrow1", "tblrow2");
		for ($i = 0; $i < $this->object->getRowCount(); $i++)
		{
			$tplrow = new ilTemplate("tpl.il_svy_out_matrix_row.html", TRUE, TRUE, "Modules/SurveyQuestionPool");
			for ($j = 0; $j < $this->object->getColumnCount(); $j++)
			{
				if (($i == 0) && ($j == 0))
				{
					if ((strlen($this->object->getBipolarAdjective(0))) && (strlen($this->object->getBipolarAdjective(1))))
					{
						$tplrow->setCurrentBlock("bipolar_start");
						$tplrow->setVariable("TEXT_BIPOLAR_START", ilUtil::prepareFormOutput($this->object->getBipolarAdjective(0)));
						$tplrow->setVariable("ROWSPAN", $this->object->getRowCount());
						$tplrow->parseCurrentBlock();
					}
				}
				if (($i == 0) && ($j == $this->object->getColumnCount()-1))
				{
					if ((strlen($this->object->getBipolarAdjective(0))) && (strlen($this->object->getBipolarAdjective(1))))
					{
						$tplrow->setCurrentBlock("bipolar_end");
						$tplrow->setVariable("TEXT_BIPOLAR_END", ilUtil::prepareFormOutput($this->object->getBipolarAdjective(1)));
						$tplrow->setVariable("ROWSPAN", $this->object->getRowCount());
						$tplrow->parseCurrentBlock();
					}
				}
				switch ($this->object->getSubtype())
				{
					case 0:
						$tplrow->setCurrentBlock("radiobutton");
						$tplrow->setVariable("QUESTION_ID", $this->object->getId());
						$tplrow->setVariable("ROW", $i);
						$tplrow->setVariable("VALUE", $j);
						if (is_array($working_data))
						{
							foreach ($working_data as $data)
							{
								if (($data["value"] == $j) && ($data["rowvalue"] == $i))
								{
									$tplrow->setVariable("CHECKED_RADIOBUTTON", " checked=\"checked\"");
								}
							}
						}
						$tplrow->parseCurrentBlock();
						break;
					case 1:
						$tplrow->setCurrentBlock("checkbox");
						$tplrow->setVariable("QUESTION_ID", $this->object->getId());
						$tplrow->setVariable("ROW", $i);
						$tplrow->setVariable("VALUE", $j);
						if (is_array($working_data))
						{
							foreach ($working_data as $data)
							{
								if (($data["value"] == $j) && ($data["rowvalue"] == $i))
								{
									$tplrow->setVariable("CHECKED_CHECKBOX", " checked=\"checked\"");
								}
							}
						}
						$tplrow->parseCurrentBlock();
						break;
				}
				$tplrow->setCurrentBlock("answer");
				$style = array();
				
				if ($this->object->getColumnSeparators() == 1)
				{
					if ($j < $this->object->getColumnCount() - 1)
					{
						array_push($style, "border-right: 1px solid $bordercolor!important");
					}
				}

				if ($this->object->getRowSeparators() == 1)
				{
					if ($i < $this->object->getRowCount() - 1)
					{
						array_push($style, "border-bottom: 1px solid $bordercolor!important");
					}
				}
				if (count($style))
				{
					$tplrow->setVariable("STYLE", " style=\"" . implode(";", $style) . "\"");
				}
				$tplrow->parseCurrentBlock();

			}
			
			if (strlen($this->object->getNeutralColumn()))
			{
				$j = $this->object->getNeutralColumnIndex();
				switch ($this->object->getSubtype())
				{
					case 0:
						$tplrow->setCurrentBlock("neutral_radiobutton");
						$tplrow->setVariable("QUESTION_ID", $this->object->getId());
						$tplrow->setVariable("ROW", $i);
						$tplrow->setVariable("VALUE", $j);
						if (is_array($working_data))
						{
							foreach ($working_data as $data)
							{
								if (($data["value"] == $j) && ($data["rowvalue"] == $i))
								{
									$tplrow->setVariable("CHECKED_RADIOBUTTON", " checked=\"checked\"");
								}
							}
						}
						$tplrow->parseCurrentBlock();
						break;
					case 1:
						$tplrow->setCurrentBlock("neutral_checkbox");
						$tplrow->setVariable("QUESTION_ID", $this->object->getId());
						$tplrow->setVariable("ROW", $i);
						$tplrow->setVariable("VALUE", $j);
						if (is_array($working_data))
						{
							foreach ($working_data as $data)
							{
								if (($data["value"] == $j) && ($data["rowvalue"] == $i))
								{
									$tplrow->setVariable("CHECKED_CHECKBOX", " checked=\"checked\"");
								}
							}
						}
						$tplrow->parseCurrentBlock();
						break;
				}
				$tplrow->setCurrentBlock("neutral_answer");
				$style = array();
				if ($this->object->getNeutralColumnSeparator())
				{
					array_push($style, "border-left: $neutralstyle!important");
				}
				if ($this->object->getColumnSeparators() == 1)
				{
					if ($j < $this->object->getColumnCount() - 1)
					{
						array_push($style, "border-right: 1px solid $bordercolor!important");
					}
				}

				if ($this->object->getRowSeparators() == 1)
				{
					if ($i < $this->object->getRowCount() - 1)
					{
						array_push($style, "border-bottom: 1px solid $bordercolor!important");
					}
				}
				if (count($style))
				{
					$tplrow->setVariable("STYLE", " style=\"" . implode(";", $style) . "\"");
				}
				$tplrow->parseCurrentBlock();
			}

			$tplrow->setVariable("TEXT_ROW", ilUtil::prepareFormOutput($this->object->getRow($i)));
			$tplrow->setVariable("ROWCLASS", $rowclass[$i % 2]);
			if ($this->object->getRowSeparators() == 1)
			{
				if ($i < $this->object->getRowCount() - 1)
				{
					$tplrow->setVariable("STYLE", " style=\"border-bottom: 1px solid $bordercolor!important\"");
				}
			}
			$template->setCurrentBlock("matrix_row");
			$template->setVariable("ROW", $tplrow->get());
			$template->parseCurrentBlock();
		}
		
		if ($question_title)
		{
			$template->setVariable("QUESTION_TITLE", ilUtil::prepareFormOutput($this->object->getTitle()));
		}
		$template->setCurrentBlock("question_data_matrix");
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
		$layout = $this->object->getLayout();
		$neutralstyle = "3px solid #808080";
		$bordercolor = "#808080";
		$template = new ilTemplate("tpl.il_svy_qpl_matrix_printview.html", TRUE, TRUE, "Modules/SurveyQuestionPool");

		if ($this->show_layout_row)
		{
			$layout_row = $this->getLayoutRow();
			$template->setCurrentBlock("matrix_row");
			$template->setVariable("ROW", $layout_row);
			$template->parseCurrentBlock();
		}
		
		$tplheaders = new ilTemplate("tpl.il_svy_out_matrix_columnheaders.html", TRUE, TRUE, "Modules/SurveyQuestionPool");
		if ((strlen($this->object->getBipolarAdjective(0))) && (strlen($this->object->getBipolarAdjective(1))))
		{
			$tplheaders->setCurrentBlock("bipolar_start");
			$style = array();
			array_push($style, sprintf("width: %.2f%s!important", $layout["percent_bipolar_adjective1"], "%"));
			if (count($style) > 0)
			{
				$tplheaders->setVariable("STYLE", " style=\"" . implode(";", $style) . "\"");
			}
			$tplheaders->parseCurrentBlock();
		}
		// column headers
		for ($i = 0; $i < $this->object->getColumnCount(); $i++)
		{
			$style = array();
			if ($this->object->getColumnSeparators() == 1)
			{
				if (($i < $this->object->getColumnCount() - 1))
				{
					array_push($style, "border-right: 1px solid $bordercolor!important");
				}
			}
			array_push($style, sprintf("width: %.2f%s!important", $layout["percent_columns"] / $this->object->getColumnCount(), "%"));
			$tplheaders->setCurrentBlock("column_header");
			$tplheaders->setVariable("TEXT", ilUtil::prepareFormOutput($this->object->getColumn($i)));
			$tplheaders->setVariable("CLASS", "center");
			if (count($style) > 0)
			{
				$tplheaders->setVariable("STYLE", " style=\"" . implode(";", $style) . "\"");
			}
			$tplheaders->parseCurrentBlock();
		}
		if (strlen($this->object->getNeutralColumn()))
		{
			$tplheaders->setCurrentBlock("neutral_column_header");
			$tplheaders->setVariable("TEXT", ilUtil::prepareFormOutput($this->object->getNeutralColumn()));
			$tplheaders->setVariable("CLASS", "rsep");
			$style = array();
			array_push($style, sprintf("width: %.2f%s!important", $layout["percent_neutral"], "%"));
			if ($this->object->getNeutralColumnSeparator())
			{
				array_push($style, "border-left: $neutralstyle!important;");
			}
			if (count($style) > 0)
			{
				$tplheaders->setVariable("STYLE", " style=\"" . implode(";", $style) . "\"");
			}
			$tplheaders->parseCurrentBlock();
		}
		if ((strlen($this->object->getBipolarAdjective(0))) && (strlen($this->object->getBipolarAdjective(1))))
		{
			$tplheaders->setCurrentBlock("bipolar_end");
			$style = array();
			array_push($style, sprintf("width: %.2f%s!important", $layout["percent_bipolar_adjective2"], "%"));
			if (count($style) > 0)
			{
				$tplheaders->setVariable("STYLE", " style=\"" . implode(";", $style) . "\"");
			}
			$tplheaders->parseCurrentBlock();
		}

		$style = array();
		array_push($style, sprintf("width: %.2f%s!important", $layout["percent_row"], "%"));
		if (count($style) > 0)
		{
			$tplheaders->setVariable("STYLE", " style=\"" . implode(";", $style) . "\"");
		}
		
		$template->setCurrentBlock("matrix_row");
		$template->setVariable("ROW", $tplheaders->get());
		$template->parseCurrentBlock();

		$rowclass = array("tblrow1", "tblrow2");
		
		for ($i = 0; $i < $this->object->getRowCount(); $i++)
		{
			$tplrow = new ilTemplate("tpl.il_svy_qpl_matrix_printview_row.html", TRUE, TRUE, "Modules/SurveyQuestionPool");
			for ($j = 0; $j < $this->object->getColumnCount(); $j++)
			{
				if (($i == 0) && ($j == 0))
				{
					if ((strlen($this->object->getBipolarAdjective(0))) && (strlen($this->object->getBipolarAdjective(1))))
					{
						$tplrow->setCurrentBlock("bipolar_start");
						$tplrow->setVariable("TEXT_BIPOLAR_START", ilUtil::prepareFormOutput($this->object->getBipolarAdjective(0)));
						$tplrow->setVariable("ROWSPAN", $this->object->getRowCount());
						$tplrow->parseCurrentBlock();
					}
				}
				if (($i == 0) && ($j == $this->object->getColumnCount()-1))
				{
					if ((strlen($this->object->getBipolarAdjective(0))) && (strlen($this->object->getBipolarAdjective(1))))
					{
						$tplrow->setCurrentBlock("bipolar_end");
						$tplrow->setVariable("TEXT_BIPOLAR_END", ilUtil::prepareFormOutput($this->object->getBipolarAdjective(1)));
						$tplrow->setVariable("ROWSPAN", $this->object->getRowCount());
						$tplrow->parseCurrentBlock();
					}
				}
				switch ($this->object->getSubtype())
				{
					case 0:
						$tplrow->setCurrentBlock("radiobutton");
						$tplrow->setVariable("IMAGE_RADIO", ilUtil::getHtmlPath(ilUtil::getImagePath("radiobutton_unchecked.gif")));
						$tplrow->setVariable("ALT_RADIO", $this->lng->txt("unchecked"));
						$tplrow->setVariable("TITLE_RADIO", $this->lng->txt("unchecked"));
						$tplrow->parseCurrentBlock();
						break;
					case 1:
						$tplrow->setCurrentBlock("checkbox");
						$tplrow->setVariable("IMAGE_CHECKBOX", ilUtil::getHtmlPath(ilUtil::getImagePath("checkbox_unchecked.gif")));
						$tplrow->setVariable("ALT_CHECKBOX", $this->lng->txt("unchecked"));
						$tplrow->setVariable("TITLE_CHECKBOX", $this->lng->txt("unchecked"));
						$tplrow->parseCurrentBlock();
						break;
				}
				$tplrow->setCurrentBlock("answer");
				$style = array();
				
				if ($this->object->getColumnSeparators() == 1)
				{
					if ($j < $this->object->getColumnCount() - 1)
					{
						array_push($style, "border-right: 1px solid $bordercolor!important");
					}
				}

				if ($this->object->getRowSeparators() == 1)
				{
					if ($i < $this->object->getRowCount() - 1)
					{
						array_push($style, "border-bottom: 1px solid $bordercolor!important");
					}
				}
				if (count($style))
				{
					$tplrow->setVariable("STYLE", " style=\"" . implode(";", $style) . "\"");
				}
				$tplrow->parseCurrentBlock();
			}

			if (strlen($this->object->getNeutralColumn()))
			{
				$j = $this->object->getRowCount();
				switch ($this->object->getSubtype())
				{
					case 0:
						$tplrow->setCurrentBlock("neutral_radiobutton");
						$tplrow->setVariable("IMAGE_RADIO", ilUtil::getHtmlPath(ilUtil::getImagePath("radiobutton_unchecked.gif")));
						$tplrow->setVariable("ALT_RADIO", $this->lng->txt("unchecked"));
						$tplrow->setVariable("TITLE_RADIO", $this->lng->txt("unchecked"));
						$tplrow->parseCurrentBlock();
						break;
					case 1:
						$tplrow->setCurrentBlock("neutral_checkbox");
						$tplrow->setVariable("IMAGE_CHECKBOX", ilUtil::getHtmlPath(ilUtil::getImagePath("checkbox_unchecked.gif")));
						$tplrow->setVariable("ALT_CHECKBOX", $this->lng->txt("unchecked"));
						$tplrow->setVariable("TITLE_CHECKBOX", $this->lng->txt("unchecked"));
						$tplrow->parseCurrentBlock();
						break;
				}
				$tplrow->setCurrentBlock("neutral_answer");
				$style = array();
				if ($this->object->getNeutralColumnSeparator())
				{
					array_push($style, "border-left: $neutralstyle!important");
				}
				if ($this->object->getColumnSeparators() == 1)
				{
					if ($j < $this->object->getColumnCount() - 1)
					{
						array_push($style, "border-right: 1px solid $bordercolor!important");
					}
				}

				if ($this->object->getRowSeparators() == 1)
				{
					if ($i < $this->object->getRowCount() - 1)
					{
						array_push($style, "border-bottom: 1px solid $bordercolor!important");
					}
				}
				if (count($style))
				{
					$tplrow->setVariable("STYLE", " style=\"" . implode(";", $style) . "\"");
				}
				$tplrow->parseCurrentBlock();
			}

			$tplrow->setVariable("TEXT_ROW", ilUtil::prepareFormOutput($this->object->getRow($i)));
			$tplrow->setVariable("ROWCLASS", $rowclass[$i % 2]);
			if ($this->object->getRowSeparators() == 1)
			{
				if ($i < $this->object->getRowCount() - 1)
				{
					$tplrow->setVariable("STYLE", " style=\"border-bottom: 1px solid $bordercolor!important\"");
				}
			}
			$template->setCurrentBlock("matrix_row");
			$template->setVariable("ROW", $tplrow->get());
			$template->parseCurrentBlock();
		}
		
		if ($question_title)
		{
			$template->setVariable("QUESTION_TITLE", ilUtil::prepareFormOutput($this->object->getTitle()));
		}
		$template->setCurrentBlock();
		if ($show_questiontext)
		{
			$questiontext = $this->object->getQuestiontext();
			$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($questiontext, TRUE));
		}
		if (! $this->object->getObligatory())
		{
			$template->setVariable("OBLIGATORY_TEXT", $this->lng->txt("survey_question_optional"));
		}
		$template->parseCurrentBlock();
		return $template->get();
	}
	
/**
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
* Creates a layout view of the question
*
* @access public
*/
	function layout()
	{
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_qpl_layout.html", "Modules/SurveyQuestionPool");
		$this->show_layout_row = TRUE;
		$question_output = $this->getWorkingForm();
		$this->tpl->setVariable("QUESTION_OUTPUT", $question_output);
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this, "saveLayout"));
		$this->tpl->setVariable("SAVE", $this->lng->txt("save"));
	}
	
/**
 * Saves the layout for the matrix question
 *
 * @return void
 **/
	function saveLayout()
	{
		$this->object->saveLayout($_POST["percent_row"], $_POST['percent_columns'], $_POST['percent_bipolar_adjective1'], $_POST['percent_bipolar_adjective2'], $_POST["percent_neutral"]);
		$percent_values = array(
			"percent_row" => $_POST["percent_row"],
			"percent_columns" => $_POST["percent_columns"],
			"percent_bipolar_adjective1" => $_POST['percent_bipolar_adjective1'],
			"percent_bipolar_adjective2" => $_POST['percent_bipolar_adjective2'],
			"percent_neutral" => $_POST["percent_neutral"]
		);
		$this->object->setLayout($percent_values);
		$this->layout();
	}

/**
* Creates a row to define the matrix question layout with percentage values
*
* @access public
*/
	function getLayoutRow()
	{
		$percent_values = $this->object->getLayout();
		$template = new ilTemplate("tpl.il_svy_out_matrix_layout.html", TRUE, TRUE, "Modules/SurveyQuestionPool");
		if (strlen($this->object->getBipolarAdjective(0)) && strlen($this->object->getBipolarAdjective(1)))
		{
			$template->setCurrentBlock("bipolar_start");
			$template->setVariable("VALUE_PERCENT_BIPOLAR_ADJECTIVE1", " value=\"" . $percent_values["percent_bipolar_adjective1"] . "\"");
			$template->setVariable("STYLE", " style=\"width:" . $percent_values["percent_bipolar_adjective1"] . "%\"");
			$template->parseCurrentBlock();
			$template->setCurrentBlock("bipolar_end");
			$template->setVariable("VALUE_PERCENT_BIPOLAR_ADJECTIVE2", " value=\"" . $percent_values["percent_bipolar_adjective2"] . "\"");
			$template->setVariable("STYLE", " style=\"width:" . $percent_values["percent_bipolar_adjective2"] . "%\"");
			$template->parseCurrentBlock();
		}
		if (strlen($this->object->getNeutralColumn()))
		{
			$template->setCurrentBlock("bipolar_end");
			$template->setVariable("VALUE_PERCENT_NEUTRAL", " value=\"" . $percent_values["percent_neutral"] . "\"");
			$template->setVariable("STYLE_NEUTRAL", " style=\"width:" . $percent_values["percent_neutral"] . "%\"");
			$template->parseCurrentBlock();
		}
		$template->setVariable("VALUE_PERCENT_ROW", " value=\"" . $percent_values["percent_row"] . "\"");
		$template->setVariable("STYLE_ROW", " style=\"width:" . $percent_values["percent_row"] . "%\"");
		$counter = $this->object->getColumnCount();
		$template->setVariable("COLSPAN_COLUMNS", $counter);
		$template->setVariable("VALUE_PERCENT_COLUMNS", " value=\"" . $percent_values["percent_columns"] . "\"");
		$template->setVariable("STYLE_COLUMNS", " style=\"width:" . $percent_values["percent_columns"] . "%\"");
		return $template->get();
	}

	/**
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
		$template->setVariable("TEXT_OPTION_VALUE", $this->cumulated["TOTAL"]["USERS_ANSWERED"]);
		$template->parseCurrentBlock();
		$template->setCurrentBlock("detail_row");
		$template->setVariable("TEXT_OPTION", $this->lng->txt("users_skipped"));
		$template->setVariable("TEXT_OPTION_VALUE", $this->cumulated["TOTAL"]["USERS_SKIPPED"]);
		$template->parseCurrentBlock();
		
		$template->setCurrentBlock("detail_row");
		$template->setVariable("TEXT_OPTION", $this->lng->txt("mode"));
		$template->setVariable("TEXT_OPTION_VALUE", $this->cumulated["TOTAL"]["MODE"]);
		$template->parseCurrentBlock();
		$template->setCurrentBlock("detail_row");
		$template->setVariable("TEXT_OPTION", $this->lng->txt("mode_nr_of_selections"));
		$template->setVariable("TEXT_OPTION_VALUE", $this->cumulated["TOTAL"]["MODE_NR_OF_SELECTIONS"]);
		$template->parseCurrentBlock();
		$template->setCurrentBlock("detail_row");
		$template->setVariable("TEXT_OPTION", $this->lng->txt("median"));
		$template->setVariable("TEXT_OPTION_VALUE", $this->cumulated["TOTAL"]["MEDIAN"]);
		$template->parseCurrentBlock();
		
		$template->setCurrentBlock("detail_row");
		$template->setVariable("TEXT_OPTION", $this->lng->txt("categories"));
		$columns = "";
		foreach ($this->cumulated["TOTAL"]["variables"] as $key => $value)
		{
			$columns .= "<li>" . $this->lng->txt("title") . ":" . "<span class=\"bold\">" . $value["title"] . "</span><br />" .
				$this->lng->txt("category_nr_selected") . ": " . "<span class=\"bold\">" . $value["selected"] . "</span><br />" .
				$this->lng->txt("percentage_of_selections") . ": " . "<span class=\"bold\">" . sprintf("%.2f", 100*$value["percentage"]) . "</span></li>";
		}
		$columns = "<ol>$columns</ol>";
		$template->setVariable("TEXT_OPTION_VALUE", $columns);
		$template->parseCurrentBlock();
		
		foreach ($this->cumulated as $key => $value)
		{
			if (is_numeric($key))
			{
				$template->setCurrentBlock("detail_row");
				$template->setVariable("TEXT_OPTION", $this->lng->txt("row"));
				$questiontext = $value["ROW"];
				$template->setVariable("TEXT_OPTION_VALUE", $this->object->prepareTextareaOutput($questiontext, TRUE));
				$template->parseCurrentBlock();
				$template->setCurrentBlock("detail_row");
				$template->setVariable("TEXT_OPTION", $this->lng->txt("users_answered"));
				$template->setVariable("TEXT_OPTION_VALUE", $value["USERS_ANSWERED"]);
				$template->parseCurrentBlock();
				$template->setCurrentBlock("detail_row");
				$template->setVariable("TEXT_OPTION", $this->lng->txt("users_skipped"));
				$template->setVariable("TEXT_OPTION_VALUE", $value["USERS_SKIPPED"]);
				$template->parseCurrentBlock();
				
				$template->setCurrentBlock("detail_row");
				$template->setVariable("TEXT_OPTION", $this->lng->txt("mode"));
				$template->setVariable("TEXT_OPTION_VALUE", $value["MODE"]);
				$template->parseCurrentBlock();
				$template->setCurrentBlock("detail_row");
				$template->setVariable("TEXT_OPTION", $this->lng->txt("mode_nr_of_selections"));
				$template->setVariable("TEXT_OPTION_VALUE", $value["MODE_NR_OF_SELECTIONS"]);
				$template->parseCurrentBlock();
				$template->setCurrentBlock("detail_row");
				$template->setVariable("TEXT_OPTION", $this->lng->txt("median"));
				$template->setVariable("TEXT_OPTION_VALUE", $value["MEDIAN"]);
				$template->parseCurrentBlock();
				
				$template->setCurrentBlock("detail_row");
				$template->setVariable("TEXT_OPTION", $this->lng->txt("categories"));
				$columns = "";
				foreach ($value["variables"] as $key => $value)
				{
					$columns .= "<li>" . $this->lng->txt("title") . ":" . "<span class=\"bold\">" . $value["title"] . "</span><br />" .
						$this->lng->txt("category_nr_selected") . ": " . "<span class=\"bold\">" . $value["selected"] . "</span><br />" .
						$this->lng->txt("percentage_of_selections") . ": " . "<span class=\"bold\">" . sprintf("%.2f", 100*$value["percentage"]) . "</span></li>";
				}
				$columns = "<ol>$columns</ol>";
				$template->setVariable("TEXT_OPTION_VALUE", $columns);
				$template->parseCurrentBlock();
			}
		}
		
		// display chart for matrix question for array $eval["variables"]
		foreach ($this->cumulated as $key => $value)
		{
			if (is_numeric($key))
			{
				$template->setCurrentBlock("chartimage");

				$charturl = "";
				include_once "./Services/Administration/classes/class.ilSetting.php";
				$surveySetting = new ilSetting("survey");
				if ($surveySetting->get("googlechart") == 1)
				{
					$chartcolors = array("2A4BD7", "9DAFFF", "1D6914", "81C57A", "814A19", "E9DEBB", "8126C0", "AD2323", "29D0D0", "FFEE33", "FF9233", "FFCDF3", "A0A0A0", "575757", "000000");
					$selections = array();
					$values = array();
					$maxselection = 0;
					foreach ($value["variables"] as $val)
					{
						if ($val["selected"] > $maxselection) $maxselection = $val["selected"];
						array_push($selections, $val["selected"]);
						array_push($values, str_replace(" ", "+", $val["title"]));
					}
					$chartwidth = 800;
					$selectionlabels = "";
					if ($maxselection % 2 == 0)
					{
						$selectionlabels = "0|" . ($maxselection / 2) . "|$maxselection";
					}
					else
					{
						$selectionlabels = "0|$maxselection";
					}
					$charturl = "http://chart.apis.google.com/chart?chco=" . implode("|", array_slice($chartcolors, 0, count($values))). "&cht=bvs&chs=" . $chartwidth . "x250&chd=t:" . implode(",", $selections) . "&chds=0,$maxselection&chxt=y,y&chxl=0:|$selectionlabels|1:||".str_replace(" ", "+", $this->lng->txt("mode_nr_of_selections"))."|" . "&chxr=1,0,$maxselection&chtt=" . str_replace(" ", "+", $value["ROW"]) . "&chbh=20," . round($chartwidth/(count($values)+1.5)) . "&chdl=" . implode("|", $values) . "&chdlp=b";
				}
				else
				{
					$this->ctrl->setParameterByClass("ilsurveyevaluationgui", "type", $key);
					$this->ctrl->setParameterByClass("ilsurveyevaluationgui", "survey", $survey_id);
					$this->ctrl->setParameterByClass("ilsurveyevaluationgui", "question", $this->object->getId());
					$charturl = $this->ctrl->getLinkTargetByClass("ilsurveyevaluationgui", "outChart");
				}
				$template->setVariable("CHART", $charturl);
				$template->setVariable("ALT_CHART", $this->lng->txt("chart"));
				$template->parseCurrentBlock();
			}
		}
		$template->setCurrentBlock("chart");
		$template->setVariable("TEXT_CHART", $this->lng->txt("chart"));
		$template->parseCurrentBlock();

		$template->setVariable("QUESTION_TITLE", "$counter. ".$this->object->getTitle());
		$output = $template->get();
		return $output;
	}

	function setQuestionTabs()
	{
		global $rbacsystem,$ilTabs;
		$this->ctrl->setParameterByClass("$guiclass", "sel_question_types", $this->getQuestionType());
		$this->ctrl->setParameterByClass("$guiclass", "q_id", $_GET["q_id"]);

		if (($_GET["calling_survey"] > 0) || ($_GET["new_for_survey"] > 0))
		{
			$ref_id = $_GET["calling_survey"];
			if (!strlen($ref_id)) $ref_id = $_GET["new_for_survey"];
			$addurl = "";
			if (strlen($_GET["new_for_survey"]))
			{
				$addurl = "&new_id=" . $_GET["q_id"];
			}
			$ilTabs->setBackTarget($this->lng->txt("menubacktosurvey"), "ilias.php?baseClass=ilObjSurveyGUI&ref_id=$ref_id&cmd=questions" . $addurl);
		}
		else
		{
			$ilTabs->setBackTarget($this->lng->txt("spl"), $this->ctrl->getLinkTargetByClass("ilObjSurveyQuestionPoolGUI", "questions"));
		}
		if ($_GET["q_id"])
		{
			$ilTabs->addTarget("preview",
				$this->ctrl->getLinkTarget($this, "preview"), 
				array("preview"),
				"",
				"");
				
			$ilTabs->addTarget("layout",
				$this->ctrl->getLinkTarget($this, "layout"), 
				array("layout", "saveLayout"),
				"",
				"");
		}
		if ($rbacsystem->checkAccess('edit', $_GET["ref_id"])) 
		{
			$ilTabs->addTarget("edit_properties",
				$this->ctrl->getLinkTarget($this, "editQuestion"), 
				array("editQuestion", "cancelExplorer", "linkChilds", "addGIT", "addST",
					"addPG", "editQuestion", "addMaterial", "removeMaterial", 
					"save", "cancel", "savePhrasecolumns", "confirmSavePhrase",
					"downcolumns", "upcolumns", "addcolumns", "removecolumns",
					"downrows", "uprows", "addrows", "removerows", "wizardcolumns",
					"addSelectedPhrase", "insertStandardNumbers"),
				"",
				"");
		}

		if ($this->object->getId() > 0) 
		{
			$ilTabs->addTarget("material",
									 $this->ctrl->getLinkTarget($this, "material"), 
									array("material", "cancelExplorer", "linkChilds", "addGIT", "addST",
											 "addPG", "addMaterial", "removeMaterial"),
									 "$guiclass");
		}
		
		if ($this->object->getId() > 0) 
		{
			$title = $this->lng->txt("edit") . " &quot;" . $this->object->getTitle() . "&quot";
		} 
		else 
		{
			$title = $this->lng->txt("create_new") . " " . $this->lng->txt($this->getQuestionType());
		}

		$this->tpl->setVariable("HEADER", $title);
	}

}
?>

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

require_once "./assessment/classes/class.assQuestionGUI.php";
require_once "./assessment/classes/class.assMultipleChoice.php";

/**
* Multiple choice question GUI representation
*
* The ASS_MultipleChoiceGUI class encapsulates the GUI representation
* for multiple choice questions.
*
* @author		Helmut Schottmüller <hschottm@tzi.de>
* @version	$Id$
* @module   class.assMultipleChoiceGUI.php
* @modulegroup   Assessment
*/
class ASS_MultipleChoiceGUI extends ASS_QuestionGUI
{
	/**
	* ASS_MultipleChoiceGUI constructor
	*
	* The constructor takes possible arguments an creates an instance of the ASS_MultipleChoiceGUI object.
	*
	* @param integer $id The database id of a multiple choice question object
	* @access public
	*/
	function ASS_MultipleChoiceGUI(
			$id = -1
	)
	{
		$this->ASS_QuestionGUI();
		$this->object = new ASS_MultipleChoice();
		if ($id >= 0)
		{
			$this->object->loadFromDb($id);
		}
	}

	function getCommand($cmd)
	{
		if (substr($cmd, 0, 6) == "delete")
		{
			$cmd = "delete";
		}

		return $cmd;
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
		if ($this->object->get_response() == RESPONSE_SINGLE)
		{
			return "qt_multiple_choice_sr";
		}
		else
		{
			return "qt_multiple_choice_mr";
		}
	}

	/**
	* Creates an output of the edit form for the question
	*
	* Creates an output of the edit form for the question
	*
	* @access public
	*/
	function showEditForm()
	{
echo "<br>ASS_MultipleChoiceGUI->showEditForm()";
		if ($this->object->get_response() == RESPONSE_SINGLE)
		{
			$this->tpl->addBlockFile("QUESTION_DATA", "question_data", "tpl.il_as_qpl_mc_sr.html", true);
			$this->tpl->addBlockFile("OTHER_QUESTION_DATA", "other_question_data", "tpl.il_as_qpl_other_question_data.html", true);
			// output of existing single response answers
			for ($i = 0; $i < $this->object->get_answer_count(); $i++)
			{
				$this->tpl->setCurrentBlock("deletebutton");
				$this->tpl->setVariable("DELETE", $this->lng->txt("delete"));
				$this->tpl->setVariable("ANSWER_ORDER", $i);
				$this->tpl->parseCurrentBlock();
				$this->tpl->setCurrentBlock("answers");
				$answer = $this->object->get_answer($i);
				$this->tpl->setVariable("VALUE_ANSWER_COUNTER", $answer->get_order() + 1);
				$this->tpl->setVariable("ANSWER_ORDER", $answer->get_order());
				$this->tpl->setVariable("VALUE_ANSWER", $answer->get_answertext());
				$this->tpl->setVariable("TEXT_POINTS", $this->lng->txt("points"));
				$this->tpl->setVariable("TEXT_ANSWER", $this->lng->txt("answer"));
				$this->tpl->setVariable("TEXT_ANSWER_TEXT", $this->lng->txt("answer_text"));
				$this->tpl->setVariable("VALUE_MULTIPLE_CHOICE_POINTS", sprintf("%d", $answer->get_points()));
				$this->tpl->setVariable("VALUE_TRUE", $this->lng->txt("true"));
				if ($answer->is_true())
				{
					$this->tpl->setVariable("CHECKED_ANSWER", " checked=\"checked\"");
				}
				$this->tpl->parseCurrentBlock();
			}
			if (strlen($_POST["cmd"]["add"]) > 0)
			{
				// Create template for a new answer
				$this->tpl->setCurrentBlock("answers");
				$this->tpl->setVariable("VALUE_ANSWER_COUNTER", $this->object->get_answer_count() + 1);
				$this->tpl->setVariable("ANSWER_ORDER", $this->object->get_answer_count());
				$this->tpl->setVariable("TEXT_ANSWER", $this->lng->txt("answer"));
				$this->tpl->setVariable("TEXT_ANSWER_TEXT", $this->lng->txt("answer_text"));
				$this->tpl->setVariable("TEXT_POINTS", $this->lng->txt("points"));
				$this->tpl->setVariable("VALUE_TRUE", $this->lng->txt("true"));
				$this->tpl->parseCurrentBlock();
			}
			// call to other question data i.e. material, estimated working time block
			$this->outOtherQuestionData();

			$this->tpl->setCurrentBlock("question_data");
			$this->tpl->setVariable("MULTIPLE_CHOICE_ID", $this->object->getId());
			$this->tpl->setVariable("VALUE_MULTIPLE_CHOICE_TITLE", $this->object->getTitle());
			$this->tpl->setVariable("VALUE_MULTIPLE_CHOICE_COMMENT", $this->object->getComment());
			$this->tpl->setVariable("VALUE_MULTIPLE_CHOICE_AUTHOR", $this->object->getAuthor());
			$this->tpl->setVariable("VALUE_QUESTION", $this->object->get_question());
			$this->tpl->setVariable("VALUE_ADD_ANSWER", $this->lng->txt("add_answer"));
			$this->tpl->setVariable("VALUE_ADD_ANSWER_YN", $this->lng->txt("add_answer_yn"));
			$this->tpl->setVariable("VALUE_ADD_ANSWER_TF", $this->lng->txt("add_answer_tf"));
			$this->tpl->setVariable("TEXT_TITLE", $this->lng->txt("title"));
			$this->tpl->setVariable("TEXT_AUTHOR", $this->lng->txt("author"));
			$this->tpl->setVariable("TEXT_COMMENT", $this->lng->txt("description"));
			$this->tpl->setVariable("TEXT_QUESTION", $this->lng->txt("question"));
			$this->tpl->setVariable("TEXT_SHUFFLE_ANSWERS", $this->lng->txt("shuffle_answers"));
			$this->tpl->setVariable("TXT_YES", $this->lng->txt("yes"));
			$this->tpl->setVariable("TXT_NO", $this->lng->txt("no"));
			if ($this->object->getShuffle())
			{
				$this->tpl->setVariable("SELECTED_YES", " selected=\"selected\"");
			}
			else
			{
				$this->tpl->setVariable("SELECTED_NO", " selected=\"selected\"");
			}
			$this->tpl->setVariable("SAVE",$this->lng->txt("save"));
			$this->tpl->setVariable("APPLY", $this->lng->txt("apply"));
			$this->tpl->setVariable("CANCEL",$this->lng->txt("cancel"));
			$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
			$this->ctrl->setParameter($this, "sel_question_types", "qt_multiple_choice_sr");
			$this->tpl->setVariable("ACTION_MULTIPLE_CHOICE_TEST",
				$this->ctrl->getFormAction($this));

			$this->tpl->parseCurrentBlock();
		}
		else
		{
			$this->tpl->addBlockFile("QUESTION_DATA", "question_data", "tpl.il_as_qpl_mc_mr.html", true);
			$this->tpl->addBlockFile("OTHER_QUESTION_DATA", "other_question_data", "tpl.il_as_qpl_other_question_data.html", true);

			// output of existing multiple response answers
			for ($i = 0; $i < $this->object->get_answer_count(); $i++)
			{
				$this->tpl->setCurrentBlock("deletebutton");
				$this->tpl->setVariable("DELETE", $this->lng->txt("delete"));
				$this->tpl->setVariable("ANSWER_ORDER", $i);
				$this->tpl->parseCurrentBlock();
				$this->tpl->setCurrentBlock("answers");
				$answer = $this->object->get_answer($i);
				$this->tpl->setVariable("TEXT_POINTS", $this->lng->txt("points"));
				$this->tpl->setVariable("VALUE_ANSWER_COUNTER", $answer->get_order() + 1);
				$this->tpl->setVariable("VALUE_MULTIPLE_CHOICE_POINTS", sprintf("%d", $answer->get_points()));
				$this->tpl->setVariable("ANSWER_ORDER", $answer->get_order());
				$this->tpl->setVariable("VALUE_ANSWER", $answer->get_answertext());
				$this->tpl->setVariable("TEXT_ANSWER", $this->lng->txt("answer"));
				$this->tpl->setVariable("TEXT_ANSWER_TEXT", $this->lng->txt("answer_text"));
				$this->tpl->setVariable("VALUE_TRUE", $this->lng->txt("true"));
				if ($answer->is_true())
				{
					$this->tpl->setVariable("CHECKED_ANSWER", " checked=\"checked\"");
				}
				$this->tpl->parseCurrentBlock();
			}

			if (strlen($_POST["cmd"]["add"]) > 0)
			{
				// Create template for a new answer
				$this->tpl->setCurrentBlock("answers");
				$this->tpl->setVariable("TEXT_POINTS", $this->lng->txt("points"));
				$this->tpl->setVariable("VALUE_ANSWER_COUNTER", $this->object->get_answer_count() + 1);
				$this->tpl->setVariable("ANSWER_ORDER", $this->object->get_answer_count());
				$this->tpl->setVariable("TEXT_ANSWER", $this->lng->txt("answer"));
				$this->tpl->setVariable("TEXT_ANSWER_TEXT", $this->lng->txt("answer_text"));
				$this->tpl->setVariable("VALUE_MULTIPLE_CHOICE_POINTS", "0");
				$this->tpl->setVariable("VALUE_TRUE", $this->lng->txt("true"));
				$this->tpl->parseCurrentBlock();
			}

			// call to other question data i.e. material, estimated working time block
			$this->outOtherQuestionData();

			$this->tpl->setCurrentBlock("question_data");

			$this->tpl->setVariable("TEXT_TITLE", $this->lng->txt("title"));
			$this->tpl->setVariable("TEXT_AUTHOR", $this->lng->txt("author"));
			$this->tpl->setVariable("TEXT_COMMENT", $this->lng->txt("description"));
			$this->tpl->setVariable("TEXT_QUESTION", $this->lng->txt("question"));
			$this->tpl->setVariable("MULTIPLE_CHOICE_ID", $this->object->getId());
			$this->tpl->setVariable("VALUE_MULTIPLE_CHOICE_TITLE", $this->object->getTitle());
			$this->tpl->setVariable("VALUE_MULTIPLE_CHOICE_COMMENT", $this->object->getComment());
			$this->tpl->setVariable("VALUE_MULTIPLE_CHOICE_AUTHOR", $this->object->getAuthor());
			$this->tpl->setVariable("VALUE_QUESTION", $this->object->get_question());
			$this->tpl->setVariable("VALUE_ADD_ANSWER", $this->lng->txt("add_answer"));
			$this->tpl->setVariable("TEXT_SHUFFLE_ANSWERS", $this->lng->txt("shuffle_answers"));
			$this->tpl->setVariable("TXT_YES", $this->lng->txt("yes"));
			$this->tpl->setVariable("TXT_NO", $this->lng->txt("no"));
			if ($this->object->getShuffle())
			{
				$this->tpl->setVariable("SELECTED_YES", " selected=\"selected\"");
			}
			else
			{
				$this->tpl->setVariable("SELECTED_NO", " selected=\"selected\"");
			}
			$this->tpl->setVariable("SAVE",$this->lng->txt("save"));
			$this->tpl->setVariable("APPLY", $this->lng->txt("apply"));
			$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
			$this->tpl->setVariable("CANCEL", $this->lng->txt("cancel"));
			$this->tpl->setVariable("ACTION_MULTIPLE_CHOICE_TEST", $_SERVER["PHP_SELF"] . "?ref_id=" . $_GET["ref_id"] . "&cmd=question&sel_question_types=qt_multiple_choice_mr");
			$this->tpl->parseCurrentBlock();
		}
	}

	/**
	* Sets the extra fields i.e. estimated working time and material of a question from a posted create/edit form
	*
	* Sets the extra fields i.e. estimated working time and material of a question from a posted create/edit form
	*
	* @access private
	*/
	function outOtherQuestionData()
	{
echo "<br>ASS_MultipleChoiceGUI->outOtherQuestionData()";
		$colspan = " colspan=\"3\"";

		if (!empty($this->object->materials))
		{
			$this->tpl->setCurrentBlock("select_block");
			foreach ($this->object->materials as $key => $value)
			{
				$this->tpl->setVariable("MATERIAL_VALUE", $key);
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setCurrentBlock("materiallist_block");
			$i = 1;
			foreach ($this->object->materials as $key => $value)
			{
				$this->tpl->setVariable("MATERIAL_COUNTER", $i);
				$this->tpl->setVariable("MATERIAL_VALUE", $key);
				$this->tpl->setVariable("MATERIAL_FILE_VALUE", $value);
				$this->tpl->parseCurrentBlock();
				$i++;
			}
			$this->tpl->setVariable("UPLOADED_MATERIAL", $this->lng->txt("uploaded_material"));
			$this->tpl->setVariable("VALUE_MATERIAL_DELETE", $this->lng->txt("delete"));
			$this->tpl->setVariable("COLSPAN_MATERIAL", $colspan);
			$this->tpl->parse("mainselect_block");
		}

		$this->tpl->setCurrentBlock("other_question_data");
		$est_working_time = $this->object->getEstimatedWorkingTime();
		$this->tpl->setVariable("TEXT_WORKING_TIME", $this->lng->txt("working_time"));
		$this->tpl->setVariable("TIME_FORMAT", $this->lng->txt("time_format"));
		$this->tpl->setVariable("VALUE_WORKING_TIME", ilUtil::makeTimeSelect("Estimated", false, $est_working_time[h], $est_working_time[m], $est_working_time[s]));
		$this->tpl->setVariable("TEXT_MATERIAL", $this->lng->txt("material"));
		$this->tpl->setVariable("TEXT_MATERIAL_FILE", $this->lng->txt("material_file"));
		$this->tpl->setVariable("VALUE_MATERIAL_UPLOAD", $this->lng->txt("upload"));
		$this->tpl->setVariable("COLSPAN_MATERIAL", $colspan);
		$this->tpl->parseCurrentBlock();
	}

	function addYesNo()
	{
		$this->setObjectData();

		if (!$this->checkInput())
		{
			sendInfo($this->lng->txt("fill_out_all_required_fields_add_answer"));
		}
		else
		{
			// add a yes/no answer template
			$this->object->add_answer(
				$this->lng->txt("yes"),
				0,
				false,
				count($this->object->answers)
			);
			$this->object->add_answer(
				$this->lng->txt("no"),
				0,
				false,
				count($this->object->answers)
			);
		}

		$this->showEditForm();
	}

	/**
	* add true/false answer
	*/
	function addTrueFalse()
	{
		$this->setObjectData();

		if (!$this->checkInput())
		{
			sendInfo($this->lng->txt("fill_out_all_required_fields_add_answer"));
		}
		else
		{
			// add a true/false answer template
			$this->object->add_answer(
				$this->lng->txt("true"),
				0,
				false,
				count($this->object->answers)
			);
			$this->object->add_answer(
				$this->lng->txt("false"),
				0,
				false,
				count($this->object->answers)
			);
		}

		$this->showEditForm();
	}

	/**
	* add an answer
	*/
	function add()
	{
		$this->setObjectData();

		if (!$this->checkInput())
		{
			sendInfo($this->lng->txt("fill_out_all_required_fields_add_answer"));
		}
		else
		{
			// add an answer template
			$this->object->add_answer(
				$this->lng->txt(""),
				0,
				false,
				count($this->object->answers)
			);
		}

		$this->showEditForm();
	}

	/**
	* delete an answer
	*/
	function delete()
	{
		$this->setObjectData();

		if (!$this->checkInput())
		{
			sendInfo($this->lng->txt("fill_out_all_required_fields_add_answer"));
		}
		else
		{
			foreach ($_POST["cmd"] as $key => $value)
			{
				// was one of the answers deleted
				if (preg_match("/delete_(\d+)/", $key, $matches))
				{
					$this->object->delete_answer($matches[1]);
				}
			}
		}

		$this->showEditForm();
	}

	/**
	* apply changes
	*/
	function apply()
	{
		$this->setObjectData();
		$this->showEditForm();
	}

	/**
	* upload material
	*/
	function uploadingMaterial()
	{
		$this->setObjectData();
		$this->showEditForm();
	}


	/**
	* check input fields
	*/
	function checkInput()
	{
		$cmd = $this->ctrl->getCmd();

		if ((!$_POST["title"]) or (!$_POST["author"]) or (!$_POST["question"]))
		{
echo "<br>checkInput1:FALSE";
			return false;
		}
		foreach ($_POST as $key => $value)
		{
			if (preg_match("/answer_(\d+)/", $key, $matches))
			{
				if (!$value)
				{
echo "<br>checkInput2:FALSE";
					return false;
				}
			}
		}

		return true;
	}

	function setObjectData()
	{
		$this->object->setTitle(ilUtil::stripSlashes($_POST["title"]));
		$this->object->setAuthor(ilUtil::stripSlashes($_POST["author"]));
		$this->object->setComment(ilUtil::stripSlashes($_POST["comment"]));
		$this->object->set_question(ilUtil::stripSlashes($_POST["question"]));
		$this->object->setShuffle($_POST["shuffle"]);

		// adding materials uris
		//$saved = $this->writeOtherPostData($result);
		$saved = $this->writeOtherPostData($result);

		// Delete all existing answers and create new answers from the form data
		$this->object->flush_answers();

		// Add all answers from the form into the object
		if ($this->object->get_response() == RESPONSE_SINGLE)
		{
			// ...for multiple choice with single response
			foreach ($_POST as $key => $value)
			{
				if (preg_match("/answer_(\d+)/", $key, $matches))
				{
					if ($_POST["radio"] == $matches[1])
					{
						$is_true = TRUE;
					}
					else
					{
						$is_true = FALSE;
					}
					$this->object->add_answer(
						ilUtil::stripSlashes($_POST["$key"]),
						ilUtil::stripSlashes($_POST["points_$matches[1]"]),
						ilUtil::stripSlashes($is_true),
						ilUtil::stripSlashes($matches[1])
						);
				}
			}
		}
		else
		{
			// ...for multiple choice with multiple response
			foreach ($_POST as $key => $value)
			{
				if (preg_match("/answer_(\d+)/", $key, $matches))
				{
					if ($_POST["checkbox_$matches[1]"] == $matches[1])
					{
						$is_true = TRUE;
					}
					else
					{
						$is_true = FALSE;
					}
					$this->object->add_answer(
						ilUtil::stripSlashes($_POST["$key"]),
						ilUtil::stripSlashes($_POST["points_$matches[1]"]),
						ilUtil::stripSlashes($is_true),
						ilUtil::stripSlashes($matches[1])
						);
				}
			}
		}
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
//echo "here!"; exit;
echo "<br>ASS_MultipleChoiceGUI->writePostData()";
		$result = 0;
		if ((!$_POST["title"]) or (!$_POST["author"]) or (!$_POST["question"]))
		{
			$result = 1;
		}

		if (($result) and (($_POST["cmd"]["add"]) or ($_POST["cmd"]["add_tf"]) or ($_POST["cmd"]["add_yn"])))
		{
			// You cannot add answers before you enter the required data
			sendInfo($this->lng->txt("fill_out_all_required_fields_add_answer"));
			$_POST["cmd"]["add"] = "";
			$_POST["cmd"]["add_yn"] = "";
			$_POST["cmd"]["add_tf"] = "";
		}

		// Check the creation of new answer text fields
		if ($_POST["cmd"]["add"] or $_POST["cmd"]["add_yn"] or $_POST["cmd"]["add_tf"])
		{
			foreach ($_POST as $key => $value)
			{
				if (preg_match("/answer_(\d+)/", $key, $matches))
				{
					if (!$value)
					{
						$_POST["cmd"]["add"] = "";
						$_POST["cmd"]["add_yn"] = "";
						$_POST["cmd"]["add_tf"] = "";
						sendInfo($this->lng->txt("fill_out_all_answer_fields"));
					}
			 	}
			}
		}

		$this->object->setTitle(ilUtil::stripSlashes($_POST["title"]));
		$this->object->setAuthor(ilUtil::stripSlashes($_POST["author"]));
		$this->object->setComment(ilUtil::stripSlashes($_POST["comment"]));
		$this->object->set_question(ilUtil::stripSlashes($_POST["question"]));
		$this->object->setShuffle($_POST["shuffle"]);

		// adding materials uris
		$saved = $this->writeOtherPostData($result);

		// Delete all existing answers and create new answers from the form data
		$this->object->flush_answers();

		// Add all answers from the form into the object
		if ($this->object->get_response() == RESPONSE_SINGLE)
		{
			// ...for multiple choice with single response
			foreach ($_POST as $key => $value)
			{
				if (preg_match("/answer_(\d+)/", $key, $matches))
				{
					if ($_POST["radio"] == $matches[1])
					{
						$is_true = TRUE;
					}
					else
					{
						$is_true = FALSE;
					}
					$this->object->add_answer(
						ilUtil::stripSlashes($_POST["$key"]),
						ilUtil::stripSlashes($_POST["points_$matches[1]"]),
						ilUtil::stripSlashes($is_true),
						ilUtil::stripSlashes($matches[1])
						);
				}
			}
			if ($_POST["cmd"]["add_tf"])
			{
				// add a true/false answer template
				$this->object->add_answer(
					$this->lng->txt("true"),
					0,
					false,
					count($this->object->answers)
				);
				$this->object->add_answer(
					$this->lng->txt("false"),
					0,
					false,
					count($this->object->answers)
				);
			}
			if ($_POST["cmd"]["add_yn"])
			{
				// add a true/false answer template
				$this->object->add_answer(
					$this->lng->txt("yes"),
					0,
					false,
					count($this->object->answers)
				);
				$this->object->add_answer(
					$this->lng->txt("no"),
					0,
					false,
					count($this->object->answers)
				);
			}
		}
		else
		{
			// ...for multiple choice with multiple response
			foreach ($_POST as $key => $value)
			{
				if (preg_match("/answer_(\d+)/", $key, $matches))
				{
					if ($_POST["checkbox_$matches[1]"] == $matches[1])
					{
						$is_true = TRUE;
					}
					else
					{
						$is_true = FALSE;
					}
					$this->object->add_answer(
						ilUtil::stripSlashes($_POST["$key"]),
						ilUtil::stripSlashes($_POST["points_$matches[1]"]),
						ilUtil::stripSlashes($is_true),
						ilUtil::stripSlashes($matches[1])
						);
				}
			}
		}

		// After adding all questions from the form we have to check if the learner pressed a delete button
		foreach ($_POST as $key => $value)
		{
			// was one of the answers deleted
			if (preg_match("/delete_(\d+)/", $key, $matches))
			{
				$this->object->delete_answer($matches[1]);
			}
		}

		// Set the question id from a hidden form parameter
		if ($_POST["multiple_choice_id"] > 0)
		{
			$this->object->setId($_POST["multiple_choice_id"]);
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

	/**
	* Creates the question output form for the learner
	*
	* Creates the question output form for the learner
	*
	* @access public
	*/
	function outWorkingForm($test_id = "", $is_postponed = false)
	{
echo "<br>ASS_MultipleChoiceGUI->outWorkingForm()";
		$this->tpl->addBlockFile("MULTIPLE_CHOICE_QUESTION", "multiple_choice", "tpl.il_as_execute_multiple_choice_question.html", true);
		$solutions = array();
		$postponed = "";
		if ($test_id)
		{
			$solutions =& $this->object->getSolutionValues($test_id);
		}
		if ($is_postponed)
		{
			$postponed = " (" . $this->lng->txt("postponed") . ")";
		}
		if (!empty($this->object->materials))
		{
			$i = 1;
			$this->tpl->setCurrentBlock("material_preview");
			foreach ($this->object->materials as $key => $value)
			{
				$this->tpl->setVariable("COUNTER", $i++);
				$this->tpl->setVariable("VALUE_MATERIAL_DOWNLOAD", $key);
				$this->tpl->setVariable("URL_MATERIAL_DOWNLOAD", $this->object->getMaterialsPathWeb().$value);
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setCurrentBlock("material_download");
			$this->tpl->setVariable("TEXT_MATERIAL_DOWNLOAD", $this->lng->txt("material_download"));
			$this->tpl->parseCurrentBlock();
		}

		if ($this->object->response == RESPONSE_SINGLE)
		{
			$this->tpl->setCurrentBlock("single");
			$akeys = array_keys($this->object->answers);
			if ($this->object->shuffle)
			{
				$akeys = $this->object->pcArrayShuffle($akeys);
			}
			foreach ($akeys as $key)
			{
				$value = $this->object->answers[$key];
				$this->tpl->setVariable("MULTIPLE_CHOICE_ANSWER_VALUE", $key);
				$this->tpl->setVariable("MULTIPLE_CHOICE_ANSWER_TEXT", $value->get_answertext());
				foreach ($solutions as $idx => $solution_value)
				{
					if ($solution_value->value1 == $key)
					{
						$this->tpl->setVariable("VALUE_CHECKED", " checked=\"checked\"");
					}
				}
				$this->tpl->parseCurrentBlock();
			}
		}
		else
		{
			$this->tpl->setCurrentBlock("multiple");
			$akeys = array_keys($this->object->answers);
			if ($this->object->shuffle)
			{
				$akeys = $this->object->pcArrayShuffle($akeys);
			}
			foreach ($akeys as $key)
			{
				$value = $this->object->answers[$key];
				$this->tpl->setVariable("MULTIPLE_CHOICE_ANSWER_VALUE", $key);
				$this->tpl->setVariable("MULTIPLE_CHOICE_ANSWER_TEXT", $value->get_answertext());
				foreach ($solutions as $idx => $solution_value)
				{
					if ($solution_value->value1 == $key)
					{
						$this->tpl->setVariable("VALUE_CHECKED", " checked=\"checked\"");
					}
				}
				$this->tpl->parseCurrentBlock();
			}
		}

		$this->tpl->setCurrentBlock("multiple_choice");
		$this->tpl->setVariable("MULTIPLE_CHOICE_HEADLINE", $this->object->getTitle() . $postponed);
		$this->tpl->setVariable("MULTIPLE_CHOICE_QUESTION", $this->object->get_question());
		$this->tpl->parseCurrentBlock();
	}

	/**
	* Creates a preview of the question
	*
	* Creates a preview of the question
	*
	* @access private
	*/
	function outPreviewForm()
	{
echo "<br>ASS_MultipleChoiceGUI->outPreviewForm()";
		$this->outWorkingForm();
	}

}
?>

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
* X out of all question GUI representation
*
* The ASS_XoutofAllGUI class encapsulates the GUI representation
* for x out of all questions.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @author		Nina Gharib <nina@wgserve.de>
* @version	$Id$
* @module   class.assXoutofAllGUI.php
* @modulegroup   Assessment
*/
class ASS_XoutofAllGUI extends ASS_QuestionGUI
{
	/**
	* ASS_XoutofAllGUI constructor
	*
	* The constructor takes possible arguments an creates an instance of the 
	* ASS_XoutofAllGUI object.
	*
	* @param integer $id The database id of a x out of all question object
	* @access public
	*/
	function ASS_XoutofAllGUI(
			$id = -1
	)
	{
		$this->ASS_QuestionGUI();
		include_once "./assessment/classes/class.assXoutofAll.php";
		$this->object = new ASS_XoutofAll();
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
		return "qt_xooa";
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
		//$this->tpl->setVariable("HEADER", $this->object->getTitle());
		$javascript = "<script type=\"text/javascript\">function initialSelect() {\n%s\n}</script>";
		$this->getQuestionTemplate("qt_xooa");
		$this->tpl->addBlockFile("QUESTION_DATA", "question_data", "tpl.il_as_qpl_xooa.html", true);

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
			$this->tpl->setVariable("VALUE_ANSWER", htmlspecialchars($answer->get_answertext()));
			$this->tpl->setVariable("TEXT_POINTS", $this->lng->txt("points"));
			$this->tpl->setVariable("TEXT_ANSWER_TEXT", $this->lng->txt("answer_text"));
			$this->tpl->setVariable("VALUE_X_OUT_OF_ALL_POINTS", sprintf("%d", $answer->get_points()));
			$this->tpl->parseCurrentBlock();
		}
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
		
		$this->tpl->setCurrentBlock("HeadContent");
		if ($this->object->get_answer_count() == 0)
		{
			$this->tpl->setVariable("CONTENT_BLOCK", sprintf($javascript, "document.frm_xooa.title.focus();"));
		}
		else
		{
			switch ($this->ctrl->getCmd())
			{
				case "add":
					$this->tpl->setVariable("CONTENT_BLOCK", sprintf($javascript, "document.frm_xooa.answer_".($this->object->get_answer_count() - 1).".focus(); document.getElementById('answer_".($this->object->get_answer_count() - 1)."').scrollIntoView(\"true\");"));
					break;
				case "":
					if ($this->object->get_answer_count() == 0)
					{
						$this->tpl->setVariable("CONTENT_BLOCK", sprintf($javascript, "document.frm_xooa.title.focus();"));
					}
					else
					{
						$this->tpl->setVariable("CONTENT_BLOCK", sprintf($javascript, "document.frm_xooa.answer_".($this->object->get_answer_count() - 1).".focus(); document.getElementById('answer_".($this->object->get_answer_count() - 1)."').scrollIntoView(\"true\");"));
					}
					break;
				default:
					$this->tpl->setVariable("CONTENT_BLOCK", sprintf($javascript, "document.frm_xooa.title.focus();"));
					break;
			}
		}
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("question_data");
		$this->tpl->setVariable("X_OUT_OF_ALL_ID", $this->object->getId());
		$this->tpl->setVariable("VALUE_X_OUT_OF_ALL_TITLE", htmlspecialchars($this->object->getTitle()));
		$this->tpl->setVariable("VALUE_X_OUT_OF_ALL_COMMENT", htmlspecialchars($this->object->getComment()));
		$this->tpl->setVariable("VALUE_X_OUT_OF_ALL_AUTHOR", htmlspecialchars($this->object->getAuthor()));
		$questiontext = $this->object->get_question();
		$questiontext = preg_replace("/<br \/>/", "\n", $questiontext);
		$this->tpl->setVariable("VALUE_QUESTION", htmlspecialchars($questiontext));
		$this->tpl->setVariable("VALUE_POINTS", htmlspecialchars($this->object->getPoints()));
//		Formularvariablen Wert number_X
		$this->tpl->setVariable("VALUE_NUMBER_X", $this->object->get_number_X());
		$this->tpl->setVariable("VALUE_ADD_ANSWER", $this->lng->txt("add_answer"));
		$this->tpl->setVariable("TEXT_TITLE", $this->lng->txt("title"));
		$this->tpl->setVariable("TEXT_AUTHOR", $this->lng->txt("author"));
		$this->tpl->setVariable("TEXT_COMMENT", $this->lng->txt("description"));
		$this->tpl->setVariable("TEXT_QUESTION", $this->lng->txt("question"));
		$this->tpl->setVariable("TEXT_POINTS", $this->lng->txt("points"));
// 		Formularvariablen Beschreibungstext number_X
		$this->tpl->setVariable("TEXT_NUMBER_X", $this->lng->txt("number_X"));
		$this->tpl->setVariable("TEXT_SOLUTION_HINT", $this->lng->txt("solution_hint"));
		if (count($this->object->suggested_solutions))
		{
			$solution_array = $this->object->getSuggestedSolution(0);
			include_once "./assessment/classes/class.assQuestion.php";
			$href = ASS_Question::_getInternalLinkHref($solution_array["internal_link"]);
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
		$this->ctrl->setParameter($this, "sel_question_types", "qt_xooa");
		$this->tpl->setVariable("ACTION_X_OUT_OF_ALL_TEST", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TEXT_QUESTION_TYPE", $this->lng->txt("qt_xooa"));
		$this->tpl->parseCurrentBlock();
		
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("BODY_ATTRIBUTES", " onload=\"initialSelect();\""); 
		$this->tpl->parseCurrentBlock();
	}

	/**
	* Sets the extra fields i.e. estimated working time of a question from a posted create/edit form
	*
	* Sets the extra fields i.e. estimated working time of a question from a posted create/edit form
	*
	* @access private
	*/
	function outOtherQuestionData()
	{
//echo "<br>ASS_MultipleChoiceGUI->outOtherQuestionData()";
		$this->tpl->setCurrentBlock("other_question_data");
		$est_working_time = $this->object->getEstimatedWorkingTime();
		$this->tpl->setVariable("TEXT_WORKING_TIME", $this->lng->txt("working_time"));
		$this->tpl->setVariable("TIME_FORMAT", $this->lng->txt("time_format"));
		$this->tpl->setVariable("VALUE_WORKING_TIME", ilUtil::makeTimeSelect("Estimated", false, $est_working_time[h], $est_working_time[m], $est_working_time[s]));
		$this->tpl->parseCurrentBlock();
	}


	/**
	* add an answer
	*/
	function add()
	{
		//$this->setObjectData();
		$this->writePostData();

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
				0,
				count($this->object->answers)
			);
		}

		$this->editQuestion();
	}

	/**
	* delete an answer
	*/
	function delete()
	{
		//$this->setObjectData();
		$this->writePostData();

		foreach ($_POST["cmd"] as $key => $value)
		{
			// was one of the answers deleted
			if (preg_match("/delete_(\d+)/", $key, $matches))
			{
				$this->object->delete_answer($matches[1]);
			}
		}

		$this->editQuestion();
	}

	/**
	* check input fields
	*/
	function checkInput()
	{
		$cmd = $this->ctrl->getCmd();

		if ((!$_POST["title"]) or (!$_POST["author"]) or (!$_POST["question"]) or (!$_POST["number_X"]))
		{
//echo "<br>checkInput1:FALSE";
			return false;
		}
		foreach ($_POST as $key => $value)
		{
			if (preg_match("/answer_(\d+)/", $key, $matches))
			{
				if (!$value)
				{
//echo "<br>checkInput2:FALSE";
					return false;
				}
			}
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
//echo "here!"; exit;
//echo "<br>ASS_XoutofAllGUI->writePostData()";
		$result = 0;
		if ((!$_POST["title"]) or (!$_POST["author"]) or (!$_POST["question"]) or (!$_POST["number_X"]))
		{
			$result = 1;
		}

		if (($result) and (($_POST["cmd"]["add"])))
		{
			// You cannot add answers before you enter the required data
			sendInfo($this->lng->txt("fill_out_all_required_fields_add_answer"));
			$_POST["cmd"]["add"] = "";
		}

		// Check the creation of new answer text fields
		if ($_POST["cmd"]["add"])
		{
			foreach ($_POST as $key => $value)
			{
				if (preg_match("/answer_(\d+)/", $key, $matches))
				{
					if (!$value)
					{
						$_POST["cmd"]["add"] = "";
						sendInfo($this->lng->txt("fill_out_all_answer_fields"));
					}
			 	}
			}
		}

		$this->object->setTitle(ilUtil::stripSlashes($_POST["title"]));
		$this->object->setAuthor(ilUtil::stripSlashes($_POST["author"]));
		$this->object->setComment(ilUtil::stripSlashes($_POST["comment"]));
		$questiontext = ilUtil::stripSlashes($_POST["question"], true, "<strong><em><code><cite>");
		$questiontext = preg_replace("/\n/", "<br />", $questiontext);
		$this->object->set_question($questiontext);
// 		wegschreiben der number x ins object
		$this->object->set_number_X($_POST["number_X"]);
		$this->object->setSuggestedSolution($_POST["solution_hint"], 0);
		$this->object->setShuffle($_POST["shuffle"]);

		$saved = $this->writeOtherPostData($result);

		// Delete all existing answers and create new answers from the form data
		$this->object->flush_answers();

		// Add all answers from the form into the object for x out of all
		foreach ($_POST as $key => $value)
		{
			if (preg_match("/answer_(\d+)/", $key, $matches))
			{
				$points = $_POST["points_$matches[1]"];
				if (preg_match("/\d+/", $points))
				{
					if ($points < 0)
					{
						$points = 0.0;
						sendInfo($this->lng->txt("negative_points_not_allowed"), true);
					}
				}
				else
				{
					$points = 0.0;
				}
				$this->object->add_answer(
					ilUtil::stripSlashes($_POST["$key"]),
					ilUtil::stripSlashes($points),
					ilUtil::stripSlashes($_POST["status_$matches[1]"]),
					ilUtil::stripSlashes($matches[1])
					);
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
		if ($_POST["xooa_id"] > 0)
		{
			$this->object->setId($_POST["xooa_id"]);
		}
		
		if ($saved)
		{
			// If the question was saved automatically before an upload, we have to make
			// sure, that the state after the upload is saved. Otherwise the user could be
			// irritated, if he presses cancel, because he only has the question state before
			// the upload process.
			$this->object->saveToDb();
			$_GET["q_id"] = $this->object->getId();
		}

		return $result;
	}

	/**
	* Creates the question output form for the learner
	*
	* Creates the question output form for the learner
	*
	* @param integer $test_id Database ID of a test which contains the question
	* @param boolean $is_postponed True if the question is a postponed question ("Postponed" added to the title)
	* @param boolean $showsolution Forces the output of the users solution if set to true
	* @param boolean $show_question_page Forces the output of the question only (without the surrounding page) when set to false. Default is true.
	* @param boolean $show_solution_only Forces the output of the correct question solution only when set to true. Default is false
	* @param object  $ilUser The user object of the user who answered the question
	* @param integer $pass The pass of the question which should be displayed
	* @param boolean $mixpass Mixes test passes (takes the last pass of the question) when set to true. Default is false.
	* @access public
	*/
	function outWorkingForm(
		$test_id = "", 
		$is_postponed = false, 
		$showsolution = 0, 
		$show_question_page = true, 
		$show_solution_only = false, 
		$ilUser = NULL, 
		$pass = NULL, 
		$mixpass = false
	)
	{
		if (!is_object($ilUser)) 
		{
			global $ilUser;
		}
		$output = $this->outQuestionPage(($show_solution_only)?"":"X_OUT_OF_ALL_QUESTION", $is_postponed, $test_id);
		
		if ($showsolution && !$show_solution_only)
		{
			$solutionintroduction = "<p>" . $this->lng->txt("tst_your_answer_was") . "</p>";
			$output = preg_replace("/(<div[^<]*?ilc_PageTitle.*?<\/div>)/", "\\1" . $solutionintroduction, $output);
		}
		$solutionoutput = preg_replace("/.*?(<div[^<]*?ilc_Question.*?<\/div>).*/", "\\1", $output);
		$solutionoutput = preg_replace("/\"xooa/", "\"solution_xooa", $solutionoutput);
		$solutionoutput = preg_replace("/xooa_result/", "solution_xooa_result", $solutionoutput);
		
		
		if (!$show_question_page)
			$output = preg_replace("/.*?(<div[^<]*?ilc_Question.*?<\/div>).*/", "\\1", $output);

		// if wants solution only then strip the question element from output
		if ($show_solution_only) {
			$output = preg_replace("/(<div[^<]*?ilc_Question[^>]*>.*?<\/div>)/", "", $output);
		}
		
		
			
//		preg_match("/(<div[^<]*?ilc_Question.*?<\/div>)/is", $output, $matches);
//		$solutionoutput = $matches[1];
		// set solutions
		//echo "<br>".htmlentities($output);
		if ($test_id)
		{
			$solutions = NULL;
			include_once "./assessment/classes/class.ilObjTest.php";
			if ((!$showsolution) && ilObjTest::_getHidePreviousResults($test_id, true))
			{
				if (is_null($pass)) $pass = ilObjTest::_getPass($ilUser->id, $test_id);
			}
			if ($mixpass) $pass = NULL;
			$solutions =& $this->object->getSolutionValues($test_id, $ilUser, $pass);
			foreach ($solutions as $idx => $solution_value)
			{
				$repl_str = "dummy=\"xooa".$solution_value["value1"]."\"";
				//echo "<br>".htmlentities($repl_str);
				
				//replace all checked answers with x or checkbox
				if (!$show_question_page) 
				{
					// rku $output = $this->replaceInputElements($repl_str,"X",$output); 
					
					$output = $this->replaceInputElements($repl_str,"X",$output,"(",")"); /* ) preg_replace ("/(<input[^>]*?$repl_str.*?>)/" ,"X", $output); */
				}
				else $output = str_replace($repl_str, $repl_str." checked=\"checked\"", $output);				
			}
			
			// now replace all not-checked checkboxes with an 0
			if (!$show_question_page) 
			{
				// rku $output = $this->replaceInputElements("","O", $output); //)()preg_replace ("/(<input[^>]*>)/" ,"O", $output);			
				$output = $this->replaceInputElements("","O", $output,"(",")"); //)()preg_replace ("/(<input[^>]*>)/" ,"O", $output);
			}
		}

		if ($showsolution) 
		{			
			$maxpoints = 0;
			$maxindex = -1;
			foreach ($this->object->answers as $idx => $answer)
			{
				if ($answer->get_points() > $maxpoints)
				{
					$maxpoints = $answer->get_points();
					$maxindex = $idx;
				}
			}
			foreach ($this->object->answers as $idx => $answer)
			{
				if ($this->object->get_response() == RESPONSE_MULTIPLE)
				{
					if ($answer->isStateChecked() && ($answer->get_points() > 0))
					{
						$repl_str = "dummy=\"solution_xooa$idx\"";
						$solutionoutput = str_replace($repl_str, $repl_str." checked=\"checked\"", $solutionoutput);						
					}
					$sol = '(<em>';
					if ($show_solution_only)
						$sol .= $this->lng->txt("checkbox_checked").' = ';
					else
						$sol .= '<input name="checkbox' . time() . $idx . '" type="checkbox" readonly="readonly" checked="checked" /> = ';
					if ($answer->isStateChecked())
					{
						$sol .= $answer->get_points();
					}
					else
					{
						$sol .= "0";
					}
					$sol .= ' ' . $this->lng->txt("points") . ', ';
					if ($show_solution_only)
						$sol .= $this->lng->txt("checkbox_unchecked").' = ';
					else
						$sol .= '<input name="checkbox' . time() . $idx . '" type="checkbox" readonly="readonly" /> = ';
					if (!$answer->isStateChecked())
					{
						$sol .= $answer->get_points();
					}
					else
					{
						$sol .= "0";
					}
					$sol .= ' ' . $this->lng->txt("points");
					$sol .= '</em>)';
					$solutionoutput = preg_replace("/(<tr.*?dummy=\"solution_xooa$idx"."[^\d].*?)<\/tr>/", "\\1<td>" . $sol . "</td></tr>", $solutionoutput);
					
					if ($show_solution_only) 
						if ($answer->isStateChecked()) 
						{
							$repl_str = "dummy=\"solution_xooa$idx\"";
							$solutionoutput = $this->replaceInputElements ($repl_str, "X", $solutionoutput);						
						} else {
							$repl_str = "dummy=\"solution_xooa$idx\"";
							$solutionoutput = $this->replaceInputElements ($repl_str, "O", $solutionoutput);
						}
				}
				else
				{
					$sol = '(<em>';
					if ($show_solution_only)
						$sol .= $this->lng->txt("checkbox_checked").' = ';
					else
						$sol .= '<input name="radio' . time() . $idx . '" type="radio" readonly="readonly" checked="checked" /> = ';
					if ($answer->isStateChecked())
					{
						$sol .= $answer->get_points();
					}
					else
					{
						$sol .= "0";
					}
					$sol .= ' ' . $this->lng->txt("points") . ', ';
					if ($show_solution_only)
						$sol .= $this->lng->txt("checkbox_unchecked").' = ';
					else
						$sol .= '<input name="radio' . time() . $idx . '" type="radio" readonly="readonly" /> = ';
					
					if (!$answer->isStateChecked())
					{
						$sol .= $answer->get_points();
					}
					else
					{
						$sol .= "0";
					}
					$sol .= ' ' . $this->lng->txt("points");
					$sol .= '</em>)';
					
					$solutionoutput = preg_replace("/(<tr.*?dummy=\"solution_xooa$idx" . "[^\d].*?)<\/tr>/", "\\1<td>" . $sol . "</td></tr>", $solutionoutput);					 				
				}
			}
			if (($maxindex > -1) && ($this->object->get_response() == RESPONSE_SINGLE))
			{
				$repl_str = "dummy=\"solution_xooa$maxindex\"";				
				if ($show_solution_only) 
				{
					// rku $solutionoutput = $this->replaceInputElements($repl_str,"X",$solutionoutput);
					$solutionoutput = $this->replaceInputElements($repl_str,"X",$solutionoutput,"(",")");
				}
				else 
					$solutionoutput = str_replace($repl_str, $repl_str." checked=\"checked\"", $solutionoutput);
			}
			if ($show_solution_only && ($this->object->get_response() == RESPONSE_SINGLE)) 
			{
				if ($maxindex > -1) 
				{
					$repl_str = "dummy=\"solution_mc$maxindex\"";				
					// rku $solutionoutput = $this->replaceInputElements($repl_str,"X",$solutionoutput);
					$solutionoutput = $this->replaceInputElements($repl_str,"X",$solutionoutput,"(",")");
				}
				// rku $solutionoutput = $this->replaceInputElements("","O",$solutionoutput);
				$solutionoutput = $this->replaceInputElements("","O",$solutionoutput,"(",")");
			}

			if (!$show_solution_only)
			{
				$solutionoutput = "<p>" . $this->lng->txt("correct_solution_is") . ":</p><p>$solutionoutput</p>";
			}
 
			if ($test_id) 
			{
				$reached_points = $this->object->getReachedPoints($ilUser->id, $test_id);
				$received_points = "<p>" . sprintf($this->lng->txt("you_received_a_of_b_points"), $reached_points, $this->object->getMaximumPoints());
				$mc_comment = "";
				if (($this->object->get_response() == RESPONSE_MULTIPLE) && ($reached_points == 0))
				{
					$mc_comment = $this->object->getSolutionCommentMCScoring($test_id);
					if (strlen($mc_comment))
					{
						$mc_comment = "<span class=\"asterisk\">*</span><br /><br /><span class=\"asterisk\">*</span>$mc_comment";
					}
				}
				$count_comment = "";
				if ($reached_points == 0)
				{
					$count_comment = $this->object->getSolutionCommentCountSystem($test_id);
					if (strlen($count_comment))
					{
						if (strlen($mc_comment) == 0)
						{
							$count_comment = "<span class=\"asterisk\">*</span><br /><br /><span class=\"asterisk\">*</span>$count_comment";
						}
						else
						{
							$count_comment = "<br /><span class=\"asterisk\">*</span>$count_comment";
						}
					}
				}
				$received_points .= $mc_comment . $count_comment;
				$received_points .= "</p>";
			}
		} 			 // end of show solution

		if (!$showsolution) {
			$solutionoutput="";
			$received_points = "";
		}
		
		$this->tpl->setVariable("MULTIPLE_CHOICE_QUESTION", $output.$solutionoutput.$received_points);
	}

	function addSuggestedSolution()
	{
		$_SESSION["subquestion_index"] = 0;
		if ($_POST["cmd"]["addSuggestedSolution"])
		{
			$this->writePostData();
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
		$this->getQuestionTemplate("qt_xooa");
		parent::addSuggestedSolution();
	}
}
?>

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

include_once "./Modules/TestQuestionPool/classes/class.assQuestionGUI.php";
include_once "./Modules/Test/classes/inc.AssessmentConstants.php";

/**
* Matching question GUI representation
*
* The assMatchingQuestionGUI class encapsulates the GUI representation
* for matching questions.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesTestQuestionPool
*/
class assMatchingQuestionGUI extends assQuestionGUI
{
	/**
	* assMatchingQuestionGUI constructor
	*
	* The constructor takes possible arguments an creates an instance of the assMatchingQuestionGUI object.
	*
	* @param integer $id The database id of a image map question object
	* @access public
	*/
	function assMatchingQuestionGUI(
		$id = -1
	)
	{
		$this->assQuestionGUI();
		include_once "./Modules/TestQuestionPool/classes/class.assMatchingQuestion.php";
		$this->object = new assMatchingQuestion();
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
		if (substr($cmd, 0, 6) == "upload")
		{
			$cmd = "upload";
		}

		return $cmd;
	}

	/**
	* Creates an output of the edit form for the question
	*
	* Creates an output of the edit form for the question
	*
	* @access public
	*/
	function editQuestion($has_error = 0, $delete = false)
	{
		$multiline_answers = $this->object->getMultilineAnswerSetting();
		$this->getQuestionTemplate();
		$this->tpl->addBlockFile("QUESTION_DATA", "question_data", "tpl.il_as_qpl_matching.html", "Modules/TestQuestionPool");

		$tblrow = array("tblrow1top", "tblrow2top");
		// Vorhandene Anworten ausgeben
		for ($i = 0; $i < $this->object->get_matchingpair_count(); $i++)
		{
			$thispair = $this->object->get_matchingpair($i);
			if ($this->object->get_matching_type() == MT_TERMS_PICTURES)
			{
				$this->tpl->setCurrentBlock("pictures");
				$this->tpl->setVariable("ANSWER_ORDER", $i);
				$this->tpl->setVariable("PICTURE_ID", $thispair->getPictureId());
				$this->tpl->setVariable("COLOR_CLASS", $tblrow[$i % 2]);
				$filename = $thispair->getPicture();
				if ($filename)
				{
					$imagepath = $this->object->getImagePathWeb() . $thispair->getPicture();
					$this->tpl->setVariable("UPLOADED_IMAGE", "<img src=\"$imagepath.thumb.jpg\" alt=\"" . $this->lng->txt("qpl_display_fullsize_image") . "\" title=\"" . $this->lng->txt("qpl_display_fullsize_image") . "\" border=\"\" />");
					$this->tpl->setVariable("IMAGE_FILENAME", $thispair->getPicture());
					$this->tpl->setVariable("VALUE_PICTURE", $thispair->getPicture());
				}
				$this->tpl->setVariable("UPLOAD", $this->lng->txt("upload"));
			}
			elseif ($this->object->get_matching_type() == MT_TERMS_DEFINITIONS)
			{
				$this->tpl->setCurrentBlock("definitions");
				$this->tpl->setVariable("ANSWER_ORDER", $i);
				$this->tpl->setVariable("DEFINITION_ID", $thispair->getDefinitionId());
				$this->tpl->setVariable("VALUE_DEFINITION", ilUtil::prepareFormOutput($thispair->getDefinition()));
				$this->tpl->setVariable("COLOR_CLASS", $tblrow[$i % 2]);
			}
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("answers");
			$this->tpl->setVariable("VALUE_ANSWER_COUNTER", $i + 1);
			$this->tpl->setVariable("ANSWER_ORDER", $i);
			$this->tpl->setVariable("TERM_ID", $thispair->getTermId());
			$this->tpl->setVariable("VALUE_TERM", ilUtil::prepareFormOutput($thispair->getTerm()));
			$this->tpl->setVariable("TEXT_MATCHES", $this->lng->txt("matches"));
			$this->tpl->setVariable("VALUE_MATCHINGPAIR_POINTS", $thispair->getPoints());
			$this->tpl->setVariable("COLOR_CLASS", $tblrow[$i % 2]);
			$this->tpl->parseCurrentBlock();
		}
		
		if ($this->object->get_matching_type() == MT_TERMS_DEFINITIONS)
		{
			/*
			$this->tpl->setCurrentBlock("multiline_answers");
			if ($multiline_answers)
			{
				$this->tpl->setVariable("SELECTED_SHOW_MULTILINE_ANSWERS", " selected=\"selected\"");
			}
			$this->tpl->setVariable("TEXT_HIDE_MULTILINE_ANSWERS", $this->lng->txt("multiline_definitions_hide"));
			$this->tpl->setVariable("TEXT_SHOW_MULTILINE_ANSWERS", $this->lng->txt("multiline_definitions_show"));
			$this->tpl->parseCurrentBlock();
			*/
		}
		
		if ($this->object->get_matchingpair_count())
		{
			$this->tpl->setCurrentBlock("answerhead");
			$this->tpl->setVariable("TEXT_POINTS", $this->lng->txt("points"));
			$this->tpl->setVariable("TERM", $this->lng->txt("term"));
			if ($this->object->get_matching_type() == MT_TERMS_PICTURES)
			{
				$this->tpl->setVariable("PICTURE_OR_DEFINITION", $this->lng->txt("picture"));
			}
			else
			{
				$this->tpl->setVariable("PICTURE_OR_DEFINITION", $this->lng->txt("definition"));
			}
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("selectall");
			$this->tpl->setVariable("SELECT_ALL", $this->lng->txt("select_all"));
			$i++;
			$this->tpl->setVariable("COLOR_CLASS", $tblrow[$i % 2]);
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("QFooter");
			$this->tpl->setVariable("ARROW", "<img src=\"" . ilUtil::getImagePath("arrow_downright.gif") . "\" alt=\"".$this->lng->txt("arrow_downright")."\"/>");
			$this->tpl->setVariable("DELETE", $this->lng->txt("delete"));
			$this->tpl->parseCurrentBlock();
		}
		// call to other question data i.e. estimated working time block
		$this->outOtherQuestionData();

		// Check the creation of new answer text fields
		$allow_add_pair = 1;
		foreach ($_POST as $key => $value)
		{
			if (preg_match("/(term|picture|definition)_(\d+)_(\d+)/", $key, $matches))
			{
				if (!$value)
				{
					$allow_add_pair = 0;
				}
			}
		}
		$add_random_id = "";
		if (($this->ctrl->getCmd() == "addPair") and $allow_add_pair and (!$has_error))
		{
			$i++;
			// Template für neue Antwort erzeugen
			if ($this->object->get_matching_type() == MT_TERMS_PICTURES)
			{
				$this->tpl->setCurrentBlock("pictures");
				$this->tpl->setVariable("ANSWER_ORDER", $this->object->get_matchingpair_count());
				$this->tpl->setVariable("PICTURE_ID", $this->object->get_random_id());
				$this->tpl->setVariable("VALUE_PICTURE", "");
				$this->tpl->setVariable("UPLOAD", $this->lng->txt("upload"));
			}
			elseif ($this->object->get_matching_type() == MT_TERMS_DEFINITIONS)
			{
				$this->tpl->setCurrentBlock("definitions");
				$this->tpl->setVariable("ANSWER_ORDER", $this->object->get_matchingpair_count());
				$this->tpl->setVariable("DEFINITION_ID", $this->object->get_random_id());
				$this->tpl->setVariable("VALUE_DEFINITION", "");
			}
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("answers");
			$this->tpl->setVariable("TEXT_MATCHING_PAIR", $this->lng->txt("matching_pair"));
			$this->tpl->setVariable("VALUE_ANSWER_COUNTER", $this->object->get_matchingpair_count() + 1);
			$this->tpl->setVariable("TEXT_MATCHES", $this->lng->txt("matches"));
			$this->tpl->setVariable("ANSWER_ORDER", $this->object->get_matchingpair_count());
			$add_random_id = $this->object->get_random_id();
			$this->tpl->setVariable("TERM_ID", $add_random_id);
			$this->tpl->setVariable("VALUE_MATCHINGPAIR_POINTS", sprintf("%s", 0));
			$this->tpl->setVariable("COLOR_CLASS", $tblrow[$i % 2]);
			$this->tpl->parseCurrentBlock();
		}
		else if ($this->ctrl->getCmd() == "addPair")
		{
			$this->error .= $this->lng->txt("fill_out_all_matching_pairs") . "<br />";
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
		
		$this->tpl->setCurrentBlock("HeadContent");
		$this->tpl->addJavascript("./Services/JavaScript/js/Basic.js");
		$javascript = "<script type=\"text/javascript\">ilAddOnLoad(initialSelect);\n".
			"function initialSelect() {\n%s\n}</script>";
		if ($delete)
		{
			if ($this->object->get_matchingpair_count() > 0)
			{
				$thispair = $this->object->get_matchingpair($this->object->get_matchingpair_count()-1);
				$this->tpl->setVariable("CONTENT_BLOCK", sprintf($javascript, "document.frm_matching.term_".($this->object->get_matchingpair_count()-1)."_" . $thispair->getTermId().".focus(); document.frm_matching.term_".($this->object->get_matchingpair_count()-1)."_" . $thispair->getTermId().".scrollIntoView(\"true\");"));
			}
			else
			{
				$this->tpl->setVariable("CONTENT_BLOCK", sprintf($javascript, "document.frm_matching.title.focus();"));
			}
		}
		else
		{
			switch ($this->ctrl->getCmd())
			{
				case "addPair":
					$this->tpl->setVariable("CONTENT_BLOCK", sprintf($javascript, "document.frm_matching.term_".($this->object->get_matchingpair_count())."_" . $add_random_id.".focus(); document.frm_matching.term_".($this->object->get_matchingpair_count())."_" . $add_random_id.".scrollIntoView(\"true\");"));
					break;
				default:
					$this->tpl->setVariable("CONTENT_BLOCK", sprintf($javascript, "document.frm_matching.title.focus();"));
					break;
			}
		}
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("question_data");
		$this->tpl->setVariable("TEXT_TITLE", $this->lng->txt("title"));
		$this->tpl->setVariable("TEXT_AUTHOR", $this->lng->txt("author"));
		$this->tpl->setVariable("TEXT_COMMENT", $this->lng->txt("description"));
		$this->tpl->setVariable("TEXT_QUESTION", $this->lng->txt("question"));
		$this->tpl->setVariable("TEXT_SHUFFLE_ANSWERS", $this->lng->txt("shuffle_answers"));
		$this->tpl->setVariable("TXT_SHUFFLE_TERMS", $this->lng->txt("matching_shuffle_terms"));
		if ($this->object->get_matching_type() == MT_TERMS_DEFINITIONS)
		{
			$this->tpl->setVariable("TXT_YES", $this->lng->txt("matching_shuffle_terms_definitions"));
			$this->tpl->setVariable("TXT_SHUFFLE_PICTURES", $this->lng->txt("matching_shuffle_definitions"));
		}
		else
		{
			$this->tpl->setVariable("TXT_YES", $this->lng->txt("matching_shuffle_terms_pictures"));
			$this->tpl->setVariable("TXT_SHUFFLE_PICTURES", $this->lng->txt("matching_shuffle_pictures"));
		}
		$this->tpl->setVariable("TXT_NO", $this->lng->txt("no"));
		switch ($this->object->getShuffle())
		{
			case 1:
				$this->tpl->setVariable("SELECTED_YES", " selected=\"selected\"");
				break;
			case 2:
				$this->tpl->setVariable("SELECTED_SHUFFLE_TERMS", " selected=\"selected\"");
				break;
			case 3:
				$this->tpl->setVariable("SELECTED_SHUFFLE_PICTURES", " selected=\"selected\"");
				break;
			default:
				$this->tpl->setVariable("SELECTED_NO", " selected=\"selected\"");
				break;
		}
		$this->tpl->setVariable("MATCHING_ID", $this->object->getId());
		$this->tpl->setVariable("VALUE_MATCHING_TITLE", ilUtil::prepareFormOutput($this->object->getTitle()));
		$this->tpl->setVariable("VALUE_MATCHING_COMMENT", ilUtil::prepareFormOutput($this->object->getComment()));
		$this->tpl->setVariable("VALUE_MATCHING_AUTHOR", ilUtil::prepareFormOutput($this->object->getAuthor()));
		$questiontext = $this->object->getQuestion();
		$this->tpl->setVariable("VALUE_QUESTION", ilUtil::prepareFormOutput($this->object->prepareTextareaOutput($questiontext)));
		$this->tpl->setVariable("VALUE_ADD_ANSWER", $this->lng->txt("add_matching_pair"));
		$this->tpl->setVariable("TEXT_TYPE", $this->lng->txt("type"));
		$this->tpl->setVariable("TEXT_TYPE_TERMS_PICTURES", $this->lng->txt("match_terms_and_pictures"));
		$this->tpl->setVariable("TEXT_TYPE_TERMS_DEFINITIONS", $this->lng->txt("match_terms_and_definitions"));
		if ($this->object->get_matching_type() == MT_TERMS_DEFINITIONS)
		{
			$this->tpl->setVariable("SELECTED_DEFINITIONS", " selected=\"selected\"");
		}
		elseif ($this->object->get_matching_type() == MT_TERMS_PICTURES)
		{
			$this->tpl->setVariable("SELECTED_PICTURES", " selected=\"selected\"");
		}
		$this->tpl->setVariable("TEXT_SOLUTION_HINT", $this->lng->txt("solution_hint"));
		if (count($this->object->suggested_solutions))
		{
			$solution_array = $this->object->getSuggestedSolution(0);
			include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
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
		$this->tpl->setVariable("SAVE", $this->lng->txt("save"));
		$this->tpl->setVariable("SET_EDIT_MODE", $this->lng->txt("set_edit_mode"));
		$this->tpl->setVariable("SAVE_EDIT", $this->lng->txt("save_edit"));
		$this->tpl->setVariable("CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		$this->ctrl->setParameter($this, "sel_question_types", "assMatchingQuestion");
		$this->tpl->setVariable("TEXT_QUESTION_TYPE", $this->lng->txt("assMatchingQuestion"));
		$this->tpl->setVariable("ACTION_MATCHING_QUESTION",	$this->ctrl->getFormAction($this));

		$this->tpl->parseCurrentBlock();
		if ($this->error)
		{
			ilUtil::sendInfo($this->error);
		}
		include_once "./Services/RTE/classes/class.ilRTE.php";
		$rtestring = ilRTE::_getRTEClassname();
		include_once "./Services/RTE/classes/class.$rtestring.php";
		$rte = new $rtestring();
		$rte->addPlugin("latex");
		$rte->addButton("latex");
		include_once "./classes/class.ilObject.php";
		$obj_id = $_GET["q_id"];
		$obj_type = ilObject::_lookupType($_GET["ref_id"], TRUE);
		$rte->addRTESupport($obj_id, $obj_type, "assessment");
		$this->tpl->setCurrentBlock("adm_content");
		//$this->tpl->setVariable("BODY_ATTRIBUTES", " onload=\"initialSelect();\""); 
		$this->tpl->parseCurrentBlock();
	}

	/**
	* add matching pair
	*/
	function addPair()
	{
		$result = $this->writePostData();
		$this->editQuestion($result);
	}

	/**
	* upload matching picture or material
	*/
	function upload()
	{
		$this->writePostData();
		$this->editQuestion();
	}


	/**
	* delete matching pair
	*/
	function delete()
	{
		$this->writePostData();
		if (is_array($_POST["chb_answer"]))
		{
			$deleteanswers = $_POST["chb_answer"];
			rsort($deleteanswers);
			foreach ($deleteanswers as $value)
			{
				$this->object->delete_matchingpair($value);
			}
		}
		$this->editQuestion(0, true);
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
		$saved = false;
		$result = 0;

		if (!$this->checkInput())
		{
			$result = 1;
		}

		if (($result) and ($_POST["cmd"]["addPair"]))
		{
			// You cannot add matching pairs before you enter the required data
			$this->error .= $this->lng->txt("fill_out_all_required_fields_add_matching") . "<br />";
		}

		$this->object->setTitle(ilUtil::stripSlashes($_POST["title"]));
		$this->object->setAuthor(ilUtil::stripSlashes($_POST["author"]));
		$this->object->setComment(ilUtil::stripSlashes($_POST["comment"]));
		include_once "./classes/class.ilObjAdvancedEditing.php";
		$questiontext = ilUtil::stripSlashes($_POST["question"], false, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("assessment"));
		$this->object->setQuestion($questiontext);
		$this->object->setSuggestedSolution($_POST["solution_hint"], 0);
		$this->object->setShuffle($_POST["shuffle"]);
		// adding estimated working time
		$saved = $saved | $this->writeOtherPostData($result);
		$this->object->setMatchingType($_POST["matching_type"]);
		//$this->object->setMultilineAnswerSetting($_POST["multilineAnswers"]);

		// Delete all existing answers and create new answers from the form data
		$this->object->flush_matchingpairs();
		$saved = false;

		// Add all answers from the form into the object
		$postvalues = $_POST;
		foreach ($postvalues as $key => $value)
		{
			$matching_text = "";
			if (preg_match("/term_(\d+)_(\d+)/", $key, $matches))
			{
				// find out random id for term
				foreach ($_POST as $key2 => $value2)
				{
					if (preg_match("/(definition|picture)_$matches[1]_(\d+)/", $key2, $matches2))
					{
						$matchingtext_id = $matches2[2];
						if (strcmp($matches2[1], "definition") == 0)
						{
							$matching_text = $_POST["definition_$matches[1]_$matches2[2]"];
						}
						else
						{
							$matching_text = $_POST["picture_$matches[1]_$matches2[2]"];
						}
					}
				}
				
				// save picture file if matching terms and pictures
				if ($this->object->get_matching_type() == MT_TERMS_PICTURES)
				{
					foreach ($_FILES as $key2 => $value2)
					{
						if (preg_match("/picture_$matches[1]_(\d+)/", $key2, $matches2))
						{
							if ($value2["tmp_name"])
							{
								// upload the matching picture
								if ($this->object->getId() <= 0)
								{
									$this->object->saveToDb();
									$saved = true;
									$this->error .= $this->lng->txt("question_saved_for_upload") . "<br />";
								}
								$value2['name'] = $this->object->createNewImageFileName($value2['name']);
								$upload_result = $this->object->setImageFile($value2['name'], $value2['tmp_name']);
								switch ($upload_result)
								{
									case 0:
										$_POST["picture_$matches[1]_".$matches2[1]] = $value2['name'];
										$matching_text = $value2['name'];
										break;
									case 1:
										$this->error .= $this->lng->txt("error_image_upload_wrong_format") . "<br />";
										break;
									case 2:
										$this->error .= $this->lng->txt("error_image_upload_copy_file") . "<br />";
										break;
								}
							}
						}
					}
				}
				$points = $_POST["points_$matches[1]"];
				if ($points < 0)
				{
					$result = 1;
					$this->setErrorMessage($this->lng->txt("negative_points_not_allowed"));
				}
				$this->object->add_matchingpair(
					ilUtil::stripSlashes($_POST["$key"]),
					ilUtil::stripSlashes($matching_text),
					ilUtil::stripSlashes($points),
					ilUtil::stripSlashes($matches[2]),
					ilUtil::stripSlashes($matchingtext_id)
				);
			}
		}

		if ($saved)
		{
			// If the question was saved automatically before an upload, we have to make
			// sure, that the state after the upload is saved. Otherwise the user could be
			// irritated, if he presses cancel, because he only has the question state before
			// the upload process.
			$this->object->saveToDb();
			$this->ctrl->setParameter($this, "q_id", $this->object->getId());
		}
		return $result;
	}

	function outQuestionForTest($formaction, $active_id, $pass = NULL, $is_postponed = FALSE, $user_post_solution = FALSE)
	{
		$test_output = $this->getTestOutput($active_id, $pass, $is_postponed, $user_post_solution); 
		$this->tpl->setVariable("QUESTION_OUTPUT", $test_output);
		if ($this->object->getOutputType() == OUTPUT_JAVASCRIPT)
		{
			$this->tpl->setVariable("BODY_ATTRIBUTES", " onload=\"setDragelementPositions();show_solution();\"");
		}
		$this->tpl->setVariable("FORMACTION", $formaction);
	}

	function getSolutionOutput($active_id, $pass = NULL, $graphicalOutput = FALSE, $result_output = FALSE, $show_question_only = TRUE, $show_feedback = FALSE)
	{
		// generate the question output
		include_once "./classes/class.ilTemplate.php";
		$template = new ilTemplate("tpl.il_as_qpl_matching_output_solution.html", TRUE, TRUE, "Modules/TestQuestionPool");
		$solutiontemplate = new ilTemplate("tpl.il_as_tst_solution_output.html",TRUE, TRUE, "Modules/TestQuestionPool");
		
		$keys = array_keys($this->object->matchingpairs);

		$solutions = array();
		if ($active_id)
		{
			include_once "./Modules/Test/classes/class.ilObjTest.php";
			$solutions =& $this->object->getSolutionValues($active_id, $pass);
			$solution_script .= "";
		}
		else
		{
			foreach ($this->object->matchingpairs as $pair)
			{
				array_push($solutions, array("value1" => $pair->getTermId(), "value2" => $pair->getDefinitionId()));
			}
		}
		foreach ($keys as $idx)
		{
			$answer = $this->object->matchingpairs[$idx];
			if ($active_id)
			{
				if ($graphicalOutput)
				{
					// output of ok/not ok icons for user entered solutions
					$ok = FALSE;
					foreach ($solutions as $solution)
					{
						if ($answer->getDefinitionId() == $solution["value2"])
						{
							if ($answer->getTermId() == $solution["value1"])
							{
								$ok = TRUE;
							}
						}
					}
					if ($ok)
					{
						$template->setCurrentBlock("icon_ok");
						$template->setVariable("ICON_OK", ilUtil::getImagePath("icon_ok.gif"));
						$template->setVariable("TEXT_OK", $this->lng->txt("answer_is_right"));
						$template->parseCurrentBlock();
					}
					else
					{
						$template->setCurrentBlock("icon_ok");
						$template->setVariable("ICON_NOT_OK", ilUtil::getImagePath("icon_not_ok.gif"));
						$template->setVariable("TEXT_NOT_OK", $this->lng->txt("answer_is_wrong"));
						$template->parseCurrentBlock();
					}
				}
			}

			if ($this->object->get_matching_type() == MT_TERMS_PICTURES)
			{
				$template->setCurrentBlock("standard_matching_pictures");
				$template->setVariable("DEFINITION_ID", $answer->getPictureId());
				$size = getimagesize($this->object->getImagePath() . $answer->getPicture()  . ".thumb.jpg");
				$template->setVariable("THUMBNAIL_WIDTH", $size[0]);
				$template->setVariable("THUMBNAIL_HEIGHT", $size[1]);
				$template->setVariable("THUMBNAIL_HREF", $this->object->getImagePathWeb() . $answer->getPicture() . ".thumb.jpg");
				$template->setVariable("THUMB_ALT", $this->lng->txt("image"));
				$template->setVariable("THUMB_TITLE", $this->lng->txt("image"));
				$template->parseCurrentBlock();
			}
			else
			{
				$template->setCurrentBlock("standard_matching_terms");
				$template->setVariable("DEFINITION", $this->object->prepareTextareaOutput($answer->getDefinition(), TRUE));
				$template->parseCurrentBlock();
			}

			$template->setCurrentBlock("standard_matching_row");
			$template->setVariable("MATCHES", $this->lng->txt("matches"));
			if ($result_output)
			{
				$points = $answer->getPoints();
				$resulttext = ($points == 1) ? "(%s " . $this->lng->txt("point") . ")" : "(%s " . $this->lng->txt("points") . ")"; 
				$template->setVariable("RESULT_OUTPUT", sprintf($resulttext, $points));
			}
			$hasoutput = FALSE;
			foreach ($solutions as $solution)
			{
				if ($answer->getDefinitionId() == $solution["value2"])
				{
					foreach ($this->object->matchingpairs as $pair)
					{
						if ($pair->getTermId() == $solution["value1"])
						{
							$template->setVariable("SOLUTION", ilUtil::prepareFormOutput($pair->getTerm()));
							$hasoutput = TRUE;
						}
					}
				}
			}
			if (!$hasoutput)
			{
				$template->setVariable("SOLUTION", "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;");
			}
			$template->parseCurrentBlock();
		}
		
		$questiontext = $this->object->getQuestion();
		$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($questiontext, TRUE));
		$questionoutput = $template->get();
		$feedback = ($show_feedback) ? $this->getAnswerFeedbackOutput($active_id, $pass) : "";
		if (strlen($feedback)) $solutiontemplate->setVariable("FEEDBACK", $feedback);
		$solutiontemplate->setVariable("SOLUTION_OUTPUT", $questionoutput);

		$solutionoutput = $solutiontemplate->get(); 
		if (!$show_question_only)
		{
			// get page object output
			$pageoutput = $this->getILIASPage();
			$solutionoutput = preg_replace("/(\<div( xmlns:xhtml\=\"http:\/\/www.w3.org\/1999\/xhtml\"){0,1} class\=\"ilc_Question\">\<\/div>)/ims", "<div class=\"ilc_Question\">" . $solutionoutput . "</div>", $pageoutput);
		}
		return $solutionoutput;
	}
	
	function getPreview($show_question_only = FALSE)
	{
		// generate the question output
		include_once "./classes/class.ilTemplate.php";
		$template = new ilTemplate("tpl.il_as_qpl_matching_output.html", TRUE, TRUE, "Modules/TestQuestionPool");
		
		// shuffle output
		$keys = array_keys($this->object->matchingpairs);
		$keys2 = $keys;
		if ($this->object->getShuffle())
		{
			if (($this->object->getShuffle() == 3) || ($this->object->getShuffle() == 1))
				$keys = $this->object->pcArrayShuffle(array_keys($this->object->matchingpairs));
			if (($this->object->getShuffle() == 2) || ($this->object->getShuffle() == 1))
				$keys2 = $this->object->pcArrayShuffle(array_keys($this->object->matchingpairs));
		}

		foreach ($keys as $idx)
		{
			$answer = $this->object->matchingpairs[$idx];
			foreach ($keys2 as $comboidx)
			{
				$comboanswer = $this->object->matchingpairs[$comboidx];
				$template->setCurrentBlock("matching_selection");
				$template->setVariable("VALUE_SELECTION", $comboanswer->getTermId());
				$template->setVariable("TEXT_SELECTION", ilUtil::prepareFormOutput($comboanswer->getTerm()));
				$template->parseCurrentBlock();
			}
			if ($this->object->get_matching_type() == MT_TERMS_PICTURES)
			{
				$template->setCurrentBlock("standard_matching_pictures");
				$template->setVariable("DEFINITION_ID", $answer->getPictureId());
				$template->setVariable("IMAGE_HREF", $this->object->getImagePathWeb() . $answer->getPicture());
				$template->setVariable("THUMBNAIL_HREF", $this->object->getImagePathWeb() . $answer->getPicture() . ".thumb.jpg");
				$template->setVariable("THUMB_ALT", $this->lng->txt("image"));
				$template->setVariable("THUMB_TITLE", $this->lng->txt("image"));
				$template->parseCurrentBlock();
			}
			else
			{
				$template->setCurrentBlock("standard_matching_terms");
				$template->setVariable("DEFINITION", $this->object->prepareTextareaOutput($answer->getDefinition(), TRUE));
				$template->parseCurrentBlock();
			}

			$template->setCurrentBlock("standard_matching_row");
			$template->setVariable("MATCHES", $this->lng->txt("matches"));
			$template->setVariable("DEFINITION_ID", $answer->getDefinitionId());
			$template->setVariable("PLEASE_SELECT", $this->lng->txt("please_select"));
			$template->parseCurrentBlock();
		}
		
		$questiontext = $this->object->getQuestion();
		$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($questiontext, TRUE));
		$questionoutput = $template->get();
		if (!$show_question_only)
		{
			// get page object output
			$pageoutput = $this->getILIASPage();
			$questionoutput = preg_replace("/(\<div( xmlns:xhtml\=\"http:\/\/www.w3.org\/1999\/xhtml\"){0,1} class\=\"ilc_Question\">\<\/div>)/ims", $questionoutput, $pageoutput);
		}
		else
		{
			$questionoutput = preg_replace("/\<div[^>]*?>(.*)\<\/div>/is", "\\1", $questionoutput);
		}

		return $questionoutput;
	}

	function getTestOutput($active_id, $pass = NULL, $is_postponed = FALSE, $user_post_solution = FALSE)
	{
		// get page object output
		$pageoutput = $this->outQuestionPage("", $is_postponed, $active_id);

		// generate the question output
		include_once "./classes/class.ilTemplate.php";
		$template = new ilTemplate("tpl.il_as_qpl_matching_output.html", TRUE, TRUE, "Modules/TestQuestionPool");
		
		// shuffle output
		$keys = array_keys($this->object->matchingpairs);
		$keys2 = $keys;
		if (is_array($user_post_solution))
		{
			$keys = $_SESSION["matching_keys"];
			$keys2 = $_SESSION["matching_keys2"];
		}
		else
		{
			if ($this->object->getShuffle())
			{
				if (($this->object->getShuffle() == 3) || ($this->object->getShuffle() == 1))
					$keys = $this->object->pcArrayShuffle(array_keys($this->object->matchingpairs));
				if (($this->object->getShuffle() == 2) || ($this->object->getShuffle() == 1))
					$keys2 = $this->object->pcArrayShuffle(array_keys($this->object->matchingpairs));
			}
		}
		$_SESSION["matching_keys"] = $keys;
		$_SESSION["matching_keys2"] = $keys2;

		if ($active_id)
		{
			$solutions = NULL;
			include_once "./Modules/Test/classes/class.ilObjTest.php";
			if (!ilObjTest::_getUsePreviousAnswers($active_id, true))
			{
				if (is_null($pass)) $pass = ilObjTest::_getPass($active_id);
			}
			if (is_array($user_post_solution)) 
			{ 
				$solutions = array();
				foreach ($user_post_solution as $key => $value)
				{
					if (preg_match("/sel_matching_(\d+)/", $key, $matches))
					{
						array_push($solutions, array("value1" => $value, "value2" => $matches[1]));
					}
				}
			}
			else
			{ 
				$solutions =& $this->object->getSolutionValues($active_id, $pass);
			}
			$solution_script .= "";
			foreach ($solutions as $idx => $solution_value)
			{
				if ($this->object->getOutputType() == OUTPUT_JAVASCRIPT)
				{
					if (($solution_value["value2"] > 1) && ($solution_value["value1"] > 1))
					{
						$solution_script .= "addSolution(" . $solution_value["value1"] . "," . $solution_value["value2"] . ");\n";
					}
				}
			}
		}
		if ($this->object->getOutputType() == OUTPUT_JAVASCRIPT)
		{
			foreach ($keys as $idx)
			{
				$answer = $this->object->matchingpairs[$idx];
				$template->setCurrentBlock("dragelements");
				$template->setVariable("DRAGELEMENT", $answer->getDefinitionId());
				$template->parseCurrentBlock();
				$template->setCurrentBlock("dropzones");
				$template->setVariable("DROPZONE", $answer->getTermId());
				$template->parseCurrentBlock();
				$template->setCurrentBlock("hidden_values");
				$template->setVariable("MATCHING_ID", $answer->getDefinitionId());
				$template->parseCurrentBlock();
			}

			foreach ($keys as $arrayindex => $idx)
			{
				$answer = $this->object->matchingpairs[$idx];
				if ($this->object->get_matching_type() == MT_TERMS_PICTURES)
				{
					$template->setCurrentBlock("matching_pictures");
					$template->setVariable("DEFINITION_ID", $answer->getPictureId());
					$template->setVariable("IMAGE_HREF", $this->object->getImagePathWeb() . $answer->getPicture());
					$template->setVariable("THUMBNAIL_HREF", $this->object->getImagePathWeb() . $answer->getPicture() . ".thumb.jpg");
					$template->setVariable("THUMB_ALT", $this->lng->txt("image"));
					$template->setVariable("THUMB_TITLE", $this->lng->txt("image"));
					$template->setVariable("ENLARGE_HREF", ilUtil::getImagePath("enlarge.gif", false));
					$template->setVariable("ENLARGE_ALT", $this->lng->txt("enlarge"));
					$template->setVariable("ENLARGE_TITLE", $this->lng->txt("enlarge"));
					$template->parseCurrentBlock();
				}
				else
				{
					$template->setCurrentBlock("matching_terms");
					$template->setVariable("DEFINITION_ID", $answer->getDefinitionId());
					$template->setVariable("DEFINITION_TEXT", $this->object->prepareTextareaOutput($answer->getDefinition(), TRUE));
					$template->parseCurrentBlock();
				}
				$template->setCurrentBlock("javascript_matching_row");
				$template->setVariable("MATCHES", $this->lng->txt("matches"));
				$shuffledTerm = $this->object->matchingpairs[$keys2[$arrayindex]];
				$template->setVariable("DROPZONE_ID", $shuffledTerm->getTermId());
				$template->setVariable("TERM_ID", $shuffledTerm->getTermId());
				$template->setVariable("TERM_TEXT", $shuffledTerm->getTerm());
				$template->parseCurrentBlock();
			}
			
			if ($this->object->get_matching_type() == MT_TERMS_PICTURES)
			{
				$template->setVariable("RESET_TEXT", $this->lng->txt("reset_pictures"));
			}
			else
			{
				$template->setVariable("RESET_TEXT", $this->lng->txt("reset_definitions"));
			}
			$template->setVariable("JAVASCRIPT_HINT", $this->lng->txt("matching_question_javascript_hint"));
			$template->setVariable("SHOW_SOLUTIONS", "<script type=\"text/javascript\">\nfunction show_solution() {\n$solution_script\n}\n</script>\n");
		}
		else
		{
			foreach ($keys as $idx)
			{
				$answer = $this->object->matchingpairs[$idx];
				foreach ($keys2 as $comboidx)
				{
					$comboanswer = $this->object->matchingpairs[$comboidx];
					$template->setCurrentBlock("matching_selection");
					$template->setVariable("VALUE_SELECTION", $comboanswer->getTermId());
					$template->setVariable("TEXT_SELECTION", ilUtil::prepareFormOutput($comboanswer->getTerm()));
					foreach ($solutions as $solution)
					{
						if (($comboanswer->getTermId() == $solution["value1"]) && ($answer->getDefinitionId() == $solution["value2"]))
						{
							$template->setVariable("SELECTED_SELECTION", " selected=\"selected\"");
						}
					}
					$template->parseCurrentBlock();
				}
				if ($this->object->get_matching_type() == MT_TERMS_PICTURES)
				{
					$template->setCurrentBlock("standard_matching_pictures");
					$template->setVariable("DEFINITION_ID", $answer->getPictureId());
					$template->setVariable("IMAGE_HREF", $this->object->getImagePathWeb() . $answer->getPicture());
					$template->setVariable("THUMBNAIL_HREF", $this->object->getImagePathWeb() . $answer->getPicture() . ".thumb.jpg");
					$template->setVariable("THUMB_ALT", $this->lng->txt("image"));
					$template->setVariable("THUMB_TITLE", $this->lng->txt("image"));
					$template->parseCurrentBlock();
				}
				else
				{
					$template->setCurrentBlock("standard_matching_terms");
					$template->setVariable("DEFINITION", $this->object->prepareTextareaOutput($answer->getDefinition(), TRUE));
					$template->parseCurrentBlock();
				}

				$template->setCurrentBlock("standard_matching_row");
				$template->setVariable("MATCHES", $this->lng->txt("matches"));
				$template->setVariable("DEFINITION_ID", $answer->getDefinitionId());
				$template->setVariable("PLEASE_SELECT", $this->lng->txt("please_select"));
				$template->parseCurrentBlock();
			}
		}
		
		$questiontext = $this->object->getQuestion();
		$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($questiontext, TRUE));
		$questionoutput = $template->get();
		$questionoutput = preg_replace("/(\<div( xmlns:xhtml\=\"http:\/\/www.w3.org\/1999\/xhtml\"){0,1} class\=\"ilc_Question\">\<\/div>)/ims", $questionoutput, $pageoutput);
		return $questionoutput;
	}

	/**
	* check input fields
	*/
	function checkInput()
	{
		if ((!$_POST["title"]) or (!$_POST["author"]) or (!$_POST["question"]))
		{
			return false;
		}
		return true;
	}


	function addSuggestedSolution()
	{
		$_SESSION["subquestion_index"] = 0;
		if ($_POST["cmd"]["addSuggestedSolution"])
		{
			if ($this->writePostData())
			{
				ilUtil::sendInfo($this->getErrorMessage());
				$this->editQuestion();
				return;
			}
			if (!$this->checkInput())
			{
				ilUtil::sendInfo($this->lng->txt("fill_out_all_required_fields_add_answer"));
				$this->editQuestion();
				return;
			}
		}
		$this->object->saveToDb();
		$this->ctrl->setParameter($this, "q_id", $this->object->getId());
		$this->tpl->setVariable("HEADER", $this->object->getTitle());
		$this->getQuestionTemplate();
		parent::addSuggestedSolution();
	}	

	function editMode()
	{
		global $ilUser;
		
		//$this->object->setMultilineAnswerSetting($_POST["multilineAnswers"]);
		$this->object->setMatchingType($_POST["matching_type"]);
		$this->writePostData();
		$this->editQuestion();
	}

	/**
	* Saves the feedback for a single choice question
	*
	* Saves the feedback for a single choice question
	*
	* @access public
	*/
	function saveFeedback()
	{
		include_once "./classes/class.ilObjAdvancedEditing.php";
		$this->object->saveFeedbackGeneric(0, ilUtil::stripSlashes($_POST["feedback_incomplete"], false, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("assessment")));
		$this->object->saveFeedbackGeneric(1, ilUtil::stripSlashes($_POST["feedback_complete"], false, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("assessment")));
		$this->object->cleanupMediaObjectUsage();
		parent::saveFeedback();
	}

	/**
	* Creates the output of the feedback page for a single choice question
	*
	* Creates the output of the feedback page for a single choice question
	*
	* @access public
	*/
	function feedback()
	{
		$this->tpl->addBlockFile("ADM_CONTENT", "feedback", "tpl.il_as_qpl_matching_feedback.html", "Modules/TestQuestionPool");
		$this->tpl->setVariable("FEEDBACK_TEXT", $this->lng->txt("feedback"));
		$this->tpl->setVariable("FEEDBACK_COMPLETE", $this->lng->txt("feedback_complete_solution"));
		$this->tpl->setVariable("VALUE_FEEDBACK_COMPLETE", ilUtil::prepareFormOutput($this->object->prepareTextareaOutput($this->object->getFeedbackGeneric(1)), FALSE));
		$this->tpl->setVariable("FEEDBACK_INCOMPLETE", $this->lng->txt("feedback_incomplete_solution"));
		$this->tpl->setVariable("VALUE_FEEDBACK_INCOMPLETE", ilUtil::prepareFormOutput($this->object->prepareTextareaOutput($this->object->getFeedbackGeneric(0)), FALSE));
		$this->tpl->setVariable("SAVE", $this->lng->txt("save"));
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

		include_once "./Services/RTE/classes/class.ilRTE.php";
		$rtestring = ilRTE::_getRTEClassname();
		include_once "./Services/RTE/classes/class.$rtestring.php";
		$rte = new $rtestring();
		$rte->addPlugin("latex");
		$rte->addButton("latex");
		include_once "./classes/class.ilObject.php";
		$obj_id = $_GET["q_id"];
		$obj_type = ilObject::_lookupType($_GET["ref_id"], TRUE);
		$rte->addRTESupport($obj_id, $obj_type, "assessment");
	}

	/**
	* Sets the ILIAS tabs for this question type
	*
	* Sets the ILIAS tabs for this question type
	*
	* @access public
	*/
	function setQuestionTabs()
	{
		global $rbacsystem, $ilTabs;
		
		$this->ctrl->setParameterByClass("ilpageobjectgui", "q_id", $_GET["q_id"]);
		include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
		$q_type = $this->object->getQuestionType();

		if (strlen($q_type))
		{
			$classname = $q_type . "GUI";
			$this->ctrl->setParameterByClass(strtolower($classname), "sel_question_types", $q_type);
			$this->ctrl->setParameterByClass(strtolower($classname), "q_id", $_GET["q_id"]);
		}

		if ($_GET["q_id"])
		{
			if ($rbacsystem->checkAccess('write', $_GET["ref_id"]))
			{
				// edit page
				$ilTabs->addTarget("edit_content",
					$this->ctrl->getLinkTargetByClass("ilPageObjectGUI", "view"),
					array("view", "insert", "exec_pg"),
					"", "", $force_active);
			}
	
			// edit page
			$ilTabs->addTarget("preview",
				$this->ctrl->getLinkTargetByClass("ilPageObjectGUI", "preview"),
				array("preview"),
				"ilPageObjectGUI", "", $force_active);
		}

		$force_active = false;
		if ($rbacsystem->checkAccess('write', $_GET["ref_id"]))
		{
			$url = "";
			if ($classname) $url = $this->ctrl->getLinkTargetByClass($classname, "editQuestion");
			// edit question properties
			$ilTabs->addTarget("edit_properties",
				$url,
				array("editQuestion", "save", "cancel", "addSuggestedSolution",
					"cancelExplorer", "linkChilds", "removeSuggestedSolution",
					"addPair", "delete", "editMode", "upload",
					"saveEdit"),
				$classname, "", $force_active);
		}

		if ($_GET["q_id"])
		{
			$ilTabs->addTarget("feedback",
				$this->ctrl->getLinkTargetByClass($classname, "feedback"),
				array("feedback", "saveFeedback"),
				$classname, "");
		}
		
		// Assessment of questions sub menu entry
		if ($_GET["q_id"])
		{
			$ilTabs->addTarget("statistics",
				$this->ctrl->getLinkTargetByClass($classname, "assessment"),
				array("assessment"),
				$classname, "");
		}
		
		if (($_GET["calling_test"] > 0) || ($_GET["test_ref_id"] > 0))
		{
			$ref_id = $_GET["calling_test"];
			if (strlen($ref_id) == 0) $ref_id = $_GET["test_ref_id"];
			$ilTabs->setBackTarget($this->lng->txt("backtocallingtest"), "ilias.php?baseClass=ilObjTestGUI&cmd=questions&ref_id=$ref_id");
		}
		else
		{
			$ilTabs->setBackTarget($this->lng->txt("qpl"), $this->ctrl->getLinkTargetByClass("ilobjquestionpoolgui", "questions"));
		}
	}
}
?>

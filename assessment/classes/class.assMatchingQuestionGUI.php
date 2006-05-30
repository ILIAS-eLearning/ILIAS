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
* Matching question GUI representation
*
* The assMatchingQuestionGUI class encapsulates the GUI representation
* for matching questions.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @module   class.assMatchingQuestionGUI.php
* @modulegroup   Assessment
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
		include_once "./assessment/classes/class.assMatchingQuestion.php";
		$this->object = new assMatchingQuestion();
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
		return "assMatchingQuestion";
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
		$this->getQuestionTemplate("qt_matching");
		$this->tpl->addBlockFile("QUESTION_DATA", "question_data", "tpl.il_as_qpl_matching.html", true);

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
			$this->tpl->setVariable("VALUE_MATCHINGPAIR_POINTS", sprintf("%d", $thispair->getPoints()));
			$this->tpl->setVariable("COLOR_CLASS", $tblrow[$i % 2]);
			$this->tpl->parseCurrentBlock();
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
			$this->tpl->setVariable("VALUE_MATCHINGPAIR_POINTS", sprintf("%d", 0));
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
		$javascript = "<script type=\"text/javascript\">function initialSelect() {\n%s\n}</script>";
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
		$this->tpl->setVariable("MATCHING_ID", $this->object->getId());
		$this->tpl->setVariable("VALUE_MATCHING_TITLE", ilUtil::prepareFormOutput($this->object->getTitle()));
		$this->tpl->setVariable("VALUE_MATCHING_COMMENT", ilUtil::prepareFormOutput($this->object->getComment()));
		$this->tpl->setVariable("VALUE_MATCHING_AUTHOR", ilUtil::prepareFormOutput($this->object->getAuthor()));
		$questiontext = $this->object->getQuestion();
		$questiontext = preg_replace("/<br \/>/", "\n", $questiontext);
		$this->tpl->setVariable("VALUE_QUESTION", ilUtil::prepareFormOutput($questiontext));
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
		$this->tpl->setVariable("SAVE", $this->lng->txt("save"));
		$this->tpl->setVariable("SAVE_EDIT", $this->lng->txt("save_edit"));
		$this->tpl->setVariable("CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		$this->ctrl->setParameter($this, "sel_question_types", "assMatchingQuestion");
		$this->tpl->setVariable("TEXT_QUESTION_TYPE", $this->lng->txt("assMatchingQuestion"));
		$this->tpl->setVariable("ACTION_MATCHING_QUESTION",	$this->ctrl->getFormAction($this));

		$this->tpl->parseCurrentBlock();
		if ($this->error)
		{
			sendInfo($this->error);
		}
		$this->checkAdvancedEditor();
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("BODY_ATTRIBUTES", " onload=\"initialSelect();\""); 
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
		include_once "./classes/class.ilObjAssessmentFolder.php";
		$questiontext = ilUtil::stripSlashes($_POST["question"], true, ilObjAssessmentFolder::_getUsedHTMLTagsAsString());
		$questiontext = preg_replace("/\n/", "<br />", $questiontext);
		$this->object->setQuestion($questiontext);
		$this->object->setSuggestedSolution($_POST["solution_hint"], 0);
		$this->object->setShuffle($_POST["shuffle"]);
		// adding estimated working time
		$saved = $saved | $this->writeOtherPostData($result);
		$this->object->setMatchingType($_POST["matching_type"]);

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
				if (preg_match("/\d+/", $points))
				{
					if ($points < 0)
					{
						$result = 1;
						$this->setErrorMessage($this->lng->txt("negative_points_not_allowed"));
					}
				}
				else
				{
					$points = 0.0;
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
			$_GET["q_id"] = $this->object->getId();
		}
		return $result;
	}

	function outQuestionForTest($formaction, $active_id, $pass = NULL, $is_postponed = FALSE, $use_post_solutions = FALSE)
	{
		$test_output = $this->getTestOutput($active_id, $pass, $is_postponed, $use_post_solutions); 
		$this->tpl->setVariable("QUESTION_OUTPUT", $test_output);
		$this->tpl->setVariable("BODY_ATTRIBUTES", " onload=\"setDragelementPositions();show_solution();\"");
		$this->tpl->setVariable("FORMACTION", $formaction);
	}

	function getSolutionOutput($active_id, $pass = NULL)
	{
		// get page object output
		$pageoutput = $this->outQuestionPage("", $is_postponed, $active_id);

		// generate the question output
		include_once "./classes/class.ilTemplate.php";
		$template = new ilTemplate("tpl.il_as_qpl_matching_output_solution.html", TRUE, TRUE, TRUE);
		
		// shuffle output
		$keys = array_keys($this->object->matchingpairs);
		$key2 = $keys;

		$solutions = array();
		if ($active_id)
		{
			include_once "./assessment/classes/class.ilObjTest.php";
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

			if ($this->object->get_matching_type() == MT_TERMS_PICTURES)
			{
				$template->setCurrentBlock("standard_matching_pictures");
				$template->setVariable("DEFINITION_ID", $answer->getPictureId());
				$template->setVariable("THUMBNAIL_HREF", $this->object->getImagePathWeb() . $answer->getPicture() . ".thumb.jpg");
				$template->setVariable("THUMB_ALT", $this->lng->txt("image"));
				$template->setVariable("THUMB_TITLE", $this->lng->txt("image"));
				$template->parseCurrentBlock();
			}
			else
			{
				$template->setCurrentBlock("standard_matching_terms");
				$template->setVariable("DEFINITION", $answer->getDefinition());
				$template->parseCurrentBlock();
			}

			$template->setCurrentBlock("standard_matching_row");
			$template->setVariable("MATCHES", $this->lng->txt("matches"));
			foreach ($solutions as $solution)
			{
				if ($answer->getDefinitionId() == $solution["value2"])
				{
					foreach ($this->object->matchingpairs as $pair)
					{
						if ($pair->getTermId() == $solution["value1"])
						{
							$template->setVariable("SOLUTION", $pair->getTerm());
						}
					}
				}
			}
			$template->parseCurrentBlock();
		}
		
		$questiontext = $this->object->getQuestion();
		$questiontext = ilUtil::insertLatexImages($questiontext, "\<latex>", "\<\/latex>", $this->getLatexCGI());
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
		$template = new ilTemplate("tpl.il_as_qpl_matching_output.html", TRUE, TRUE, TRUE);
		
		// shuffle output
		$keys = array_keys($this->object->matchingpairs);
		$key2 = $keys;
		if ($this->object->getShuffle())
		{
			$keys = $this->object->pcArrayShuffle($keys);
			$keys2 = $this->object->pcArrayShuffle($keys);
		}

		foreach ($keys as $idx)
		{
			$answer = $this->object->matchingpairs[$idx];
			foreach ($keys2 as $comboidx)
			{
				$comboanswer = $this->object->matchingpairs[$comboidx];
				$template->setCurrentBlock("matching_selection");
				$template->setVariable("VALUE_SELECTION", $comboanswer->getTermId());
				$template->setVariable("TEXT_SELECTION", $comboanswer->getTerm());
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
				$template->setVariable("DEFINITION", $answer->getDefinition());
				$template->parseCurrentBlock();
			}

			$template->setCurrentBlock("standard_matching_row");
			$template->setVariable("MATCHES", $this->lng->txt("matches"));
			$template->setVariable("DEFINITION_ID", $answer->getDefinitionId());
			$template->setVariable("PLEASE_SELECT", $this->lng->txt("please_select"));
			$template->parseCurrentBlock();
		}
		
		$questiontext = $this->object->getQuestion();
		$questiontext = ilUtil::insertLatexImages($questiontext, "\<latex>", "\<\/latex>", $this->getLatexCGI());
		$template->setVariable("QUESTIONTEXT", $questiontext);
		$questionoutput = $template->get();
		$questionoutput = preg_replace("/\<div[^>]*?>(.*)\<\/div>/is", "\\1", $questionoutput);

		return $questionoutput;
	}

	function getTestOutput($active_id, $pass = NULL, $is_postponed = FALSE, $use_post_solutions = FALSE)
	{
		// get page object output
		$pageoutput = $this->outQuestionPage("", $is_postponed, $active_id);

		// generate the question output
		include_once "./classes/class.ilTemplate.php";
		$template = new ilTemplate("tpl.il_as_qpl_matching_output.html", TRUE, TRUE, TRUE);
		
		// shuffle output
		$keys = array_keys($this->object->matchingpairs);
		$key2 = $keys;
		if ($this->object->getShuffle())
		{
			$keys = $this->object->pcArrayShuffle($keys);
			$keys2 = $this->object->pcArrayShuffle($keys);
		}

		if ($active_id)
		{
			$solutions = NULL;
			include_once "./assessment/classes/class.ilObjTest.php";
			if (ilObjTest::_getHidePreviousResults($active_id, true))
			{
				if (is_null($pass)) $pass = ilObjTest::_getPass($active_id);
			}
			if ($use_post_solutions) 
			{ 
				$solutions = array();
				foreach ($_POST as $key => $value)
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
					$template->setVariable("DEFINITION_TEXT", $answer->getDefinition());
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
					$template->setVariable("TEXT_SELECTION", $comboanswer->getTerm());
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
					$template->setVariable("DEFINITION", $answer->getDefinition());
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
		$questiontext = ilUtil::insertLatexImages($questiontext, "\<latex>", "\<\/latex>", $this->getLatexCGI());
		$template->setVariable("QUESTIONTEXT", $questiontext);
		$questionoutput = $template->get();
		$questionoutput = str_replace("<div xmlns:xhtml=\"http://www.w3.org/1999/xhtml\" class=\"ilc_Question\"></div>", $questionoutput, $pageoutput);

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
		$this->getQuestionTemplate("qt_matching");
		parent::addSuggestedSolution();
	}	
}
?>

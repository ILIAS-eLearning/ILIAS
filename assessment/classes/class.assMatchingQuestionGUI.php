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
require_once "./assessment/classes/class.assMatchingQuestion.php";

/**
* Matching question GUI representation
*
* The ASS_MatchingQuestionGUI class encapsulates the GUI representation
* for matching questions.
*
* @author		Helmut Schottmüller <hschottm@tzi.de>
* @version	$Id$
* @module   class.assMatchingQuestionGUI.php
* @modulegroup   Assessment
*/
class ASS_MatchingQuestionGUI extends ASS_QuestionGUI
{
	/**
	* ASS_MatchingQuestionGUI constructor
	*
	* The constructor takes possible arguments an creates an instance of the ASS_MatchingQuestionGUI object.
	*
	* @param integer $id The database id of a image map question object
	* @access public
	*/
	function ASS_MatchingQuestionGUI(
		$id = -1
	)
	{
		$this->ASS_QuestionGUI();
		$this->object = new ASS_MatchingQuestion();
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
		return "qt_matching";
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
	function editQuestion($has_error = 0)
	{
		$this->tpl->setVariable("HEADER", $this->object->getTitle());
		$this->getQuestionTemplate("qt_matching");
		$this->tpl->addBlockFile("QUESTION_DATA", "question_data", "tpl.il_as_qpl_matching.html", true);

		// Vorhandene Anworten ausgeben
		for ($i = 0; $i < $this->object->get_matchingpair_count(); $i++)
		{
			$this->tpl->setCurrentBlock("deletebutton");
			$this->tpl->setVariable("DELETE", $this->lng->txt("delete"));
			$this->tpl->setVariable("ANSWER_ORDER", $i);
			$this->tpl->parseCurrentBlock();
			$thispair = $this->object->get_matchingpair($i);
			if ($this->object->get_matching_type() == MT_TERMS_PICTURES)
			{
				$this->tpl->setCurrentBlock("pictures");
				$this->tpl->setVariable("ANSWER_ORDER", $i);
				$this->tpl->setVariable("PICTURE_ID", $thispair->getPictureId());
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
				$this->tpl->setVariable("VALUE_DEFINITION", htmlspecialchars($thispair->getDefinition()));
			}
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("answers");
			$this->tpl->setVariable("VALUE_ANSWER_COUNTER", $i + 1);
			$this->tpl->setVariable("TEXT_MATCHING_PAIR", $this->lng->txt("matching_pair"));
			$this->tpl->setVariable("TEXT_MATCHES", $this->lng->txt("matches"));
			$this->tpl->setVariable("ANSWER_ORDER", $i);
			$this->tpl->setVariable("TERM_ID", $thispair->getTermId());
			$this->tpl->setVariable("VALUE_TERM", htmlspecialchars($thispair->getTerm()));
			$this->tpl->setVariable("TEXT_POINTS", $this->lng->txt("points"));
			$this->tpl->setVariable("VALUE_MATCHINGPAIR_POINTS", sprintf("%d", $thispair->getPoints()));
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
			$this->tpl->setVariable("TEXT_POINTS", $this->lng->txt("points"));
			$this->tpl->setVariable("VALUE_MATCHINGPAIR_POINTS", sprintf("%d", 0));
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
		if (preg_match("/delete_(\d+)/", $this->ctrl->getCmd()))
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
		$this->tpl->setVariable("VALUE_MATCHING_TITLE", htmlspecialchars($this->object->getTitle()));
		$this->tpl->setVariable("VALUE_MATCHING_COMMENT", htmlspecialchars($this->object->getComment()));
		$this->tpl->setVariable("VALUE_MATCHING_AUTHOR", htmlspecialchars($this->object->getAuthor()));
		$questiontext = $this->object->get_question();
		$questiontext = preg_replace("/<br \/>/", "\n", $questiontext);
		$this->tpl->setVariable("VALUE_QUESTION", htmlspecialchars($questiontext));
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
		$this->tpl->setVariable("SAVE", $this->lng->txt("save"));
		$this->tpl->setVariable("SAVE_EDIT", $this->lng->txt("save_edit"));
		$this->tpl->setVariable("CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		$this->ctrl->setParameter($this, "sel_question_types", "qt_matching");
		$this->tpl->setVariable("ACTION_MATCHING_QUESTION",	$this->ctrl->getFormAction($this));

		$this->tpl->parseCurrentBlock();
		if ($this->error)
		{
			sendInfo($this->error);
		}
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
		$this->tpl->setCurrentBlock("other_question_data");
		$est_working_time = $this->object->getEstimatedWorkingTime();
		$this->tpl->setVariable("TEXT_WORKING_TIME", $this->lng->txt("working_time"));
		$this->tpl->setVariable("TIME_FORMAT", $this->lng->txt("time_format"));
		$this->tpl->setVariable("VALUE_WORKING_TIME", ilUtil::makeTimeSelect("Estimated", false, $est_working_time[h], $est_working_time[m], $est_working_time[s]));
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

		// Delete a matching pair if the delete button was pressed
		foreach ($_POST["cmd"] as $key => $value)
		{
			if (preg_match("/delete_(\d+)/", $key, $matches))
			{
				$this->object->delete_matchingpair($matches[1]);
			}
		}
		$this->editQuestion();
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
		$questiontext = ilUtil::stripSlashes($_POST["question"], true, "<strong><em><code><cite>");
		$questiontext = preg_replace("/\n/", "<br />", $questiontext);
		$this->object->set_question($questiontext);
		$this->object->setSuggestedSolution($_POST["solution_hint"], 0);
		$this->object->setShuffle($_POST["shuffle"]);
		// adding estimated working time
		$saved = $saved | $this->writeOtherPostData($result);
		$this->object->set_matching_type($_POST["matching_type"]);

		// Delete all existing answers and create new answers from the form data
		$this->object->flush_matchingpairs();
		$saved = false;

		// Add all answers from the form into the object
		foreach ($_POST as $key => $value)
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
								$upload_result = $this->object->set_image_file($value2['name'], $value2['tmp_name']);
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
						$points = 0.0;
						$this->error .= $this->lng->txt("negative_points_not_allowed") . "<br />";
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

	/**
	* Creates the question output form for the learner
	*
	* Creates the question output form for the learner
	*
	* @access public
	*/
	function outWorkingForm($test_id = "", $is_postponed = false, $showsolution = 0, $show_question_page=true, $show_solution_only = false, $ilUser = null)
	{
		if (!is_object($ilUser)) {
			global $ilUser;
		}
		$output = $this->outQuestionPage(($show_solution_only)?"":"MATCHING_QUESTION", $is_postponed, "", !$show_question_page);
		$output = preg_replace("/&#123;/", "{", $output);
		$output = preg_replace("/&#125;/", "}", $output);

		if ($this->object->getOutputType() == OUTPUT_HTML)
		{
			$solutionoutput = preg_replace("/.*?(<div[^<]*?ilc_Question.*?<\/div>).*/", "\\1", $output);
			$solutionoutput = preg_replace("/\"match/", "\"solution_match", $solutionoutput);
			$solutionoutput = preg_replace("/name\=\"sel_matching/", "name=\"solution_sel_matching", $solutionoutput);
		}
		else
		{
			$solutionoutput = "<table border=\"0\">\n";
		}
		
		if (!$show_question_page)
			$output = preg_replace("/.*?(<div[^<]*?ilc_Question.*?<\/div>).*/", "\\1", $output);
			
		// if wants solution only then strip the question element from output
		if ($show_solution_only) {
			$output = preg_replace("/(<div[^<]*?ilc_Question[^>]*>.*?<\/div>)/", "", $output);
		}
			
		
		// set solutions
		$solution_script = "";
		if ($test_id)
		{
			$solutions =& $this->object->getSolutionValues($test_id, $ilUser);
			$solution_script .= "";//"resetValues();\n";
			foreach ($solutions as $idx => $solution_value)
			{
				if ($this->object->getOutputType() == OUTPUT_HTML || !$show_question_page)
				{					
					$repl_str = "dummy=\"match".$solution_value->value2."_".$solution_value->value1."\"";
					
					if (!$show_question_page) {
						$output = $this->replaceSelectElements ("sel_matching_".$solution_value->value2,$repl_str,$output,"[","]");
					}
					else 
						$output = str_replace($repl_str, $repl_str." selected=\"selected\"", $output);
						
				}
				else
				{
					$output = str_replace("initial_value_" . $solution_value->value2, $solution_value->value1, $output);
					if (($solution_value->value2 > 1) && ($solution_value->value1 > 1))
					{
						$solution_script .= "dd.elements.definition_" . $solution_value->value2 . ".moveTo(dd.elements.term_" . $solution_value->value1 . ".defx + 250, dd.elements.term_" . $solution_value->value1 . ".defy);\n";
						if ($this->object->get_matching_type() == MT_TERMS_DEFINITIONS)
						{
							foreach ($this->object->matchingpairs as $pdx => $pair)
							{
								if ($pair->getDefinitionId() == $solution_value->value2)
								{
									$solution_script .= "dd.elements.definition_" . $solution_value->value2 . ".write(\"<strong>" . $pair->getDefinition() . "</strong>\");\n";
								}
							}
						}
					}
				}
			}
			if (!$show_question_page) 
			{
				// remove all selects which don't have a solution
				//echo htmlentities ($output);
				$output = $this->removeFormElements($output); // preg_replace ("/<select[^>]*>.*?<\/select>/s" ,"[]", $output);				
			}
			
		}
		if ($this->object->getOutputType() == OUTPUT_JAVASCRIPT)
		{
			$output = str_replace("// solution_script", "", $output);
			$this->tpl->setVariable("JS_INITIALIZE", "<script type=\"text/javascript\">\nfunction show_solution() {\n$solution_script\n}\n</script>\n");
			$this->tpl->setVariable("BODY_ATTRIBUTES", " onload=\"show_solution();\"");
		}

		if ($this->object->getOutputType() == OUTPUT_HTML)
		{
			foreach ($this->object->matchingpairs as $idx => $answer)
			{
				$id = $answer->getDefinitionId()."_".$answer->getTermId();
				$repl_str = "dummy=\"solution_match".$id."\"";
				$solutionoutput = str_replace($repl_str, $repl_str." selected=\"selected\"", $solutionoutput);				
				$solutionoutput = preg_replace("/(<tr.*?dummy=\"solution_match$id.*?)<\/tr>/", "\\1<td>" . "<em>(" . $answer->getPoints() . " " . $this->lng->txt("points") . ")</em>" . "</td></tr>", $solutionoutput);
												
				if ($show_solution_only) {
					//$regexp = "/<select name=\"solution_match_$idx\">.*?<option[^>]*dummy=\"solution_match$id\">(.*?)<\/option>.*?<\/select>/";
					//					preg_match ($regexp, $solutionoutput, $matches);
										//$sol_points [] = $matches[1]." (".$answer->getPoints()." ".$this->lng->txt("points").")";					
					$solutionoutput = $this->replaceSelectElements("solution_sel_matching_".$answer->getDefinitionId(),$repl_str, $solutionoutput,"[","]" );
				}								
			}
								//print_r($sol_points);
								//$solutionoutput = preg_replace ("/<select[^>]*name=\"solution_gap_$idx\">.*?<\/select>/i","[".join($sol_points,", ")."]",$solutionoutput); 
		}
		else
		{
			foreach ($this->object->matchingpairs as $idx => $answer)
			{
				if ($this->object->get_matching_type() == MT_TERMS_PICTURES)
				{
					$imagepath = $this->object->getImagePathWeb() . $answer->getPicture();
					$solutionoutput .= "<tr><td><div class=\"textbox\">" . $answer->getTerm() . "</div></td><td width=\"10\"></td><td><div class=\"imagebox\"><img src=\"" . $imagepath . ".thumb.jpg\" /></div></td></tr>\n";
					$size = GetImageSize ($this->object->getImagePath() . $answer->getPicture() . ".thumb.jpg");
					$sizeorig = GetImageSize ($this->object->getImagePath() . $answer->getPicture());
					if ($size[0] >= $sizeorig[0])
					{
						// thumbnail is larger than original -> remove enlarge image
						$output = preg_replace("/<a[^>]*?>\s*<img[^>]*?enlarge[^>]*?>\s*<\/a>/", "", $output);
					}
					// add the image size to the thumbnails
					$output = preg_replace("/(<img[^>]*?".$answer->getPicture()."\.thumb\.jpg[^>]*?)(\/{0,1}\s*)?>/", "\\1 " . $size[3] . "\\2", $output);
				}
				else
				{
					$solutionoutput .= "<tr><td><div class=\"textbox\">" . $answer->getTerm() . "</div></td><td width=\"10\"></td><td><div class=\"textbox\"><strong>" . $answer->getDefinition() . "</strong></div></td></tr>\n";
				}
			}
			$solutionoutput .= "</table>";
		}
		$solutionoutput = "<p>" . $this->lng->txt("correct_solution_is") . ":</p><p>$solutionoutput</p>";
		if ($test_id) 
		{
			$received_points = "<p>" . sprintf($this->lng->txt("you_received_a_of_b_points"), $this->object->getReachedPoints($ilUser->id, $test_id), $this->object->getMaximumPoints()) . "</p>";
		}
		if (!$showsolution)
		{
			$solutionoutput = "";
			$received_points = "";
		}
		if ($this->object->get_matching_type() == MT_TERMS_PICTURES)
		{
			//$this->tpl->setCurrentBlock("adm_content");
			$output = str_replace("textbox", "textboximage", $output);
			$solutionoutput = str_replace("textbox", "textboximage", $solutionoutput);
		}
		$this->tpl->setVariable("MATCHING_QUESTION", $output.$solutionoutput.$received_points);
	}

	/**
	* Creates an output of the user's solution
	*
	* Creates an output of the user's solution
	*
	* @access public
	*/
	function outUserSolution($user_id, $test_id)
	{
		$results = $this->object->getReachedInformation($user_id, $test_id);
		foreach ($results as $key => $value)
		{
			$this->tpl->setCurrentBlock("tablerow");
			if ($value["true"])
			{
				$this->tpl->setVariable("ANSWER_IMAGE", ilUtil::getImagePath("right.png", true));
				$this->tpl->setVariable("ANSWER_IMAGE_TITLE", $this->lng->txt("answer_is_right"));
			}
			else
			{
				$this->tpl->setVariable("ANSWER_IMAGE", ilUtil::getImagePath("wrong.png", true));
				$this->tpl->setVariable("ANSWER_IMAGE_TITLE", $this->lng->txt("answer_is_wrong"));
			}
			$term = "";
			$definition = "";
			foreach ($this->object->matchingpairs as $answerkey => $answer)
			{
				if ($answer->getDefinitionId() == $value["definition"])
				{
					$definition = $answer->getDefinition();
				}
				if ($answer->getTermId() == $value["term"])
				{
					$term = $answer->getTerm();
				}
			}
			if ($this->object->get_matching_type() == MT_TERMS_PICTURES)
			{
				$definition = $this->lng->txt("selected_image");
			}
			$this->tpl->setVariable("ANSWER_DESCRIPTION", "&quot;<em>" . $definition . "</em>&quot; " . $this->lng->txt("matches") . " &quot;<em>" . $term . "</em>&quot;");
			$this->tpl->parseCurrentBlock();
		}
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
		$this->getQuestionTemplate("qt_matching");
		parent::addSuggestedSolution();
	}	
}
?>

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
* Java applet question GUI representation
*
* The ASS_MatchingQuestionGUI class encapsulates the GUI representation
* for matching questions.
*
* @author		Helmut Schottmüller <hschottm@tzi.de>
* @version	$Id$
* @module   class.assMatchingQuestionGUI.php
* @modulegroup   Assessment
*/
class ASS_MatchingQuestionGUI extends ASS_QuestionGUI {
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

/**
* Creates an output of the edit form for the question
*
* Creates an output of the edit form for the question
*
* @access public
*/
  function showEditForm() {
		$this->tpl->addBlockFile("QUESTION_DATA", "question_data", "tpl.il_as_qpl_matching.html", true);
		$this->tpl->addBlockFile("OTHER_QUESTION_DATA", "other_question_data", "tpl.il_as_qpl_other_question_data.html", true);
		
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
				$this->tpl->setVariable("A_ANSWER_ORDER", $i);
				$this->tpl->setVariable("A_MATCHING_ID", $thispair->get_matchingtext_order());
				$filename = $thispair->get_matchingtext();
				if ($filename) {
					//$this->tpl->setVariable("UPLOADED_IMAGE", $thispair->get_matchingtext());
					$imagepath = $this->object->getImagePathWeb() . $thispair->get_matchingtext();
					$this->tpl->setVariable("UPLOADED_IMAGE", "<img src=\"$imagepath.thumb.jpg\" alt=\"" . $this->lng->txt("qpl_display_fullsize_image") . "\" title=\"" . $this->lng->txt("qpl_display_fullsize_image") . "\" border=\"\" />");
					$this->tpl->setVariable("IMAGE_FILENAME", $thispair->get_matchingtext());
					$this->tpl->setVariable("A_VALUE_RIGHT", $thispair->get_matchingtext());
				}
				$this->tpl->setVariable("UPLOAD", $this->lng->txt("upload"));
			} 
			elseif ($this->object->get_matching_type() == MT_TERMS_DEFINITIONS) 
			{
				$this->tpl->setCurrentBlock("definitions");
				$this->tpl->setVariable("A_ANSWER_ORDER", $i);
				$this->tpl->setVariable("A_MATCHING_ID", $thispair->get_matchingtext_order());
				$this->tpl->setVariable("A_VALUE_RIGHT", $thispair->get_matchingtext());
			}
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("answers");
			$this->tpl->setVariable("VALUE_ANSWER_COUNTER", $i + 1);
			$this->tpl->setVariable("ANSWER_ID", $thispair->get_order());
			$pair = "#pair_" . $thispair->get_order();
			$this->tpl->setVariable("VALUE_LEFT", $thispair->get_answertext());
			$this->tpl->setVariable("ANSWER_ORDER", $i);
			$this->tpl->setVariable("VALUE_RIGHT", $thispair->get_matchingtext());
			$this->tpl->setVariable("TEXT_MATCHING_PAIR", $this->lng->txt("matching_pair"));
			$this->tpl->setVariable("TEXT_MATCHES", $this->lng->txt("matches"));
			$this->tpl->setVariable("TEXT_POINTS", $this->lng->txt("points"));
			$this->tpl->setVariable("VALUE_MATCHING_POINTS", sprintf("%d", $thispair->get_points()));
			$this->tpl->setVariable("TEXT_ANSWER", $this->lng->txt("answer"));
			$this->tpl->parseCurrentBlock();
		}
		// call to other question data i.e. material, estimated working time block
		$this->outOtherQuestionData();
		
		if (strlen($_POST["cmd"]["add"]) > 0) 
		{
			// Template für neue Antwort erzeugen
			if ($this->object->get_matching_type() == MT_TERMS_PICTURES) 
			{
				$this->tpl->setCurrentBlock("pictures");
				$this->tpl->setVariable("A_ANSWER_ORDER", $this->object->get_matchingpair_count());
				$this->tpl->setVariable("A_MATCHING_ID", $this->object->get_random_id("matching"));
				$this->tpl->setVariable("A_VALUE_RIGHT", "");
				$this->tpl->setVariable("UPLOAD", $this->lng->txt("upload"));
			} 
			elseif ($this->object->get_matching_type() == MT_TERMS_DEFINITIONS) 
			{
				$this->tpl->setCurrentBlock("definitions");
				$this->tpl->setVariable("A_ANSWER_ORDER", $this->object->get_matchingpair_count());
				$this->tpl->setVariable("A_MATCHING_ID", $this->object->get_random_id("matching"));
				$this->tpl->setVariable("A_VALUE_RIGHT", "");
			}
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("answers");
			$this->tpl->setVariable("VALUE_ANSWER_COUNTER", $this->object->get_matchingpair_count() + 1);
			$id = $this->object->get_random_id("answer");
			$this->tpl->setVariable("ANSWER_ID", $id);
			$pair = "#pair_$id";
			$this->tpl->setVariable("ANSWER_ORDER", $this->object->get_matchingpair_count());
			$this->tpl->setVariable("TEXT_MATCHES", $this->lng->txt("matches"));
			$this->tpl->setVariable("TEXT_POINTS", $this->lng->txt("points"));
			$this->tpl->setVariable("VALUE_MATCHING_POINTS", sprintf("%d", 0));
			$this->tpl->setVariable("TEXT_MATCHING_PAIR", $this->lng->txt("matching_pair"));
			$this->tpl->setVariable("TEXT_ANSWER", $this->lng->txt("answer"));
			$this->tpl->parseCurrentBlock();
		}
		
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
		$this->tpl->setVariable("VALUE_MATCHING_TITLE", $this->object->getTitle());
		$this->tpl->setVariable("VALUE_MATCHING_COMMENT", $this->object->getComment());
		$this->tpl->setVariable("VALUE_MATCHING_AUTHOR", $this->object->getAuthor());
		$this->tpl->setVariable("VALUE_QUESTION", $this->object->get_question());
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
		$this->tpl->setVariable("SAVE", $this->lng->txt("save"));
		$this->tpl->setVariable("APPLY", $this->lng->txt("apply"));
		$this->tpl->setVariable("CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		$this->tpl->setVariable("ACTION_MATCHING_QUESTION", $_SERVER["PHP_SELF"] . "?ref_id=" . $_GET["ref_id"] . "&cmd=question&sel_question_types=qt_matching$pair");
		$this->tpl->parseCurrentBlock();
  }

/**
* Sets the extra fields i.e. estimated working time and material of a question from a posted create/edit form
*
* Sets the extra fields i.e. estimated working time and material of a question from a posted create/edit form
*
* @access private
*/
  function outOtherQuestionData() {
		$colspan = " colspan=\"4\"";

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

    if ((!$_POST["title"]) or (!$_POST["author"]) or (!$_POST["question"])) $result = 1;

		if (($result) and ($_POST["cmd"]["add"])) 
		{
			// You cannot add matching pairs before you enter the required data
      sendInfo($this->lng->txt("fill_out_all_required_fields_add_matching"));
			$_POST["cmd"]["add"] = "";
		}

		// Check the creation of new answer text fields
		if ($_POST["cmd"]["add"]) 
		{
			foreach ($_POST as $key => $value) 
			{
	   		if ((preg_match("/left_(\d+)_(\d+)/", $key, $matches)) or (preg_match("/right_(\d+)_(\d+)/", $key, $matches))) 
				{
					if (!$value) 
					{
						$_POST["cmd"]["add"] = "";
						sendInfo($this->lng->txt("fill_out_all_matching_pairs"));
					}
			 	}
		  }
		}

    $this->object->setTitle(ilUtil::stripSlashes($_POST["title"]));
    $this->object->setAuthor(ilUtil::stripSlashes($_POST["author"]));
    $this->object->setComment(ilUtil::stripSlashes($_POST["comment"]));
    $this->object->set_question(ilUtil::stripSlashes($_POST["question"]));
		$this->object->setShuffle($_POST["shuffle"]);
    // adding estimated working time and materials uris
    $saved = $saved | $this->writeOtherPostData($result);
		$this->object->set_matching_type($_POST["matching_type"]);

    // Delete all existing answers and create new answers from the form data
    $this->object->flush_matchingpairs();
		$saved = false;
    // Add all answers from the form into the object
    foreach ($_POST as $key => $value) 
		{
      if (preg_match("/left_(\d+)_(\d+)/", $key, $matches)) 
			{
				foreach ($_POST as $key2 => $value2) 
				{
					if (preg_match("/right_$matches[1]_(\d+)/", $key2, $matches2)) 
					{
						$matchingtext_id = $matches2[1];
					}
				}
				if ($this->object->get_matching_type() == MT_TERMS_PICTURES) 
				{
					foreach ($_FILES as $key2 => $value2) 
					{
						if (preg_match("/right_$matches[1]_(\d+)/", $key2, $matches2)) 
						{
							if ($value2["tmp_name"]) 
							{
								// upload the matching picture
								if ($this->object->getId() <= 0) 
								{
									$this->object->saveToDb();
									$saved = true;
						      sendInfo($this->lng->txt("question_saved_for_upload"));
								}
								$this->object->set_image_file($value2['name'], $value2['tmp_name']);
								$_POST["right_$matches[1]_$matchingtext_id"] = $value2['name'];
							}
						}
					}
				}
        $this->object->add_matchingpair(
          ilUtil::stripSlashes($_POST["$key"]),
          ilUtil::stripSlashes($_POST["right_$matches[1]_$matchingtext_id"]),
          ilUtil::stripSlashes($_POST["points_$matches[1]"]),
          ilUtil::stripSlashes($matches[2]),
          ilUtil::stripSlashes($matchingtext_id)
				);
      }
    }

    // Delete a matching pair if the delete button was pressed
    foreach ($_POST as $key => $value) 
		{
      if (preg_match("/delete_(\d+)/", $key, $matches)) 
			{
        $this->object->delete_matchingpair($matches[1]);
      }
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
    $this->tpl->addBlockFile("MATCHING_QUESTION", "matching", "tpl.il_as_execute_matching_question.html", true);
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
    foreach ($this->object->matchingpairs as $key => $value) 
		{
      $array_matching[$value->get_order()] = $value->get_answertext();
    }
    asort($array_matching);
		$keys = array_keys($array_matching);
		if ($this->object->shuffle) 
		{
			$keys = $this->object->pcArrayShuffle($keys);
		}

		if (!empty($this->object->materials)) 
		{
			$i=1;
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

    foreach ($this->object->matchingpairs as $key => $value) 
		{
      $this->tpl->setCurrentBlock("matching_combo");
			foreach ($keys as $match_key) 
			{
				$match_value = $array_matching[$match_key];
        $this->tpl->setVariable("COMBO_MATCHING_VALUE", $match_value);
        $this->tpl->setVariable("COMBO_MATCHING", $match_key);
				$selected = "";
				foreach ($solutions as $idx => $solution) 
				{
					if ($solution->value2 == $value->get_matchingtext_order()) 
					{
						if ($solution->value1 == $match_key) 
						{
							$selected = " selected=\"selected\"";
						}
					}
				}
				$this->tpl->setVariable("VALUE_SELECTED", $selected);
        $this->tpl->parseCurrentBlock();
      }
	    $this->tpl->setCurrentBlock("matching_question");
      $this->tpl->setVariable("COUNTER", $value->get_matchingtext_order());
			if ($this->object->get_matching_type() == MT_TERMS_PICTURES) 
			{
				$imagepath = $this->object->getImagePathWeb() . $value->get_matchingtext();
				$this->tpl->setVariable("MATCHING_TEXT", "<a href=\"$imagepath\" target=\"_blank\"><img src=\"$imagepath.thumb.jpg\" title=\"" . $this->lng->txt("qpl_display_fullsize_image") . "\" alt=\"" . $this->lng->txt("qpl_display_fullsize_image") . "\" border=\"\" /></a>");
			} 
			else 
			{
	      $this->tpl->setVariable("MATCHING_TEXT", "<strong>" . $value->get_matchingtext() . "</strong>");
  		}
	    $this->tpl->setVariable("TEXT_MATCHES", "matches");
			$this->tpl->setVariable("PLEASE_SELECT", $this->lng->txt("please_select"));
      $this->tpl->parseCurrentBlock();
    }

    $this->tpl->setCurrentBlock("matching");
    $this->tpl->setVariable("MATCHING_QUESTION_HEADLINE", $this->object->getTitle() . $postponed);
    $this->tpl->setVariable("MATCHING_QUESTION", $this->object->get_question());
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
		$this->outWorkingForm();
	}

}
?>

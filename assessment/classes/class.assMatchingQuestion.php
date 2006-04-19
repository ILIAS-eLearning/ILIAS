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

include_once "./assessment/classes/class.assQuestion.php";
include_once "./assessment/classes/inc.AssessmentConstants.php";

/**
* Class for matching questions
*
* ASS_MatchingQuestion is a class for matching questions.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @module   class.assMatchingQuestion.php
* @modulegroup   Assessment
*/
class ASS_MatchingQuestion extends ASS_Question
{
	/**
	* The question text
	*
	* The question text of the matching question.
	*
	* @var string
	*/
	var $question;

	/**
	* The possible matching pairs of the matching question
	*
	* $matchingpairs is an array of the predefined matching pairs of the matching question
	*
	* @var array
	*/
	var $matchingpairs;

	/**
	* Type of matching question
	*
	* There are two possible types of matching questions: Matching terms and definitions (=1)
	* and Matching terms and pictures (=0).
	*
	* @var integer
	*/
	var $matching_type;

	/**
	* ASS_MatchingQuestion constructor
	*
	* The constructor takes possible arguments an creates an instance of the ASS_MatchingQuestion object.
	*
	* @param string $title A title string to describe the question
	* @param string $comment A comment string to describe the question
	* @param string $author A string containing the name of the questions author
	* @param integer $owner A numerical ID to identify the owner/creator
	* @param string $question The question string of the matching question
	* @access public
	*/
	function ASS_MatchingQuestion (
		$title = "",
		$comment = "",
		$author = "",
		$owner = -1,
		$question = "",
		$matching_type = MT_TERMS_DEFINITIONS
	)
	{
		$this->ASS_Question($title, $comment, $author, $owner);
		$this->matchingpairs = array();
		$this->question = $question;
		$this->matching_type = $matching_type;
	}

	/**
	* Returns true, if a matching question is complete for use
	*
	* Returns true, if a matching question is complete for use
	*
	* @return boolean True, if the matching question is complete for use, otherwise false
	* @access public
	*/
	function isComplete()
	{
		if (($this->title) and ($this->author) and ($this->question) and (count($this->matchingpairs)))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	* Returns a QTI xml representation of the question
	*
	* Returns a QTI xml representation of the question and sets the internal
	* domxml variable with the DOM XML representation of the QTI xml representation
	*
	* @return string The QTI xml representation of the question
	* @access public
	*/
	function to_xml($a_include_header = true, $a_include_binary = true, $a_shuffle = false, $test_output = false, $force_image_references = false)
	{
		include_once("./classes/class.ilXmlWriter.php");
		$a_xml_writer = new ilXmlWriter;
		// set xml header
		$a_xml_writer->xmlHeader();
		$a_xml_writer->xmlStartTag("questestinterop");
		$attrs = array(
			"ident" => "il_".IL_INST_ID."_qst_".$this->getId(),
			"title" => $this->getTitle()
		);
		$a_xml_writer->xmlStartTag("item", $attrs);
		// add question description
		$a_xml_writer->xmlElement("qticomment", NULL, $this->getComment());
		// add estimated working time
		$workingtime = $this->getEstimatedWorkingTime();
		$duration = sprintf("P0Y0M0DT%dH%dM%dS", $workingtime["h"], $workingtime["m"], $workingtime["s"]);
		$a_xml_writer->xmlElement("duration", NULL, $duration);
		// add ILIAS specific metadata
		$a_xml_writer->xmlStartTag("itemmetadata");
		$a_xml_writer->xmlStartTag("qtimetadata");
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "ILIAS_VERSION");
		$a_xml_writer->xmlElement("fieldentry", NULL, $this->ilias->getSetting("ilias_version"));
		$a_xml_writer->xmlEndTag("qtimetadatafield");
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "QUESTIONTYPE");
		$a_xml_writer->xmlElement("fieldentry", NULL, MATCHING_QUESTION_IDENTIFIER);
		$a_xml_writer->xmlEndTag("qtimetadatafield");
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "AUTHOR");
		$a_xml_writer->xmlElement("fieldentry", NULL, $this->getAuthor());
		$a_xml_writer->xmlEndTag("qtimetadatafield");
		$a_xml_writer->xmlEndTag("qtimetadata");
		$a_xml_writer->xmlEndTag("itemmetadata");

		// PART I: qti presentation
		$attrs = array(
			"label" => $this->getTitle()
		);
		$a_xml_writer->xmlStartTag("presentation", $attrs);
		// add flow to presentation
		$a_xml_writer->xmlStartTag("flow");
		// add material with question text to presentation
		$a_xml_writer->xmlStartTag("material");
		$a_xml_writer->xmlElement("mattext", NULL, $this->getQuestion());
		$a_xml_writer->xmlEndTag("material");
		// add answers to presentation
		$attrs = array();
		if ($this->get_matching_type() == MT_TERMS_PICTURES)
		{
			$attrs = array(
				"ident" => "MQP",
				"rcardinality" => "Multiple"
			);
		}
		else
		{
			$attrs = array(
				"ident" => "MQT",
				"rcardinality" => "Multiple"
			);
		}
		if ($this->getOutputType() == OUTPUT_JAVASCRIPT)
		{
			$attrs["output"] = "javascript";
		}
		$a_xml_writer->xmlStartTag("response_grp", $attrs);
		$solution = $this->getSuggestedSolution(0);
		if (count($solution))
		{
			if (preg_match("/il_(\d*?)_(\w+)_(\d+)/", $solution["internal_link"], $matches))
			{
				$a_xml_writer->xmlStartTag("material");
				$intlink = "il_" . IL_INST_ID . "_" . $matches[2] . "_" . $matches[3];
				if (strcmp($matches[1], "") != 0)
				{
					$intlink = $solution["internal_link"];
				}
				$attrs = array(
					"label" => "suggested_solution"
				);
				$a_xml_writer->xmlElement("mattext", $attrs, $intlink);
				$a_xml_writer->xmlEndTag("material");
			}
		}
		// shuffle output
		$attrs = array();
		if ($this->getShuffle())
		{
			$attrs = array(
				"shuffle" => "Yes"
			);
		}
		else
		{
			$attrs = array(
				"shuffle" => "No"
			);
		}
		$a_xml_writer->xmlStartTag("render_choice", $attrs);
		// add answertext
		$matchingtext_orders = array();
		foreach ($this->matchingpairs as $index => $matchingpair)
		{
			array_push($matchingtext_orders, $matchingpair->getTermId());
		}

		// shuffle it
		$pkeys = array_keys($this->matchingpairs);
		if ($this->getshuffle() && $a_shuffle)
		{
			$pkeys = $this->pcArrayShuffle($pkeys);
		}
		// add answers
		foreach ($pkeys as $index)
		{
			$matchingpair = $this->matchingpairs[$index];
			$attrs = array(
				"ident" => $matchingpair->getDefinitionId(),
				"match_max" => "1",
				"match_group" => join($matchingtext_orders, ",")
			);
			$a_xml_writer->xmlStartTag("response_label", $attrs);
			$a_xml_writer->xmlStartTag("material");
			if ($this->get_matching_type() == MT_TERMS_PICTURES)
			{
				if ($force_image_references)
				{
					$attrs = array(
						"imagtype" => "image/jpeg",
						"label" => $matchingpair->getPicture(),
						"uri" => $this->getImagePathWeb() . $matchingpair->getPicture()
					);
					$a_xml_writer->xmlElement("matimage", $attrs);
				}
				else
				{
					$imagepath = $this->getImagePath() . $matchingpair->getPicture();
					$fh = @fopen($imagepath, "rb");
					if ($fh != false)
					{
						$imagefile = fread($fh, filesize($imagepath));
						fclose($fh);
						$base64 = base64_encode($imagefile);
						$attrs = array(
							"imagtype" => "image/jpeg",
							"label" => $matchingpair->getPicture(),
							"embedded" => "base64"
						);
						$a_xml_writer->xmlElement("matimage", $attrs, $base64, FALSE, FALSE);
					}
				}
			}
			else
			{
				$a_xml_writer->xmlElement("mattext", NULL, $matchingpair->getDefinition());
			}
			$a_xml_writer->xmlEndTag("material");
			$a_xml_writer->xmlEndTag("response_label");
		}
		// shuffle again to get another order for the terms or pictures
		if ($this->getshuffle() && $a_shuffle)
		{
			$pkeys = $this->pcArrayShuffle($pkeys);
		}
		// add matchingtext
		foreach ($pkeys as $index)
		{
			$matchingpair = $this->matchingpairs[$index];
			$attrs = array(
				"ident" => $matchingpair->getTermId()
			);
			$a_xml_writer->xmlStartTag("response_label", $attrs);
			$a_xml_writer->xmlStartTag("material");
			$a_xml_writer->xmlElement("mattext", NULL, $matchingpair->getTerm());
			$a_xml_writer->xmlEndTag("material");
			$a_xml_writer->xmlEndTag("response_label");
		}
		$a_xml_writer->xmlEndTag("render_choice");
		$a_xml_writer->xmlEndTag("response_grp");
		$a_xml_writer->xmlEndTag("flow");
		$a_xml_writer->xmlEndTag("presentation");

		// PART II: qti resprocessing
		$a_xml_writer->xmlStartTag("resprocessing");
		$a_xml_writer->xmlStartTag("outcomes");
		$a_xml_writer->xmlStartTag("decvar");
		$a_xml_writer->xmlEndTag("decvar");
		$a_xml_writer->xmlEndTag("outcomes");
		// add response conditions
		foreach ($this->matchingpairs as $index => $matchingpair)
		{
			$attrs = array(
				"continue" => "Yes"
			);
			$a_xml_writer->xmlStartTag("respcondition", $attrs);
			// qti conditionvar
			$a_xml_writer->xmlStartTag("conditionvar");
			$attrs = array();
			if ($this->get_matching_type() == MT_TERMS_PICTURES)
			{
				$attrs = array(
					"respident" => "MQP"
				);
			}
				else
			{
				$attrs = array(
					"respident" => "MQT"
				);
			}
			$a_xml_writer->xmlElement("varsubset", $attrs, $matchingpair->getTermId() . "," . $matchingpair->getDefinitionId());
			$a_xml_writer->xmlEndTag("conditionvar");

			// qti setvar
			$attrs = array(
				"action" => "Add"
			);
			$a_xml_writer->xmlElement("setvar", $attrs, $matchingpair->getPoints());
			// qti displayfeedback
			$attrs = array(
				"feedbacktype" => "Response",
				"linkrefid" => "correct_" . $matchingpair->getTermId() . "_" . $matchingpair->getDefinitionId()
			);
			$a_xml_writer->xmlElement("displayfeedback", $attrs);
			$a_xml_writer->xmlEndTag("respcondition");
		}
		$a_xml_writer->xmlEndTag("resprocessing");

		// PART III: qti itemfeedback
		foreach ($this->matchingpairs as $index => $matchingpair)
		{
			$attrs = array(
				"ident" => "correct_" . $matchingpair->getTermId() . "_" . $matchingpair->getDefinitionId(),
				"view" => "All"
			);
			$a_xml_writer->xmlStartTag("itemfeedback", $attrs);
			// qti flow_mat
			$a_xml_writer->xmlStartTag("flow_mat");
			$a_xml_writer->xmlStartTag("material");
			$a_xml_writer->xmlElement("mattext");
			$a_xml_writer->xmlEndTag("material");
			$a_xml_writer->xmlEndTag("flow_mat");
			$a_xml_writer->xmlEndTag("itemfeedback");
		}
		$a_xml_writer->xmlEndTag("item");
		$a_xml_writer->xmlEndTag("questestinterop");

		$xml = $a_xml_writer->xmlDumpMem(FALSE);
		if (!$a_include_header)
		{
			$pos = strpos($xml, "?>");
			$xml = substr($xml, $pos + 2);
		}
		return $xml;
	}

	/**
	* Saves a ASS_MatchingQuestion object to a database
	*
	* Saves a ASS_MatchingQuestion object to a database (experimental)
	*
	* @param object $db A pear DB object
	* @access public
	*/
	function saveToDb($original_id = "")
	{
		global $ilias;

		$db =& $ilias->db;
		$complete = 0;
		if ($this->isComplete())
		{
			$complete = 1;
		}
		$estw_time = $this->getEstimatedWorkingTime();
		$estw_time = sprintf("%02d:%02d:%02d", $estw_time['h'], $estw_time['m'], $estw_time['s']);

		if ($original_id)
		{
			$original_id = $db->quote($original_id);
		}
		else
		{
			$original_id = "NULL";
		}

		if ($this->id == -1)
		{
			// Neuen Datensatz schreiben
			$now = getdate();
			$question_type = $this->getQuestionType();
			$created = sprintf("%04d%02d%02d%02d%02d%02d", $now['year'], $now['mon'], $now['mday'], $now['hours'], $now['minutes'], $now['seconds']);
			$query = sprintf("INSERT INTO qpl_questions (question_id, question_type_fi, obj_fi, title, comment, author, owner, question_text, working_time, points, complete, created, original_id, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, NULL)",
				$db->quote($question_type. ""),
				$db->quote($this->obj_id. ""),
				$db->quote($this->title. ""),
				$db->quote($this->comment. ""),
				$db->quote($this->author. ""),
				$db->quote($this->owner. ""),
				$db->quote($this->question. ""),
				$db->quote($estw_time. ""),
				$db->quote($this->getMaximumPoints() . ""),
				$db->quote($complete. ""),
				$db->quote($created. ""),
				$original_id
			);

			$result = $db->query($query);
			if ($result == DB_OK)
			{
				$this->id = $this->ilias->db->getLastInsertId();
				$query = sprintf("INSERT INTO qpl_question_matching (question_fi, shuffle, matching_type) VALUES (%s, %s, %s)",
					$db->quote($this->id . ""),
					$db->quote($this->shuffle . ""),
					$db->quote($this->matching_type. "")
				);
				$db->query($query);

				// create page object of question
				$this->createPageObject();

				// Falls die Frage in einen Test eingefügt werden soll, auch diese Verbindung erstellen
				if ($this->getTestId() > 0)
				{
					$this->insertIntoTest($this->getTestId());
				}
			}
		}
		else
		{
			// Vorhandenen Datensatz aktualisieren
			$query = sprintf("UPDATE qpl_questions SET obj_fi = %s, title = %s, comment = %s, author = %s, question_text = %s, working_time=%s, points = %s, complete = %s WHERE question_id = %s",
				$db->quote($this->obj_id. ""),
				$db->quote($this->title. ""),
				$db->quote($this->comment. ""),
				$db->quote($this->author. ""),
				$db->quote($this->question. ""),
				$db->quote($estw_time. ""),
				$db->quote($this->getMaximumPoints() . ""),
				$db->quote($complete. ""),
				$db->quote($this->id. "")
			);
			$result = $db->query($query);
			$query = sprintf("UPDATE qpl_question_matching SET shuffle = %s, matching_type = %s WHERE question_fi = %s",
				$db->quote($this->shuffle . ""),
				$db->quote($this->matching_type. ""),
				$db->quote($this->id . "")
			);
			$result = $db->query($query);
		}

		if ($result == DB_OK)
		{
			// Antworten schreiben
			// alte Antworten löschen
			$query = sprintf("DELETE FROM qpl_answer_matching WHERE question_fi = %s",
				$db->quote($this->id)
			);
			$result = $db->query($query);

			// Anworten wegschreiben
			foreach ($this->matchingpairs as $key => $value)
			{
				$matching_obj = $this->matchingpairs[$key];
				$query = sprintf("INSERT INTO qpl_answer_matching (answer_id, question_fi, answertext, points, aorder, matchingtext, matching_order) VALUES (NULL, %s, %s, %s, %s, %s, %s)",
					$db->quote($this->id),
					$db->quote($matching_obj->getTerm() . ""),
					$db->quote($matching_obj->getPoints() . ""),
					$db->quote($matching_obj->getTermId() . ""),
					$db->quote($matching_obj->getDefinition() . ""),
					$db->quote($matching_obj->getDefinitionId() . "")
				);
				$matching_result = $db->query($query);
			}
		}
		parent::saveToDb($original_id);
	}

	/**
	* Loads a ASS_MatchingQuestion object from a database
	*
	* Loads a ASS_MatchingQuestion object from a database (experimental)
	*
	* @param object $db A pear DB object
	* @param integer $question_id A unique key which defines the multiple choice test in the database
	* @access public
	*/
	function loadFromDb($question_id)
	{
		global $ilias;
		$db =& $ilias->db;

    $query = sprintf("SELECT qpl_questions.*, qpl_question_matching.* FROM qpl_questions, qpl_question_matching WHERE question_id = %s AND qpl_questions.question_id = qpl_question_matching.question_fi",
			$db->quote($question_id)
		);
		$result = $db->query($query);
		if (strcmp(strtolower(get_class($result)), db_result) == 0)
		{
			if ($result->numRows() == 1)
			{
				$data = $result->fetchRow(DB_FETCHMODE_OBJECT);
				$this->id = $question_id;
				$this->title = $data->title;
				$this->comment = $data->comment;
				$this->author = $data->author;
				$this->solution_hint = $data->solution_hint;
				$this->obj_id = $data->obj_fi;
				$this->original_id = $data->original_id;
				$this->owner = $data->owner;
				$this->matching_type = $data->matching_type;
				$this->question = $data->question_text;
				$this->points = $data->points;
				$this->shuffle = $data->shuffle;
				$this->setEstimatedWorkingTime(substr($data->working_time, 0, 2), substr($data->working_time, 3, 2), substr($data->working_time, 6, 2));
			}

			$query = sprintf("SELECT * FROM qpl_answer_matching WHERE question_fi = %s ORDER BY answer_id ASC",
				$db->quote($question_id)
			);
			$result = $db->query($query);
			include_once "./assessment/classes/class.assAnswerMatching.php";
			if (strcmp(strtolower(get_class($result)), db_result) == 0)
			{
				while ($data = $result->fetchRow(DB_FETCHMODE_OBJECT))
				{
					array_push($this->matchingpairs, new ASS_AnswerMatching($data->answertext, $data->points, $data->aorder, $data->matchingtext, $data->matching_order));
				}
			}
		}
		parent::loadFromDb($question_id);
	}

	/**
	* Adds an answer to the question
	*
	* Adds an answer to the question
	*
	* @access public
	*/
	function addMatchingPair($answertext, $points, $answerorder, $matchingtext, $matchingorder)
	{
		include_once "./assessment/classes/class.assAnswerMatching.php";
		array_push($this->matchingpairs, new ASS_AnswerMatching($answertext, $points, $answerorder, $matchingtext, $matchingorder));
	}
	
	
	/**
	* Duplicates an ASS_MatchingQuestion
	*
	* Duplicates an ASS_MatchingQuestion
	*
	* @access public
	*/
	function duplicate($for_test = true, $title = "", $author = "", $owner = "")
	{
		if ($this->id <= 0)
		{
			// The question has not been saved. It cannot be duplicated
			return;
		}
		// duplicate the question in database
		$clone = $this;
		include_once ("./assessment/classes/class.assQuestion.php");
		$original_id = ASS_Question::_getOriginalId($this->id);
		$clone->id = -1;
		if ($title)
		{
			$clone->setTitle($title);
		}
		if ($author)
		{
			$clone->setAuthor($author);
		}
		if ($owner)
		{
			$clone->setOwner($owner);
		}
		if ($for_test)
		{
			$clone->saveToDb($original_id);
		}
		else
		{
			$clone->saveToDb();
		}

		// copy question page content
		$clone->copyPageOfQuestion($original_id);

		// duplicate the image
		$clone->duplicateImages($original_id);
		return $clone->id;
	}

	/**
	* Copies an ASS_MatchingQuestion
	*
	* Copies an ASS_MatchingQuestion
	*
	* @access public
	*/
	function copyObject($target_questionpool, $title = "")
	{
		if ($this->id <= 0)
		{
			// The question has not been saved. It cannot be duplicated
			return;
		}
		// duplicate the question in database
		$clone = $this;
		include_once ("./assessment/classes/class.assQuestion.php");
		$original_id = ASS_Question::_getOriginalId($this->id);
		$clone->id = -1;
		if ($title)
		{
			$clone->setTitle($title);
		}
		$source_questionpool = $this->getObjId();
		$clone->setObjId($target_questionpool);
		$clone->saveToDb();

		// copy question page content
		$clone->copyPageOfQuestion($original_id);

		// duplicate the image
		$clone->copyImages($original_id, $source_questionpool);
		return $clone->id;
	}

	function duplicateImages($question_id)
	{
		if ($this->get_matching_type() == MT_TERMS_PICTURES)
		{
			$imagepath = $this->getImagePath();
			$imagepath_original = str_replace("/$this->id/images", "/$question_id/images", $imagepath);
			if (!file_exists($imagepath))
			{
				ilUtil::makeDirParents($imagepath);
			}
			foreach ($this->matchingpairs as $answer)
			{
				$filename = $answer->getPicture();
				if (!copy($imagepath_original . $filename, $imagepath . $filename))
				{
					print "image could not be duplicated!!!! ";
				}
				if (!copy($imagepath_original . $filename . ".thumb.jpg", $imagepath . $filename . ".thumb.jpg"))
				{
					print "image thumbnail could not be duplicated!!!! ";
				}
			}
		}
	}

	function copyImages($question_id, $source_questionpool)
	{
		if ($this->get_matching_type() == MT_TERMS_PICTURES)
		{
			$imagepath = $this->getImagePath();
			$imagepath_original = str_replace("/$this->id/images", "/$question_id/images", $imagepath);
			$imagepath_original = str_replace("/$this->obj_id/", "/$source_questionpool/", $imagepath_original);
			if (!file_exists($imagepath))
			{
				ilUtil::makeDirParents($imagepath);
			}
			foreach ($this->matchingpairs as $answer)
			{
				$filename = $answer->getPicture();
				if (!copy($imagepath_original . $filename, $imagepath . $filename))
				{
					print "image could not be duplicated!!!! ";
				}
				if (!copy($imagepath_original . $filename . ".thumb.jpg", $imagepath . $filename . ".thumb.jpg"))
				{
					print "image thumbnail could not be duplicated!!!! ";
				}
			}
		}
	}

	/**
	* Sets the matching question text
	*
	* Sets the matching question text
	*
	* @param string $question The question text
	* @access public
	* @see $question
	*/
	function setQuestion($question = "")
	{
		$this->question = $question;
	}

	/**
	* Sets the matching question type
	*
	* Sets the matching question type
	*
	* @param integer $matching_type The question matching type
	* @access public
	* @see $matching_type
	*/
	function setMatchingType($matching_type = MT_TERMS_DEFINITIONS)
	{
		$this->matching_type = $matching_type;
	}

	/**
	* Returns the question text
	*
	* Returns the question text
	*
	* @return string The question text string
	* @access public
	* @see $question
	*/
	function getQuestion()
	{
		return $this->question;
	}

	/**
	* Returns the matching question type
	*
	* Returns the matching question type
	*
	* @return integer The matching question type
	* @access public
	* @see $matching_type
	*/
	function get_matching_type()
	{
		return $this->matching_type;
	}

	/**
	* Adds an matching pair for an matching question
	*
	* Adds an matching pair for an matching choice question. The students have to fill in an order for the matching pair.
	* The matching pair is an ASS_AnswerMatching object that will be created and assigned to the array $this->matchingpairs.
	*
	* @param string $answertext The answer text
	* @param string $matchingtext The matching text of the answer text
	* @param double $points The points for selecting the matching pair (even negative points can be used)
	* @param integer $order A possible display order of the matching pair
	* @access public
	* @see $matchingpairs
	* @see ASS_AnswerMatching
	*/
	function add_matchingpair(
		$term = "",
		$picture_or_definition = "",
		$points = 0.0,
		$term_id = 0,
		$picture_or_definition_id = 0
	)
	{
		// append answer
		if ($term_id == 0)
		{
			$term_id = $this->get_random_id();
		}

		if ($picture_or_definition_id == 0)
		{
			$picture_or_definition_id = $this->get_random_id();
		}
		include_once "./assessment/classes/class.assAnswerMatching.php";
		$matchingpair = new ASS_AnswerMatching($term, $points, $term_id, $picture_or_definition, $picture_or_definition_id);
		array_push($this->matchingpairs, $matchingpair);
	}

	/**
	* Returns a matching pair
	*
	* Returns a matching pair with a given index. The index of the first
	* matching pair is 0, the index of the second matching pair is 1 and so on.
	*
	* @param integer $index A nonnegative index of the n-th matching pair
	* @return object ASS_AnswerMatching-Object
	* @access public
	* @see $matchingpairs
	*/
	function get_matchingpair($index = 0)
	{
		if ($index < 0)
		{
			return NULL;
		}
		if (count($this->matchingpairs) < 1)
		{
			return NULL;
		}
		if ($index >= count($this->matchingpairs))
		{
			return NULL;
		}
		return $this->matchingpairs[$index];
	}

	/**
	* Deletes a matching pair
	*
	* Deletes a matching pair with a given index. The index of the first
	* matching pair is 0, the index of the second matching pair is 1 and so on.
	*
	* @param integer $index A nonnegative index of the n-th matching pair
	* @access public
	* @see $matchingpairs
	*/
	function delete_matchingpair($index = 0)
	{
		if ($index < 0)
		{
			return;
		}
		if (count($this->matchingpairs) < 1)
		{
			return;
		}
		if ($index >= count($this->matchingpairs))
		{
			return;
		}
		unset($this->matchingpairs[$index]);
		$this->matchingpairs = array_values($this->matchingpairs);
	}

	/**
	* Deletes all matching pairs
	*
	* Deletes all matching pairs
	*
	* @access public
	* @see $matchingpairs
	*/
	function flush_matchingpairs()
	{
		$this->matchingpairs = array();
	}

	/**
	* Returns the number of matching pairs
	*
	* Returns the number of matching pairs
	*
	* @return integer The number of matching pairs of the matching question
	* @access public
	* @see $matchingpairs
	*/
	function get_matchingpair_count()
	{
		return count($this->matchingpairs);
	}

	/**
	* Returns the points, a learner has reached answering the question
	*
	* Returns the points, a learner has reached answering the question
	* The points are calculated from the given answers including checks
	* for all special scoring options in the test container.
	*
	* @param integer $user_id The database ID of the learner
	* @param integer $test_id The database Id of the test containing the question
	* @access public
	*/
	function calculateReachedPoints($user_id, $test_id, $pass = NULL)
	{
		global $ilDB;
		
		$found_value1 = array();
		$found_value2 = array();
		if (is_null($pass))
		{
			$pass = $this->getSolutionMaxPass($user_id, $test_id);
		}
		$query = sprintf("SELECT * FROM tst_solutions WHERE user_fi = %s AND test_fi = %s AND question_fi = %s AND pass = %s",
			$ilDB->quote($user_id . ""),
			$ilDB->quote($test_id . ""),
			$ilDB->quote($this->getId() . ""),
			$ilDB->quote($pass . "")
		);
		$result = $ilDB->query($query);
		while ($data = $result->fetchRow(DB_FETCHMODE_OBJECT))
		{
			if (strcmp($data->value1, "") != 0)
			{
				array_push($found_value1, $data->value1);
				array_push($found_value2, $data->value2);
			}
		}
		$points = 0;
		foreach ($found_value2 as $key => $value)
		{
			foreach ($this->matchingpairs as $answer_key => $answer_value)
			{
				if (($answer_value->getDefinitionId() == $value) and ($answer_value->getTermId() == $found_value1[$key]))
				{
					$points += $answer_value->getPoints();
				}
			}
		}

		// check for special scoring options in test
		$query = sprintf("SELECT * FROM tst_tests WHERE test_id = %s",
			$ilDB->quote($test_id)
		);
		$result = $ilDB->query($query);
		if ($result->numRows() == 1)
		{
			$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
			if ($row["count_system"] == 1)
			{
				if ($points != $this->getMaximumPoints())
				{
					$points = 0;
				}
			}
		}
		else
		{
			$points = 0;
		}
		if ($points < 0) $points = 0;
		return $points;
	}

	/**
	* Returns the evaluation data, a learner has entered to answer the question
	*
	* Returns the evaluation data, a learner has entered to answer the question
	*
	* @param integer $user_id The database ID of the learner
	* @param integer $test_id The database Id of the test containing the question
	* @access public
	*/
	function getReachedInformation($user_id, $test_id, $pass = NULL)
	{
		$found_value1 = array();
		$found_value2 = array();
		if (is_null($pass))
		{
			$pass = $this->getSolutionMaxPass($user_id, $test_id);
		}
		$query = sprintf("SELECT * FROM tst_solutions WHERE user_fi = %s AND test_fi = %s AND question_fi = %s AND pass = %s",
			$this->ilias->db->quote($user_id . ""),
			$this->ilias->db->quote($test_id . ""),
			$this->ilias->db->quote($this->getId() . ""),
			$this->ilias->db->quote($pass . "")
		);
		$result = $this->ilias->db->query($query);
		while ($data = $result->fetchRow(DB_FETCHMODE_OBJECT))
		{
			array_push($found_value1, $data->value1);
			array_push($found_value2, $data->value2);
		}
		$counter = 1;
		$user_result = array();
		foreach ($found_value1 as $key => $value)
		{
			$solution = array(
				"order" => "$counter",
				"points" => 0,
				"true" => 0,
				"term" => "",
				"definition" => ""
			);
			foreach ($this->matchingpairs as $answer_key => $answer_value)
			{
				if (($answer_value->getDefinitionId() == $found_value2[$key]) and ($answer_value->getTermId() == $value))
				{
					$points += $answer_value->getPoints();
					$solution["points"] = $answer_value->getPoints();
					$solution["term"] = $value;
					$solution["definition"] = $found_value2[$key];
					$solution["true"] = 1;
				}
				else
				{
					$solution["term"] = $value;
					$solution["definition"] = $found_value2[$key];
				}
			}
			$counter++;
			array_push($user_result, $solution);
		}
		return $user_result;
	}

	/**
	* Returns the maximum points, a learner can reach answering the question
	*
	* Returns the maximum points, a learner can reach answering the question
	*
	* @access public
	* @see $points
	*/
	function getMaximumPoints()
	{
		$points = 0;
		foreach ($this->matchingpairs as $key => $value)
		{
			if ($value->getPoints() > 0)
			{
				$points += $value->getPoints();
			}
		}
		return $points;
	}

	/**
	* Sets the image file
	*
	* Sets the image file and uploads the image to the object's image directory.
	*
	* @param string $image_filename Name of the original image file
	* @param string $image_tempfilename Name of the temporary uploaded image file
	* @return integer An errorcode if the image upload fails, 0 otherwise
	* @access public
	*/
	function setImageFile($image_filename, $image_tempfilename = "")
	{
		$result = 0;
		if (!empty($image_tempfilename))
		{
			$image_filename = str_replace(" ", "_", $image_filename);
			$imagepath = $this->getImagePath();
			if (!file_exists($imagepath))
			{
				ilUtil::makeDirParents($imagepath);
			}
			//if (!move_uploaded_file($image_tempfilename, $imagepath . $image_filename))
			if (!ilUtil::moveUploadedFile($image_tempfilename, $image_filename, $imagepath.$image_filename))
			{
				$result = 2;
			}
			else
			{
				include_once "./content/classes/Media/class.ilObjMediaObject.php";
				$mimetype = ilObjMediaObject::getMimeType($imagepath . $image_filename);
				if (!preg_match("/^image/", $mimetype))
				{
					unlink($imagepath . $image_filename);
					$result = 1;
				}
				else
				{
					// create thumbnail file
					$thumbpath = $imagepath . $image_filename . "." . "thumb.jpg";
					ilUtil::convertImage($imagepath.$image_filename, $thumbpath, "JPEG", 100);
				}
			}
		}
		return $result;
	}

	/**
	* Checks the data to be saved for consistency
	*
	* Checks the data to be saved for consistency
	*
  * @return boolean True, if the check was ok, False otherwise
	* @access public
	* @see $answers
	*/
	function checkSaveData()
	{
		$result = true;
		$matching_values = array();
		foreach ($_POST as $key => $value)
		{
			if (preg_match("/^sel_matching_(\d+)/", $key, $matches))
			{
				if ((strcmp($value, "") != 0) && ($value != -1))
				{
					array_push($matching_values, $value);
				}
			}
		}
		$check_matching = array_flip($matching_values);
		if (count($check_matching) != count($matching_values))
		{
			// duplicate matching values!!!
			$result = false;
			sendInfo($this->lng->txt("duplicate_matching_values_selected"));
		}
		return $result;
	}

	/**
	* Saves the learners input of the question to the database
	*
	* Saves the learners input of the question to the database
	*
	* @param integer $test_id The database id of the test containing this question
  * @return boolean Indicates the save status (true if saved successful, false otherwise)
	* @access public
	* @see $answers
	*/
	function saveWorkingData($test_id, $pass = NULL)
	{
		global $ilDB;
		global $ilUser;
		$saveWorkingDataResult = $this->checkSaveData();
		if ($saveWorkingDataResult)
		{
			$db =& $ilDB->db;
	
			include_once ("./assessment/classes/class.ilObjTest.php");
			$activepass = ilObjTest::_getPass($ilUser->id, $test_id);
			
			$query = sprintf("DELETE FROM tst_solutions WHERE user_fi = %s AND test_fi = %s AND question_fi = %s AND pass = %s",
				$db->quote($ilUser->id . ""),
				$db->quote($test_id . ""),
				$db->quote($this->getId() . ""),
				$db->quote($activepass . "")
			);
			$result = $db->query($query);
			foreach ($_POST as $key => $value)
			{
				if (preg_match("/^sel_matching_(\d+)/", $key, $matches))
				{
					if (!(preg_match("/initial_value_\d+/", $value)))
					{
						$query = sprintf("INSERT INTO tst_solutions (solution_id, user_fi, test_fi, question_fi, value1, value2, pass, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, %s, NULL)",
							$db->quote($ilUser->id),
							$db->quote($test_id),
							$db->quote($this->getId()),
							$db->quote($value),
							$db->quote($matches[1]),
							$db->quote($activepass . "")
						);
						$result = $db->query($query);
					}
					else
					{
						// write 0 values to prevent the following problem:
						//   with javascript enabled if you reset the positions in a later
						//   pass the input would be deleted but the solution from the previous
						//   pass would be used which is not the same as an unanswered question
						$query = sprintf("INSERT INTO tst_solutions (solution_id, user_fi, test_fi, question_fi, value1, value2, pass, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, %s, NULL)",
							$db->quote($ilUser->id),
							$db->quote($test_id),
							$db->quote($this->getId()),
							$db->quote("0"),
							$db->quote($matches[1]),
							$db->quote($activepass . "")
						);
						$result = $db->query($query);
					}
				}
			}
			$saveWorkingDataResult = true;
		}
    parent::saveWorkingData($test_id, $pass);
		return $saveWorkingDataResult;
	}

	function get_random_id()
	{
		mt_srand((double)microtime()*1000000);
		$random_number = mt_rand(1, 100000);
		$found = FALSE;
		while ($found)
		{
			$found = FALSE;
			foreach ($this->matchingpairs as $key => $value)
			{
				if (($value->getTermId() == $random_number) || ($value->getDefinitionId() == $random_number))
				{
					$found = TRUE;
					$random_number++;
				}
			}
		}
		return $random_number;
	}

	function syncWithOriginal()
	{
		global $ilias;
		if ($this->original_id)
		{
			$complete = 0;
			if ($this->isComplete())
			{
				$complete = 1;
			}
			$db = & $ilias->db;
	
			$estw_time = $this->getEstimatedWorkingTime();
			$estw_time = sprintf("%02d:%02d:%02d", $estw_time['h'], $estw_time['m'], $estw_time['s']);
	
			$query = sprintf("UPDATE qpl_questions SET obj_fi = %s, title = %s, comment = %s, author = %s, question_text = %s, working_time=%s, points = %s, complete = %s WHERE question_id = %s",
				$db->quote($this->obj_id. ""),
				$db->quote($this->title. ""),
				$db->quote($this->comment. ""),
				$db->quote($this->author. ""),
				$db->quote($this->question. ""),
				$db->quote($estw_time. ""),
				$db->quote($this->getMaximumPoints() . ""),
				$db->quote($complete. ""),
				$db->quote($this->original_id. "")
			);
			$result = $db->query($query);
			$query = sprintf("UPDATE qpl_question_matching SET shuffle = %, matching_type = %s WHERE question_fi = %s",
				$db->quote($this->shuffle . ""),
				$db->quote($this->matching_type. ""),
				$db->quote($this->original_id . "")
			);
			$result = $ilDB->query($query);

			if ($result == DB_OK)
			{
				// write answers
				// delete old answers
				$query = sprintf("DELETE FROM qpl_answer_matching WHERE question_fi = %s",
					$db->quote($this->original_id)
				);
				$result = $db->query($query);
	
				foreach ($this->matchingpairs as $key => $value)
				{
					$matching_obj = $this->matchingpairs[$key];
					$query = sprintf("INSERT INTO qpl_answer_matching (answer_id, question_fi, answertext, points, aorder, matchingtext, matching_order) VALUES (NULL, %s, %s, %s, %s, %s, %s)",
						$db->quote($this->original_id . ""),
						$db->quote($matching_obj->getTerm() . ""),
						$db->quote($matching_obj->getPoints() . ""),
						$db->quote($matching_obj->getTermId() . ""),
						$db->quote($matching_obj->getDefinition() . ""),
						$db->quote($matching_obj->getDefinitionId() . "")
					);
					$matching_result = $db->query($query);
				}
			}
			parent::syncWithOriginal();
		}
	}

	function pc_array_shuffle($array) {
		$i = count($array);
		mt_srand((double)microtime()*1000000);
		while(--$i) 
		{
			$j = mt_rand(0, $i);
			if ($i != $j) 
			{
				// swap elements
				$tmp = $array[$j];
				$array[$j] = $array[$i];
				$array[$i] = $tmp;
			}
		}
		return $array;
	}
	
	function createRandomSolution($test_id, $user_id, $pass = NULL)
	{
		global $ilDB;
		global $ilUser;
		$db =& $ilDB->db;

		if (is_null($pass))
		{
			$pass = $this->getSolutionMaxPass($user_id, $test_id);
		}
		$query = sprintf("DELETE FROM tst_solutions WHERE user_fi = %s AND test_fi = %s AND question_fi = %s AND pass = %s",
			$db->quote($user_id),
			$db->quote($test_id),
			$db->quote($this->getId()),
			$db->quote($pass . "")
		);
		$result = $db->query($query);

		$terms = array();
		$definitions = array();
		
		foreach ($this->matchingpairs as $key => $pair)
		{
			array_push($terms, $pair->getTermId());
			array_push($definitions, $pair->getDefinitionId());
		}
		$definitions = $this->pc_array_shuffle($definitions);
		foreach ($terms as $key => $value)
		{
			$query = sprintf("INSERT INTO tst_solutions (solution_id, user_fi, test_fi, question_fi, value1, value2, pass, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, %s, NULL)",
				$db->quote($user_id),
				$db->quote($test_id),
				$db->quote($this->getId()),
				$db->quote($value),
				$db->quote($definitions[$key]),
				$db->quote($pass . "")
			);
			$result = $db->query($query);
		}
	}

	/**
	* Returns the question type of the question
	*
	* Returns the question type of the question
	*
	* @return integer The question type of the question
	* @access public
	*/
	function getQuestionType()
	{
		return 4;
	}
	
	/**
	* Returns the name of the additional question data table in the database
	*
	* Returns the name of the additional question data table in the database
	*
	* @return string The additional table name
	* @access public
	*/
	function getAdditionalTableName()
	{
		return "qpl_question_matching";
	}

	/**
	* Returns the name of the answer table in the database
	*
	* Returns the name of the answer table in the database
	*
	* @return string The answer table name
	* @access public
	*/
	function getAnswerTableName()
	{
		return "qpl_answer_matching";
	}
}

?>

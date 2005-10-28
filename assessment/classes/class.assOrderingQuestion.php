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

require_once "./assessment/classes/class.assQuestion.php";
require_once "./assessment/classes/class.assAnswerOrdering.php";

define ("OQ_PICTURES", 0);
define ("OQ_TERMS", 1);

define ("ORDERING_QUESTION_IDENTIFIER", "ORDERING QUESTION");

/**
* Class for ordering questions
*
* ASS_OrderingQuestion is a class for ordering questions.
*
* @author		Helmut Schottmüller <hschottm@tzi.de>
* @version	$Id$
* @module   class.assOrderingQuestion.php
* @modulegroup   Assessment
*/
class ASS_OrderingQuestion extends ASS_Question
{
	/**
	* The question text
	*
	* The question text of the ordering question.
	*
	* @var string
	*/
	var $question;

	/**
	* The possible answers of the ordering question
	*
	* $answers is an array of the predefined answers of the ordering question
	*
	* @var array
	*/
	var $answers;

	/**
	* Type of ordering question
	*
	* There are two possible types of ordering questions: Ordering terms (=1)
	* and Ordering pictures (=0).
	*
	* @var integer
	*/
	var $ordering_type;

	/**
	* ASS_OrderingQuestion constructor
	*
	* The constructor takes possible arguments an creates an instance of the ASS_OrderingQuestion object.
	*
	* @param string $title A title string to describe the question
	* @param string $comment A comment string to describe the question
	* @param string $author A string containing the name of the questions author
	* @param integer $owner A numerical ID to identify the owner/creator
	* @param string $question The question string of the ordering test
	* @access public
	*/
	function ASS_OrderingQuestion (
		$title = "",
		$comment = "",
		$author = "",
		$owner = -1,
		$question = "",
		$ordering_type = OQ_TERMS
	)
	{
		$this->ASS_Question($title, $comment, $author, $owner);
		$this->answers = array();
		$this->question = $question;
		$this->ordering_type = $ordering_type;
	}

	/**
	* Returns true, if a ordering question is complete for use
	*
	* Returns true, if a ordering question is complete for use
	*
	* @return boolean True, if the ordering question is complete for use, otherwise false
	* @access public
	*/
	function isComplete()
	{
		if (($this->title) and ($this->author) and ($this->question) and (count($this->answers)))
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
	function to_xml($a_include_header = true, $a_include_binary = true, $a_shuffle = false, $test_output = false)
	{
		global $ilDB;
		global $ilUser;
		
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
		$a_xml_writer->xmlElement("fieldentry", NULL, ORDERING_QUESTION_IDENTIFIER);
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
		$a_xml_writer->xmlElement("mattext", NULL, $this->get_question());
		$a_xml_writer->xmlEndTag("material");
		// add answers to presentation
		$attrs = array();
		if ($this->get_ordering_type() == OQ_PICTURES)
		{
			$attrs = array(
				"ident" => "OQP",
				"rcardinality" => "Ordered"
			);
		}
			else
		{
			$attrs = array(
				"ident" => "OQT",
				"rcardinality" => "Ordered"
			);
		}
		if ($this->getOutputType() == OUTPUT_JAVASCRIPT)
		{
			$attrs["output"] = "javascript";
		}
		$a_xml_writer->xmlStartTag("response_lid", $attrs);
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
		// shuffle
		$akeys = array_keys($this->answers);
		if ($this->getshuffle() && $a_shuffle)
		{
			$akeys = $this->pcArrayShuffle($akeys);
		}

		if ($test_output)
		{
			// create array keys from an existing solution
			$pass = $this->getSolutionMaxPass($user_id, $test_id);
			$query = sprintf("SELECT * FROM tst_solutions WHERE test_fi = %s AND user_fi = %s AND question_fi = %s AND pass = %s ORDER BY value2",
				$ilDB->quote($test_output . ""),
				$ilDB->quote($ilUser->id . ""),
				$ilDB->quote($this->getId() . ""),
				$ilDB->quote($pass . "")
			);
			$queryres = $ilDB->query($query);
			if ($queryres->numRows() == count($this->answers))
			{
				$akeys = array();
				while ($row = $queryres->fetchRow(DB_FETCHMODE_ASSOC))
				{
					array_push($akeys, $row["value1"]);
				}
			}
		}

		// add answers
		foreach ($akeys as $index)
		{
			$answer = $this->answers[$index];
			$attrs = array(
				"ident" => $index
			);
			$a_xml_writer->xmlStartTag("response_label", $attrs);
			$a_xml_writer->xmlStartTag("material");
			if ($this->get_ordering_type() == OQ_PICTURES)
			{
				$imagepath = $this->getImagePath() . $answer->get_answertext();
				$fh = @fopen($imagepath, "rb");
				if ($fh != false)
				{
					$imagefile = fread($fh, filesize($imagepath));
					fclose($fh);
					$base64 = base64_encode($imagefile);
					$imagetype = "image/jpeg";
					if (preg_match("/.*\.(png|gif)$/", $answer->get_answertext(), $matches))
					{
						$imagetype = "image/".$matches[1];
					}
					$attrs = array(
						"imagtype" => $imagetype,
						"label" => $answer->get_answertext(),
						"embedded" => "base64"
					);
					$a_xml_writer->xmlElement("matimage", $attrs, $base64);
				}
			}
			else
			{
				$a_xml_writer->xmlElement("mattext", NULL, $answer->get_answertext());
			}
			$a_xml_writer->xmlEndTag("material");
			$a_xml_writer->xmlEndTag("response_label");
		}
		$a_xml_writer->xmlEndTag("render_choice");
		$a_xml_writer->xmlEndTag("response_lid");
		$a_xml_writer->xmlEndTag("flow");
		$a_xml_writer->xmlEndTag("presentation");

		// PART II: qti resprocessing
		$a_xml_writer->xmlStartTag("resprocessing");
		$a_xml_writer->xmlStartTag("outcomes");
		$a_xml_writer->xmlStartTag("decvar");
		$a_xml_writer->xmlEndTag("decvar");
		$a_xml_writer->xmlEndTag("outcomes");
		// add response conditions
		foreach ($this->answers as $index => $answer)
		{
			$attrs = array(
				"continue" => "Yes"
			);
			$a_xml_writer->xmlStartTag("respcondition", $attrs);
			// qti conditionvar
			$a_xml_writer->xmlStartTag("conditionvar");
			$attrs = array();
			if ($this->get_ordering_type() == OQ_PICTURES)
			{
				$attrs = array(
					"respident" => "OQP"
				);
			}
				else
			{
				$attrs = array(
					"respident" => "OQT"
				);
			}
			$attrs["index"] = $answer->get_solution_order();
			$a_xml_writer->xmlElement("varequal", $attrs, $index);
			$a_xml_writer->xmlEndTag("conditionvar");
			// qti setvar
			$attrs = array(
				"action" => "Add"
			);
			$a_xml_writer->xmlElement("setvar", $attrs, $answer->get_points());
			// qti displayfeedback
			$attrs = array(
				"feedbacktype" => "Response",
				"linkrefid" => "link_$index"
			);
			$a_xml_writer->xmlElement("displayfeedback", $attrs);
			$a_xml_writer->xmlEndTag("respcondition");
		}
		$a_xml_writer->xmlEndTag("resprocessing");

		// PART III: qti itemfeedback
		foreach ($this->answers as $index => $answer)
		{
			$attrs = array(
				"ident" => "link_$index",
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
	* Saves a ASS_OrderingQuestion object to a database
	*
	* Saves a ASS_OrderingQuestion object to a database (experimental)
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
			$query = sprintf("INSERT INTO qpl_questions (question_id, question_type_fi, obj_fi, title, comment, author, owner, question_text, working_time, ordering_type, points, complete, created, original_id, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, NULL)",
				$db->quote($question_type . ""),
				$db->quote($this->obj_id . ""),
				$db->quote($this->title . ""),
				$db->quote($this->comment . ""),
				$db->quote($this->author . ""),
				$db->quote($this->owner . ""),
				$db->quote($this->question . ""),
				$db->quote($estw_time . ""),
				$db->quote($this->ordering_type . ""),
				$db->quote($this->getMaximumPoints() . ""),
				$db->quote($complete . ""),
				$db->quote($created . ""),
				$original_id
			);
			$result = $db->query($query);
			if ($result == DB_OK)
			{
				$this->id = $this->ilias->db->getLastInsertId();

				// create page object of question
				$this->createPageObject();

				// Falls die Frage in einen Test eingef�gt werden soll, auch diese Verbindung erstellen
				if ($this->getTestId() > 0)
				{
					$this->insertIntoTest($this->getTestId());
				}
			}
		}
		else
		{
			// Vorhandenen Datensatz aktualisieren
			$query = sprintf("UPDATE qpl_questions SET obj_fi = %s, title = %s, comment = %s, author = %s, question_text = %s, working_time = %s, ordering_type = %s, points = %s, complete = %s WHERE question_id = %s",
				$db->quote($this->obj_id. ""),
				$db->quote($this->title . ""),
				$db->quote($this->comment . ""),
				$db->quote($this->author . ""),
				$db->quote($this->question . ""),
				$db->quote($estw_time . ""),
				$db->quote($this->ordering_type . ""),
				$db->quote($this->getMaximumPoints() . ""),
				$db->quote($complete . ""),
				$db->quote($this->id . "")
			);
			$result = $db->query($query);
		}
		if ($result == DB_OK)
		{
			// Antworten schreiben
			// alte Antworten löschen
			$query = sprintf("DELETE FROM qpl_answers WHERE question_fi = %s",
				$db->quote($this->id)
			);
			$result = $db->query($query);

			// Anworten wegschreiben
			foreach ($this->answers as $key => $value)
			{
				$answer_obj = $this->answers[$key];
				$query = sprintf("INSERT INTO qpl_answers (answer_id, question_fi, answertext, points, aorder, solution_order, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, NULL)",
					$db->quote($this->id),
					$db->quote($answer_obj->get_answertext() . ""),
					$db->quote($answer_obj->get_points() . ""),
					$db->quote($answer_obj->get_order() . ""),
					$db->quote($answer_obj->get_solution_order() . "")
				);
				$answer_result = $db->query($query);
			}
		}
		parent::saveToDb($original_id);
	}

	/**
	* Loads a ASS_OrderingQuestion object from a database
	*
	* Loads a ASS_OrderingQuestion object from a database (experimental)
	*
	* @param object $db A pear DB object
	* @param integer $question_id A unique key which defines the multiple choice test in the database
	* @access public
	*/
	function loadFromDb($question_id)
	{
		global $ilias;
		$db =& $ilias->db;

		$query = sprintf("SELECT * FROM qpl_questions WHERE question_id = %s",
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
				$this->obj_id = $data->obj_fi;
				$this->comment = $data->comment;
				$this->original_id = $data->original_id;
				$this->author = $data->author;
				$this->owner = $data->owner;
				$this->question = $data->question_text;
				$this->solution_hint = $data->solution_hint;
				$this->ordering_type = $data->ordering_type;
				$this->points = $data->points;
				$this->setEstimatedWorkingTime(substr($data->working_time, 0, 2), substr($data->working_time, 3, 2), substr($data->working_time, 6, 2));
			}

			$query = sprintf("SELECT * FROM qpl_answers WHERE question_fi = %s ORDER BY aorder ASC",
				$db->quote($question_id)
			);
			$result = $db->query($query);
			if (strcmp(strtolower(get_class($result)), db_result) == 0)
			{
				while ($data = $result->fetchRow(DB_FETCHMODE_OBJECT))
				{
					array_push($this->answers, new ASS_AnswerOrdering($data->answertext, $data->points, $data->aorder, $data->solution_order));
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
	function addAnswer($answertext, $points, $answerorder, $solutionorder)
	{
		array_push($this->answers, new ASS_AnswerOrdering($answertext, $points, $answerorder, $solutionorder));
	}
	
	/**
	* Duplicates an ASS_OrderingQuestion
	*
	* Duplicates an ASS_OrderingQuestion
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
	* Copies an ASS_OrderingQuestion object
	*
	* Copies an ASS_OrderingQuestion object
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
		$source_questionpool = $this->getObjId();
		$clone->setObjId($target_questionpool);
		if ($title)
		{
			$clone->setTitle($title);
		}
		
		$clone->saveToDb();

		// copy question page content
		$clone->copyPageOfQuestion($original_id);

		// duplicate the image
		$clone->copyImages($original_id, $source_questionpool);
		return $clone->id;
	}
	
	function duplicateImages($question_id)
	{
		if ($this->get_ordering_type() == OQ_PICTURES)
		{
			$imagepath = $this->getImagePath();
			$imagepath_original = str_replace("/$this->id/images", "/$question_id/images", $imagepath);
			if (!file_exists($imagepath)) {
				ilUtil::makeDirParents($imagepath);
			}
			foreach ($this->answers as $answer)
			{
				$filename = $answer->get_answertext();
				if (!copy($imagepath_original . $filename, $imagepath . $filename)) {
					print "image could not be duplicated!!!! ";
				}
				if (!copy($imagepath_original . $filename . ".thumb.jpg", $imagepath . $filename . ".thumb.jpg")) {
					print "image thumbnail could not be duplicated!!!! ";
				}
			}
		}
	}

	function copyImages($question_id, $source_questionpool)
	{
		if ($this->get_ordering_type() == OQ_PICTURES)
		{
			$imagepath = $this->getImagePath();
			$imagepath_original = str_replace("/$this->id/images", "/$question_id/images", $imagepath);
			$imagepath_original = str_replace("/$this->obj_id/", "/$source_questionpool/", $imagepath_original);
			if (!file_exists($imagepath)) {
				ilUtil::makeDirParents($imagepath);
			}
			foreach ($this->answers as $answer)
			{
				$filename = $answer->get_answertext();
				if (!copy($imagepath_original . $filename, $imagepath . $filename)) {
					print "image could not be copied!!!! ";
				}
				if (!copy($imagepath_original . $filename . ".thumb.jpg", $imagepath . $filename . ".thumb.jpg")) {
					print "image thumbnail could not be copied!!!! ";
				}
			}
		}
	}

	/**
	* Sets the ordering question text
	*
	* Sets the ordering question text
	*
	* @param string $question The question text
	* @access public
	* @see $question
	*/
	function set_question($question = "")
	{
		$this->question = $question;
	}

	/**
	* Sets the ordering question type
	*
	* Sets the ordering question type
	*
	* @param integer $ordering_type The question ordering type
	* @access public
	* @see $ordering_type
	*/
	function set_ordering_type($ordering_type = OQ_TERMS)
	{
		$this->ordering_type = $ordering_type;
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
	function get_question()
	{
		return $this->question;
	}

	/**
	* Returns the ordering question type
	*
	* Returns the ordering question type
	*
	* @return integer The ordering question type
	* @access public
	* @see $ordering_type
	*/
	function get_ordering_type()
	{
		return $this->ordering_type;
	}

	/**
	* Adds an answer for an ordering question
	*
	* Adds an answer for an ordering choice question. The students have to fill in an order for the answer.
	* The answer is an ASS_AnswerOrdering object that will be created and assigned to the array $this->answers.
	*
	* @param string $answertext The answer text
	* @param double $points The points for selecting the answer (even negative points can be used)
	* @param integer $order A possible display order of the answer
	* @param integer $solution_order An unique integer value representing the correct
	* order of that answer in the solution of a question
	* @access public
	* @see $answers
	* @see ASS_AnswerOrdering
	*/
	function add_answer(
		$answertext = "",
		$points = 0.0,
		$order = 0,
		$solution_order = 0
	)
	{
		$found = -1;
		foreach ($this->answers as $key => $value)
		{
			if ($value->get_order() == $order)
			{
				$found = $order;
			}
		}
		if ($found >= 0)
		{
			// Antwort einfügen
			$answer = new ASS_AnswerOrdering($answertext, $points, $found, $solution_order);
			array_push($this->answers, $answer);
			for ($i = $found + 1; $i < count($this->answers); $i++)
			{
				$this->answers[$i] = $this->answers[$i-1];
			}
			$this->answers[$found] = $answer;
		}
		else
		{
			// Anwort anhängen
			$answer = new ASS_AnswerOrdering($answertext, $points,
				count($this->answers), $solution_order);
			array_push($this->answers, $answer);
		}
	}

	/**
	* Returns an ordering answer
	*
	* Returns an ordering answer with a given index. The index of the first
	* answer is 0, the index of the second answer is 1 and so on.
	*
	* @param integer $index A nonnegative index of the n-th answer
	* @return object ASS_AnswerOrdering-Object
	* @access public
	* @see $answers
	*/
	function get_answer($index = 0)
	{
		if ($index < 0) return NULL;
		if (count($this->answers) < 1) return NULL;
		if ($index >= count($this->answers)) return NULL;
		return $this->answers[$index];
	}

	/**
	* Deletes an answer
	*
	* Deletes an answer with a given index. The index of the first
	* answer is 0, the index of the second answer is 1 and so on.
	*
	* @param integer $index A nonnegative index of the n-th answer
	* @access public
	* @see $answers
	*/
	function delete_answer($index = 0)
	{
		if ($index < 0)
		{
			return;
		}
		if (count($this->answers) < 1)
		{
			return;
		}
		if ($index >= count($this->answers))
		{
			return;
		}
		unset($this->answers[$index]);
		$this->answers = array_values($this->answers);
		for ($i = 0; $i < count($this->answers); $i++)
		{
			if ($this->answers[$i]->get_order() > $index)
			{
				$this->answers[$i]->set_order($i);
			}
		}
	}

	/**
	* Deletes all answers
	*
	* Deletes all answers
	*
	* @access public
	* @see $answers
	*/
	function flush_answers()
	{
		$this->answers = array();
	}

	/**
	* Returns the number of answers
	*
	* Returns the number of answers
	*
	* @return integer The number of answers of the ordering question
	* @access public
	* @see $answers
	*/
	function get_answer_count()
	{
		return count($this->answers);
	}

	/**
	* Returns the maximum solution order
	*
	* Returns the maximum solution order of all ordering answers
	*
	* @return integer The maximum solution order of all ordering answers
	* @access public
	*/
	function get_max_solution_order()
	{
		if (count($this->answers) == 0)
		{
			$max = 0;
		}
		else
		{
			$max = $this->answers[0]->get_solution_order();
		}
		foreach ($this->answers as $key => $value)
		{
			if ($value->get_solution_order() > $max)
			{
				$max = $value->get_solution_order();
			}
		}
		return $max;
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
		$user_order = array();
		while ($data = $result->fetchRow(DB_FETCHMODE_OBJECT))
		{
			if ((strcmp($data->value1, "") != 0) && (strcmp($data->value2, "") != 0))
			{
				$user_order[$data->value2] = $data->value1;
			}
		}
		ksort($user_order);
		$user_order = array_values($user_order);
		$answer_order = array();
		foreach ($this->answers as $key => $answer)
		{
			$answer_order[$answer->get_solution_order()] = $key;
		}
		ksort($answer_order);
		$answer_order = array_values($answer_order);
		$points = 0;
		foreach ($answer_order as $index => $answer_id)
		{
			if (strcmp($user_order[$index], "") != 0)
			{
				if ($answer_id == $user_order[$index])
				{
					$points += $this->answers[$answer_id]->get_points();
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
		$user_result = array();
		while ($data = $result->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$solution = array(
				"answer_id" => $data->value1,
				"order" => $data->value2
			);
			$user_result[$data->value1] = $solution;
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
		foreach ($this->answers as $key => $value)
		{
			$points += $value->get_points();
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
	function set_image_file($image_filename, $image_tempfilename = "")
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
			if (!ilUtil::moveUploadedFile($image_tempfilename,$image_filename, $imagepath.$image_filename))
			{
				$result = 2;
			}
			else
			{
				require_once "./content/classes/Media/class.ilObjMediaObject.php";
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
					ilUtil::convertImage($imagepath.$image_filename, $thumbpath, strtoupper($extension), 100);
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
		if ($this->getOutputType() == OUTPUT_JAVASCRIPT)
		{
			if (strlen($_POST["orderresult"]))
			{
				return $result;
			}
		}
		$order_values = array();
		foreach ($_POST as $key => $value)
		{
			if (preg_match("/^order_(\d+)/", $key, $matches))
			{
				if (strcmp($value, "") != 0)
				{
					array_push($order_values, $value);
				}
			}
		}
		$check_order = array_flip($order_values);
		if (count($check_order) != count($order_values))
		{
			// duplicate order values!!!
			$result = false;
			sendInfo($this->lng->txt("duplicate_order_values_entered"));
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
	function saveWorkingData($test_id, $limit_to = LIMIT_NO_LIMIT)
	{
		global $ilDB;
		global $ilUser;

		$saveWorkingDataResult = $this->checkSaveData();
		if ($saveWorkingDataResult)
		{
			$db =& $ilDB->db;
	
			$pass = ilObjTest::_getPass($ilUser->id, $test_id);

			$query = sprintf("DELETE FROM tst_solutions WHERE user_fi = %s AND test_fi = %s AND question_fi = %s AND pass = %s",
				$db->quote($ilUser->id . ""),
				$db->quote($test_id . ""),
				$db->quote($this->getId() . ""),
				$db->quote($pass . "")
			);
			$result = $db->query($query);
			if ($this->getOutputType() == OUTPUT_JAVASCRIPT)
			{
				$orderresult = $_POST["orderresult"];
				$orderarray = explode(":", $orderresult);
				$ordervalue = 1;
				foreach ($orderarray as $index)
				{
					$query = sprintf("INSERT INTO tst_solutions (solution_id, user_fi, test_fi, question_fi, value1, value2, pass, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, %s, NULL)",
						$db->quote($ilUser->id . ""),
						$db->quote($test_id . ""),
						$db->quote($this->getId() . ""),
						$db->quote($index . ""),
						$db->quote($ordervalue . ""),
						$db->quote($pass . "")
					);
					$result = $db->query($query);
					$ordervalue++;
				}
			}
			else
			{
				foreach ($_POST as $key => $value)
				{
					if (preg_match("/^order_(\d+)/", $key, $matches))
					{
						if (!(preg_match("/initial_value_\d+/", $value)))
						{
							$query = sprintf("INSERT INTO tst_solutions (solution_id, user_fi, test_fi, question_fi, value1, value2, pass, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, %s, NULL)",
								$db->quote($ilUser->id . ""),
								$db->quote($test_id . ""),
								$db->quote($this->getId() . ""),
								$db->quote($matches[1] . ""),
								$db->quote($value . ""),
								$db->quote($pass . "")
							);
							$result = $db->query($query);
						}
					}
				}
			}
		}
    parent::saveWorkingData($test_id);
		return $saveWorkingDataResult;
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
	
			$query = sprintf("UPDATE qpl_questions SET obj_fi = %s, title = %s, comment = %s, author = %s, question_text = %s, working_time = %s, ordering_type = %s, points = %s, complete = %s WHERE question_id = %s",
				$db->quote($this->obj_id. ""),
				$db->quote($this->title . ""),
				$db->quote($this->comment . ""),
				$db->quote($this->author . ""),
				$db->quote($this->question . ""),
				$db->quote($estw_time . ""),
				$db->quote($this->ordering_type . ""),
				$db->quote($this->getMaximumPoints() . ""),
				$db->quote($complete . ""),
				$db->quote($this->original_id . "")
			);
			$result = $db->query($query);

			if ($result == DB_OK)
			{
				// write ansers
				// delete old answers
				$query = sprintf("DELETE FROM qpl_answers WHERE question_fi = %s",
					$db->quote($this->original_id)
				);
				$result = $db->query($query);
	
				foreach ($this->answers as $key => $value)
				{
					$answer_obj = $this->answers[$key];
					$query = sprintf("INSERT INTO qpl_answers (answer_id, question_fi, answertext, points, aorder, solution_order, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, NULL)",
						$db->quote($this->original_id . ""),
						$db->quote($answer_obj->get_answertext() . ""),
						$db->quote($answer_obj->get_points() . ""),
						$db->quote($answer_obj->get_order() . ""),
						$db->quote($answer_obj->get_solution_order() . "")
					);
					$answer_result = $db->query($query);
				}
			}
			parent::syncWithOriginal();
		}
	}

	function pc_array_shuffle($array) {
		mt_srand((double)microtime()*1000000);
		$i = count($array);
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
			$db->quote($user_id . ""),
			$db->quote($test_id . ""),
			$db->quote($this->getId() . ""),
			$db->quote($pass . "")
		);
		$result = $db->query($query);

		$orders = range(1, count($this->answers));
		$orders = $this->pc_array_shuffle($orders);
		foreach ($this->answers as $key => $value)
		{
			$query = sprintf("INSERT INTO tst_solutions (solution_id, user_fi, test_fi, question_fi, value1, value2, pass, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, %s, NULL)",
				$db->quote($user_id . ""),
				$db->quote($test_id . ""),
				$db->quote($this->getId() . ""),
				$db->quote($key . ""),
				$db->quote(array_pop($orders) . ""),
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
		return 5;
	}
}

?>

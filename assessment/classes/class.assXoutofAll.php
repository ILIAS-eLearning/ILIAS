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
* Class for X out of All questions
*
* ASS_XoutofAll is a class for X out of All questions. 
* .
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @author		Nina Gharib	<nina@wgserve.de>
* @version	$Id$
* @module   class.assXoutofAll.php
* @modulegroup   Assessment
*/
class ASS_XoutofAll extends ASS_Question
{
	/**
	* Question string
	*
	* The question string of the X out of All
	*
	* @var string
	*/
	var $question;

	/**
	* The given answers of the X out of All
	*
	* $answers is an array of the given answers of the answerstring
	*
	* @var array
	*/
	var $answers;

	/**
	* The number of required answers out of all
	*
	* The number of required answers out of all
	*
	* @var integer
	*/
	var $number_X;


	/**
	* ASS_XoutofAll constructor
	*
	* The constructor takes possible arguments an creates an instance of the 
	* ASS_XoutofAll object.
	*
	* @param string $title A title string to describe the question
	* @param string $comment A comment string to describe the question
	* @param string $author A string containing the name of the questions author
	* @param integer $owner A numerical ID to identify the owner/creator
	* @param string $question The question string of the X out of all question
	* @param integer $number_X Indicates the number of required answers out of all
	* @access public
	* @see ASS_Question:ASS_Question()
	*/
	function ASS_XoutofAll(
		$title = "",
		$comment = "",
		$author = "",
		$owner = -1,
		$question = "",
		$number_X = ""
	  )
	{
		$this->ASS_Question($title, $comment, $author, $owner);
		$this->question = $question;
		$this->number_X = $number_X;
		$this->answers = array();
	}

	/**
	* Returns true, if a x out of all question is complete for use
	*
	* Returns true, if a x out of all question is complete for use
	*
	* @return boolean True, if the x out of all question is complete for use, otherwise false
	* @access public
	*/
	function isComplete()
	{
		if (($this->title) and ($this->author) and ($this->question) and (count($this->answers)) and ($this->number_X))
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
		$a_xml_writer->xmlElement("fieldentry", NULL, MULTIPLE_CHOICE_QUESTION_IDENTIFIER);
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
		if ($this->response == RESPONSE_SINGLE)
		{
			$attrs = array(
				"ident" => "MCSR",
				"rcardinality" => "Single"
			);
		}
			else
		{
			$attrs = array(
				"ident" => "MCMR",
				"rcardinality" => "Multiple"
			);
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
		$akeys = array_keys($this->answers);
		if ($this->getshuffle() && $a_shuffle)
		{
			$akeys = $this->pcArrayShuffle($akeys);
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
			$a_xml_writer->xmlElement("mattext", NULL, $answer->get_answertext());
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
			if (!$answer->isStateSet())
			{
				$a_xml_writer->xmlStartTag("not");
			}
			$attrs = array();
			if ($this->response == RESPONSE_SINGLE)
			{
				$attrs = array(
					"respident" => "MCSR"
				);
			}
				else
			{
				$attrs = array(
					"respident" => "MCMR"
				);
			}
			$a_xml_writer->xmlElement("varequal", $attrs, $index);
			if (!$answer->isStateSet())
			{
				$a_xml_writer->xmlEndTag("not");
			}
			$a_xml_writer->xmlEndTag("conditionvar");
			// qti setvar
			$attrs = array(
				"action" => "Add"
			);
			$a_xml_writer->xmlElement("setvar", $attrs, $answer->get_points());
			// qti displayfeedback
			if ($answer->isStateChecked())
			{
				if ($this->response == RESPONSE_SINGLE)
				{
					$linkrefid = "True";
				}
					else
				{
					$linkrefid = "True_$index";
				}
			}
			  else
			{
				$linkrefid = "False_$index";
			}
			$attrs = array(
				"feedbacktype" => "Response",
				"linkrefid" => $linkrefid
			);
			$a_xml_writer->xmlElement("displayfeedback", $attrs);
			$a_xml_writer->xmlEndTag("respcondition");
		}
		$a_xml_writer->xmlEndTag("resprocessing");

		// PART III: qti itemfeedback
		foreach ($this->answers as $index => $answer)
		{
			$linkrefid = "";
			if ($answer->isStateChecked())
			{
				if ($this->response == RESPONSE_SINGLE)
				{
					$linkrefid = "True";
				}
					else
				{
					$linkrefid = "True_$index";
				}
			}
			  else
			{
				$linkrefid = "False_$index";
			}
			$attrs = array(
				"ident" => $linkrefid,
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
	* Saves a ASS_XoutofAll object to a database
	*
	* Saves a ASS_XoutofAll object to a database (experimental)
	*
	* @param object $db A pear DB object
	* @access public
	*/
	function saveToDb($original_id = "")
	{
		global $ilias;

		$complete = 0;
		if ($this->isComplete())
		{
			$complete = 1;
		}
		$db = & $ilias->db;

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
			$query = sprintf("INSERT INTO qpl_questions (question_id, question_type_fi, obj_fi, title, comment, author, owner, question_text, points, number_X, working_time, complete, created, original_id, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, NULL)",
				$db->quote($question_type),
				$db->quote($this->obj_id),
				$db->quote($this->title),
				$db->quote($this->comment),
				$db->quote($this->author),
				$db->quote($this->owner),
				$db->quote($this->question),
				$db->quote($this->getMaximumPoints() . ""),
				$db->quote($this->number_X),
				$db->quote($estw_time),
				$db->quote("$complete"),
				$db->quote($created),
				$original_id
			);
			$result = $db->query($query);
			
			if ($result == DB_OK)
			{
				$this->id = $this->ilias->db->getLastInsertId();

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
			$query = sprintf("UPDATE qpl_questions SET obj_fi = %s, title = %s, comment = %s, author = %s, question_text = %s, points = %s, number_X=%s, working_time=%s, complete = %s WHERE question_id = %s",
				$db->quote($this->obj_id. ""),
				$db->quote($this->title),
				$db->quote($this->comment),
				$db->quote($this->author),
				$db->quote($this->question),
				$db->quote($this->getMaximumPoints() . ""),
				$db->quote($this->number_X),
				$db->quote($estw_time),
				$db->quote("$complete"),
				$db->quote($this->id)
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
				$query = sprintf("INSERT INTO qpl_answers (answer_id, question_fi, answertext, points, aorder, correctness, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, NULL)",
				$db->quote($this->id),
				$db->quote($answer_obj->get_answertext()),
				$db->quote($answer_obj->get_points() . ""),
				$db->quote($answer_obj->get_order() . ""),
				$db->quote($answer_obj->getState() . "")
				);
				$answer_result = $db->query($query);
			}
		}
		parent::saveToDb($original_id);
	}

	/**
	* Loads a ASS_XoutofAll object from a database
	*
	* Loads a ASS_XoutofAll object from a database (experimental)
	*
	* @param object $db A pear DB object
	* @param integer $question_id A unique key which defines the x out of all test in the database
	* @access public
	*/
	function loadFromDb($question_id)
	{
		global $ilias;

		$db = & $ilias->db;
		$query = sprintf("SELECT * FROM qpl_questions WHERE question_id = %s",
		$db->quote($question_id));
		$result = $db->query($query);
		if (strcmp(strtolower(get_class($result)), db_result) == 0)
		{
			if ($result->numRows() == 1)
			{
				$data = $result->fetchRow(DB_FETCHMODE_OBJECT);
				$this->id = $question_id;
				$this->title = $data->title;
				$this->comment = $data->comment;
				$this->solution_hint = $data->solution_hint;
				$this->original_id = $data->original_id;
				$this->obj_id = $data->obj_fi;
				$this->author = $data->author;
				$this->owner = $data->owner;
				$this->points = $data->points;
				$this->question = $data->question_text;
				$this->number_X = $data->number_X;
				$this->setEstimatedWorkingTime(substr($data->working_time, 0, 2), substr($data->working_time, 3, 2), substr($data->working_time, 6, 2));
			}

			$query = sprintf("SELECT * FROM qpl_answers WHERE question_fi = %s ORDER BY aorder ASC",
				$db->quote($question_id));

			$result = $db->query($query);

			include_once "./assessment/classes/class.assAnswerSimple.php";
			if (strcmp(strtolower(get_class($result)), db_result) == 0)
			{
				while ($data = $result->fetchRow(DB_FETCHMODE_OBJECT))
				{
					array_push($this->answers, new ASS_AnswerSimple($data->answertext, $data->points, $data->aorder, $data->correctness));
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
	function addAnswer($answertext, $points, $answerorder, $correctness)
	{
		include_once "./assessment/classes/class.assAnswerSimple.php";
		array_push($this->answers, new ASS_AnswerSimple($answertext, $points, $answerorder, $correctness));
	}
	
	/**
	* Duplicates an ASS_XoutofAllQuestion
	*
	* Duplicates an ASS_XoutofAllQuestion
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

		return $clone->id;
	}

	/**
	* Copies an ASS_XoutofAll object
	*
	* Copies an ASS_XoutofAll object
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

		return $clone->id;
	}
	
	/**
	* Gets the x out of all question
	*
	* Gets the question string of the ASS_XoutofAll object
	*
	* @return string The question string of the ASS_XoutofAll object
	* @access public
	* @see $question
	*/
	function get_question()
	{
		return $this->question;
	}

	/**
	* Sets the x out of all question
	*
	* Sets the question string of the ASS_XoutofAll object
	*
	* @param string $question A string containing the x out of all question
	* @access public
	* @see $question
	*/
	function set_question($question = "")
	{
		$this->question = $question;
	}


	/**
	* Gets the number x of a x out of all question
	*
	* Gets the number of required x out of the answerpool of a x out of all question 
	*
	* @return integer The number x of the ASS_XoutofAll object
	* @access public
	* @see $number_X
	*/
	function get_number_X()
	{
		return $this->number_X;
	}

	/**
	* Sets number x 
	*
	* Sets number x of the ASS_XoutofAll object
	*
	* @param integer $output_type A nonnegative integer value specifying the output type. It is OUTPUT_ORDER (=0) or OUTPUT_RANDOM (=1).
	* @access public
	* @see $response
	*/
	function set_number_X($number_X = "")
		{
			$this->number_X = $number_X;
		}


	/**
	* Adds a possible answer for a x out of all question
	*
	* Adds a possible answer for a x out of all question. A ASS_AnswerSimple object will be
	* created and assigned to the array $this->answers.
	*
	* @param string $answertext The answer text
	* @param double $points The points for all correct answers (even negative points can be used)
	* @param boolean $state Defines the answer as correct (TRUE) or incorrect (FALSE)
	* @param integer $order A possible display order of the answer
	* @access public
	* @see $answers
	* @see ASS_AnswerSimple
	*/
	function add_answer(
		$answertext = "",
		$points = 0.0,
		$order = 0
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
		include_once "./assessment/classes/class.assAnswerSimple.php";
		if ($found >= 0)
		{
			// Antwort einfügen
			$answer = new ASS_AnswerSimple($answertext, $points, $found);
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
			$answer = new ASS_AnswerSimple($answertext, $points, count($this->answers));
			array_push($this->answers, $answer);
		}
	}

	/**
	* Returns the number of all answers
	*
	* Returns the number of all answers
	*
	* @return integer The number of all answers of the x out of all question
	* @access public
	* @see $answers
	*/
	function get_answer_count()
	{
		return count($this->answers);
	}

	/**
	* Returns an answer
	*
	* Returns an answer with a given index. The index of the first
	* answer is 0, the index of the second answer is 1 and so on.
	*
	* @param integer $index A nonnegative index of the n-th answer
	* @return object ASS_AnswerSimple-Object containing the answer
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
		if ($index < 0) return;
		if (count($this->answers) < 1) return;
		if ($index >= count($this->answers)) return;
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
	* Returns the maximum points, a learner can reach answering the question
	*
	* Returns the maximum points, a learner can reach answering the question
	*
	* @access public
	* @see $points
	*/
	function getMaximumPoints()
	{
		$allpoints = 0;
		foreach ($this->answers as $key => $value) 
		{
			$allpoints += $value->get_points();
		}
		if (count($this->answers) > $this->get_number_X())
		{
			$allpoints = ($allpoints / count($this->answers)) * $this->get_number_X();
		}
		return $allpoints;
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
		
		$found_values = array();
// 		bereits verarbeitete eingabewerte
		$pass_value = array();
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
				array_push($found_values, $data->value1);
			}
		}
		$points = 0;
		if (count($found_values) > 0)
		{
			foreach ($found_values as $key => $value)
			{
				if (in_array($this->answers[$key], $found_values) AND !in_array($found_values, $pass_value))
				{
					array_push($pass_value, $found_values);
					$points += $answer->get_points();
				}
			}
		}	

// 		// check for special scoring options in test
// 		$query = sprintf("SELECT * FROM tst_tests WHERE test_id = %s",
// 			$ilDB->quote($test_id)
// 		);
// 		$result = $ilDB->query($query);
// 		if ($result->numRows() == 1)
// 		{
// 			$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
// 			if ($row["mc_scoring"] == 0)
// 			{
// 				if (!$this->wasAnsweredByUser($user_id, $test_id))
// 				{
// 					$points = 0;
// 				}
// 			}
// 			if ($row["count_system"] == 1)
// 			{
// 				if ($points != $this->getMaximumPoints())
// 				{
// 					$points = 0;
// 				}
// 			}
// 		}
		else
		{
			$points = 0;
		}
		return $points;
	}
	
	/**
	* Returns if the question was answered by a user or not
	*
	* Returns if the question was answered by a user or not
	*
	* @param integer $user_id The database ID of the learner
	* @param integer $test_id The database Id of the test containing the question
	* @return boolean
	* @access public
	*/
	function wasAnsweredByUser($user_id, $test_id, $pass = NULL)
	{
		global $ilDB;
		$found_values = array();
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
				array_push($found_values, $data->value1);
			}
		}
		if (count($found_values) == 0)
		{
			return FALSE;
		}
		else
		{
			return TRUE;
		}
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
		$found_values = array();
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
			array_push($found_values, $data->value1);
		}
		$counter = 1;
		$user_result = array();
		foreach ($found_values as $key => $value)
		{
			$solution = array(
				"order" => "$counter",
				"points" => 0,
				"true" => 0,
				"value" => "",
				);
			if (strlen($value) > 0)
			{
				$solution["value"] = $value;
				$solution["points"] = $this->answers[$value]->get_points();
				if ($this->answers[$value]->isStateChecked())
				{
					$solution["true"] = 1;
				}
			}
			$counter++;
			$user_result[$value] = $solution;
		}
		return $user_result;
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

		$db =& $ilDB->db;

		include_once "./assessment/classes/class.ilObjTest.php";
		$pass = ilObjTest::_getPass($ilUser->id, $test_id);

		$query = sprintf("DELETE FROM tst_solutions WHERE user_fi = %s AND test_fi = %s AND question_fi = %s AND pass = %s",
			$db->quote($ilUser->id . ""),
			$db->quote($test_id . ""),
			$db->quote($this->getId() . ""),
			$db->quote($pass . "")
		);
		$result = $db->query($query);
		foreach ($_POST as $key => $value)
		{
			if (preg_match("/^xooa_result_(\d+)/", $key, $matches))
			{
				$query = sprintf("INSERT INTO tst_solutions (solution_id, user_fi, test_fi, question_fi, value1, value2, pass, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, NULL, %s, NULL)",
					$db->quote($ilUser->id),
					$db->quote($test_id),
					$db->quote($this->getId()),
					$db->quote($value),
					$db->quote($pass . "")
				);
				$result = $db->query($query);
			}
		}
		
    parent::saveWorkingData($test_id);
		return true;
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
	
			$query = sprintf("UPDATE qpl_questions SET obj_fi = %s, title = %s, comment = %s, author = %s, question_text = %s, number_X = %s, points = %s, working_time=%s, shuffle = %s, complete = %s WHERE question_id = %s",
				$db->quote($this->obj_id. ""),
				$db->quote($this->title. ""),
				$db->quote($this->comment. ""),
				$db->quote($this->author. ""),
				$db->quote($this->question. ""),
				$db->quote($this->number_X),
				$db->quote($this->getMaximumPoints() . ""),
				$db->quote($estw_time. ""),
				$db->quote($this->shuffle. ""),
				$db->quote($complete. ""),
				$db->quote($this->original_id. "")
			);
			$result = $db->query($query);

			if ($result == DB_OK)
			{
				// write answers
				// delete old answers
				$query = sprintf("DELETE FROM qpl_answers WHERE question_fi = %s",
					$db->quote($this->original_id)
				);
				$result = $db->query($query);
	
				foreach ($this->answers as $key => $value)
				{
					$answer_obj = $this->answers[$key];
					$query = sprintf("INSERT INTO qpl_answers (answer_id, question_fi, answertext, points, aorder, correctness, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, NULL)",
					$db->quote($this->original_id. ""),
					$db->quote($answer_obj->get_answertext(). ""),
					$db->quote($answer_obj->get_points() . ""),
					$db->quote($answer_obj->get_order() . ""),
					$db->quote($answer_obj->getState() . "")
					);
					$answer_result = $db->query($query);
				}
			}
			parent::syncWithOriginal();
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
		$question_type = 10;
		return $question_type;
	}
	
}

?>

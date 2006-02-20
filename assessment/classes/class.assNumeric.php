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
* Class for numeric questions
*
* ASS_Numeric is a class for numeric questions. To solve a numeric
* question, a learner has to enter a numerical value in a defined range
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @author   Nina Gharib <nina@wgserve.de>
* @version	$Id$
* @module   class.assNumeric.php
* @modulegroup   Assessment
*/
class ASS_Numeric extends ASS_Question
{
	/**
	* Question string
	*
	* The question string of the numeric question
	*
	* @var string
	*/
	var $question;

	/**
	* The defined ranges with the associated points for entering a value in the correct range
	*
	* $ranges is an array of the defined ranges of the numeric question
	*
	* @var array
	*/
	var $ranges;

	/**
	* ASS_Numeric constructor
	*
	* The constructor takes possible arguments an creates an instance of the ASS_Numeric object.
	*
	* @param string $title A title string to describe the question
	* @param string $comment A comment string to describe the question
	* @param string $author A string containing the name of the questions author
	* @param integer $owner A numerical ID to identify the owner/creator
	* @param string $question The question string of the numeric question
	* @access public
	* @see ASS_Question:ASS_Question()
	*/
	function ASS_Numeric(
		$title = "",
		$comment = "",
		$author = "",
		$owner = -1,
		$question = ""
	  )
	{
		$this->ASS_Question($title, $comment, $author, $owner);
		$this->question = $question;
		$this->ranges = array();
	}

	/**
	* Returns true, if a numeric question is complete for use
	*
	* Returns true, if a numeric question is complete for use
	*
	* @return boolean True, if the numeric question is complete for use, otherwise false
	* @access public
	*/
	function isComplete()
	{
		if (($this->title) and ($this->author) and ($this->question) and (count($this->ranges)))
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
		return;
		
		// TODO: Create a QTI representation for numeric questions. Use the following mc code for help
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
		$akeys = array_keys($this->ranges);
		if ($this->getshuffle() && $a_shuffle)
		{
			$akeys = $this->pcArrayShuffle($akeys);
		}
		// add answers
		foreach ($akeys as $index)
		{
			$answer = $this->ranges[$index];
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
		foreach ($this->ranges as $index => $answer)
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
		foreach ($this->ranges as $index => $answer)
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
	* Saves a ASS_Numeric object to a database
	*
	* Saves a ASS_Numeric object to a database (experimental)
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
			$query = sprintf("INSERT INTO qpl_questions (question_id, question_type_fi, obj_fi, title, comment, author, owner, question_text, points, working_time, complete, created, original_id, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, NULL)",
				$db->quote($question_type),
				$db->quote($this->obj_id),
				$db->quote($this->title),
				$db->quote($this->comment),
				$db->quote($this->author),
				$db->quote($this->owner),
				$db->quote($this->question),
				$db->quote($this->getMaximumPoints() . ""),
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
			$query = sprintf("UPDATE qpl_questions SET obj_fi = %s, title = %s, comment = %s, author = %s, question_text = %s, points = %s, working_time=%s, complete = %s WHERE question_id = %s",
				$db->quote($this->obj_id. ""),
				$db->quote($this->title),
				$db->quote($this->comment),
				$db->quote($this->author),
				$db->quote($this->question),
				$db->quote($this->getMaximumPoints() . ""),
				$db->quote($estw_time),
				$db->quote("$complete"),
				$db->quote($this->id)
			);
			$result = $db->query($query);
		}
		if ($result == DB_OK)
		{
			// Write Ranges to the database
			
			// 1. delete old ranges
			$query = sprintf("DELETE FROM qpl_numeric_range WHERE question_fi = %s",
				$db->quote($this->id)
			);
			$result = $db->query($query);

			// 2. write ranges
			foreach ($this->ranges as $key => $range)
			{
				$query = sprintf("INSERT INTO qpl_numeric_range (range_id, question_fi, lowerlimit, upperlimit, points, aorder, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, NULL)",
				$db->quote($this->id),
				$db->quote($range->getLowerLimit()),
				$db->quote($range->getUpperLimit() . ""),
				$db->quote($range->getPoints() . ""),
				$db->quote($range->getOrder() . "")
				);
				$answer_result = $db->query($query);
			}
		}
		parent::saveToDb($original_id);
	}

	/**
	* Loads a ASS_Numeric object from a database
	*
	* Loads a ASS_Numeric object from a database (experimental)
	*
	* @param object $db A pear DB object
	* @param integer $question_id A unique key which defines the multiple choice test in the database
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
				$this->setEstimatedWorkingTime(substr($data->working_time, 0, 2), substr($data->working_time, 3, 2), substr($data->working_time, 6, 2));
			}

			$query = sprintf("SELECT * FROM qpl_numeric_range WHERE question_fi = %s ORDER BY aorder ASC",
				$db->quote($question_id)
			);

			$result = $db->query($query);

			include_once "./assessment/classes/class.assNumericRange.php";
			if (strcmp(strtolower(get_class($result)), db_result) == 0)
			{
				while ($data = $result->fetchRow(DB_FETCHMODE_ASSOC))
				{
					array_push($this->ranges, new ASS_NumericRange($data["lowerlimit"], $data["upperlimit"], $data["points"], $data["aorder"]));
				}
			}
		}
		parent::loadFromDb($question_id);
	}


	/**
	* Sets the numeric question
	*
	* Sets the question string of the ASS_Numeric object
	*
	* @param string $question A string containing the numeric question
	* @access public
	* @see $question
	*/
	function set_question($question = "")
	{
		$this->question = $question;
	}

	/**
	* Adds a range to the numeric question
	*
	* Adds a range to the numeric question. An ASS_NumericRange object will be
	* created and assigned to the array $this->ranges
	*
	* @param double $lowerlimit The lower limit of the range
	* @param double $upperlimit The upper limit of the range
	* @param double $points The points for entering a number in the correct range
	* @param integer $order The display order of the range
	* @access public
	* @see $ranges
	* @see ASS_NumericalRange
	*/
	function addRange(
		$lowerlimit = 0.0,
		$upperlimit = 0.0,
		$points = 0.0,
		$order = 0
	)
	{
		$found = -1;
		foreach ($this->ranges as $key => $range)
		{
			if ($range->getOrder() == $order)
			{
				$found = $order;
			}
		}
		include_once "./assessment/classes/class.assNumericRange.php";
		if ($found >= 0)
		{
			// insert range
			$range = new ASS_NumericRange($lowerlimit, $upperlimit, $points, $found);
			array_push($this->ranges, $range);
			for ($i = $found + 1; $i < count($this->ranges); $i++)
			{
				$this->ranges[$i] = $this->ranges[$i-1];
			}
			$this->ranges[$found] = $range;
		}
		else
		{
			// append range
			$range = new ASS_NumericRange($lowerlimit, $upperlimit, $points, count($this->ranges));
			array_push($this->ranges, $range);
		}
	}

	/**
	* Duplicates an ASS_NumericQuestion
	*
	* Duplicates an ASS_NumericQuestion
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
	* Copies an ASS_Numeric object
	*
	* Copies an ASS_Numeric object
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
	* Gets the numeric question text
	*
	* Gets the question string of the ASS_Numeric object
	*
	* @return string The question string of the ASS_Numeric object
	* @access public
	* @see $question
	*/
	function get_question()
	{
		return $this->question;
	}

	/**
	* Returns the number of ranges
	*
	* Returns the number of ranges
	*
	* @return integer The number of ranges of the numeric question
	* @access public
	* @see $ranges
	*/
	function getRangeCount()
	{
		return count($this->ranges);
	}

	/**
	* Returns a range
	*
	* Returns a range with a given index. The index of the first
	* range is 0, the index of the second range is 1 and so on.
	*
	* @param integer $index A nonnegative index of the n-th range
	* @return object ASS_NumericelRange-Object containing the range
	* @access public
	* @see $ranges
	*/
	function getRange($index = 0)
	{
		if ($index < 0) return NULL;
		if (count($this->ranges) < 1) return NULL;
		if ($index >= count($this->ranges)) return NULL;

		return $this->ranges[$index];
	}

	/**
	* Deletes a range
	*
	* Deletes a range with a given index. The index of the first
	* range is 0, the index of the second range is 1 and so on.
	*
	* @param integer $index A nonnegative index of the n-th range
	* @access public
	* @see $ranges
	*/
	function deleteRange($index = 0)
	{
		if ($index < 0) return;
		if (count($this->ranges) < 1) return;
		if ($index >= count($this->ranges)) return;
		unset($this->ranges[$index]);
		$this->ranges = array_values($this->ranges);
		for ($i = 0; $i < count($this->ranges); $i++)
		{
			if ($this->ranges[$i]->getOrder() > $index)
			{
				$this->ranges[$i]->setOrder($i);
			}
		}
	}

	/**
	* Deletes all ranges
	*
	* Deletes all ranges
	*
	* @access public
	* @see $ranges
	*/
	function flushRanges()
	{
		$this->ranges = array();
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
		$max = 0;
		foreach ($this->ranges as $key => $range) 
		{
			if ($range->getPoints() > $max)
			{
				$max = $range->getPoints();
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
		$data = $result->fetchRow(DB_FETCHMODE_ASSOC);
		
		$enteredvalue = $data["value1"];
		if (!is_numeric($enteredvalue)) return 0;
		$points = 0;
		foreach ($this->ranges as $key => $range)
		{
			if ($points == 0)
			{
				if ($range->contains($enteredvalue))
				{
					$points = $range->getPoints();
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
		if ($data = $result->fetchRow(DB_FETCHMODE_OBJECT))
		{
			if (strcmp($data->value1, "") != 0)
			{
				return TRUE;
			}
		}
		else
		{
			return FALSE;
		}
		return FALSE;
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
		if ($data = $result->fetchRow(DB_FETCHMODE_OBJECT))
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
				"value" => "",
				);
			if (strlen($value) > 0)
			{
				$solution["value"] = $value;
				$solution["points"] = $this->ranges[$value]->getPoints();
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
	* @see $ranges
	*/
	function saveWorkingData($test_id, $limit_to = LIMIT_NO_LIMIT)
	{
		global $ilDB;
		global $ilUser;

		$db =& $ilDB->db;

		include_once "./assessment/classes/class.ilObjTest.php";
		$pass = ilObjTest::_getPass($ilUser->id, $test_id);

		$query = sprintf("SELECT * FROM tst_solutions WHERE user_fi = %s AND test_fi = %s AND question_fi = %s AND pass = %s",
			$db->quote($ilUser->id . ""),
			$db->quote($test_id . ""),
			$db->quote($this->getId() . ""),
			$db->quote($pass . "")
		);
		$result = $db->query($query);
		$row = $result->fetchRow(DB_FETCHMODE_OBJECT);
		$update = $row->solution_id;
		if ($update)
		{
			$query = sprintf("UPDATE tst_solutions SET value1 = %s WHERE solution_id = %s",
				$db->quote($_POST["numeric_result"]),
				$db->quote($update));
		}
		else
		{
			$query = sprintf("INSERT INTO tst_solutions (solution_id, user_fi, test_fi, question_fi, value1, value2, pass, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, NULL, %s, NULL)",
				$db->quote($ilUser->id),
				$db->quote($test_id),
				$db->quote($this->getId()),
				$db->quote($_POST["numeric_result"]),
				$db->quote($pass . "")
			);
		}
		$result = $db->query($query);
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
	
			$query = sprintf("UPDATE qpl_questions SET obj_fi = %s, title = %s, comment = %s, author = %s, question_text = %s, points = %s, working_time=%s, complete = %s WHERE question_id = %s",
				$db->quote($this->obj_id. ""),
				$db->quote($this->title. ""),
				$db->quote($this->comment. ""),
				$db->quote($this->author. ""),
				$db->quote($this->question. ""),
				$db->quote($this->getMaximumPoints() . ""),
				$db->quote($estw_time. ""),
				$db->quote($complete. ""),
				$db->quote($this->original_id. "")
			);
			$result = $db->query($query);

			if ($result == DB_OK)
			{
				// Write Ranges to the database
				
				// 1. delete old ranges
				$query = sprintf("DELETE FROM qpl_numeric_range WHERE question_fi = %s",
					$db->quote($this->original_id)
				);
				$result = $db->query($query);
	
				// 2. write ranges
				foreach ($this->ranges as $key => $range)
				{
					$query = sprintf("INSERT INTO qpl_numeric_range (range_id, question_fi, lowerlimit, upperlimit, points, aorder, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, NULL)",
					$db->quote($this->original_id),
					$db->quote($range->getLowerLimit()),
					$db->quote($range->getUpperLimit() . ""),
					$db->quote($range->getPoints() . ""),
					$db->quote($range->getOrder() . "")
					);
					$answer_result = $db->query($query);
				}
			}
			parent::syncWithOriginal();
		}
	}
}

?>

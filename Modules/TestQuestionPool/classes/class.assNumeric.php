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
include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
include_once "./Modules/Test/classes/inc.AssessmentConstants.php";

/**
* Class for numeric questions
*
* assNumeric is a class for numeric questions. To solve a numeric
* question, a learner has to enter a numerical value in a defined range
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @author   Nina Gharib <nina@wgserve.de>
* @version	$Id$
* @ingroup ModulesTestQuestionPool
*/
class assNumeric extends assQuestion
{
	/**
	* The defined ranges with the associated points for entering a value in the correct range
	*
	* $ranges is an array of the defined ranges of the numeric question
	*
	* @var array
	*/
	var $ranges;
	
	/**
	* The maximum number of characters for the numeric input field
	*
	* The maximum number of characters for the numeric input field
	*
	* @var integer
	*/
	var $maxchars;

	/**
	* assNumeric constructor
	*
	* The constructor takes possible arguments an creates an instance of the assNumeric object.
	*
	* @param string $title A title string to describe the question
	* @param string $comment A comment string to describe the question
	* @param string $author A string containing the name of the questions author
	* @param integer $owner A numerical ID to identify the owner/creator
	* @param string $question The question string of the numeric question
	* @access public
	* @see assQuestion:assQuestion()
	*/
	function assNumeric(
		$title = "",
		$comment = "",
		$author = "",
		$owner = -1,
		$question = ""
	  )
	{
		$this->assQuestion($title, $comment, $author, $owner, $question);
		$this->ranges = array();
		$this->maxchars = 6;
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
		if (($this->title) and ($this->author) and ($this->question) and (count($this->ranges)) and ($this->getMaximumPoints() > 0))
		{
			return true;
		}
			else
		{
			return false;
		}
	}

	/**
	* Creates a question from a QTI file
	*
	* Receives parameters from a QTI parser and creates a valid ILIAS question object
	*
	* @param object $item The QTI item object
	* @param integer $questionpool_id The id of the parent questionpool
	* @param integer $tst_id The id of the parent test if the question is part of a test
	* @param object $tst_object A reference to the parent test object
	* @param integer $question_counter A reference to a question counter to count the questions of an imported question pool
	* @param array $import_mapping An array containing references to included ILIAS objects
	* @access public
	*/
	function fromXML(&$item, &$questionpool_id, &$tst_id, &$tst_object, &$question_counter, &$import_mapping)
	{
		global $ilUser;

		// empty session variable for imported xhtml mobs
		unset($_SESSION["import_mob_xhtml"]);
		$presentation = $item->getPresentation(); 
		$duration = $item->getDuration();
		$now = getdate();
		$maxchars = 0;
		$points = 0;
		$upperlimit = 0;
		$lowerlimit = 0;
		$created = sprintf("%04d%02d%02d%02d%02d%02d", $now['year'], $now['mon'], $now['mday'], $now['hours'], $now['minutes'], $now['seconds']);
		foreach ($presentation->order as $entry)
		{
			switch ($entry["type"])
			{
				case "response":
					$response = $presentation->response[$entry["index"]];
					$rendertype = $response->getRenderType(); 
					switch (strtolower(get_class($rendertype)))
					{
						case "ilqtirenderfib":
							$maxchars = $rendertype->getMaxchars();
							break;
					}
					break;
			}
		}

		foreach ($item->resprocessing as $resprocessing)
		{
			foreach ($resprocessing->respcondition as $respcondition)
			{
				$conditionvar = $respcondition->getConditionvar();
				foreach ($conditionvar->order as $order)
				{
					switch ($order["field"])
					{
						case "varlte":
							$upperlimit = $conditionvar->varlte[$order["index"]]->getContent();
							break;
						case "vargte":
							$lowerlimit = $conditionvar->vargte[$order["index"]]->getContent();
							break;
					}
				}
				foreach ($respcondition->setvar as $setvar)
				{
					$points = $setvar->getContent();
				}
			}
		}
		
		$this->setTitle($item->getTitle());
		$this->setComment($item->getComment());
		$this->setAuthor($item->getAuthor());
		$this->setOwner($ilUser->getId());
		$this->setQuestion($this->QTIMaterialToString($item->getQuestiontext()));
		$this->setObjId($questionpool_id);
		$this->setEstimatedWorkingTime($duration["h"], $duration["m"], $duration["s"]);
		$this->setMaxChars($maxchars);
		$this->addRange($lowerlimit, $upperlimit, $points);
		$this->saveToDb();
		if (count($item->suggested_solutions))
		{
			foreach ($item->suggested_solutions as $suggested_solution)
			{
				$this->setSuggestedSolution($suggested_solution["solution"]->getContent(), $suggested_solution["gap_index"], true);
			}
			$this->saveToDb();
		}
		// handle the import of media objects in XHTML code
		if (is_array($_SESSION["import_mob_xhtml"]))
		{
			include_once "./Services/MediaObjects/classes/class.ilObjMediaObject.php";
			include_once "./Services/RTE/classes/class.ilRTE.php";
			foreach ($_SESSION["import_mob_xhtml"] as $mob)
			{
				if ($tst_id > 0)
				{
					include_once "./Modules/Test/classes/class.ilObjTest.php";
					$importfile = ilObjTest::_getImportDirectory() . "/" . $_SESSION["tst_import_subdir"] . "/" . $mob["uri"];
				}
				else
				{
					include_once "./Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php";
					$importfile = ilObjQuestionPool::_getImportDirectory() . "/" . $_SESSION["qpl_import_subdir"] . "/" . $mob["uri"];
				}
				$media_object =& ilObjMediaObject::_saveTempFileAsMediaObject(basename($importfile), $importfile, FALSE);
				ilObjMediaObject::_saveUsage($media_object->getId(), "qpl:html", $this->getId());
				$this->setQuestion(ilRTE::_replaceMediaObjectImageSrc(str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $this->getQuestion()), 1));
			}
			$this->saveToDb();
		}
		if ($tst_id > 0)
		{
			$q_1_id = $this->getId();
			$question_id = $this->duplicate(true);
			$tst_object->questions[$question_counter++] = $question_id;
			$import_mapping[$item->getIdent()] = array("pool" => $q_1_id, "test" => $question_id);
		}
		else
		{
			$import_mapping[$item->getIdent()] = array("pool" => $this->getId(), "test" => 0);
		}
		//$ilLog->write(strftime("%D %T") . ": finished import multiple choice question (single response)");
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
	function toXML($a_include_header = true, $a_include_binary = true, $a_shuffle = false, $test_output = false, $force_image_references = false)
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
		$a_xml_writer->xmlElement("fieldentry", NULL, NUMERIC_QUESTION_IDENTIFIER);
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
		$this->addQTIMaterial($a_xml_writer, $this->getQuestion());
		// add answers to presentation
		$attrs = array(
			"ident" => "NUM",
			"rcardinality" => "Single",
			"numtype" => "Decimal"
		);
		$a_xml_writer->xmlStartTag("response_num", $attrs);
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
		$attrs = array(
			"fibtype" => "Decimal",
			"maxchars" => $this->getMaxChars()
		);
		$a_xml_writer->xmlStartTag("render_fib", $attrs);
		$a_xml_writer->xmlEndTag("render_fib");
		$a_xml_writer->xmlEndTag("response_num");
		$a_xml_writer->xmlEndTag("flow");
		$a_xml_writer->xmlEndTag("presentation");
		
		// PART II: qti resprocessing
		$a_xml_writer->xmlStartTag("resprocessing");
		$a_xml_writer->xmlStartTag("outcomes");
		$a_xml_writer->xmlStartTag("decvar");
		$a_xml_writer->xmlEndTag("decvar");
		$a_xml_writer->xmlEndTag("outcomes");
		// add response conditions
		foreach ($this->ranges as $index => $range)
		{
			$a_xml_writer->xmlStartTag("respcondition");
			// qti conditionvar
			$a_xml_writer->xmlStartTag("conditionvar");
			$attrs = array(
				"respident" => "NUM"
			);
			$a_xml_writer->xmlElement("vargte", $attrs, $range->getLowerLimit());
			$a_xml_writer->xmlElement("varlte", $attrs, $range->getUpperLimit());
			$a_xml_writer->xmlEndTag("conditionvar");
			// qti setvar
			$attrs = array(
				"action" => "Add"
			);
			$a_xml_writer->xmlElement("setvar", $attrs, $range->getPoints());
			// qti displayfeedback
			$attrs = array(
				"feedbacktype" => "Response",
				"linkrefid" => "Correct"
			);
			$a_xml_writer->xmlElement("displayfeedback", $attrs);
			$a_xml_writer->xmlEndTag("respcondition");
		}
		$a_xml_writer->xmlEndTag("resprocessing");

		// PART III: qti itemfeedback
		foreach ($this->ranges as $index => $range)
		{
			$attrs = array(
				"ident" => "Correct",
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
	* Saves a assNumeric object to a database
	*
	* Saves a assNumeric object to a database (experimental)
	*
	* @param object $db A pear DB object
	* @access public
	*/
	function saveToDb($original_id = "")
	{
		global $ilDB;

		$complete = 0;
		if ($this->isComplete())
		{
			$complete = 1;
		}
		$estw_time = $this->getEstimatedWorkingTime();
		$estw_time = sprintf("%02d:%02d:%02d", $estw_time['h'], $estw_time['m'], $estw_time['s']);

		if ($original_id)
		{
			$original_id = $ilDB->quote($original_id);
		}
		else
		{
			$original_id = "NULL";
		}

		// cleanup RTE images which are not inserted into the question text
		include_once("./Services/RTE/classes/class.ilRTE.php");
		if ($this->id == -1)
		{
			// Neuen Datensatz schreiben
			$now = getdate();
			$question_type = $this->getQuestionTypeID();
			$created = sprintf("%04d%02d%02d%02d%02d%02d", $now['year'], $now['mon'], $now['mday'], $now['hours'], $now['minutes'], $now['seconds']);
			$query = sprintf("INSERT INTO qpl_questions (question_id, question_type_fi, obj_fi, title, comment, author, owner, question_text, points, working_time, complete, created, original_id, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, NULL)",
				$ilDB->quote($question_type),
				$ilDB->quote($this->obj_id),
				$ilDB->quote($this->title),
				$ilDB->quote($this->comment),
				$ilDB->quote($this->author),
				$ilDB->quote($this->owner),
				$ilDB->quote(ilRTE::_replaceMediaObjectImageSrc($this->question, 0)),
				$ilDB->quote($this->getMaximumPoints() . ""),
				$ilDB->quote($estw_time),
				$ilDB->quote("$complete"),
				$ilDB->quote($created),
				$original_id
			);
			$result = $ilDB->query($query);
			
			if ($result == DB_OK)
			{
				$this->id = $ilDB->getLastInsertId();
				$query = sprintf("INSERT INTO qpl_question_numeric (question_fi, maxNumOfChars) VALUES (%s, %s)",
					$ilDB->quote($this->id . ""),
					$ilDB->quote($this->getMaxChars() . "")
				);
				$ilDB->query($query);

				// create page object of question
				$this->createPageObject();

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
				$ilDB->quote($this->obj_id. ""),
				$ilDB->quote($this->title),
				$ilDB->quote($this->comment),
				$ilDB->quote($this->author),
				$ilDB->quote(ilRTE::_replaceMediaObjectImageSrc($this->question, 0)),
				$ilDB->quote($this->getMaximumPoints() . ""),
				$ilDB->quote($estw_time),
				$ilDB->quote("$complete"),
				$ilDB->quote($this->id)
			);
			$result = $ilDB->query($query);
			$query = sprintf("UPDATE qpl_question_numeric SET maxNumOfChars = %s WHERE question_fi = %s",
				$ilDB->quote($this->getMaxChars() . ""),
				$ilDB->quote($this->id . "")
			);
			$result = $ilDB->query($query);
		}
		if ($result == DB_OK)
		{
			// Write Ranges to the database
			
			// 1. delete old ranges
			$query = sprintf("DELETE FROM qpl_numeric_range WHERE question_fi = %s",
				$ilDB->quote($this->id)
			);
			$result = $ilDB->query($query);

			// 2. write ranges
			foreach ($this->ranges as $key => $range)
			{
				$query = sprintf("INSERT INTO qpl_numeric_range (range_id, question_fi, lowerlimit, upperlimit, points, aorder, lastchange) VALUES (NULL, %s, %s, %s, %s, %s, NULL)",
				$ilDB->quote($this->id),
				$ilDB->quote($range->getLowerLimit()),
				$ilDB->quote($range->getUpperLimit() . ""),
				$ilDB->quote($range->getPoints() . ""),
				$ilDB->quote($range->getOrder() . "")
				);
				$answer_result = $ilDB->query($query);
			}
		}
		parent::saveToDb($original_id);
	}

	/**
	* Loads a assNumeric object from a database
	*
	* Loads a assNumeric object from a database (experimental)
	*
	* @param object $db A pear DB object
	* @param integer $question_id A unique key which defines the multiple choice test in the database
	* @access public
	*/
	function loadFromDb($question_id)
	{
		global $ilDB;
		
    $query = sprintf("SELECT qpl_questions.*, qpl_question_numeric.* FROM qpl_questions, qpl_question_numeric WHERE question_id = %s AND qpl_questions.question_id = qpl_question_numeric.question_fi",
			$ilDB->quote($question_id)
		);
		$result = $ilDB->query($query);
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
				include_once("./Services/RTE/classes/class.ilRTE.php");
				$this->question = ilRTE::_replaceMediaObjectImageSrc($data->question_text, 1);
				$this->maxchars = $data->maxNumOfChars;
				$this->setEstimatedWorkingTime(substr($data->working_time, 0, 2), substr($data->working_time, 3, 2), substr($data->working_time, 6, 2));
			}

			$query = sprintf("SELECT * FROM qpl_numeric_range WHERE question_fi = %s ORDER BY aorder ASC",
				$ilDB->quote($question_id)
			);

			$result = $ilDB->query($query);

			include_once "./Modules/TestQuestionPool/classes/class.assNumericRange.php";
			if (strcmp(strtolower(get_class($result)), db_result) == 0)
			{
				while ($data = $result->fetchRow(DB_FETCHMODE_ASSOC))
				{
					array_push($this->ranges, new assNumericRange($data["lowerlimit"], $data["upperlimit"], $data["points"], $data["aorder"]));
				}
			}
		}
		parent::loadFromDb($question_id);
	}

	/**
	* Adds a range to the numeric question
	*
	* Adds a range to the numeric question. An assNumericRange object will be
	* created and assigned to the array $this->ranges
	*
	* @param double $lowerlimit The lower limit of the range
	* @param double $upperlimit The upper limit of the range
	* @param double $points The points for entering a number in the correct range
	* @param integer $order The display order of the range
	* @access public
	* @see $ranges
	* @see assNumericalRange
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
		include_once "./Modules/TestQuestionPool/classes/class.assNumericRange.php";
		if ($found >= 0)
		{
			// insert range
			$range = new assNumericRange($lowerlimit, $upperlimit, $points, $found);
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
			$range = new assNumericRange($lowerlimit, $upperlimit, $points, count($this->ranges));
			array_push($this->ranges, $range);
		}
	}

	/**
	* Duplicates an assNumericQuestion
	*
	* Duplicates an assNumericQuestion
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
		include_once ("./Modules/TestQuestionPool/classes/class.assQuestion.php");
		$original_id = assQuestion::_getOriginalId($this->id);
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
		// copy XHTML media objects
		$clone->copyXHTMLMediaObjectsOfQuestion($original_id);
		// duplicate the generic feedback
		$clone->duplicateFeedbackGeneric($original_id);

		return $clone->id;
	}

	/**
	* Copies an assNumeric object
	*
	* Copies an assNumeric object
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
		include_once ("./Modules/TestQuestionPool/classes/class.assQuestion.php");
		$original_id = assQuestion::_getOriginalId($this->id);
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
		// copy XHTML media objects
		$clone->copyXHTMLMediaObjectsOfQuestion($original_id);
		// duplicate the generic feedback
		$clone->duplicateFeedbackGeneric($original_id);

		return $clone->id;
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
	* @return object assNumericelRange-Object containing the range
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
	* Returns the range with the maximum points, a learner can reach answering the question
	*
	* Returns the range with the maximum points, a learner can reach answering the question
	*
	* @access public
	* @see $points
	*/
	function getBestRange()
	{
		$max = 0;
		$bestrange = NULL;
		foreach ($this->ranges as $key => $range) 
		{
			if ($range->getPoints() > $max)
			{
				$max = $range->getPoints();
				$bestrange = $range;
			}
		}
		return $bestrange;
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
	function calculateReachedPoints($active_id, $pass = NULL)
	{
		global $ilDB;
		
		$found_values = array();
		if (is_null($pass))
		{
			$pass = $this->getSolutionMaxPass($active_id);
		}
		$query = sprintf("SELECT * FROM tst_solutions WHERE active_fi = %s AND question_fi = %s AND pass = %s",
			$ilDB->quote($active_id . ""),
			$ilDB->quote($this->getId() . ""),
			$ilDB->quote($pass . "")
		);
		$result = $ilDB->query($query);
		$data = $result->fetchRow(DB_FETCHMODE_ASSOC);
		
		$enteredvalue = $data["value1"];
		//if (!is_numeric($enteredvalue)) return 0;
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

		$points = parent::calculateReachedPoints($active_id, $pass = NULL, $points);
		return $points;
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
	function saveWorkingData($active_id, $pass = NULL)
	{
		global $ilDB;
		global $ilUser;

		if (is_null($pass))
		{
			include_once "./Modules/Test/classes/class.ilObjTest.php";
			$pass = ilObjTest::_getPass($active_id);
		}
		$entered_values = 0;
		$numeric_result = str_replace(",",".",$_POST["numeric_result"]);
		$query = sprintf("SELECT * FROM tst_solutions WHERE active_fi = %s AND question_fi = %s AND pass = %s",
			$ilDB->quote($active_id . ""),
			$ilDB->quote($this->getId() . ""),
			$ilDB->quote($pass . "")
		);
		$result = $ilDB->query($query);
		$row = $result->fetchRow(DB_FETCHMODE_OBJECT);
		$update = $row->solution_id;
		if ($update)
		{
			if (strlen($numeric_result))
			{
				$query = sprintf("UPDATE tst_solutions SET value1 = %s WHERE solution_id = %s",
					$ilDB->quote(trim($numeric_result)),
					$ilDB->quote($update)
				);
				$result = $ilDB->query($query);
				$entered_values++;
			}
			else
			{
				$query = sprintf("DELETE FROM tst_solutions WHERE solution_id = %s",
					$ilDB->quote($update)
				);
				$result = $ilDB->query($query);
			}
		}
		else
		{
			if (strlen($numeric_result))
			{
				$query = sprintf("INSERT INTO tst_solutions (solution_id, active_fi, question_fi, value1, value2, pass, TIMESTAMP) VALUES (NULL, %s, %s, %s, NULL, %s, NULL)",
					$ilDB->quote($active_id),
					$ilDB->quote($this->getId()),
					$ilDB->quote(trim($numeric_result)),
					$ilDB->quote($pass . "")
				);
				$result = $ilDB->query($query);
				$entered_values++;
			}
		}
		if ($entered_values)
		{
			include_once ("./classes/class.ilObjAssessmentFolder.php");
			if (ilObjAssessmentFolder::_enabledAssessmentLogging())
			{
				$this->logAction($this->lng->txtlng("assessment", "log_user_entered_values", ilObjAssessmentFolder::_getLogLanguage()), $active_id, $this->getId());
			}
		}
		else
		{
			include_once ("./classes/class.ilObjAssessmentFolder.php");
			if (ilObjAssessmentFolder::_enabledAssessmentLogging())
			{
				$this->logAction($this->lng->txtlng("assessment", "log_user_not_entered_values", ilObjAssessmentFolder::_getLogLanguage()), $active_id, $this->getId());
			}
		}
    parent::saveWorkingData($active_id, $pass);
		return true;
	}

	function syncWithOriginal()
	{
		global $ilDB;
		
		if ($this->original_id)
		{
			$complete = 0;
			if ($this->isComplete())
			{
				$complete = 1;
			}
	
			$estw_time = $this->getEstimatedWorkingTime();
			$estw_time = sprintf("%02d:%02d:%02d", $estw_time['h'], $estw_time['m'], $estw_time['s']);
	
			$query = sprintf("UPDATE qpl_questions SET obj_fi = %s, title = %s, comment = %s, author = %s, question_text = %s, points = %s, working_time=%s, complete = %s WHERE question_id = %s",
				$ilDB->quote($this->obj_id. ""),
				$ilDB->quote($this->title. ""),
				$ilDB->quote($this->comment. ""),
				$ilDB->quote($this->author. ""),
				$ilDB->quote($this->question. ""),
				$ilDB->quote($this->getMaximumPoints() . ""),
				$ilDB->quote($estw_time. ""),
				$ilDB->quote($complete. ""),
				$ilDB->quote($this->original_id. "")
			);
			$result = $ilDB->query($query);
			$query = sprintf("UPDATE qpl_question_numeric SET maxNumOfChars = %s WHERE question_fi = %s",
				$ilDB->quote($this->getMaxChars() . ""),
				$ilDB->quote($this->original_id . "")
			);
			$result = $ilDB->query($query);

			if ($result == DB_OK)
			{
				// Write Ranges to the database
				
				// 1. delete old ranges
				$query = sprintf("DELETE FROM qpl_numeric_range WHERE question_fi = %s",
					$ilDB->quote($this->original_id)
				);
				$result = $ilDB->query($query);
	
				// 2. write ranges
				foreach ($this->ranges as $key => $range)
				{
					$query = sprintf("INSERT INTO qpl_numeric_range (range_id, question_fi, lowerlimit, upperlimit, points, aorder, lastchange) VALUES (NULL, %s, %s, %s, %s, %s, NULL)",
					$ilDB->quote($this->original_id),
					$ilDB->quote($range->getLowerLimit()),
					$ilDB->quote($range->getUpperLimit() . ""),
					$ilDB->quote($range->getPoints() . ""),
					$ilDB->quote($range->getOrder() . "")
					);
					$answer_result = $ilDB->query($query);
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
		return "assNumeric";
	}
	
	/**
	* Returns the maximum number of characters for the numeric input field
	*
	* Returns the maximum number of characters for the numeric input field
	*
	* @return integer The maximum number of characters
	* @access public
	*/
	function getMaxChars()
	{
		return $this->maxchars;
	}
	
	/**
	* Sets the maximum number of characters for the numeric input field
	*
	* Sets the maximum number of characters for the numeric input field
	*
	* @param integer $maxchars The maximum number of characters
	* @access public
	*/
	function setMaxChars($maxchars)
	{
		$this->maxchars = $maxchars;
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
		return "qpl_question_numeric";
	}

	/**
	* Collects all text in the question which could contain media objects
	* which were created with the Rich Text Editor
	*/
	function getRTETextWithMediaObjects()
	{
		return parent::getRTETextWithMediaObjects();
	}
}

?>

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
* Class for TextSubset questions
*
* assTextSubset is a class for TextSubset questions. To solve a TextSubset
* question, a learner has to enter a TextSubsetal value in a defined range
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @author   Nina Gharib <nina@wgserve.de>
* @version	$Id$
* @ingroup ModulesTestQuestionPool
*/
class assTextSubset extends assQuestion
{
	/**
	* The text which defines the correct set of answers 
	*
	* The text which defines the correct set of answers 
	*
	* @var array
	*/
	var $answers;
	
	/**
	* The number of correct answers to solve the question
	*
	* The number of correct answers to solve the question
	*
	* @var integer
	*/
	var $correctanswers;

	/**
	* The method which should be chosen for text comparisons
	*
	* The method which should be chosen for text comparisons
	*
	* @var string
	*/
	var $text_rating;

	/**
	* assTextSubset constructor
	*
	* The constructor takes possible arguments an creates an instance of the assTextSubset object.
	*
	* @param string $title A title string to describe the question
	* @param string $comment A comment string to describe the question
	* @param string $author A string containing the name of the questions author
	* @param integer $owner A TextSubsetal ID to identify the owner/creator
	* @param string $question The question string of the TextSubset question
	* @access public
	* @see assQuestion:assQuestion()
	*/
	function assTextSubset(
		$title = "",
		$comment = "",
		$author = "",
		$owner = -1,
		$question = ""
	  )
	{
		$this->assQuestion($title, $comment, $author, $owner, $question);
		$this->answers = array();
		$this->correctanswers = 0;
	}

	/**
	* Returns true, if a TextSubset question is complete for use
	*
	* Returns true, if a TextSubset question is complete for use
	*
	* @return boolean True, if the TextSubset question is complete for use, otherwise false
	* @access public
	*/
	function isComplete()
	{
		if (($this->title) and ($this->author) and ($this->question) and (count($this->answers) >= $this->correctanswers) and ($this->getMaximumPoints() > 0))
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
		$shuffle = 0;
		$idents = array();
		$now = getdate();
		$created = sprintf("%04d%02d%02d%02d%02d%02d", $now['year'], $now['mon'], $now['mday'], $now['hours'], $now['minutes'], $now['seconds']);
		$gaps = array();
		foreach ($presentation->order as $entry)
		{
			switch ($entry["type"])
			{
				case "response":
					$response = $presentation->response[$entry["index"]];
					if ($response->getResponseType() == RT_RESPONSE_STR)
					{
						array_push($idents, $response->getIdent());
					}
					break;
			}
		}
		$responses = array();
		foreach ($item->resprocessing as $resprocessing)
		{
			foreach ($resprocessing->respcondition as $respcondition)
			{
				$ident = "";
				$correctness = 1;
				$conditionvar = $respcondition->getConditionvar();
				foreach ($conditionvar->order as $order)
				{
					switch ($order["field"])
					{
						case "varsubset":
							$respident = $conditionvar->varsubset[$order["index"]]->getRespident();
							$content = $conditionvar->varsubset[$order["index"]]->getContent();
							if (!is_array($responses[$respident])) $responses[$respident] = array();
							$vars = split(",", $content);
							foreach ($vars as $var)
							{
								array_push($responses[$respident], array("solution" => $var, "points" => ""));
							}
							break;
					}
				}
				foreach ($respcondition->setvar as $setvar)
				{
					if ((strcmp($setvar->getVarname(), "matches") == 0) && ($setvar->getAction() == ACTION_ADD))
					{
						foreach ($responses[$respident] as $idx => $solutionarray)
						{
							if (strlen($solutionarray["points"] == 0))
							{
								$responses[$respident][$idx]["points"] = $setvar->getContent();
							}
						}
					}
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
		$textrating = $item->getMetadataEntry("textrating");
		if (strlen($textrating) == 0) $textrating = "ci";
		$this->setTextRating($textgap_rating);
		$this->setCorrectAnswers($item->getMetadataEntry("correctanswers"));
		$response = current($responses);
		$counter = 0;
		if (is_array($response))
		{
			foreach ($response as $answer)
			{
				$this->addAnswer($answer["solution"], $answer["points"], $counter);
				$counter++;
			}
		}
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
		$a_xml_writer->xmlElement("fieldentry", NULL, TEXTSUBSET_QUESTION_IDENTIFIER);
		$a_xml_writer->xmlEndTag("qtimetadatafield");
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "AUTHOR");
		$a_xml_writer->xmlElement("fieldentry", NULL, $this->getAuthor());
		$a_xml_writer->xmlEndTag("qtimetadatafield");
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "textrating");
		$a_xml_writer->xmlElement("fieldentry", NULL, $this->getTextRating());
		$a_xml_writer->xmlEndTag("qtimetadatafield");
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "correctanswers");
		$a_xml_writer->xmlElement("fieldentry", NULL, $this->getCorrectAnswers());
		$a_xml_writer->xmlEndTag("qtimetadatafield");
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "points");
		$a_xml_writer->xmlElement("fieldentry", NULL, $this->getPoints());
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
		for ($counter = 1; $counter <= $this->getCorrectAnswers(); $counter++)
		{
			$attrs = array(
				"ident" => "TEXTSUBSET_" . sprintf("%02d", $counter),
				"rcardinality" => "Single"
			);
			$a_xml_writer->xmlStartTag("response_str", $attrs);
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
				"fibtype" => "String",
				"columns" => $this->getMaxTextboxWidth()
			);
			$a_xml_writer->xmlStartTag("render_fib", $attrs);
			$a_xml_writer->xmlEndTag("render_fib");
			$a_xml_writer->xmlEndTag("response_str");
		}
		
		$a_xml_writer->xmlEndTag("flow");
		$a_xml_writer->xmlEndTag("presentation");
		
		// PART II: qti resprocessing
		$a_xml_writer->xmlStartTag("resprocessing");
		$a_xml_writer->xmlStartTag("outcomes");
		$a_xml_writer->xmlStartTag("decvar");
		$a_xml_writer->xmlEndTag("decvar");
		$attribs = array(
			"varname" => "matches",
			"defaultval" => "0"
		);
		$a_xml_writer->xmlElement("decvar", $attribs, NULL);
		$a_xml_writer->xmlEndTag("outcomes");
		// add response conditions
		for ($counter = 1; $counter <= $this->getCorrectAnswers(); $counter++)
		{
			$scoregroups =& $this->joinAnswers();
			foreach ($scoregroups as $points => $scoreanswers)
			{
				$attrs = array(
					"continue" => "Yes"
				);
				$a_xml_writer->xmlStartTag("respcondition", $attrs);
				// qti conditionvar
				$a_xml_writer->xmlStartTag("conditionvar");
				$attrs = array(
					"respident" => "TEXTSUBSET_" . sprintf("%02d", $counter)
				);
				$a_xml_writer->xmlElement("varsubset", $attrs, join(",", $scoreanswers));
				$a_xml_writer->xmlEndTag("conditionvar");
				// qti setvar
				$attrs = array(
					"varname" => "matches",
					"action" => "Add"
				);
				$a_xml_writer->xmlElement("setvar", $attrs, $points);
				// qti displayfeedback
				$attrs = array(
					"feedbacktype" => "Response",
					"linkrefid" => "Matches_" . sprintf("%02d", $counter)
				);
				$a_xml_writer->xmlElement("displayfeedback", $attrs);
				$a_xml_writer->xmlEndTag("respcondition");
			}
		}
		$a_xml_writer->xmlEndTag("resprocessing");

		// PART III: qti itemfeedback
		for ($counter = 1; $counter <= $this->getCorrectAnswers(); $counter++)
		{
			$attrs = array(
				"ident" => "Matches_" . sprintf("%02d", $counter),
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
	* Saves a assTextSubset object to a database
	*
	* Saves a assTextSubset object to a database (experimental)
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
				$query = sprintf("INSERT INTO qpl_question_textsubset (question_fi, textgap_rating, correctanswers) VALUES (%s, %s, %s)",
					$ilDB->quote($this->id . ""),
					$ilDB->quote($this->getTextRating() . ""),
					$ilDB->quote($this->getCorrectAnswers() . "")
				);
				$ilDB->query($query);

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
			$query = sprintf("UPDATE qpl_question_textsubset SET textgap_rating = %s, correctanswers = %s WHERE question_fi = %s",
				$ilDB->quote($this->getTextRating() . ""),
				$ilDB->quote($this->getCorrectAnswers() . ""),
				$ilDB->quote($this->id . "")
			);
			$result = $ilDB->query($query);
		}
		if ($result == DB_OK)
		{
			// Write Ranges to the database
			
			// 1. delete old ranges
			$query = sprintf("DELETE FROM qpl_answer_textsubset WHERE question_fi = %s",
				$ilDB->quote($this->id)
			);
			$result = $ilDB->query($query);

			// 2. write ranges
			foreach ($this->answers as $key => $value)
			{
				$answer_obj = $this->answers[$key];
				$query = sprintf("INSERT INTO qpl_answer_textsubset (answer_id, question_fi, answertext, points, aorder) VALUES (NULL, %s, %s, %s, %s)",
					$ilDB->quote($this->id),
					$ilDB->quote($answer_obj->getAnswertext()),
					$ilDB->quote($answer_obj->getPoints() . ""),
					$ilDB->quote($answer_obj->getOrder() . "")
				);
				$answer_result = $ilDB->query($query);
			}
		}
		parent::saveToDb($original_id);
	}

	/**
	* Loads a assTextSubset object from a database
	*
	* Loads a assTextSubset object from a database (experimental)
	*
	* @param object $db A pear DB object
	* @param integer $question_id A unique key which defines the multiple choice test in the database
	* @access public
	*/
	function loadFromDb($question_id)
	{
		global $ilDB;

    $query = sprintf("SELECT qpl_questions.*, qpl_question_textsubset.* FROM qpl_questions, qpl_question_textsubset WHERE question_id = %s AND qpl_questions.question_id = qpl_question_textsubset.question_fi",
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
				$this->correctanswers = $data->correctanswers;
				$this->text_rating = $data->textgap_rating;
				$this->setEstimatedWorkingTime(substr($data->working_time, 0, 2), substr($data->working_time, 3, 2), substr($data->working_time, 6, 2));
			}

			$query = sprintf("SELECT * FROM qpl_answer_textsubset WHERE question_fi = %s ORDER BY aorder ASC",
				$ilDB->quote($question_id)
			);
			$result = $ilDB->query($query);

			include_once "./Modules/TestQuestionPool/classes/class.assAnswerSimple.php";
			if (strcmp(strtolower(get_class($result)), db_result) == 0)
			{
				while ($data = $result->fetchRow(DB_FETCHMODE_ASSOC))
				{
					array_push($this->answers, new ASS_AnswerSimple($data["answertext"], $data["points"], $data["aorder"]));
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
	function addAnswer($answertext, $points, $answerorder)
	{
		include_once "./Modules/TestQuestionPool/classes/class.assAnswerSimple.php";
		array_push($this->answers, new ASS_AnswerSimple($answertext, $points, $answerorder));
	}
	
	/**
	* Duplicates an assTextSubsetQuestion
	*
	* Duplicates an assTextSubsetQuestion
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
	* Copies an assTextSubset object
	*
	* Copies an assTextSubset object
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
	* Returns the number of answers
	*
	* Returns the number of answers
	*
	* @return integer The number of answers of the TextSubset question
	* @access public
	* @see $ranges
	*/
	function getAnswerCount()
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
	function getAnswer($index = 0)
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
	function deleteAnswer($index = 0)
	{
		if ($index < 0) return;
		if (count($this->answers) < 1) return;
		if ($index >= count($this->answers)) return;
		unset($this->answers[$index]);
		$this->answers = array_values($this->answers);
		for ($i = 0; $i < count($this->answers); $i++)
		{
			if ($this->answers[$i]->getOrder() > $index)
			{
				$this->answers[$i]->setOrder($i);
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
	function flushAnswers()
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
		$points = array();
		foreach ($this->answers as $answer)
		{
			if ($answer->getPoints() > 0)
			{
				array_push($points, $answer->getPoints());
			}
		}
		rsort($points, SORT_NUMERIC);
		$maxpoints = 0;
		for ($counter = 0; $counter < $this->getCorrectAnswers(); $counter++)
		{
			$maxpoints += $points[$counter];
		}
		return $maxpoints;
	}
	
	/**
	* Returns the available answers for the question
	*
	* Returns the available answers for the question
	*
	* @access private
	* @see $answers
	*/
	function &getAvailableAnswers()
	{
		$available_answers = array();
		foreach ($this->answers as $answer)
		{
			array_push($available_answers, $answer->getAnswertext());
		}
		return $available_answers;
	}

	/**
	* Determines wheather a given answer is correct or not
	*
	* Returns the index of the found answer, if the given answer is in the 
	* set of correct answers and matchess
	* the matching options, otherwise FALSE is returned
	*
	* @param array $answers An array containing the correct answers
	* @param string $answer The text of the given answer
	* @return mixed The index of the correct answer, FALSE otherwise
	* @access public
	*/
	function isAnswerCorrect($answers, $answer)
	{
		$textrating = $this->getTextRating();
		foreach ($answers as $key => $value)
		{
			switch ($textrating)
			{
				case TEXTGAP_RATING_CASEINSENSITIVE:
					if (strcmp(strtolower(utf8_decode($value)), strtolower(utf8_decode($answer))) == 0) return $key;
					break;
				case TEXTGAP_RATING_CASESENSITIVE:
					if (strcmp(utf8_decode($value), utf8_decode($answer)) == 0) return $key;
					break;
				case TEXTGAP_RATING_LEVENSHTEIN1:
					if (levenshtein(utf8_decode($value), utf8_decode($answer)) <= 1) return $key;
					break;
				case TEXTGAP_RATING_LEVENSHTEIN2:
					if (levenshtein(utf8_decode($value), utf8_decode($answer)) <= 2) return $key;
					break;
				case TEXTGAP_RATING_LEVENSHTEIN3:
					if (levenshtein(utf8_decode($value), utf8_decode($answer)) <= 3) return $key;
					break;
				case TEXTGAP_RATING_LEVENSHTEIN4:
					if (levenshtein(utf8_decode($value), utf8_decode($answer)) <= 4) return $key;
					break;
				case TEXTGAP_RATING_LEVENSHTEIN5:
					if (levenshtein(utf8_decode($value), utf8_decode($answer)) <= 5) return $key;
					break;
			}
		}
		return FALSE;
	}

	/**
	* Returns the rating option for text comparisons
	*
	* Returns the rating option for text comparisons
	*
	* @return string The rating option for text comparisons
	* @see $text_rating
	* @access public
	*/
	function getTextRating()
	{
		return $this->text_rating;
	}
	
	/**
	* Sets the rating option for text comparisons
	*
	* Sets the rating option for text comparisons
	*
	* @param string $a_textgap_rating The rating option for text comparisons
	* @see $textgap_rating
	* @access public
	*/
	function setTextRating($a_text_rating)
	{
		switch ($a_text_rating)
		{
			case TEXTGAP_RATING_CASEINSENSITIVE:
			case TEXTGAP_RATING_CASESENSITIVE:
			case TEXTGAP_RATING_LEVENSHTEIN1:
			case TEXTGAP_RATING_LEVENSHTEIN2:
			case TEXTGAP_RATING_LEVENSHTEIN3:
			case TEXTGAP_RATING_LEVENSHTEIN4:
			case TEXTGAP_RATING_LEVENSHTEIN5:
				$this->text_rating = $a_text_rating;
				break;
			default:
				$this->text_rating = TEXTGAP_RATING_CASEINSENSITIVE;
				break;
		}
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
		
		$available_answers =& $this->getAvailableAnswers();
		$found_counter = 0;
		
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
		$points = 0;
		while ($data = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$enteredtext = $data["value1"];
			$index = $this->isAnswerCorrect($available_answers, $enteredtext);
			if ($index !== FALSE)
			{
				unset($available_answers[$index]);
				$points += $this->answers[$index]->getPoints();
			}
		}

		$points = parent::calculateReachedPoints($active_id, $pass = NULL, $points);
		return $points;
	}
	
	/**
	* Sets the number of correct answers needed to solve the question
	*
	* Sets the number of correct answers needed to solve the question
	*
	* @param integer $a_correct_anwers The number of correct answers
	* @access public
	*/
	function setCorrectAnswers($a_correct_answers)
	{
		$this->correctanswers = $a_correct_answers;
	}
	
	/**
	* Returns the number of correct answers needed to solve the question
	*
	* Returns the number of correct answers needed to solve the question
	*
	* @return integer The number of correct answers
	* @access public
	*/
	function getCorrectAnswers()
	{
		return $this->correctanswers;
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
		
		$query = sprintf("DELETE FROM tst_solutions WHERE active_fi = %s AND question_fi = %s AND pass = %s",
			$ilDB->quote($active_id . ""),
			$ilDB->quote($this->getId() . ""),
			$ilDB->quote($pass . "")
		);
		$result = $ilDB->query($query);
		foreach ($_POST as $key => $value)
		{
			if (preg_match("/^TEXTSUBSET_(\d+)/", $key, $matches))
			{
				if (strlen($value))
				{
					$query = sprintf("INSERT INTO tst_solutions (solution_id, active_fi, question_fi, value1, value2, pass, TIMESTAMP) VALUES (NULL, %s, %s, %s, NULL, %s, NULL)",
						$ilDB->quote($active_id),
						$ilDB->quote($this->getId()),
						$ilDB->quote(trim($value)),
						$ilDB->quote($pass . "")
					);
					$result = $ilDB->query($query);
					$entered_values++;
				}
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
			$query = sprintf("UPDATE qpl_question_textsubset SET textgap_rating = %s, correctanswers = %s WHERE question_fi = %s",
				$ilDB->quote($this->getTextRating() . ""),
				$ilDB->quote($this->getCorrectAnswers() . ""),
				$ilDB->quote($this->original_id . "")
			);
			$result = $ilDB->query($query);

			if ($result == DB_OK)
			{
				// Write Ranges to the database
				
				// 1. delete old ranges
				$query = sprintf("DELETE FROM qpl_answer_textsubset WHERE question_fi = %s",
					$ilDB->quote($this->original_id)
				);
				$result = $ilDB->query($query);
	
				// 2. write ranges
				foreach ($this->answers as $key => $value)
				{
					$answer_obj = $this->answers[$key];
					$query = sprintf("INSERT INTO qpl_answer_textsubset (answer_id, question_fi, answertext, points, aorder) VALUES (NULL, %s, %s, %s, %s)",
						$ilDB->quote($this->original_id. ""),
						$ilDB->quote($answer_obj->getAnswertext(). ""),
						$ilDB->quote($answer_obj->getPoints() . ""),
						$ilDB->quote($answer_obj->getOrder() . "")
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
		return "assTextSubset";
	}
	
	/**
	* Returns the answers of the question as a comma separated string
	*
	* Returns the answers of the question as a comma separated string
	*
	* @return string The answer string
	* @access public
	*/
	function &joinAnswers()
	{
		$join = array();
		foreach ($this->answers as $answer)
		{
			if (!is_array($join[$answer->getPoints() . ""]))
			{
				$join[$answer->getPoints() . ""] = array();
			}
			array_push($join[$answer->getPoints() . ""], $answer->getAnswertext());
		}
		return $join;
	}
	
	/**
	* Returns the maximum width needed for the answer textboxes
	*
	* Returns the maximum width needed for the answer textboxes
	*
	* @return integer Maximum textbox width
	* @access public
	*/
	function getMaxTextboxWidth()
	{
		$maxwidth = 0;
		foreach ($this->answers as $answer)
		{
			$len = strlen($answer->getAnswertext());
			if ($len > $maxwidth) $maxwidth = $len;
		}
		return $maxwidth + 3;
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
		return "qpl_question_textsubset";
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
		return "qpl_answer_textsubset";
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

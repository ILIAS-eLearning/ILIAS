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
* Class for cloze tests
*
* ASS_ClozeText is a class for cloze tests using text or select gaps.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com> 
* @version	$Id$
* @module   class.assClozeTest.php
* @modulegroup   Assessment
*/
class assClozeTest extends assQuestion
{
	/**
	* The cloze text containing variables defininig the clozes
	*
	* The cloze text containing variables defininig the clozes. The syntax for the cloze variables is *[varname],
	* where varname has to be an unique identifier.
	*
	* @var string
	*/
	var $cloze_text;

	/**
	* The gaps of the cloze question
	*
	* $gaps is an array of the predefined gaps of the cloze question
	*
	* @var array
	*/
	var $gaps;

	/**
	* The start tag beginning a cloze gap
	*
	* The start tag is set to "*[" by default.
	*
	* @var string
	*/
	var $start_tag;

	/**
	* The end tag beginning a cloze gap
	*
	* The end tag is set to "]" by default.
	*
	* @var string
	*/
	var $end_tag;
	
	/**
	* The rating option for text gaps
	*
	* This could contain one of the following options:
	* - case insensitive text gaps
	* - case sensitive text gaps
	* - various levenshtein distances
	*
	* @var string
	*/
	var $textgap_rating;
	
	/**
	* assClozeTest constructor
	*
	* The constructor takes possible arguments an creates an instance of the assClozeTest object.
	*
	* @param string $title A title string to describe the question
	* @param string $comment A comment string to describe the question
	* @param string $author A string containing the name of the questions author
	* @param integer $owner A numerical ID to identify the owner/creator
	* @param string $cloze_text The question string of the cloze test
	* @access public
	*/
	function assClozeTest(
		$title = "",
		$comment = "",
		$author = "",
		$owner = -1,
		$cloze_text = ""
	)
	{
		$this->start_tag = "<gap>";
		$this->end_tag = "</gap>";
		$this->assQuestion($title, $comment, $author, $owner);
		$this->gaps = array();
		$this->setClozeText($cloze_text);
	}

	/**
	* Returns true, if a cloze test is complete for use
	*
	* Returns true, if a cloze test is complete for use
	*
	* @return boolean True, if the cloze test is complete for use, otherwise false
	* @access public
	*/
	function isComplete()
	{
		if (($this->title) and ($this->author) and ($this->cloze_text) and (count($this->gaps)) and ($this->getMaximumPoints() > 0))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	* Creates an associative array from the close text
	*
	* Creates an associative array from the close text
	*
	* @return array Associative array containing all separated close text parts
	* @access public
	*/
	function &createCloseTextArray()
	{
		$result = array();
		$search_pattern = "|<gap([^>]*?)>(.*?)</gap>|i";
		preg_match_all($search_pattern, $this->cloze_text, $gaps);
		if (count($gaps[0]))
		{
			// found at least one gap
			$delimiters = preg_split($search_pattern, $this->cloze_text, -1, PREG_SPLIT_OFFSET_CAPTURE);
			$result["gaps"] = array();
			foreach ($gaps[0] as $index => $gap)
			{
				$result["gaps"][$index] = array();
				$result["gaps"][$index]["gap"] = $gap;
				$result["gaps"][$index]["params"] = array();
				$result["gaps"][$index]["params"]["text"] = $gaps[1][$index];
				// separate gap params
				if (preg_match("/name\=\"([^\"]*?)\"/", $gaps[1][$index], $params))
				{
					$result["gaps"][$index]["params"]["name"] = $params[1];
				}
				else
				{
					$result["gaps"][$index]["params"]["name"] = $this->lng->txt("gap") . " " . ($index+1);
				}
				if (preg_match("/type\=\"([^\"]*?)\"/", $gaps[1][$index], $params))
				{
					$result["gaps"][$index]["params"]["type"] = $params[1];
				}
				else
				{
					$result["gaps"][$index]["params"]["type"] = "text";
				}
				if (preg_match("/shuffle\=\"([^\"]*?)\"/", $gaps[1][$index], $params))
				{
					$result["gaps"][$index]["params"]["shuffle"] = $params[1];
				}
				else
				{
					if (strcmp(strtolower($result["gaps"][$index]["params"]["type"]), "select") == 0)
					{
						$result["gaps"][$index]["params"]["shuffle"] = "yes";
					}
				}
				$result["gaps"][$index]["text"] = array();
				$result["gaps"][$index]["text"]["text"] = $gaps[2][$index];
				$textparams = preg_split("/(?<!\\\\),/", $gaps[2][$index]);
				foreach ($textparams as $key => $value)
				{
					$result["gaps"][$index]["text"][$key] = $value;
				}
			}
			$result["delimiters"] = $delimiters;
		}
		//echo str_replace("\n", "<br />", str_replace(" ", "&nbsp;", ilUtil::prepareFormOutput(print_r($result, true))));
		return $result;		
	}

	/**
	* Re-creates the close text from an an associative array
	*
	* Re-creates the close text from an an associative array
	*
	* @param array $assoc_array Associative array containing all separated close text parts
	* @access public
	*/
	function createCloseTextFromArray($assoc_array)
	{
		$this->cloze_text = "";
		if (count($assoc_array))
		{
			$gap = 0;
			foreach ($assoc_array["delimiters"] as $key => $value)
			{
				if (($key > 0) && ($key < count($assoc_array["delimiters"])))
				{
					if (strcmp($assoc_array["gaps"][$gap]["params"]["shuffle"], "") == 0)
					{
						$shuffle = "";
					}
					else
					{
						$shuffle = " shuffle=\"" . $assoc_array["gaps"][$gap]["params"]["shuffle"] . "\"";
					}
					$textarray = array();
					foreach ($assoc_array["gaps"][$gap]["text"] as $textindex => $textvalue)
					{
						if (preg_match("/\d+/", $textindex))
						{
							array_push($textarray, $textvalue);
						}
					}
					$this->cloze_text .= sprintf("<gap name=\"%s\" type=\"%s\"%s>%s</gap>",
						$assoc_array["gaps"][$gap]["params"]["name"],
						$assoc_array["gaps"][$gap]["params"]["type"],
						$shuffle,
						join(",", $textarray)
					);
					$gap++;
				}
				$this->cloze_text .= $value[0];
			}
		}
	}
	
	/**
	* Saves a assClozeTest object to a database
	*
	* Saves a assClozeTest object to a database (experimental)
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

		if ($this->id == -1)
		{
			// Neuen Datensatz schreiben
			$now = getdate();
			$created = sprintf("%04d%02d%02d%02d%02d%02d", $now['year'], $now['mon'], $now['mday'], $now['hours'], $now['minutes'], $now['seconds']);
			$query = sprintf("INSERT INTO qpl_questions (question_id, question_type_fi, obj_fi, title, comment, points, author, owner, question_text, working_time, complete, created, original_id, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, NULL)",
				$ilDB->quote($this->getQuestionType()),
				$ilDB->quote($this->obj_id),
				$ilDB->quote($this->title),
				$ilDB->quote($this->comment),
				$ilDB->quote($this->getMaximumPoints() . ""),
				$ilDB->quote($this->author),
				$ilDB->quote($this->owner),
				$ilDB->quote($this->cloze_text),
				$ilDB->quote($estw_time),
				$ilDB->quote("$complete"),
				$ilDB->quote($created),
				$original_id
			);
			$result = $ilDB->query($query);
			if ($result == DB_OK)
			{
				$this->id = $ilDB->getLastInsertId();
				$query = sprintf("INSERT INTO qpl_question_cloze (question_fi, textgap_rating) VALUES (%s, %s)",
					$ilDB->quote($this->id . ""),
					$ilDB->quote($this->textgap_rating . "")
				);
				$ilDB->query($query);

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
			$query = sprintf("UPDATE qpl_questions SET obj_fi = %s, title = %s, comment = %s, points = %s, author = %s, question_text = %s, working_time = %s, complete = %s WHERE question_id = %s",
				$ilDB->quote($this->obj_id. ""),
				$ilDB->quote($this->title),
				$ilDB->quote($this->comment),
				$ilDB->quote($this->getMaximumPoints() . ""),
				$ilDB->quote($this->author),
				$ilDB->quote($this->cloze_text),
				$ilDB->quote($estw_time),
				$ilDB->quote("$complete"),
				$ilDB->quote($this->id)
				);
			$result = $ilDB->query($query);
			$query = sprintf("UPDATE qpl_question_cloze SET textgap_rating = %s WHERE question_fi = %s",
				$ilDB->quote($this->textgap_rating . ""),
				$ilDB->quote($this->id . "")
			);
			$result = $ilDB->query($query);
		}

		if ($result == DB_OK)
		{
			// Antworten schreiben

			// delete old answers
			$query = sprintf("DELETE FROM qpl_answer_cloze WHERE question_fi = %s",
				$ilDB->quote($this->id)
			);
			$result = $ilDB->query($query);
			// Anworten wegschreiben
			foreach ($this->gaps as $key => $value)
			{
				foreach ($value as $answer_id => $answer_obj)
				{
					$query = sprintf("INSERT INTO qpl_answer_cloze (answer_id, question_fi, gap_id, answertext, points, aorder, cloze_type, name, shuffle, correctness) VALUES (NULL, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
						$ilDB->quote($this->id),
						$ilDB->quote($key),
						$ilDB->quote($answer_obj->getAnswertext() . ""),
						$ilDB->quote($answer_obj->getPoints() . ""),
						$ilDB->quote($answer_obj->getOrder() . ""),
						$ilDB->quote($answer_obj->getClozeType() . ""),
						$ilDB->quote($answer_obj->getName() . ""),
						$ilDB->quote($answer_obj->getShuffle() . ""),
						$ilDB->quote($answer_obj->getState() . "")
						);
					$answer_result = $ilDB->query($query);
				}
			}
		}
		parent::saveToDb($original_id);
	}

/**
* Loads a assClozeTest object from a database
*
* Loads a assClozeTest object from a database
*
* @param object $db A pear DB object
* @param integer $question_id A unique key which defines the cloze test in the database
* @access public
*/
  function loadFromDb($question_id)
  {
    global $ilDB;

		include_once "./assessment/classes/class.assAnswerCloze.php";
    $query = sprintf("SELECT qpl_questions.*, qpl_question_cloze.* FROM qpl_questions, qpl_question_cloze WHERE question_id = %s AND qpl_questions.question_id = qpl_question_cloze.question_fi",
      $ilDB->quote($question_id)
    );
    $result = $ilDB->query($query);
    if (strcmp(strtolower(get_class($result)), db_result) == 0) {
      if ($result->numRows() == 1) {
        $data = $result->fetchRow(DB_FETCHMODE_OBJECT);
        $this->id = $question_id;
        $this->obj_id = $data->obj_fi;
        $this->title = $data->title;
        $this->comment = $data->comment;
				$this->solution_hint = $data->solution_hint;
				$this->original_id = $data->original_id;
        $this->author = $data->author;
				$this->points = $data->points;
        $this->owner = $data->owner;
        $this->cloze_text = $data->question_text;
				$this->setTextgapRating($data->textgap_rating);
        $this->setEstimatedWorkingTime(substr($data->working_time, 0, 2), substr($data->working_time, 3, 2), substr($data->working_time, 6, 2));
      }

      $query = sprintf("SELECT * FROM qpl_answer_cloze WHERE question_fi = %s ORDER BY gap_id, aorder ASC",
        $ilDB->quote($question_id)
      );
      $result = $ilDB->query($query);
      if (strcmp(strtolower(get_class($result)), db_result) == 0) {
        $counter = -1;
        while ($data = $result->fetchRow(DB_FETCHMODE_OBJECT)) {
          if ($data->gap_id != $counter) {
            $answer_array = array();
            array_push($this->gaps, $answer_array);
            $counter = $data->gap_id;
          }
					if ($data->cloze_type == CLOZE_SELECT)
					{
						if ($data->correctness == 0)
						{
							// fix for older single response answers where points could be given for unchecked answers
							$data->correctness = 1;
							$data->points = 0;
						}
					}
          array_push($this->gaps[$counter], new ASS_AnswerCloze($data->answertext, $data->points, $data->aorder, $data->correctness, $data->cloze_type, $data->name, $data->shuffle, $data->answer_id));
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
	function addAnswer($gap, $answertext, $points, $answerorder, $correctness, $clozetype, $name, $shuffle, $answer_id = -1)
	{
		include_once "./assessment/classes/class.assAnswerCloze.php";
		if (!is_array($this->gaps[$gap]))
		{
			$this->gaps[$gap] = array();
		}
		array_push($this->gaps[$gap], new ASS_AnswerCloze($answertext, $points, $answerorder, $correctness, $clozetype, $name, $shuffle, $answer_id));
	}
	
/**
* Duplicates an assClozeTest
*
* Duplicates an assClozeTest
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

		return $clone->id;
	}

	/**
	* Copies an assClozeTest object
	*
	* Copies an assClozeTest object
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

		return $clone->id;
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
		//global $ilLog;
		
		//$ilLog->write(strftime("%D %T") . ": import multiple choice question (single response)");
		$presentation = $item->getPresentation(); 
		$duration = $item->getDuration();
		$questiontext = array();
		$shuffle = 0;
		$now = getdate();
		$created = sprintf("%04d%02d%02d%02d%02d%02d", $now['year'], $now['mon'], $now['mday'], $now['hours'], $now['minutes'], $now['seconds']);
		$gaps = array();
		foreach ($presentation->order as $entry)
		{
			switch ($entry["type"])
			{
				case "material":
					$material = $presentation->material[$entry["index"]];
					if (count($material->mattext))
					{
						foreach ($material->mattext as $mattext)
						{
							array_push($questiontext, $mattext->getContent());
						}
					}
					break;
				case "response":
					$response = $presentation->response[$entry["index"]];
					$rendertype = $response->getRenderType(); 
					array_push($questiontext, "<<" . $response->getIdent() . ">>");
					switch (strtolower(get_class($response->getRenderType())))
					{
						case "ilqtirenderfib":
							array_push($gaps, array("ident" => $response->getIdent(), "type" => "text", "answers" => array()));
							break;
						case "ilqtirenderchoice":
							$answers = array();
							$shuffle = $rendertype->getShuffle();
							$answerorder = 0;
							foreach ($rendertype->response_labels as $response_label)
							{
								$ident = $response_label->getIdent();
								$answertext = "";
								foreach ($response_label->material as $mat)
								{
									foreach ($mat->mattext as $matt)
									{
										$answertext .= $matt->getContent();
									}
								}
								$answers[$ident] = array(
									"answertext" => $answertext,
									"points" => 0,
									"answerorder" => $answerorder++,
									"action" => "",
									"shuffle" => $rendertype->getShuffle()
								);
							}
							array_push($gaps, array("ident" => $response->getIdent(), "type" => "choice", "shuffle" => $rendertype->getShuffle(), "answers" => $answers));
							break;
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
						case "varequal":
							$equals = $conditionvar->varequal[$order["index"]]->getContent();
							$gapident = $conditionvar->varequal[$order["index"]]->getRespident();
							break;
					}
				}
				foreach ($respcondition->setvar as $setvar)
				{
					if (strcmp($gapident, "") != 0)
					{
						foreach ($gaps as $gi => $g)
						{
							if (strcmp($g["ident"], $gapident) == 0)
							{
								if (strcmp($g["type"], "choice") == 0)
								{
									foreach ($gaps[$gi]["answers"] as $ai => $answer)
									{
										if (strcmp($answer["answertext"], $equals) == 0)
										{
											$gaps[$gi]["answers"][$ai]["action"] = $setvar->getAction();
											$gaps[$gi]["answers"][$ai]["points"] = $setvar->getContent();
										}
									}
								}
								else if (strcmp($g["type"], "text") == 0)
								{
									array_push($gaps[$gi]["answers"], array(
										"answertext" => $equals,
										"points" => $setvar->getContent(),
										"answerorder" => count($gaps[$gi]["answers"]),
										"action" => $setvar->getAction(),
										"shuffle" => 1
									));
								}
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
		$this->setObjId($questionpool_id);
		$this->setEstimatedWorkingTime($duration["h"], $duration["m"], $duration["s"]);
		$textgap_rating = $item->getMetadataEntry("textgaprating");
		if (strlen($textgap_rating) == 0) $textgap_rating = "ci";
		$this->setTextgapRating($textgap_rating);
		$gaptext = array();
		foreach ($gaps as $gapidx => $gap)
		{
			$gapcontent = array();
			$type = 0;
			$typetext = "text";
			$shuffletext = "";
			if (strcmp($gap["type"], "choice") == 0)
			{
				$type = 1;
				$typetext = "select";
				if ($gap["shuffle"] == 0)
				{
					$shuffletext = "  shuffle=\"no\"";
				}
				else
				{
					$shuffletext = "  shuffle=\"yes\"";
				}
			}
			foreach ($gap["answers"] as $index => $answer)
			{
				$this->addAnswer($gapidx, $answer["answertext"], $answer["points"], $answer["answerorder"], 1, $type, $gap["ident"], $answer["shuffle"]);
				array_push($gapcontent, $answer["answertext"]);
			}
			$gaptext[$gap["ident"]] = "<gap type=\"$typetext\" name=\"" . $gap["ident"] . "\"$shuffletext>" . join(",", $gapcontent). "</gap>";
		}
		$clozetext = join("", $questiontext);
		foreach ($gaptext as $idx => $val)
		{
			$clozetext = str_replace("<<" . $idx . ">>", $val, $clozetext);
		}
		$this->cloze_text = $clozetext;
		$this->saveToDb();
		if (count($item->suggested_solutions))
		{
			foreach ($item->suggested_solutions as $suggested_solution)
			{
				$this->setSuggestedSolution($suggested_solution["solution"]->getContent(), $suggested_solution["gap_index"], true);
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
		$a_xml_writer->xmlElement("fieldentry", NULL, CLOZE_TEST_IDENTIFIER);
		$a_xml_writer->xmlEndTag("qtimetadatafield");
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "AUTHOR");
		$a_xml_writer->xmlElement("fieldentry", NULL, $this->getAuthor());
		$a_xml_writer->xmlEndTag("qtimetadatafield");
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "textgaprating");
		$a_xml_writer->xmlElement("fieldentry", NULL, $this->getTextgapRating());
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
		$text_parts = preg_split("/\<gap.*?\<\/gap\>/", $this->getClozeText());
		// add material with question text to presentation
		for ($i = 0; $i <= $this->getGapCount(); $i++)
		{
			// n-th text part
			$a_xml_writer->xmlStartTag("material");
			$a_xml_writer->xmlElement("mattext", NULL, $text_parts[$i]);
			$a_xml_writer->xmlEndTag("material");

			if ($i < $this->getGapCount())
			{
				// add gap
				$gap = $this->getGap($i);
				if ($gap[0]->getClozeType() == CLOZE_SELECT)
				{
					// comboboxes
					$attrs = array(
						"ident" => "gap_$i",
						"rcardinality" => "Single"
					);
					$a_xml_writer->xmlStartTag("response_str", $attrs);
					$solution = $this->getSuggestedSolution($i);
					if (count($solution))
					{
						if (preg_match("/il_(\d*?)_(\w+)_(\d+)/", $solution["internal_link"], $matches))
						{
							$attrs = array(
								"label" => "suggested_solution"
							);
							$a_xml_writer->xmlStartTag("material", $attrs);
							$intlink = "il_" . IL_INST_ID . "_" . $matches[2] . "_" . $matches[3];
							if (strcmp($matches[1], "") != 0)
							{
								$intlink = $solution["internal_link"];
							}
							$a_xml_writer->xmlElement("mattext", NULL, $intlink);
							$a_xml_writer->xmlEndTag("material");
						}
					}
					
					$attrs = array();
					if ($gap[0]->getShuffle())
					{
						$attrs = array("shuffle" => "Yes");
					}
					else
					{
						$attrs = array("shuffle" => "No");
					}
					$a_xml_writer->xmlStartTag("render_choice", $attrs);

					// shuffle output
					$gkeys = array_keys($gap);
					if ($gap[0]->getShuffle() && $a_shuffle)
					{
						$gkeys = $this->pcArrayShuffle($gkeys);
					}

					// add answers
					foreach ($gkeys as $key)
					{
						$value = $gap[$key];
						$attrs = array(
							"ident" => $key
						);
						$a_xml_writer->xmlStartTag("response_label", $attrs);
						$a_xml_writer->xmlStartTag("material");
						$a_xml_writer->xmlElement("mattext", NULL, $value->getAnswertext());
						$a_xml_writer->xmlEndTag("material");
						$a_xml_writer->xmlEndTag("response_label");
					}
					$a_xml_writer->xmlEndTag("render_choice");
					$a_xml_writer->xmlEndTag("response_str");
				}
				else
				{
					// text fields
					$attrs = array(
						"ident" => "gap_$i",
						"rcardinality" => "Single"
					);
					$a_xml_writer->xmlStartTag("response_str", $attrs);
					$solution = $this->getSuggestedSolution($i);
					if (count($solution))
					{
						if (preg_match("/il_(\d*?)_(\w+)_(\d+)/", $solution["internal_link"], $matches))
						{
							$attrs = array(
								"label" => "suggested_solution"
							);
							$a_xml_writer->xmlStartTag("material", $attrs);
							$intlink = "il_" . IL_INST_ID . "_" . $matches[2] . "_" . $matches[3];
							if (strcmp($matches[1], "") != 0)
							{
								$intlink = $solution["internal_link"];
							}
							$a_xml_writer->xmlElement("mattext", NULL, $intlink);
							$a_xml_writer->xmlEndTag("material");
						}
					}
					$attrs = array(
						"fibtype" => "String",
						"prompt" => "Box",
						"columns" => $this->getColumnSize($gap)
					);
					$a_xml_writer->xmlStartTag("render_fib");
					$attrs = array(
						"ident" => $i
					);
					$a_xml_writer->xmlEndTag("render_fib");
					$a_xml_writer->xmlEndTag("response_str");
				}
			}
		}
		$a_xml_writer->xmlEndTag("flow");
		$a_xml_writer->xmlEndTag("presentation");

		// PART II: qti resprocessing
		$a_xml_writer->xmlStartTag("resprocessing");
		$a_xml_writer->xmlStartTag("outcomes");
		$a_xml_writer->xmlStartTag("decvar");
		$a_xml_writer->xmlEndTag("decvar");
		$a_xml_writer->xmlEndTag("outcomes");

		// add response conditions
		for ($i = 0; $i < $this->getGapCount(); $i++)
		{
			$gap = $this->getGap($i);
			if ($gap[0]->getClozeType() == CLOZE_SELECT)
			{
				foreach ($gap as $index => $answer)
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

					$attrs = array(
						"respident" => "gap_$i"
					);
					$a_xml_writer->xmlElement("varequal", $attrs, $answer->getAnswertext());
					if (!$answer->isStateSet())
					{
						$a_xml_writer->xmlEndTag("not");
					}
					$a_xml_writer->xmlEndTag("conditionvar");
					// qti setvar
					$attrs = array(
						"action" => "Add"
					);
					$a_xml_writer->xmlElement("setvar", $attrs, $answer->getPoints());
					// qti displayfeedback
					$linkrefid = "";
					if ($answer->getPoints() > 0)
					{
						$linkrefid = "$i" . "_True";
					}
						else
					{
						$linkrefid = "$i" . "_False_$index";
					}
					$attrs = array(
						"feedbacktype" => "Response",
						"linkrefid" => $linkrefid
					);
					$a_xml_writer->xmlElement("displayfeedback", $attrs);
					$a_xml_writer->xmlEndTag("respcondition");
				}
			}
			else
			{
				foreach ($gap as $index => $answer)
				{
					$attrs = array(
						"continue" => "Yes"
					);
					$a_xml_writer->xmlStartTag("respcondition", $attrs);
					// qti conditionvar
					$a_xml_writer->xmlStartTag("conditionvar");
					$attrs = array(
						"respident" => "gap_$i"
					);
					$a_xml_writer->xmlElement("varequal", $attrs, $answer->getAnswertext());
					$a_xml_writer->xmlEndTag("conditionvar");
					// qti setvar
					$attrs = array(
						"action" => "Add"
					);
					$a_xml_writer->xmlElement("setvar", $attrs, $answer->getPoints());
					// qti displayfeedback
					$attrs = array(
						"feedbacktype" => "Response",
						"linkrefid" => "$i" . "_True_$index"
					);
					$a_xml_writer->xmlElement("displayfeedback", $attrs);
					$a_xml_writer->xmlEndTag("respcondition");
				}
			}
		}
		$a_xml_writer->xmlEndTag("resprocessing");

		// PART III: qti itemfeedback
		for ($i = 0; $i < $this->getGapCount(); $i++)
		{
			$gap = $this->getGap($i);
			if ($gap[0]->getClozeType() == CLOZE_SELECT)
			{
				foreach ($gap as $index => $answer)
				{
					$linkrefid = "";
					if ($answer->isStateSet())
					{
						$linkrefid = "$i" . "_True";
					}
						else
					{
						$linkrefid = "$i" . "_False_$index";
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
			}
			else
			{
				foreach ($gap as $index => $answer)
				{
					$linkrefid = "";
					if ($answer->isStateSet())
					{
						$linkrefid = "$i" . "_True_$index";
					}
						else
					{
						$linkrefid = "$i" . "_False_$index";
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
			}
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
	* Evaluates the text gap solutions from the cloze text
	*
	* Evaluates the text gap solutions from the cloze text. A single or multiple text gap solutions
	* could be entered using the following syntax in the cloze text:
	* solution1 [, solution2, ..., solutionN] enclosed in the text gap selector *[]
	*
	* @param string $cloze_text The cloze text with all gaps and gap gaps
	* @access public
	* @see $cloze_text
	*/
	function setClozeText($cloze_text = "")
	{
		$this->gaps = array();
		$this->cloze_text =& $cloze_text;
		$close = $this->createCloseTextArray();
		if (count($close))
		{
			foreach ($close["gaps"] as $key => $value)
			{
				if (strcmp(strtolower($value["params"]["type"]), "select") == 0)
				{
					$type = CLOZE_SELECT;
				}
					else
				{
					$type = CLOZE_TEXT;
				}
				if ($type == CLOZE_TEXT)
				{
					$default_state = 1;
				}
				else
				{
					$default_state = 0;
				}
				$name = $value["params"]["name"];
				if (strcmp(strtolower($value["params"]["shuffle"]), "no") == 0)
				{
					$shuffle = 0;
				}
					else
				{
					$shuffle = 1;
				}
				$answer_array = array();
				include_once "./assessment/classes/class.assAnswerCloze.php";
				foreach ($value["text"] as $index => $textvalue)
				{
					if (preg_match("/\d+/", $index))
					{
						$textvalue = str_replace("\,", ",", $textvalue);
						array_push($answer_array, new ASS_AnswerCloze($textvalue, 0, $index, $default_state, $type, $name, $shuffle));
					}
				}
				array_push($this->gaps, $answer_array);
			}
		}
	}

/**
* Returns the cloze text
*
* Returns the cloze text
*
* @return string The cloze text string
* @access public
* @see $cloze_text
*/
  function getClozeText() {
    return $this->cloze_text;
  }

/**
* Returns the start tag of a cloze gap
*
* Returns the start tag of a cloze gap
*
* @return string The start tag of a cloze gap
* @access public
* @see $start_tag
*/
  function getStartTag() {
    return $this->start_tag;
  }

/**
* Returns the end tag of a cloze gap
*
* Returns the end tag of a cloze gap
*
* @return string The end tag of a cloze gap
* @access public
* @see $end_tag
*/
  function getEndTag() {
    return $this->end_tag;
  }

/**
* Sets the start tag of a cloze gap
*
* Sets the start tag of a cloze gap
*
* @param string $start_tag The start tag for a cloze gap
* @access public
* @see $start_tag
*/
  function setStartTag($start_tag = "<gap>") {
    $this->start_tag = $start_tag;
  }


/**
* Sets the end tag of a cloze gap
*
* Sets the end tag of a cloze gap
*
* @param string $end_tag The end tag for a cloze gap
* @access public
* @see $end_tag
*/
  function setEndTag($end_tag = "</gap>") {
    $this->end_tag = $end_tag;
  }

/**
* Replaces the gap values with the values of the gaps array
*
* Replaces the gap values with the values of the gaps array
*
* @access public
* @see $cloze_text
*/
  function rebuildClozeText() 
	{
		$close =& $this->createCloseTextArray();
		if (count($close))
		{
			for ($i = 0; $i < count($this->gaps); $i++)
			{
				$gaptext = $this->getGapTextList($i);
				$textparams = preg_split("/(?<!\\\\),/", $gaptext);
				$close["gaps"][$i]["text"] = array();
				$close["gaps"][$i]["text"]["text"] = $gaptext;
				foreach ($textparams as $key => $value)
				{
					$close["gaps"][$i]["text"][$key] = $value;
				}
			}
		}
		$this->createCloseTextFromArray($close);
  }

/**
* Returns an array of gap answers
*
* Returns the array of gap answers with a given index. The index of the first
* gap is 0, the index of the second gap is 1 and so on.
*
* @param integer $index A nonnegative index of the n-th gap
* @return array Array of ASS_AnswerCloze-Objects containing the gap gaps
* @access public
* @see $gaps
*/
  function getGap($index = 0) {
    if ($index < 0) return array();
    if (count($this->gaps) < 1) return array();
    if ($index >= count($this->gaps)) return array();
    return $this->gaps[$index];
  }

/**
* Returns the number of gaps
*
* Returns the number of gaps
*
* @return integer The number of gaps in the question text
* @access public
* @see $gaps
*/
  function getGapCount() {
    return count($this->gaps);
  }

/**
* Returns a separated string of all answers for a given text gap
*
* Returns a separated string of all answers for a given gap. The index of the first
* gap is 0, the index of the second gap is 1 and so on.
*
* @param integer $index A nonnegative index of the n-th gap
* @param string $separator A string that separates the answer strings
* @return string Separated string containing the answer strings
* @access public
* @see $gaps
*/
  function getGapTextList($index = 0, $separator = ",") {
    if ($index < 0) return "";
    if (count($this->gaps) < 1) return "";
    if ($index >= count($this->gaps)) return "";
    $result = array();
    foreach ($this->gaps[$index] as $key => $value) {
			array_push($result, str_replace(",", "\,", $value->getAnswertext()));
    }
    return join($separator, $result);
  }
/**
* Returns a count of all answers of a gap
*
* Returns a count of all answers of a gap
*
* @param integer $index A nonnegative index of the n-th gap
* @access public
* @see $gaps
*/
  function getGapTextCount($index = 0) {
    if ($index < 0) return 0;
    if (count($this->gaps) < 1) return 0;
    if ($index >= count($this->gaps)) return 0;
    return count($this->gaps[$index]);
  }
/**
* Deletes a gap
*
* Deletes a gap with a given index. The index of the first
* gap is 0, the index of the second gap is 1 and so on.
*
* @param integer $index A nonnegative index of the n-th gap
* @access public
* @see $gaps
*/
  function deleteGap($index = 0) {
    if ($index < 0) return;
    if (count($this->gaps) < 1) return;
    if ($index >= count($this->gaps)) return;
		$close = $this->createCloseTextArray();
		unset($close["gaps"][$index]);
		$this->createCloseTextFromArray($close);
    unset($this->gaps[$index]);
    $this->gaps = array_values($this->gaps);
  }

/**
* Deletes all gaps without changing the cloze text
*
* Deletes all gaps without changing the cloze text
*
* @access public
* @see $gaps
*/
  function flushGaps() {
    $this->gaps = array();
  }

/**
* Deletes an answer text of a gap
*
* Deletes an answer text of a gap with a given index. The index of the first
* gap is 0, the index of the second gap is 1 and so on.
*
* @param integer $index A nonnegative index of the n-th gap
* @param string $answertext The answer text that should be deleted
* @access public
* @see $gaps
*/
  function deleteAnswertextByIndex($gap_index = 0, $answertext_index = 0) {
    if ($gap_index < 0) return;
    if (count($this->gaps) < 1) return;
    if ($gap_index >= count($this->gaps)) return;
    $old_text = $this->getGapTextList($gap_index);
		if (count($this->gaps[$gap_index]) == 1) {
			$this->deleteGap($gap_index);
		} else {
			$close = $this->createCloseTextArray();
			unset($this->gaps[$gap_index][$answertext_index]);
      $this->gaps[$gap_index] = array_values($this->gaps[$gap_index]);
			unset($close["gaps"][$gap_index]["text"][$answertext_index]);
			$this->createCloseTextFromArray($close);
		}
  }

/**
* Sets an answer text of a gap
*
* Sets an answer text of a gap with a given index. The index of the first
* gap is 0, the index of the second gap is 1 and so on.
*
* @param integer $index A nonnegative index of the n-th gap
* @param integer $answertext_index A nonnegative index of the n-th answertext
* @param string $answertext The answer text that should be deleted
* @access public
* @see $gaps
*/
  function setAnswertext($index = 0, $answertext_index = 0, $answertext = "", $add_gaptext=0) 
	{
		$answertext = str_replace("\,", ",", $answertext);
  	if ($add_gaptext == 1)
		{
    	$arr = $this->gaps[$index][0];
    	if (strlen($this->gaps[$index][count($this->gaps[$index])-1]->getAnswertext()) != 0) 
			{
				$default_state = 0;
				$default_points = 0;
				if ($arr->getClozeType() == CLOZE_TEXT)
				{
					$default_state = 1;
					if ($answertext_index > 0) $default_points = $this->gaps[$index][0]->getPoints();
				}
				include_once "./assessment/classes/class.assAnswerCloze.php";
    		array_push($this->gaps[$index], new ASS_AnswerCloze($answertext, $default_points, count($this->gaps[$index]),
    			$default_state, $arr->getClozeType(),
    			$arr->getName(), $arr->getShuffle()));
    		$this->rebuildClozeText();
    	}
    	return;
    }
    if ($index < 0) return;
    if (count($this->gaps) < 1) return;
    if ($index >= count($this->gaps)) return;
    if ($answertext_index < 0) return;
    if (count($this->gaps[$index]) < 1) return;
    if ($answertext_index >= count($this->gaps[$index])) return;


    if (strlen($answertext) == 0) 
		{
      // delete the answertext
      $this->deleteAnswertext($index, $this->gaps[$index][$answertext_index]->getAnswertext());
    } 
		else 
		{
      $this->gaps[$index][$answertext_index]->setAnswertext($answertext);
      $this->rebuildClozeText();
    }
  }

/**
* Updates the cloze text setting the cloze type for every gap
*
* Updates the cloze text setting the cloze type for every gap
*
* @access public
* @see $cloze_text
*/
	function updateAllGapParams() 
	{
		global $lng;
		$close = $this->createCloseTextArray();
		for ($i = 0; $i < $this->getGapCount(); $i++)
		{
			$gaptext = $this->getGapTextList($i);
			if ($this->gaps[$i][0]->getClozeType() == CLOZE_TEXT)
			{
				$close["gaps"][$i]["params"]["type"] = "text";
				if (array_key_exists("shuffle", $close["gaps"][$i]["params"]))
				{
					unset($close["gaps"][$i]["params"]["shuffle"]);
				}
			}
				else
			{
				$close["gaps"][$i]["params"]["type"] = "select";
				if ($this->gaps[$i][0]->getShuffle() == 0)
				{
					$close["gaps"][$i]["params"]["shuffle"] = "no";
				}
					else
				{
					$close["gaps"][$i]["params"]["shuffle"] = "yes";
				}
			}
			$name = $this->gaps[$i][0]->getName();
			if (!$name)
			{
				$name = $this->lng->txt("gap") . " " . ($i+1);
			}
			$close["gaps"][$i]["params"]["name"] = $name;
		}
		$this->createCloseTextFromArray($close);
	}

/**
* Sets the cloze type of the gap
*
* Sets the cloze type of the gap
*
* @param integer $index The index of the chosen gap
* @param integer $cloze_type The cloze type of the gap
* @access public
* @see $gaps
*/
	function setClozeType($index, $cloze_type = CLOZE_TEXT) {
    if ($index < 0) return;
    if (count($this->gaps) < 1) return;
    if ($index >= count($this->gaps)) return;
		$close = $this->createCloseTextArray();
		foreach ($this->gaps[$index] as $key => $value) {
			$this->gaps[$index][$key]->setClozeType($cloze_type);
			$this->gaps[$index][$key]->setState(1);
		}
		if ($cloze_type == CLOZE_TEXT)
		{
			$type = "text";
		}
		else
		{
			$type = "select";
		}
		$close["gaps"][$index]["type"] = $type;
		$this->createCloseTextFromArray($close);
	}

/**
* Sets the points of a gap
*
* Sets the points of a gap with a given index. The index of the first
* gap is 0, the index of the second gap is 1 and so on.
*
* @param integer $index A nonnegative index of the n-th gap
* @param double $points The points for the correct solution of the gap
* @access public
* @see $gaps
*/
  function setGapPoints($index = 0, $points = 0.0) {
    if ($index < 0) return;
    if (count($this->gaps) < 1) return;
    if ($index >= count($this->gaps)) return;
    foreach ($this->gaps[$index] as $key => $value) {
      $this->gaps[$index][$key]->setPoints($points);
    }
  }

/**
* Sets the shuffle state of a gap
*
* Sets the shuffle state of a gap with a given index. The index of the first
* gap is 0, the index of the second gap is 1 and so on.
*
* @param integer $index A nonnegative index of the n-th gap
* @param integer $shuffle Turn shuffle on (=1) or off (=0)
* @access public
* @see $gaps
*/
  function setGapShuffle($index = 0, $shuffle = 1) {
    if ($index < 0) return;
    if (count($this->gaps) < 1) return;
    if ($index >= count($this->gaps)) return;
    foreach ($this->gaps[$index] as $key => $value) {
      $this->gaps[$index][$key]->setShuffle($shuffle);
    }
  }


/**
* Sets the points of a gap answer
*
* Sets the points of a gap answer with a given index. The index of the first
* gap is 0, the index of the second gap is 1 and so on.
*
* @param integer $index_gaps A nonnegative index of the n-th gap
* @param integer $index_answerobject A nonnegative index of the n-th answer in the specified gap
* @param double $points The points for the correct solution of the answer
* @access public
* @see $gaps
*/
  function setSingleAnswerPoints($index_gaps = 0, $index_answerobject = 0, $points = 0.0) {
    if ($index_gaps < 0) return;
    if (count($this->gaps) < 1) return;
    if ($index_gaps >= count($this->gaps)) return;
    if ($index_answerobject < 0) return;
    if (count($this->gaps[$index_gaps]) < 1) return;
    if ($index_answerobject >= count($this->gaps[$index_gaps])) return;
    $this->gaps[$index_gaps][$index_answerobject]->setPoints($points);
  }

/**
* Sets the state of a gap answer
*
* Sets the state of a gap answer with a given index. The index of the first
* gap is 0, the index of the second gap is 1 and so on.
*
* @param integer $index_gaps A nonnegative index of the n-th gap
* @param integer $index_answerobject A nonnegative index of the n-th answer in the specified gap
* @param boolean $state The state of the answer
* @access public
* @see $gaps
*/
  function setSingleAnswerState($index_gaps = 0, $index_answerobject = 0, $state = 0) {
    if ($index_gaps < 0) return;
    if (count($this->gaps) < 1) return;
    if ($index_gaps >= count($this->gaps)) return;
    if ($index_answerobject < 0) return;
    if (count($this->gaps[$index_gaps]) < 1) return;
    if ($index_answerobject >= count($this->gaps[$index_gaps])) return;
    $this->gaps[$index_gaps][$index_answerobject]->setState($state);
  }
	
	/**
	* Returns the points for a text gap
	*
	* Returns the points for a text gap and compares the given solution with
	* the entered solution using the text gap rating options.
	*
	* @param string $a_original The original (correct) text
	* @param string $a_entered The text entered by the user
	* @param integer $max_points The maximum number of points for the solution
	* @access public
	*/
	function getTextgapPoints($a_original, $a_entered, $max_points)
	{
		$result = 0;
		$gaprating = $this->getTextgapRating();
		switch ($gaprating)
		{
			case TEXTGAP_RATING_CASEINSENSITIVE:
				if (strcmp(strtolower(utf8_decode($a_original)), strtolower(utf8_decode($a_entered))) == 0) $result = $max_points;
				break;
			case TEXTGAP_RATING_CASESENSITIVE:
				if (strcmp(utf8_decode($a_original), utf8_decode($a_entered)) == 0) $result = $max_points;
				break;
			case TEXTGAP_RATING_LEVENSHTEIN1:
				if (levenshtein(utf8_decode($a_original), utf8_decode($a_entered)) <= 1) $result = $max_points;
				break;
			case TEXTGAP_RATING_LEVENSHTEIN2:
				if (levenshtein(utf8_decode($a_original), utf8_decode($a_entered)) <= 2) $result = $max_points;
				break;
			case TEXTGAP_RATING_LEVENSHTEIN3:
				if (levenshtein(utf8_decode($a_original), utf8_decode($a_entered)) <= 3) $result = $max_points;
				break;
			case TEXTGAP_RATING_LEVENSHTEIN4:
				if (levenshtein(utf8_decode($a_original), utf8_decode($a_entered)) <= 4) $result = $max_points;
				break;
			case TEXTGAP_RATING_LEVENSHTEIN5:
				if (levenshtein(utf8_decode($a_original), utf8_decode($a_entered)) <= 5) $result = $max_points;
				break;
		}
		return $result;
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
		
    $found_value1 = array();
    $found_value2 = array();
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
		$user_result = array();
    while ($data = $result->fetchRow(DB_FETCHMODE_OBJECT)) 
		{
			if (strcmp($data->value2, "") != 0)
			{
				$user_result[$data->value1] = array(
					"gap_id" => $data->value1,
					"value" => $data->value2
				);
			}
    }
    $points = 0;
    $counter = 0;
		foreach ($user_result as $gap_id => $value) 
		{
			if ($this->gaps[$gap_id][0]->getClozeType() == CLOZE_TEXT) 
			{
				$gappoints = 0;
				foreach ($this->gaps[$gap_id] as $k => $v) 
				{
					$gotpoints = $this->getTextgapPoints($v->getAnswertext(), $value["value"], $v->getPoints());
					if ($gotpoints > $gappoints) $gappoints = $gotpoints;
				}
				$points += $gappoints;
			} 
			else 
			{
				if ($value["value"] >= 0)
				{
					foreach ($this->gaps[$gap_id] as $answerkey => $answer)
					{
						if ($value["value"] == $answerkey)
						{
							$points += $answer->getPoints();
						}
					}
				}
			}
    }

		$points = parent::calculateReachedPoints($active_id, $pass = NULL, $points);
		return $points;
	}

/**
* Returns the maximum points, a learner can reach answering the question
*
* Returns the maximum points, a learner can reach answering the question
*
* @access public
* @see $points
*/
  function getMaximumPoints() {
    $points = 0;
    foreach ($this->gaps as $key => $value) {
      if ($value[0]->getClozeType() == CLOZE_TEXT) 
			{
				$gap_max_points = 0;
        foreach ($value as $key2 => $value2) 
				{
					if ($value2->getPoints() > $gap_max_points)
					{
						$gap_max_points = $value2->getPoints();
					}
				}
        $points += $gap_max_points;
      } else 
			{
				$srpoints = 0;
        foreach ($value as $key2 => $value2) 
				{
					if ($value2->getPoints() > $srpoints)
					{
						$srpoints = $value2->getPoints();
					}
				}
				$points += $srpoints;
      }
    }
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
* @see $answers
*/
  function saveWorkingData($active_id, $pass = NULL) 
	{
    global $ilDB;
		global $ilUser;

		include_once "./assessment/classes/class.ilObjTest.php";
		$activepass = ilObjTest::_getPass($active_id);
		
    $query = sprintf("DELETE FROM tst_solutions WHERE active_fi = %s AND question_fi = %s AND pass = %s",
			$ilDB->quote($active_id),
			$ilDB->quote($this->getId()),
			$ilDB->quote($activepass . "")
    );
    $result = $ilDB->query($query);

		$entered_values = 0;
    foreach ($_POST as $key => $value) {
      if (preg_match("/^gap_(\d+)/", $key, $matches)) 
			{ 
				$value = ilUtil::stripSlashes($value);
				if (strlen($value))
				{
					$gap = $this->getGap($matches[1]);
					if (!(($gap[0]->getClozeType() == CLOZE_SELECT) && ($value == -1)))
					{
						$query = sprintf("INSERT INTO tst_solutions (solution_id, active_fi, question_fi, value1, value2, pass, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, NULL)",
							$ilDB->quote($active_id),
							$ilDB->quote($this->getId()),
							$ilDB->quote($matches[1]),
							$ilDB->quote($value),
							$ilDB->quote($activepass . "")
						);
						$result = $ilDB->query($query);
						$entered_values++;
					}
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
	
			$query = sprintf("UPDATE qpl_questions SET obj_fi = %s, title = %s, comment = %s, points = %s, author = %s, question_text = %s, working_time = %s, complete = %s WHERE question_id = %s",
				$ilDB->quote($this->obj_id. ""),
				$ilDB->quote($this->title . ""),
				$ilDB->quote($this->comment . ""),
				$ilDB->quote($this->getMaximumPoints() . ""),
				$ilDB->quote($this->author . ""),
				$ilDB->quote($this->cloze_text . ""),
				$ilDB->quote($estw_time . ""),
				$ilDB->quote($complete . ""),
				$ilDB->quote($this->textgap_rating . ""),
				$ilDB->quote($this->original_id . "")
				);
			$result = $ilDB->query($query);
			$query = sprintf("UPDATE qpl_question_cloze SET textgap_rating = %s WHERE question_fi = %s",
				$ilDB->quote($this->textgap_rating . ""),
				$ilDB->quote($this->original_id . "")
			);
			$result = $ilDB->query($query);

			if ($result == DB_OK)
			{
				// write answers
				// delete old answers
				$query = sprintf("DELETE FROM qpl_answer_cloze WHERE question_fi = %s",
					$ilDB->quote($this->original_id)
				);
				$result = $ilDB->query($query);
	
				foreach ($this->gaps as $key => $value)
				{
					foreach ($value as $answer_id => $answer_obj)
					{
						$query = sprintf("INSERT INTO qpl_answer_cloze (answer_id, question_fi, gap_id, answertext, points, aorder, cloze_type, name, shuffle, correctness) VALUES (NULL, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
							$ilDB->quote($this->original_id . ""),
							$ilDB->quote($key . ""),
							$ilDB->quote($answer_obj->getAnswertext() . ""),
							$ilDB->quote($answer_obj->getPoints() . ""),
							$ilDB->quote($answer_obj->getOrder() . ""),
							$ilDB->quote($answer_obj->getClozeType() . ""),
							$ilDB->quote($answer_obj->getName() . ""),
							$ilDB->quote($answer_obj->getShuffle() . ""),
							$ilDB->quote($answer_obj->getState() . "")
						);
						$answer_result = $ilDB->query($query);
					}
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
		return 3;
	}
	
	/**
	* Returns the maximum number of text columns within which a user can type their answer
	*
	* Returns the maximum number of text columns within which a user can type their answer
	*
	* @return integer The column size of the gap
	* @access public
	*/
	function getColumnSize($gap)
	{
		$size = 0;
		foreach ($gap as $answer)
		{
			include_once "./classes/class.ilStr.php";
			$answertextsize = ilStr::strLen($answer->getAnswertext());
			if ($answertextsize > $size) $size = $answertextsize;
		}
		return $size;
	}
	
	/**
	* Returns the rating option for text gaps
	*
	* Returns the rating option for text gaps
	*
	* @return string The rating option for text gaps
	* @see $textgap_rating
	* @access public
	*/
	function getTextgapRating()
	{
		return $this->textgap_rating;
	}
	
	/**
	* Sets the rating option for text gaps
	*
	* Sets the rating option for text gaps
	*
	* @param string $a_textgap_rating The rating option for text gaps
	* @see $textgap_rating
	* @access public
	*/
	function setTextgapRating($a_textgap_rating)
	{
		switch ($a_textgap_rating)
		{
			case TEXTGAP_RATING_CASEINSENSITIVE:
			case TEXTGAP_RATING_CASESENSITIVE:
			case TEXTGAP_RATING_LEVENSHTEIN1:
			case TEXTGAP_RATING_LEVENSHTEIN2:
			case TEXTGAP_RATING_LEVENSHTEIN3:
			case TEXTGAP_RATING_LEVENSHTEIN4:
			case TEXTGAP_RATING_LEVENSHTEIN5:
				$this->textgap_rating = $a_textgap_rating;
				break;
			default:
				$this->textgap_rating = TEXTGAP_RATING_CASEINSENSITIVE;
				break;
		}
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
		return "qpl_question_cloze";
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
		return "qpl_answer_cloze";
	}
}

?>

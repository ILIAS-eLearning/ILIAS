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
* Class for matching questions
*
* assMatchingQuestion is a class for matching questions.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesTestQuestionPool
*/
class assMatchingQuestion extends assQuestion
{
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
	* assMatchingQuestion constructor
	*
	* The constructor takes possible arguments an creates an instance of the assMatchingQuestion object.
	*
	* @param string $title A title string to describe the question
	* @param string $comment A comment string to describe the question
	* @param string $author A string containing the name of the questions author
	* @param integer $owner A numerical ID to identify the owner/creator
	* @param string $question The question string of the matching question
	* @access public
	*/
	function assMatchingQuestion (
		$title = "",
		$comment = "",
		$author = "",
		$owner = -1,
		$question = "",
		$matching_type = MT_TERMS_DEFINITIONS
	)
	{
		$this->assQuestion($title, $comment, $author, $owner, $question);
		$this->matchingpairs = array();
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
		if (($this->title) and ($this->author) and ($this->question) and (count($this->matchingpairs)) and ($this->getMaximumPoints() > 0))
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
		$now = getdate();
		$created = sprintf("%04d%02d%02d%02d%02d%02d", $now['year'], $now['mon'], $now['mday'], $now['hours'], $now['minutes'], $now['seconds']);
		$terms = array();
		$matches = array();
		$foundimage = FALSE;
		foreach ($presentation->order as $entry)
		{
			switch ($entry["type"])
			{
				case "response":
					$response = $presentation->response[$entry["index"]];
					$rendertype = $response->getRenderType();
					switch (strtolower(get_class($rendertype)))
					{
						case "ilqtirenderchoice":
							$shuffle = $rendertype->getShuffle();
							$answerorder = 0;
							foreach ($rendertype->response_labels as $response_label)
							{
								$ident = $response_label->getIdent();
								$answertext = "";
								$answerimage = array();
								foreach ($response_label->material as $mat)
								{
									for ($m = 0; $m < $mat->getMaterialCount(); $m++)
									{
										$foundmat = $mat->getMaterial($m);
										if (strcmp($foundmat["type"], "mattext") == 0)
										{
											$answertext .= $foundmat["material"]->getContent();
										}
										if (strcmp($foundmat["type"], "matimage") == 0)
										{
											$foundimage = TRUE;
											$answerimage = array(
												"imagetype" => $foundmat["material"]->getImageType(),
												"label" => $foundmat["material"]->getLabel(),
												"content" => $foundmat["material"]->getContent()
											);
										}
									}
								}
								if (($response_label->getMatchMax() == 1) && (strlen($response_label->getMatchGroup())))
								{
									$terms[$ident] = array(
										"answertext" => $answertext,
										"answerimage" => $answerimage,
										"points" => 0,
										"answerorder" => $ident,
										"action" => ""
									);
								}
								else
								{
									$matches[$ident] = array(
										"answertext" => $answertext,
										"answerimage" => $answerimage,
										"points" => 0,
										"matchingorder" => $ident,
										"action" => ""
									);
								}
							}
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
				$subset = array();
				$correctness = 1;
				$conditionvar = $respcondition->getConditionvar();
				foreach ($conditionvar->order as $order)
				{
					switch ($order["field"])
					{
						case "varsubset":
							$subset = split(",", $conditionvar->varsubset[$order["index"]]->getContent());
							break;
					}
				}
				foreach ($respcondition->setvar as $setvar)
				{
					array_push($responses, array("subset" => $subset, "action" => $setvar->getAction(), "points" => $setvar->getContent())); 
				}
			}
		}
		$type = 1;
		if ($foundimage)
		{
			$type = 0;
		}
		$this->setTitle($item->getTitle());
		$this->setComment($item->getComment());
		$this->setAuthor($item->getAuthor());
		$this->setOwner($ilUser->getId());
		$this->setQuestion($this->QTIMaterialToString($item->getQuestiontext()));
		$this->setMatchingType($type);
		$this->setObjId($questionpool_id);
		$this->setEstimatedWorkingTime($duration["h"], $duration["m"], $duration["s"]);
		$extended_shuffle = $item->getMetadataEntry("shuffle");
		if (strlen($extended_shuffle) > 0)
		{
			$shuffle = $extended_shuffle;
		}
		$this->setShuffle($shuffle);
		foreach ($responses as $response)
		{
			$subset = $response["subset"];
			$term = array();
			$match = array();
			foreach ($subset as $ident)
			{
				if (array_key_exists($ident, $terms))
				{
					$term = $terms[$ident];
				}
				if (array_key_exists($ident, $matches))
				{
					$match = $matches[$ident];
				}
			}
			if ($type == 0)
			{
				$this->addMatchingPair($match["answertext"], $response["points"], $match["matchingorder"], $term["answerimage"]["label"], $term["answerorder"]);
			}
			else
			{
				$this->addMatchingPair($match["answertext"], $response["points"], $match["matchingorder"], $term["answertext"], $term["answerorder"]);
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
		foreach ($responses as $response)
		{
			$subset = $response["subset"];
			$term = array();
			$match = array();
			foreach ($subset as $ident)
			{
				if (array_key_exists($ident, $terms))
				{
					$term = $terms[$ident];
				}
				if (array_key_exists($ident, $matches))
				{
					$match = $matches[$ident];
				}
			}
			if ($type == 0)
			{
				$image =& base64_decode($term["answerimage"]["content"]);
				$imagepath = $this->getImagePath();
				include_once "./Services/Utilities/classes/class.ilUtil.php";
				if (!file_exists($imagepath))
				{
					ilUtil::makeDirParents($imagepath);
				}
				$imagepath .=  $term["answerimage"]["label"];
				$fh = fopen($imagepath, "wb");
				if ($fh == false)
				{
//									global $ilErr;
//									$ilErr->raiseError($this->lng->txt("error_save_image_file") . ": $php_errormsg", $ilErr->MESSAGE);
//									return;
				}
				else
				{
					$imagefile = fwrite($fh, $image);
					fclose($fh);
				}
				// create thumbnail file
				$thumbpath = $imagepath . "." . "thumb.jpg";
				ilUtil::convertImage($imagepath, $thumbpath, "JPEG", 100);
			}
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
		$a_xml_writer->xmlElement("fieldentry", NULL, MATCHING_QUESTION_IDENTIFIER);
		$a_xml_writer->xmlEndTag("qtimetadatafield");
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "AUTHOR");
		$a_xml_writer->xmlElement("fieldentry", NULL, $this->getAuthor());
		$a_xml_writer->xmlEndTag("qtimetadatafield");
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "shuffle");
		$a_xml_writer->xmlElement("fieldentry", NULL, $this->getShuffle());
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
			if ($this->get_matching_type() == MT_TERMS_PICTURES)
			{
				$a_xml_writer->xmlStartTag("material");
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
				$a_xml_writer->xmlEndTag("material");
			}
			else
			{
				$a_xml_writer->xmlStartTag("material");
				$this->addQTIMaterial($a_xml_writer, $matchingpair->getDefinition(), TRUE, FALSE);
				$a_xml_writer->xmlEndTag("material");
			}
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
			$attrs = array(
				"texttype" => "text/plain"
			);
			if ($this->isHTML($matchingpair->getTerm()))
			{
				$attrs["texttype"] = "text/xhtml";
			}
			$a_xml_writer->xmlElement("mattext", $attrs, $matchingpair->getTerm());
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
	* Saves a assMatchingQuestion object to a database
	*
	* Saves a assMatchingQuestion object to a database (experimental)
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
			$query = sprintf("INSERT INTO qpl_questions (question_id, question_type_fi, obj_fi, title, comment, author, owner, question_text, working_time, points, complete, created, original_id, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, NULL)",
				$ilDB->quote($question_type. ""),
				$ilDB->quote($this->obj_id. ""),
				$ilDB->quote($this->title. ""),
				$ilDB->quote($this->comment. ""),
				$ilDB->quote($this->author. ""),
				$ilDB->quote($this->owner. ""),
				$ilDB->quote(ilRTE::_replaceMediaObjectImageSrc($this->question, 0)),
				$ilDB->quote($estw_time. ""),
				$ilDB->quote($this->getMaximumPoints() . ""),
				$ilDB->quote($complete. ""),
				$ilDB->quote($created. ""),
				$original_id
			);

			$result = $ilDB->query($query);
			if ($result == DB_OK)
			{
				$this->id = $ilDB->getLastInsertId();
				$query = sprintf("INSERT INTO qpl_question_matching (question_fi, shuffle, matching_type) VALUES (%s, %s, %s)",
					$ilDB->quote($this->id . ""),
					$ilDB->quote($this->shuffle . ""),
					$ilDB->quote($this->matching_type. "")
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
			$query = sprintf("UPDATE qpl_questions SET obj_fi = %s, title = %s, comment = %s, author = %s, question_text = %s, working_time=%s, points = %s, complete = %s WHERE question_id = %s",
				$ilDB->quote($this->obj_id. ""),
				$ilDB->quote($this->title. ""),
				$ilDB->quote($this->comment. ""),
				$ilDB->quote($this->author. ""),
				$ilDB->quote(ilRTE::_replaceMediaObjectImageSrc($this->question, 0)),
				$ilDB->quote($estw_time. ""),
				$ilDB->quote($this->getMaximumPoints() . ""),
				$ilDB->quote($complete. ""),
				$ilDB->quote($this->id. "")
			);
			$result = $ilDB->query($query);
			$query = sprintf("UPDATE qpl_question_matching SET shuffle = %s, matching_type = %s WHERE question_fi = %s",
				$ilDB->quote($this->shuffle . ""),
				$ilDB->quote($this->matching_type. ""),
				$ilDB->quote($this->id . "")
			);
			$result = $ilDB->query($query);
		}

		if ($result == DB_OK)
		{
			// Antworten schreiben
			// alte Antworten löschen
			$query = sprintf("DELETE FROM qpl_answer_matching WHERE question_fi = %s",
				$ilDB->quote($this->id)
			);
			$result = $ilDB->query($query);

			// Anworten wegschreiben
			foreach ($this->matchingpairs as $key => $value)
			{
				$matching_obj = $this->matchingpairs[$key];
				$query = sprintf("INSERT INTO qpl_answer_matching (answer_id, question_fi, answertext, points, aorder, matchingtext, matching_order) VALUES (NULL, %s, %s, %s, %s, %s, %s)",
					$ilDB->quote($this->id),
					$ilDB->quote($matching_obj->getTerm() . ""),
					$ilDB->quote($matching_obj->getPoints() . ""),
					$ilDB->quote($matching_obj->getTermId() . ""),
					$ilDB->quote($matching_obj->getDefinition() . ""),
					$ilDB->quote($matching_obj->getDefinitionId() . "")
				);
				$matching_result = $ilDB->query($query);
			}
		}
		parent::saveToDb($original_id);
	}

	/**
	* Loads a assMatchingQuestion object from a database
	*
	* Loads a assMatchingQuestion object from a database (experimental)
	*
	* @param object $db A pear DB object
	* @param integer $question_id A unique key which defines the multiple choice test in the database
	* @access public
	*/
	function loadFromDb($question_id)
	{
		global $ilDB;

    $query = sprintf("SELECT qpl_questions.*, qpl_question_matching.* FROM qpl_questions, qpl_question_matching WHERE question_id = %s AND qpl_questions.question_id = qpl_question_matching.question_fi",
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
				$this->author = $data->author;
				$this->solution_hint = $data->solution_hint;
				$this->obj_id = $data->obj_fi;
				$this->original_id = $data->original_id;
				$this->owner = $data->owner;
				$this->matching_type = $data->matching_type;
				include_once("./Services/RTE/classes/class.ilRTE.php");
				$this->question = ilRTE::_replaceMediaObjectImageSrc($data->question_text, 1);
				$this->points = $data->points;
				$this->shuffle = $data->shuffle;
				$this->setEstimatedWorkingTime(substr($data->working_time, 0, 2), substr($data->working_time, 3, 2), substr($data->working_time, 6, 2));
			}

			$query = sprintf("SELECT * FROM qpl_answer_matching WHERE question_fi = %s ORDER BY answer_id ASC",
				$ilDB->quote($question_id)
			);
			$result = $ilDB->query($query);
			include_once "./Modules/TestQuestionPool/classes/class.assAnswerMatching.php";
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
		include_once "./Modules/TestQuestionPool/classes/class.assAnswerMatching.php";
		array_push($this->matchingpairs, new ASS_AnswerMatching($answertext, $points, $answerorder, $matchingtext, $matchingorder));
	}
	
	
	/**
	* Duplicates an assMatchingQuestion
	*
	* Duplicates an assMatchingQuestion
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

		// duplicate the image
		$clone->duplicateImages($original_id);
		return $clone->id;
	}

	/**
	* Copies an assMatchingQuestion
	*
	* Copies an assMatchingQuestion
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
		if ($title)
		{
			$clone->setTitle($title);
		}
		$source_questionpool = $this->getObjId();
		$clone->setObjId($target_questionpool);
		$clone->saveToDb();

		// copy question page content
		$clone->copyPageOfQuestion($original_id);
		// copy XHTML media objects
		$clone->copyXHTMLMediaObjectsOfQuestion($original_id);
		// duplicate the generic feedback
		$clone->duplicateFeedbackGeneric($original_id);

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
		include_once "./Modules/TestQuestionPool/classes/class.assAnswerMatching.php";
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
				include_once "./Services/MediaObjects/classes/class.ilObjMediaObject.php";
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
			ilUtil::sendInfo($this->lng->txt("duplicate_matching_values_selected"), TRUE);
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
	function saveWorkingData($active_id, $pass = NULL)
	{
		global $ilDB;
		global $ilUser;
		
		$saveWorkingDataResult = $this->checkSaveData();
		$entered_values = 0;
		if ($saveWorkingDataResult)
		{
			if (is_null($pass))
			{
				include_once "./Modules/Test/classes/class.ilObjTest.php";
				$pass = ilObjTest::_getPass($active_id);
			}
			
			$query = sprintf("DELETE FROM tst_solutions WHERE active_fi = %s AND question_fi = %s AND pass = %s",
				$ilDB->quote($active_id . ""),
				$ilDB->quote($this->getId() . ""),
				$ilDB->quote($pass . "")
			);
			$result = $ilDB->query($query);
			foreach ($_POST as $key => $value)
			{
				if (preg_match("/^sel_matching_(\d+)/", $key, $matches))
				{
					if (!(preg_match("/initial_value_\d+/", $value)))
					{
						if ($value > -1) // -1 is the unselected value in the non javascript version
						{
							$entered_values++;
							$query = sprintf("INSERT INTO tst_solutions (solution_id, active_fi, question_fi, value1, value2, pass, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, NULL)",
								$ilDB->quote($active_id),
								$ilDB->quote($this->getId()),
								$ilDB->quote(trim($value)),
								$ilDB->quote(trim($matches[1])),
								$ilDB->quote($pass . "")
							);
							$result = $ilDB->query($query);
						}
					}
				}
			}
			$saveWorkingDataResult = true;
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
	
			$query = sprintf("UPDATE qpl_questions SET obj_fi = %s, title = %s, comment = %s, author = %s, question_text = %s, working_time=%s, points = %s, complete = %s WHERE question_id = %s",
				$ilDB->quote($this->obj_id. ""),
				$ilDB->quote($this->title. ""),
				$ilDB->quote($this->comment. ""),
				$ilDB->quote($this->author. ""),
				$ilDB->quote($this->question. ""),
				$ilDB->quote($estw_time. ""),
				$ilDB->quote($this->getMaximumPoints() . ""),
				$ilDB->quote($complete. ""),
				$ilDB->quote($this->original_id. "")
			);
			$result = $ilDB->query($query);
			$query = sprintf("UPDATE qpl_question_matching SET shuffle = %, matching_type = %s WHERE question_fi = %s",
				$ilDB->quote($this->shuffle . ""),
				$ilDB->quote($this->matching_type. ""),
				$ilDB->quote($this->original_id . "")
			);
			$result = $ilDB->query($query);

			if ($result == DB_OK)
			{
				// write answers
				// delete old answers
				$query = sprintf("DELETE FROM qpl_answer_matching WHERE question_fi = %s",
					$ilDB->quote($this->original_id)
				);
				$result = $ilDB->query($query);
	
				foreach ($this->matchingpairs as $key => $value)
				{
					$matching_obj = $this->matchingpairs[$key];
					$query = sprintf("INSERT INTO qpl_answer_matching (answer_id, question_fi, answertext, points, aorder, matchingtext, matching_order) VALUES (NULL, %s, %s, %s, %s, %s, %s)",
						$ilDB->quote($this->original_id . ""),
						$ilDB->quote($matching_obj->getTerm() . ""),
						$ilDB->quote($matching_obj->getPoints() . ""),
						$ilDB->quote($matching_obj->getTermId() . ""),
						$ilDB->quote($matching_obj->getDefinition() . ""),
						$ilDB->quote($matching_obj->getDefinitionId() . "")
					);
					$matching_result = $ilDB->query($query);
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
	
	/**
	* Sets the shuffle flag
	*
	* Sets the shuffle flag
	*
	* @param boolean $shuffle A flag indicating whether the answers are shuffled or not
	* @access public
	* @see $shuffle
	*/
	function setShuffle($shuffle)
	{
		switch ($shuffle)
		{
			case 0:
			case 1:
			case 2:
			case 3:
				$this->shuffle = $shuffle;
				break;
			default:
				$this->shuffle = 1;
				break;
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
		return "assMatchingQuestion";
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

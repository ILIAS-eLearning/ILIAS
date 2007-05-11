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

include_once "./Modules/TestQuestionPool/classes/import/qti12/class.assQuestionImport.php";

/**
* Class for cloze question imports
*
* assClozeTestImport is a class for cloze question imports
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesTestQuestionPool
*/
class assClozeTestImport extends assQuestionImport
{
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
	function fromXML(&$item, $questionpool_id, &$tst_id, &$tst_object, &$question_counter, &$import_mapping)
	{
		global $ilUser;

		// empty session variable for imported xhtml mobs
		unset($_SESSION["import_mob_xhtml"]);
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
					array_push($questiontext, $this->object->QTIMaterialToString($material));
					break;
				case "response":
					$response = $presentation->response[$entry["index"]];
					$rendertype = $response->getRenderType(); 
					array_push($questiontext, "<<" . $response->getIdent() . ">>");
					switch (strtolower(get_class($response->getRenderType())))
					{
						case "ilqtirenderfib":
							switch ($response->getRenderType()->getFibtype())
							{
								case FIBTYPE_DECIMAL:
								case FIBTYPE_INTEGER:
									array_push($gaps, 
										array(
											"ident" => $response->getIdent(), 
											"type" => CLOZE_NUMERIC, 
											"answers" => array(), 
											"minnumber" => $response->getRenderType()->getMinnumber(), 
											"maxnumber" => $response->getRenderType()->getMaxnumber()
										)
									);
									break;
								default:
								case FIBTYPE_STRING:
									array_push($gaps, array("ident" => $response->getIdent(), "type" => CLOZE_TEXT, "answers" => array()));
									break;
							}
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
									$answertext .= $this->object->QTIMaterialToString($mat);
								}
								$answers[$ident] = array(
									"answertext" => $answertext,
									"points" => 0,
									"answerorder" => $answerorder++,
									"action" => "",
									"shuffle" => $rendertype->getShuffle()
								);
							}
							array_push($gaps, array("ident" => $response->getIdent(), "type" => CLOZE_SELECT, "shuffle" => $rendertype->getShuffle(), "answers" => $answers));
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
								if ($g["type"] == CLOZE_SELECT)
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
								else if ($g["type"] == CLOZE_TEXT)
								{
									array_push($gaps[$gi]["answers"], array(
										"answertext" => $equals,
										"points" => $setvar->getContent(),
										"answerorder" => count($gaps[$gi]["answers"]),
										"action" => $setvar->getAction()
									));
								}
								else if ($g["type"] == CLOZE_NUMERIC)
								{
									array_push($gaps[$gi]["answers"], array(
										"answertext" => $equals,
										"points" => $setvar->getContent(),
										"answerorder" => count($gaps[$gi]["answers"]),
										"action" => $setvar->getAction()
									));
								}
							}
						}
					}
				}
			}
		}
		$this->object->setTitle($item->getTitle());
		$this->object->setComment($item->getComment());
		$this->object->setAuthor($item->getAuthor());
		$this->object->setOwner($ilUser->getId());
		$this->object->setObjId($questionpool_id);
		$this->object->setEstimatedWorkingTime($duration["h"], $duration["m"], $duration["s"]);
		$textgap_rating = $item->getMetadataEntry("textgaprating");
		$this->object->setFixedTextLength($item->getMetadataEntry("fixedTextLength"));
		if (strlen($textgap_rating) == 0) $textgap_rating = "ci";
		$this->object->setTextgapRating($textgap_rating);
		$gaptext = array();
		foreach ($gaps as $gapidx => $gap)
		{
			$gapcontent = array();
			include_once "./Modules/TestQuestionPool/classes/class.assClozeGap.php";
			$clozegap = new assClozeGap($gap["type"]);
			foreach ($gap["answers"] as $index => $answer)
			{
				include_once "./Modules/TestQuestionPool/classes/class.assAnswerCloze.php";
				$gapanswer = new assAnswerCloze($answer["answertext"], $answer["points"], $answer["answerorder"]);
				switch ($clozegap->getType())
				{
					case CLOZE_SELECT:
						$clozegap->setShuffle($answer["shuffle"]);
						break;
					case CLOZE_NUMERIC:
						$gapanswer->setLowerBound($gap["minnumber"]);
						$gapanswer->setUpperBound($gap["maxnumber"]);
						break;
				}
				$clozegap->addItem($gapanswer);
				array_push($gapcontent, $answer["answertext"]);
			}
			$this->object->addGapAtIndex($clozegap, $gapidx);
			$gaptext[$gap["ident"]] = "[gap]" . join(",", $gapcontent). "[/gap]";
		}
		$clozetext = join("", $questiontext);
		foreach ($gaptext as $idx => $val)
		{
			$clozetext = str_replace("<<" . $idx . ">>", $val, $clozetext);
		}
		$this->object->setQuestion($clozetext);
		$this->object->saveToDb();
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
				$this->object->setQuestion(ilRTE::_replaceMediaObjectImageSrc(str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $this->object->getQuestion()), 1));
			}
			$this->object->saveToDb();
		}
		if (count($item->suggested_solutions))
		{
			foreach ($item->suggested_solutions as $suggested_solution)
			{
				$this->object->setSuggestedSolution($suggested_solution["solution"]->getContent(), $suggested_solution["gap_index"], true);
			}
			$this->object->saveToDb();
		}
		if ($tst_id > 0)
		{
			$q_1_id = $this->object->getId();
			$question_id = $this->object->duplicate(true);
			$tst_object->questions[$question_counter++] = $question_id;
			$import_mapping[$item->getIdent()] = array("pool" => $q_1_id, "test" => $question_id);
		}
		else
		{
			$import_mapping[$item->getIdent()] = array("pool" => $this->object->getId(), "test" => 0);
		}
	}
}

?>

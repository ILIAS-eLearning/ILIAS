<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Modules/TestQuestionPool/classes/import/qti12/class.assQuestionImport.php";

/**
* Class for numeric question imports
*
* assNumericImport is a class for numeric question imports
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesTestQuestionPool
*/
class assNumericImport extends assQuestionImport
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
		global $DIC;
		$ilUser = $DIC['ilUser'];

		// empty session variable for imported xhtml mobs
		unset($_SESSION["import_mob_xhtml"]);
		$presentation = $item->getPresentation(); 
		$duration = $item->getDuration();
		$now = getdate();
		$maxchars = 0;
		$points = 0;
		$upperlimit = 0;
		$lowerlimit = 0;
		$feedbacksgeneric = array();
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
				if (count($respcondition->displayfeedback))
				{
					foreach ($respcondition->displayfeedback as $feedbackpointer)
					{
						if (strlen($feedbackpointer->getLinkrefid()))
						{
							foreach ($item->itemfeedback as $ifb)
							{
								if (strcmp($ifb->getIdent(), "response_allcorrect") == 0)
								{
									// found a feedback for the identifier
									if (count($ifb->material))
									{
										foreach ($ifb->material as $material)
										{
											$feedbacksgeneric[1] = $material;
										}
									}
									if ((count($ifb->flow_mat) > 0))
									{
										foreach ($ifb->flow_mat as $fmat)
										{
											if (count($fmat->material))
											{
												foreach ($fmat->material as $material)
												{
													$feedbacksgeneric[1] = $material;
												}
											}
										}
									}
								} 
								else if (strcmp($ifb->getIdent(), "response_onenotcorrect") == 0)
								{
									// found a feedback for the identifier
									if (count($ifb->material))
									{
										foreach ($ifb->material as $material)
										{
											$feedbacksgeneric[0] = $material;
										}
									}
									if ((count($ifb->flow_mat) > 0))
									{
										foreach ($ifb->flow_mat as $fmat)
										{
											if (count($fmat->material))
											{
												foreach ($fmat->material as $material)
												{
													$feedbacksgeneric[0] = $material;
												}
											}
										}
									} 
								}
							}
						}
					}
				}
			}
		}

		$this->addGeneralMetadata($item);
		$this->object->setTitle($item->getTitle());
		$this->object->setNrOfTries($item->getMaxattempts());
		$this->object->setComment($item->getComment());
		$this->object->setAuthor($item->getAuthor());
		$this->object->setOwner($ilUser->getId());
		$this->object->setQuestion($this->object->QTIMaterialToString($item->getQuestiontext()));
		$this->object->setObjId($questionpool_id);
		$this->object->setEstimatedWorkingTime($duration["h"], $duration["m"], $duration["s"]);
		$this->object->setMaxChars($maxchars);
		$this->object->setPoints($points);
		$this->object->setLowerLimit($lowerlimit);
		$this->object->setUpperLimit($upperlimit);
		// additional content editing mode information
		$this->object->setAdditionalContentEditingMode(
				$this->fetchAdditionalContentEditingModeInformation($item)
		);		
		$this->object->saveToDb();
		if (count($item->suggested_solutions))
		{
			foreach ($item->suggested_solutions as $suggested_solution)
			{
				$this->object->setSuggestedSolution($suggested_solution["solution"]->getContent(), $suggested_solution["gap_index"], true);
			}
			$this->object->saveToDb();
		}
		foreach ($feedbacksgeneric as $correctness => $material)
		{
			$m = $this->object->QTIMaterialToString($material);
			$feedbacksgeneric[$correctness] = $m;
		}
		// handle the import of media objects in XHTML code
		$questiontext = $this->object->getQuestion();
		if (is_array($_SESSION["import_mob_xhtml"]))
		{
			include_once "./Services/MediaObjects/classes/class.ilObjMediaObject.php";
			include_once "./Services/RTE/classes/class.ilRTE.php";
			foreach ($_SESSION["import_mob_xhtml"] as $mob)
			{
				if ($tst_id > 0)
				{
					$importfile = $this->getTstImportArchivDirectory() . '/' . $mob["uri"];
				}
				else
				{
					$importfile = $this->getQplImportArchivDirectory() . '/' . $mob["uri"];
				}
				
				global $DIC; /* @var ILIAS\DI\Container $DIC */
				$DIC['ilLog']->write(__METHOD__.': import mob from dir: '. $importfile);
				
				$media_object =& ilObjMediaObject::_saveTempFileAsMediaObject(basename($importfile), $importfile, FALSE);
				ilObjMediaObject::_saveUsage($media_object->getId(), "qpl:html", $this->object->getId());
				$questiontext = str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $questiontext);
				foreach ($feedbacksgeneric as $correctness => $material)
				{
					$feedbacksgeneric[$correctness] = str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $material);
				}
			}
		}
		$this->object->setQuestion(ilRTE::_replaceMediaObjectImageSrc($questiontext, 1));
		foreach ($feedbacksgeneric as $correctness => $material)
		{
			$this->object->feedbackOBJ->importGenericFeedback(
					$this->object->getId(), $correctness, ilRTE::_replaceMediaObjectImageSrc($material, 1)
			);
		}
		$this->object->saveToDb();
		if ($tst_id > 0)
		{
			$q_1_id = $this->object->getId();
			$question_id = $this->object->duplicate(true, null, null, null, $tst_id);
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

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
* Class for matching question imports
*
* assMatchingQuestionImport is a class for matching question imports
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesTestQuestionPool
*/
class assMatchingQuestionImport extends assQuestionImport
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
		$this->object->setTitle($item->getTitle());
		$this->object->setComment($item->getComment());
		$this->object->setAuthor($item->getAuthor());
		$this->object->setOwner($ilUser->getId());
		$this->object->setQuestion($this->object->QTIMaterialToString($item->getQuestiontext()));
		$this->object->setMatchingType($type);
		$this->object->setObjId($questionpool_id);
		$this->object->setEstimatedWorkingTime($duration["h"], $duration["m"], $duration["s"]);
		$extended_shuffle = $item->getMetadataEntry("shuffle");
		if (strlen($extended_shuffle) > 0)
		{
			$shuffle = $extended_shuffle;
		}
		$this->object->setShuffle($shuffle);
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
				$this->object->addMatchingPair($match["answertext"], $response["points"], $match["matchingorder"], $term["answerimage"]["label"], $term["answerorder"]);
			}
			else
			{
				$this->object->addMatchingPair($match["answertext"], $response["points"], $match["matchingorder"], $term["answertext"], $term["answerorder"]);
			}
		}
		$this->object->saveToDb();
		if (count($item->suggested_solutions))
		{
			foreach ($item->suggested_solutions as $suggested_solution)
			{
				$this->object->setSuggestedSolution($suggested_solution["solution"]->getContent(), $suggested_solution["gap_index"], true);
			}
			$this->object->saveToDb();
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
				$imagepath = $this->object->getImagePath();
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
//									$ilErr->raiseError($this->object->lng->txt("error_save_image_file") . ": $php_errormsg", $ilErr->MESSAGE);
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
				ilObjMediaObject::_saveUsage($media_object->getId(), "qpl:html", $this->object->getId());
				$this->object->setQuestion(ilRTE::_replaceMediaObjectImageSrc(str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $this->object->getQuestion()), 1));
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

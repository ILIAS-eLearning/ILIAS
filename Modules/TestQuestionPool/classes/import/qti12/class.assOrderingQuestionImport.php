<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Modules/TestQuestionPool/classes/import/qti12/class.assQuestionImport.php";

/**
* Class for ordering question imports
*
* assOrderingQuestionImport is a class for ordering question imports
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesTestQuestionPool
*/
class assOrderingQuestionImport extends assQuestionImport
{
    /**
     * @var assOrderingQuestion
     */
    public $object;
    
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
    public function fromXML(&$item, $questionpool_id, &$tst_id, &$tst_object, &$question_counter, &$import_mapping)
    {
        global $DIC;
        $ilUser = $DIC['ilUser'];

        // empty session variable for imported xhtml mobs
        unset($_SESSION["import_mob_xhtml"]);
        $presentation = $item->getPresentation();
        $duration = $item->getDuration();
        $shuffle = 0;
        $now = getdate();
        $foundimage = false;
        $created = sprintf("%04d%02d%02d%02d%02d%02d", $now['year'], $now['mon'], $now['mday'], $now['hours'], $now['minutes'], $now['seconds']);
        $answers = array();
        $type = OQ_TERMS;
        
        foreach ($presentation->order as $entry) {
            switch ($entry["type"]) {
                case "response":
                    $response = $presentation->response[$entry["index"]];
                    $type = $response->getIdent();
                    if ($response->getIdent() == 'OQP') {
                        $type = OQ_PICTURES;
                    } elseif ($response->getIdent() == 'OQNP') {
                        $type = OQ_NESTED_PICTURES;
                    } elseif ($response->getIdent() == 'OQNT') {
                        $type = OQ_NESTED_TERMS;
                    } elseif ($response->getIdent() == 'OQT') {
                        $type = OQ_TERMS;
                    }

                    $rendertype = $response->getRenderType();
                    switch (strtolower(get_class($rendertype))) {
                        case "ilqtirenderchoice":
                            $shuffle = $rendertype->getShuffle();
                            $answerorder = 0;
                            foreach ($rendertype->response_labels as $response_label) {
                                $ident = $response_label->getIdent();
                                $answertext = "";
                                foreach ($response_label->material as $mat) {
                                    for ($m = 0; $m < $mat->getMaterialCount(); $m++) {
                                        $foundmat = $mat->getMaterial($m);

                                        if (strcmp($foundmat["material"]->getLabel(), "answerdepth") == 0) {
                                            $answerdepth = $foundmat["material"]->getContent();
                                        }
                                        if (strcmp($foundmat["type"], "mattext") == 0
                                        && strcmp($foundmat["material"]->getLabel(), "answerdepth") != 0) {
                                            $answertext .= $foundmat["material"]->getContent();
                                        }
                                        if (strcmp($foundmat["type"], "matimage") == 0
                                            && strcmp($foundmat["material"]->getLabel(), "answerdepth") != 0) {
                                            $foundimage = true;
                                            $answerimage = array(
                                                "imagetype" => $foundmat["material"]->getImageType(),
                                                "label" => $foundmat["material"]->getLabel(),
                                                "content" => $foundmat["material"]->getContent()
                                            );
                                        }
                                    }
                                }
                                $answers[$answerorder] = array(
                                    'ident' => $ident,
                                    "answertext" => $answertext,
                                    "answerimage" => $answerimage,
                                    "points" => 0,
                                    "answerorder" => $answerorder,
                                    "answerdepth" => $answerdepth,
                                    "correctness" => "",
                                    "action" => ""
                                );
                                $answerorder++;
                            }
                            break;
                    }
                    break;
            }
        }
        $responses = array();
        $feedbacksgeneric = array();
        foreach ($item->resprocessing as $resprocessing) {
            foreach ($resprocessing->respcondition as $respcondition) {
                $ident = "";
                $correctness = 1;
                $conditionvar = $respcondition->getConditionvar();
                foreach ($conditionvar->order as $order) {
                    switch ($order["field"]) {
                        case "arr_not":
                            $correctness = 0;
                            break;
                        case "varequal":
                            $ident = $conditionvar->varequal[$order["index"]]->getContent();
                            $orderindex = $conditionvar->varequal[$order["index"]]->getIndex();
                            break;
                    }
                }
                foreach ($respcondition->setvar as $setvar) {
                    if (strcmp($ident, "") != 0) {
                        $answers[$ident]["solutionorder"] = $orderindex;
                        $answers[$ident]["action"] = $setvar->getAction();
                        $answers[$ident]["points"] = $setvar->getContent();
                    }
                }
                if (count($respcondition->displayfeedback)) {
                    foreach ($respcondition->displayfeedback as $feedbackpointer) {
                        if (strlen($feedbackpointer->getLinkrefid())) {
                            foreach ($item->itemfeedback as $ifb) {
                                if (strcmp($ifb->getIdent(), "response_allcorrect") == 0) {
                                    // found a feedback for the identifier
                                    if (count($ifb->material)) {
                                        foreach ($ifb->material as $material) {
                                            $feedbacksgeneric[1] = $material;
                                        }
                                    }
                                    if ((count($ifb->flow_mat) > 0)) {
                                        foreach ($ifb->flow_mat as $fmat) {
                                            if (count($fmat->material)) {
                                                foreach ($fmat->material as $material) {
                                                    $feedbacksgeneric[1] = $material;
                                                }
                                            }
                                        }
                                    }
                                } elseif (strcmp($ifb->getIdent(), "response_onenotcorrect") == 0) {
                                    // found a feedback for the identifier
                                    if (count($ifb->material)) {
                                        foreach ($ifb->material as $material) {
                                            $feedbacksgeneric[0] = $material;
                                        }
                                    }
                                    if ((count($ifb->flow_mat) > 0)) {
                                        foreach ($ifb->flow_mat as $fmat) {
                                            if (count($fmat->material)) {
                                                foreach ($fmat->material as $material) {
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
        
        $itemfeedbacks = $this->getFeedbackAnswerSpecific($item, 'link_');

        $this->addGeneralMetadata($item);
        $this->object->setTitle($item->getTitle());
        $this->object->setNrOfTries($item->getMaxattempts());
        $this->object->setComment($item->getComment());
        $this->object->setAuthor($item->getAuthor());
        $this->object->setOwner($ilUser->getId());
        $this->object->setQuestion($this->object->QTIMaterialToString($item->getQuestiontext()));
        $this->object->setOrderingType($type);
        $this->object->setObjId($questionpool_id);
        $this->object->setThumbGeometry($item->getMetadataEntry("thumb_geometry"));
        $this->object->setElementHeight($item->getMetadataEntry("element_height"));
        $this->object->setEstimatedWorkingTime($duration["h"], $duration["m"], $duration["s"]);
        $this->object->setShuffle($shuffle);
        $points = 0;
        $solanswers = array();
        
        foreach ($answers as $answer) {
            $solanswers[$answer["solutionorder"]] = $answer;
        }
        ksort($solanswers);
        $position = 0;
        foreach ($solanswers as $answer) {
            $points += $answer["points"];
            
            $element = new ilAssOrderingElement();
            
            if ($element->isExportIdent($answer['ident'])) {
                $element->setExportIdent($answer['ident']);
            } else {
                $element->setPosition($position++);
                if (isset($answer['answerdepth'])) {
                    $element->setIndentation($answer['answerdepth']);
                }
            }
            
            if ($this->object->isImageOrderingType()) {
                $element->setContent($answer["answerimage"]["label"]);
            } else {
                $element->setContent($answer["answertext"]);
            }
            
            $this->object->getOrderingElementList()->addElement($element);
        }
        $points = ($item->getMetadataEntry("points") > 0) ? $item->getMetadataEntry("points") : $points;
        $this->object->setPoints($points);
        // additional content editing mode information
        $this->object->setAdditionalContentEditingMode(
            $this->fetchAdditionalContentEditingModeInformation($item)
        );
        $this->object->saveToDb();
        if (count($item->suggested_solutions)) {
            foreach ($item->suggested_solutions as $suggested_solution) {
                $this->object->setSuggestedSolution($suggested_solution["solution"]->getContent(), $suggested_solution["gap_index"], true);
            }
            $this->object->saveToDb();
        }
        foreach ($answers as $answer) {
            if ($type == OQ_PICTURES || $type == OQ_NESTED_PICTURES) {
                include_once "./Services/Utilities/classes/class.ilUtil.php";
                if (strlen($answer['answerimage']['label']) && strlen($answer['answerimage']['content'])) {
                    $image = &base64_decode($answer["answerimage"]["content"]);
                    $imagepath = $this->object->getImagePath();
                    if (!file_exists($imagepath)) {
                        ilUtil::makeDirParents($imagepath);
                    }
                    $imagepath .= $answer["answerimage"]["label"];
                    $fh = fopen($imagepath, "wb");
                    if ($fh == false) {
                        //									global $DIC;
//									$ilErr = $DIC['ilErr'];
//									$ilErr->raiseError($this->object->lng->txt("error_save_image_file") . ": $php_errormsg", $ilErr->MESSAGE);
//									return;
                    } else {
                        $imagefile = fwrite($fh, $image);
                        fclose($fh);
                    }
                    // create thumbnail file
                    $thumbpath = $imagepath . "." . "thumb.jpg";
                    ilUtil::convertImage($imagepath, $thumbpath, "JPEG", $this->object->getThumbGeometry());
                }
            }
        }
        foreach ($feedbacksgeneric as $correctness => $material) {
            $m = $this->object->QTIMaterialToString($material);
            $feedbacksgeneric[$correctness] = $m;
        }
        $questiontext = $this->object->getQuestion();

        // handle the import of media objects in XHTML code
        if (is_array($_SESSION["import_mob_xhtml"])) {
            include_once "./Services/MediaObjects/classes/class.ilObjMediaObject.php";
            include_once "./Services/RTE/classes/class.ilRTE.php";
            foreach ($_SESSION["import_mob_xhtml"] as $mob) {
                if ($tst_id > 0) {
                    $importfile = $this->getTstImportArchivDirectory() . '/' . $mob["uri"];
                } else {
                    $importfile = $this->getQplImportArchivDirectory() . '/' . $mob["uri"];
                }
                
                global $DIC; /* @var ILIAS\DI\Container $DIC */
                $DIC['ilLog']->write(__METHOD__ . ': import mob from dir: ' . $importfile);
                
                $media_object = &ilObjMediaObject::_saveTempFileAsMediaObject(basename($importfile), $importfile, false);
                ilObjMediaObject::_saveUsage($media_object->getId(), "qpl:html", $this->object->getId());
                $questiontext = str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $questiontext);
                foreach ($this->object->getOrderingElementList() as $element) {
                    $element->setContent(str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $element->getContent()));
                }
                foreach ($feedbacksgeneric as $correctness => $material) {
                    $feedbacksgeneric[$correctness] = str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $material);
                }
                foreach ($itemfeedbacks as $ident => $material) {
                    $itemfeedbacks[$ident] = str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $material);
                }
            }
        }
        $this->object->setQuestion(ilRTE::_replaceMediaObjectImageSrc($questiontext, 1));
        foreach ($this->object->getOrderingElementList() as $element) {
            $element->setContent(ilRTE::_replaceMediaObjectImageSrc($element->getContent(), 1));
        }
        foreach ($feedbacksgeneric as $correctness => $material) {
            $this->object->feedbackOBJ->importGenericFeedback(
                $this->object->getId(),
                $correctness,
                ilRTE::_replaceMediaObjectImageSrc($material, 1)
            );
        }
        foreach ($itemfeedbacks as $ident => $material) {
            $index = $this->fetchIndexFromFeedbackIdent($ident, 'link_');
            
            $this->object->feedbackOBJ->importSpecificAnswerFeedback(
                $this->object->getId(),
                0,
                $index,
                ilRTE::_replaceMediaObjectImageSrc($material, 1)
            );
        }
        $this->object->saveToDb();
        if ($tst_id > 0) {
            $this->object->setObjId($tst_id);
            $tstQid = $this->object->getId();
            $qplQid = $this->object->duplicate(true, null, null, null, $questionpool_id);
            assQuestion::resetOriginalId($qplQid);
            assQuestion::saveOriginalId($tstQid, $qplQid);
            $tst_object->questions[$question_counter++] = $tstQid;
            $import_mapping[$item->getIdent()] = array("pool" => $qplQid, "test" => $tstQid);
        } else {
            $import_mapping[$item->getIdent()] = array("pool" => $this->object->getId(), "test" => 0);
        }
    }
}

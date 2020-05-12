<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Modules/TestQuestionPool/classes/import/qti12/class.assQuestionImport.php";

/**
* Class for multiple choice question imports
*
* assMultipleChoiceImport is a class for multiple choice question imports
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesTestQuestionPool
*/
class assMultipleChoiceImport extends assQuestionImport
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
    public function fromXML(&$item, $questionpool_id, &$tst_id, &$tst_object, &$question_counter, &$import_mapping)
    {
        global $DIC;
        $ilUser = $DIC['ilUser'];
        // empty session variable for imported xhtml mobs
        unset($_SESSION["import_mob_xhtml"]);
        $presentation = $item->getPresentation();
        $duration = $item->getDuration();
        $shuffle = 0;
        $selectionLimit = null;
        $now = getdate();
        $created = sprintf("%04d%02d%02d%02d%02d%02d", $now['year'], $now['mon'], $now['mday'], $now['hours'], $now['minutes'], $now['seconds']);
        $answers = array();
        foreach ($presentation->order as $entry) {
            switch ($entry["type"]) {
                case "response":
                    $response = $presentation->response[$entry["index"]];
                    $rendertype = $response->getRenderType();
                    switch (strtolower(get_class($response->getRenderType()))) {
                        case "ilqtirenderchoice":
                            $shuffle = $rendertype->getShuffle();
                            if ($rendertype->getMaxnumber()) {
                                $selectionLimit = $rendertype->getMaxnumber();
                            }
                            $answerorder = 0;
                            $foundimage = false;
                            foreach ($rendertype->response_labels as $response_label) {
                                $ident = $response_label->getIdent();
                                $answertext = "";
                                $answerimage = array();
                                foreach ($response_label->material as $mat) {
                                    $embedded = false;
                                    for ($m = 0; $m < $mat->getMaterialCount(); $m++) {
                                        $foundmat = $mat->getMaterial($m);
                                        if (strcmp($foundmat["type"], "mattext") == 0) {
                                        }
                                        if (strcmp($foundmat["type"], "matimage") == 0) {
                                            if (strlen($foundmat["material"]->getEmbedded())) {
                                                $embedded = true;
                                            }
                                        }
                                    }
                                    if ($embedded) {
                                        for ($m = 0; $m < $mat->getMaterialCount(); $m++) {
                                            $foundmat = $mat->getMaterial($m);
                                            if (strcmp($foundmat["type"], "mattext") == 0) {
                                                $answertext .= $foundmat["material"]->getContent();
                                            }
                                            if (strcmp($foundmat["type"], "matimage") == 0) {
                                                $foundimage = true;
                                                $answerimage = array(
                                                    "imagetype" => $foundmat["material"]->getImageType(),
                                                    "label" => $foundmat["material"]->getLabel(),
                                                    "content" => $foundmat["material"]->getContent()
                                                );
                                            }
                                        }
                                    } else {
                                        $answertext = $this->object->QTIMaterialToString($mat);
                                    }
                                }
                                $answers[$ident] = array(
                                    "answertext" => $answertext,
                                    "imagefile" => $answerimage,
                                    "points" => 0,
                                    "answerorder" => $answerorder++,
                                    "points_unchecked" => 0,
                                    "action" => ""
                                );
                            }
                            break;
                    }
                    break;
            }
        }
        $responses = array();
        $feedbacks = array();
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
                            break;
                    }
                }
                foreach ($respcondition->setvar as $setvar) {
                    if (strcmp($ident, "") != 0) {
                        if ($correctness) {
                            $answers[$ident]["action"] = $setvar->getAction();
                            $answers[$ident]["points"] = $setvar->getContent();
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
                                            if (strcmp($ifb->getIdent(), $feedbackpointer->getLinkrefid()) == 0) {
                                                // found a feedback for the identifier
                                                if (count($ifb->material)) {
                                                    foreach ($ifb->material as $material) {
                                                        $feedbacks[$ident] = $material;
                                                    }
                                                }
                                                if ((count($ifb->flow_mat) > 0)) {
                                                    foreach ($ifb->flow_mat as $fmat) {
                                                        if (count($fmat->material)) {
                                                            foreach ($fmat->material as $material) {
                                                                $feedbacks[$ident] = $material;
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        } else {
                            $answers[$ident]["action"] = $setvar->getAction();
                            $answers[$ident]["points_unchecked"] = $setvar->getContent();
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
        $this->object->setShuffle($shuffle);
        $this->object->setSelectionLimit($selectionLimit);
        $this->object->setThumbSize($item->getMetadataEntry("thumb_size"));

        foreach ($answers as $answer) {
            if ($item->getMetadataEntry('singleline') || (is_array($answer["imagefile"]) && count($answer["imagefile"]) > 0)) {
                $this->object->isSingleline = true;
            }
            $this->object->addAnswer($answer["answertext"], $answer["points"], $answer["points_unchecked"], $answer["answerorder"], $answer["imagefile"]["label"]);
        }
        // additional content editing mode information
        $this->object->setAdditionalContentEditingMode(
            $this->fetchAdditionalContentEditingModeInformation($item)
        );
        $this->object->saveToDb();
        foreach ($answers as $answer) {
            if (is_array($answer["imagefile"]) && (count($answer["imagefile"]) > 0)) {
                $image = &base64_decode($answer["imagefile"]["content"]);
                $imagepath = $this->object->getImagePath();
                include_once "./Services/Utilities/classes/class.ilUtil.php";
                if (!file_exists($imagepath)) {
                    ilUtil::makeDirParents($imagepath);
                }
                $imagepath .= $answer["imagefile"]["label"];
                $fh = fopen($imagepath, "wb");
                if ($fh == false) {
                } else {
                    $imagefile = fwrite($fh, $image);
                    fclose($fh);
                }
            }
        }

        $feedbackSetting = $item->getMetadataEntry('feedback_setting');
        if (!is_null($feedbackSetting)) {
            $this->object->feedbackOBJ->saveSpecificFeedbackSetting($this->object->getId(), $feedbackSetting);
        }

        // handle the import of media objects in XHTML code
        foreach ($feedbacks as $ident => $material) {
            $m = $this->object->QTIMaterialToString($material);
            $feedbacks[$ident] = $m;
        }
        foreach ($feedbacksgeneric as $correctness => $material) {
            $m = $this->object->QTIMaterialToString($material);
            $feedbacksgeneric[$correctness] = $m;
        }
        $questiontext = $this->object->getQuestion();
        $answers = &$this->object->getAnswers();
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
                foreach ($answers as $key => $value) {
                    $answer_obj = &$answers[$key];
                    $answer_obj->setAnswertext(str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $answer_obj->getAnswertext()));
                }
                foreach ($feedbacks as $ident => $material) {
                    $feedbacks[$ident] = str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $material);
                }
                foreach ($feedbacksgeneric as $correctness => $material) {
                    $feedbacksgeneric[$correctness] = str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $material);
                }
            }
        }
        $this->object->setQuestion(ilRTE::_replaceMediaObjectImageSrc($questiontext, 1));
        foreach ($answers as $key => $value) {
            $answer_obj = &$answers[$key];
            $answer_obj->setAnswertext(ilRTE::_replaceMediaObjectImageSrc($answer_obj->getAnswertext(), 1));
        }
        foreach ($feedbacks as $ident => $material) {
            $this->object->feedbackOBJ->importSpecificAnswerFeedback(
                $this->object->getId(),
                0,
                $ident,
                ilRTE::_replaceMediaObjectImageSrc($material, 1)
            );
        }
        foreach ($feedbacksgeneric as $correctness => $material) {
            $this->object->feedbackOBJ->importGenericFeedback(
                $this->object->getId(),
                $correctness,
                ilRTE::_replaceMediaObjectImageSrc($material, 1)
            );
        }
        $this->object->saveToDb();
        if (count($item->suggested_solutions)) {
            foreach ($item->suggested_solutions as $suggested_solution) {
                $this->object->setSuggestedSolution($suggested_solution["solution"]->getContent(), $suggested_solution["gap_index"], true);
            }
            $this->object->saveToDb();
        }
        if ($tst_id > 0) {
            $q_1_id = $this->object->getId();
            $question_id = $this->object->duplicate(true, null, null, null, $tst_id);
            $tst_object->questions[$question_counter++] = $question_id;
            $import_mapping[$item->getIdent()] = array("pool" => $q_1_id, "test" => $question_id);
        } else {
            $import_mapping[$item->getIdent()] = array("pool" => $this->object->getId(), "test" => 0);
        }
        //$ilLog->write(strftime("%D %T") . ": finished import multiple choice question (single response)");
    }
}

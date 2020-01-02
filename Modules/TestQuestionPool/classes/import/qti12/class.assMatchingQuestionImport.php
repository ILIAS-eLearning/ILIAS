<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

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
    public function saveImage($data, $filename)
    {
        $image =&base64_decode($data);
        $imagepath = $this->object->getImagePath();
        include_once "./Services/Utilities/classes/class.ilUtil.php";
        if (!file_exists($imagepath)) {
            ilUtil::makeDirParents($imagepath);
        }
        $imagepath .=  $filename;
        $fh = fopen($imagepath, "wb");
        if ($fh == false) {
        } else {
            $imagefile = fwrite($fh, $image);
            fclose($fh);
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
        $created = sprintf("%04d%02d%02d%02d%02d%02d", $now['year'], $now['mon'], $now['mday'], $now['hours'], $now['minutes'], $now['seconds']);
        $definitions = array();
        $terms = array();
        $foundimage = false;
        foreach ($presentation->order as $entry) {
            switch ($entry["type"]) {
                case "response":
                    $response = $presentation->response[$entry["index"]];
                    $rendertype = $response->getRenderType();
                    switch (strtolower(get_class($rendertype))) {
                        case "ilqtirenderchoice":
                            $shuffle = $rendertype->getShuffle();
                            $answerorder = 0;
                            foreach ($rendertype->response_labels as $response_label) {
                                $ident = $response_label->getIdent();
                                $answertext = "";
                                $answerimage = array();
                                foreach ($response_label->material as $mat) {
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
                                }
                                if (($response_label->getMatchMax() == 1) && (strlen($response_label->getMatchGroup()))) {
                                    $definitions[$ident] = array(
                                        "answertext" => $answertext,
                                        "answerimage" => $answerimage,
                                        "points" => 0,
                                        "answerorder" => $ident,
                                        "action" => ""
                                    );
                                } else {
                                    $terms[$ident] = array(
                                        "term" => $answertext,
                                        "answerimage" => $answerimage,
                                        "points" => 0,
                                        "ident" => $ident,
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
        $feedbacksgeneric = array();
        foreach ($item->resprocessing as $resprocessing) {
            foreach ($resprocessing->respcondition as $respcondition) {
                $subset = array();
                $correctness = 1;
                $conditionvar = $respcondition->getConditionvar();
                foreach ($conditionvar->order as $order) {
                    switch ($order["field"]) {
                        case "varsubset":
                            $subset = explode(",", $conditionvar->varsubset[$order["index"]]->getContent());
                            break;
                    }
                }
                foreach ($respcondition->setvar as $setvar) {
                    array_push($responses, array("subset" => $subset, "action" => $setvar->getAction(), "points" => $setvar->getContent()));
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
        
        include_once "./Modules/TestQuestionPool/classes/class.assAnswerMatchingTerm.php";
        include_once "./Modules/TestQuestionPool/classes/class.assAnswerMatchingDefinition.php";
        include_once "./Modules/TestQuestionPool/classes/class.assAnswerMatchingPair.php";
        $this->object->createNewQuestion();
        $this->addGeneralMetadata($item);
        $this->object->setTitle($item->getTitle());
        $this->object->setNrOfTries($item->getMaxattempts());
        $this->object->setComment($item->getComment());
        $this->object->setAuthor($item->getAuthor());
        $this->object->setOwner($ilUser->getId());
        $this->object->setQuestion($this->object->QTIMaterialToString($item->getQuestiontext()));
        $this->object->setObjId($questionpool_id);
        $this->object->setEstimatedWorkingTime($duration["h"], $duration["m"], $duration["s"]);
        $extended_shuffle = $item->getMetadataEntry("shuffle");
        $this->object->setThumbGeometry($item->getMetadataEntry("thumb_geometry"));

        if (strlen($item->getMetadataEntry('matching_mode'))) {
            $this->object->setMatchingMode($item->getMetadataEntry('matching_mode'));
        } else {
            $this->object->setMatchingMode(assMatchingQuestion::MATCHING_MODE_1_ON_1);
        }

        // save images
        foreach ($terms as $term) {
            if (count($term['answerimage'])) {
                $this->saveImage($term['answerimage']['content'], $term['answerimage']['label']);
            }
        }
        foreach ($definitions as $definition) {
            if (count($definition['answerimage'])) {
                $this->saveImage($definition['answerimage']['content'], $definition['answerimage']['label']);
            }
        }

        foreach ($terms as $termindex => $term) {
            $this->object->addTerm(new assAnswerMatchingTerm($term["term"], $term['answerimage']['label'], $term["ident"]));
        }
        foreach ($definitions as $definitionindex => $definition) {
            $this->object->addDefinition(new assAnswerMatchingDefinition($definition["answertext"], $definition['answerimage']['label'], $definition["answerorder"]));
        }

        if (strlen($extended_shuffle) > 0) {
            $shuffle = $extended_shuffle;
        }
        $this->object->setShuffle($shuffle);

        foreach ($responses as $response) {
            $subset = $response["subset"];
            foreach ($subset as $ident) {
                if (array_key_exists($ident, $definitions)) {
                    $definition = $definitions[$ident];
                }
                if (array_key_exists($ident, $terms)) {
                    $term = $terms[$ident];
                }
            }
            $this->object->addMatchingPair(new assAnswerMatchingTerm('', '', $term["ident"]), new assAnswerMatchingDefinition('', '', $definition["answerorder"]), $response['points']);
        }
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
        foreach ($responses as $response) {
            $subset = $response["subset"];
            foreach ($subset as $ident) {
                if (array_key_exists($ident, $definitions)) {
                    $definition = $definitions[$ident];
                }
                if (array_key_exists($ident, $terms)) {
                    $term = $terms[$ident];
                }
            }
        }

        foreach ($feedbacksgeneric as $correctness => $material) {
            $m = $this->object->QTIMaterialToString($material);
            $feedbacksgeneric[$correctness] = $m;
        }

        $feedbacks = $this->getFeedbackAnswerSpecific($item, 'correct_');

        // handle the import of media objects in XHTML code
        $questiontext = $this->object->getQuestion();
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
                
                $media_object =&ilObjMediaObject::_saveTempFileAsMediaObject(basename($importfile), $importfile, false);
                ilObjMediaObject::_saveUsage($media_object->getId(), "qpl:html", $this->object->getId());
                $questiontext = str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $questiontext);
                foreach ($feedbacks as $ident => $material) {
                    $feedbacks[$ident] = str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $material);
                }
                foreach ($feedbacksgeneric as $correctness => $material) {
                    $feedbacksgeneric[$correctness] = str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $material);
                }
            }
        }
        $this->object->setQuestion(ilRTE::_replaceMediaObjectImageSrc($questiontext, 1));
        foreach ($feedbacks as $ident => $material) {
            $index = $this->fetchIndexFromFeedbackIdent($ident, 'correct_');
            
            $this->object->feedbackOBJ->importSpecificAnswerFeedback(
                $this->object->getId(),
                0,
                $index,
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
        if ($tst_id > 0) {
            $q_1_id = $this->object->getId();
            $question_id = $this->object->duplicate(true, null, null, null, $tst_id);
            $tst_object->questions[$question_counter++] = $question_id;
            $import_mapping[$item->getIdent()] = array("pool" => $q_1_id, "test" => $question_id);
        } else {
            $import_mapping[$item->getIdent()] = array("pool" => $this->object->getId(), "test" => 0);
        }
    }
    
    /**
     * @param $feedbackIdent
     * @param string $prefix
     * @return int
     */
    protected function fetchIndexFromFeedbackIdent($feedbackIdent, $prefix = 'response_')
    {
        list($termId, $definitionId) = explode('_', str_replace($prefix, '', $feedbackIdent));
        
        foreach ($this->object->getMatchingPairs() as $index => $pair) {
            /* @var assAnswerMatchingPair $pair */
            
            if ($pair->term->identifier != $termId) {
                continue;
            }
            
            if ($pair->definition->identifier != $definitionId) {
                continue;
            }
            
            return (int) $index;
        }
        
        return -1;
    }
}

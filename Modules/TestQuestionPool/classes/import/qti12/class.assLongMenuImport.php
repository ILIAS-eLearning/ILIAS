<?php
require_once 'Modules/TestQuestionPool/classes/import/qti12/class.assQuestionImport.php';
require_once 'Modules/TestQuestionPool/classes/class.assLongMenu.php';

class assLongMenuImport extends assQuestionImport
{
    public $object;

    public function fromXML(&$item, $questionpool_id, &$tst_id, &$tst_object, &$question_counter, &$import_mapping)
    {
        global $DIC;
        $ilUser = $DIC['ilUser'];

        unset($_SESSION["import_mob_xhtml"]);

        $presentation = $item->getPresentation();
        $duration = $item->getDuration();
        $questiontext = array();
        $seperate_question_field = $item->getMetadataEntry("question");
        $clozetext = array();
        $now = getdate();
        $created = sprintf("%04d%02d%02d%02d%02d%02d", $now['year'], $now['mon'], $now['mday'], $now['hours'], $now['minutes'], $now['seconds']);
        $answers = array();
        $correct_answers = array();
        $presentation = $item->getPresentation();
        $gap_types = json_decode($item->getMetadataEntry("gapTypes"));
        foreach ($presentation->order as $entry) {
            switch ($entry["type"]) {
                case "material":

                    $material = $presentation->material[$entry["index"]];
                    if (preg_match('/\[Longmenu \d\]/', $this->object->QTIMaterialToString($material))) {
                        $this->object->setLongMenuTextValue($this->object->QTIMaterialToString($material));
                    } else {
                        $this->object->setQuestion($this->object->QTIMaterialToString($material));
                    }

                    
                    break;
            }
        }

        // fixLongMenuImageImport - process images in question and long menu text when question is imported
        $questiontext = $this->object->getQuestion();
        $longmenutext = $this->object->getLongMenuTextValue();
        if (is_array($_SESSION["import_mob_xhtml"]))
        {
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

                $media_object = ilObjMediaObject::_saveTempFileAsMediaObject(basename($importfile), $importfile, FALSE);
                ilObjMediaObject::_saveUsage($media_object->getId(), "qpl:html", $this->object->getId());

                $questiontext = str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $questiontext);
                $longmenutext = str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $longmenutext);

            }
        }
        $this->object->setQuestion(ilRTE::_replaceMediaObjectImageSrc($questiontext, 1));
        $this->object->setLongMenuTextValue(ilRTE::_replaceMediaObjectImageSrc($longmenutext, 1));
        // fau.

        foreach ($item->resprocessing as $resprocessing) {
            foreach ($resprocessing->respcondition as $respcondition) {
                $correctness = 1;
                $conditionvar = $respcondition->getConditionvar();
                foreach ($conditionvar->order as $order) {
                    switch ($order["field"]) {
                        case "varequal":
                            $equals = $conditionvar->varequal[$order["index"]]->getContent();
                            $gapident = $conditionvar->varequal[$order["index"]]->getRespident();
                            $id = $this->getIdFromGapIdent($gapident);
                            $answers[$id][] = $equals;
                            break;
                    }
                }
                foreach ($respcondition->setvar as $setvar) {
                    if (strcmp($gapident, "") != 0) {
                        if ($setvar->getContent() > 0) {
                            $id = $this->getIdFromGapIdent($gapident);
                            $correct_answers[$id][0][] = $equals;
                            $correct_answers[$id][1] = $setvar->getContent();
                            if (is_array($gap_types) && key_exists($id, $gap_types)) {
                                $correct_answers[$id][2] = $gap_types[$id];
                            }
                        }
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
                                } else {
                                    // found a feedback for the identifier
                                    if (count($ifb->material)) {
                                        foreach ($ifb->material as $material) {
                                            $feedbacks[$ifb->getIdent()] = $material;
                                        }
                                    }
                                    if ((count($ifb->flow_mat) > 0)) {
                                        foreach ($ifb->flow_mat as $fmat) {
                                            if (count($fmat->material)) {
                                                foreach ($fmat->material as $material) {
                                                    $feedbacks[$ifb->getIdent()] = $material;
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

        $sum = 0;
        foreach ($correct_answers as $row) {
            $sum += $row[1];
        }
        $this->object->setAnswers($answers);
        // handle the import of media objects in XHTML code
        if (count($feedbacks) > 0) {
            foreach ($feedbacks as $ident => $material) {
                $m = $this->object->QTIMaterialToString($material);
                $feedbacks[$ident] = $m;
            }
        }
        if (is_array($feedbacksgeneric) && count($feedbacksgeneric) > 0) {
            foreach ($feedbacksgeneric as $correctness => $material) {
                $m = $this->object->QTIMaterialToString($material);
                $feedbacksgeneric[$correctness] = $m;
            }
        }
        
        $this->addGeneralMetadata($item);
        $this->object->setTitle($item->getTitle());
        $this->object->setNrOfTries($item->getMaxattempts());
        $this->object->setComment($item->getComment());
        $this->object->setAuthor($item->getAuthor());
        $this->object->setOwner($ilUser->getId());
        $this->object->setObjId($questionpool_id);
        $this->object->setMinAutoComplete($item->getMetadataEntry("minAutoCompleteLength"));
        $this->object->setIdenticalscoring((int) $item->getMetadataEntry("identical_scoring"));
        $this->object->setEstimatedWorkingTime($duration["h"], $duration["m"], $duration["s"]);
        $this->object->setCorrectAnswers($correct_answers);
        $this->object->setPoints($sum);
        // additional content editing mode information
        $this->object->setAdditionalContentEditingMode(
            $this->fetchAdditionalContentEditingModeInformation($item)
        );
        $this->object->saveToDb();

        if (count($feedbacks) > 0) {
            foreach ($feedbacks as $ident => $material) {
                $this->object->feedbackOBJ->importSpecificAnswerFeedback(
                    $this->object->getId(),
                    0,
                    $ident,
                    ilRTE::_replaceMediaObjectImageSrc($material, 1)
                );
            }
        }
        if (is_array($feedbacksgeneric) && count($feedbacksgeneric) > 0) {
            foreach ($feedbacksgeneric as $correctness => $material) {
                $this->object->feedbackOBJ->importGenericFeedback(
                    $this->object->getId(),
                    $correctness,
                    ilRTE::_replaceMediaObjectImageSrc($material, 1)
                );
            }
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
    }

    private function getIdFromGapIdent($ident)
    {
        $id = preg_split('/_/', $ident);
        return $id[1] - 1;
    }
}

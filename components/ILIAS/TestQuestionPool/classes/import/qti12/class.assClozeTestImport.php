<?php
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
* Class for cloze question imports
*
* assClozeTestImport is a class for cloze question imports
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup components\ILIASTestQuestionPool
*/
class assClozeTestImport extends assQuestionImport
{
    /**
    * Creates a question from a QTI file
    *
    * Receives parameters from a QTI parser and creates a valid ILIAS question object
    *
    * @param ilQTIItem $item The QTI item object
    * @param integer $questionpool_id The id of the parent questionpool
    * @param integer $tst_id The id of the parent test if the question is part of a test
    * @param object $tst_object A reference to the parent test object
    * @param integer $question_counter A reference to a question counter to count the questions of an imported question pool
    * @param array $import_mapping An array containing references to included ILIAS objects
    * @access public
    */
    public function fromXML(
        string $importdirectory,
        int $user_id,
        ilQTIItem $item,
        int $questionpool_id,
        ?int $tst_id,
        ?ilObject &$tst_object,
        int &$question_counter,
        array $import_mapping
    ): array {
        // empty session variable for imported xhtml mobs
        ilSession::clear('import_mob_xhtml');
        $presentation = $item->getPresentation();

        $questiontext = $this->processNonAbstractedImageReferences(
            $item->getMetadataEntry("question") ?? '&nbsp;',
            $item->getIliasSourceNic()
        );

        $clozetext_array = [];
        $shuffle = 0;
        $gaps = [];
        foreach ($presentation->order as $entry) {
            switch ($entry["type"]) {
                case "material":

                    $material_string = $this->QTIMaterialToString(
                        $presentation->material[$entry["index"]]
                    );

                    array_push($clozetext_array, $material_string);

                    break;
                case "response":
                    $response = $presentation->response[$entry["index"]];
                    $rendertype = $response->getRenderType();
                    array_push($clozetext_array, "<<" . $response->getIdent() . ">>");

                    switch (strtolower(get_class($response->getRenderType()))) {
                        case "ilqtirenderfib":
                            switch ($response->getRenderType()->getFibtype()) {
                                case ilQTIRenderFib::FIBTYPE_DECIMAL:
                                case ilQTIRenderFib::FIBTYPE_INTEGER:
                                    array_push(
                                        $gaps,
                                        [
                                            "ident" => $response->getIdent(),
                                            "type" => assClozeGap::TYPE_NUMERIC,
                                            "answers" => [],
                                            "minnumber" => $response->getRenderType()->getMinnumber(),
                                            "maxnumber" => $response->getRenderType()->getMaxnumber(),
                                            'gap_size' => $response->getRenderType()->getMaxchars()
                                        ]
                                    );
                                    break;
                                default:
                                case ilQTIRenderFib::FIBTYPE_STRING:
                                    array_push(
                                        $gaps,
                                        ["ident" => $response->getIdent(),
                                              "type" => assClozeGap::TYPE_TEXT,
                                              "answers" => [],
                                              'gap_size' => $response->getRenderType()->getMaxchars()
                                        ]
                                    );
                                    break;
                            }
                            break;
                        case "ilqtirenderchoice":
                            $answers = [];
                            $shuffle = $rendertype->getShuffle();
                            $answerorder = 0;
                            foreach ($rendertype->response_labels as $response_label) {
                                $ident = $response_label->getIdent();
                                $answertext = "";
                                foreach ($response_label->material as $mat) {
                                    $answertext .= $this->QTIMaterialToString($mat);
                                }
                                $answers[$ident] = [
                                    "answertext" => $answertext,
                                    "points" => 0,
                                    "answerorder" => $answerorder++,
                                    "action" => "",
                                    "shuffle" => $rendertype->getShuffle()
                                ];
                            }
                            $gaps[] = [
                                'ident' => $response->getIdent(),
                                'type' => assClozeGap::TYPE_SELECT,
                                'shuffle' => $rendertype->getShuffle(),
                                'answers' => $answers
                            ];
                            break;
                    }
                    break;
            }
        }
        $feedbacks = [];
        $feedbacksgeneric = [];
        foreach ($item->resprocessing as $resprocessing) {
            foreach ($resprocessing->respcondition as $respcondition) {
                $ident = "";
                $correctness = 1;
                $conditionvar = $respcondition->getConditionvar();
                foreach ($conditionvar->order as $order) {
                    switch ($order["field"]) {
                        case "varequal":
                            $equals = $conditionvar->varequal[$order["index"]]->getContent();
                            $gapident = $conditionvar->varequal[$order["index"]]->getRespident();
                            break;
                    }
                }
                foreach ($respcondition->setvar as $setvar) {
                    if (strcmp($gapident, "") != 0) {
                        foreach ($gaps as $gi => $g) {
                            if (strcmp($g["ident"], $gapident) == 0) {
                                if ($g["type"] == assClozeGap::TYPE_SELECT) {
                                    foreach ($gaps[$gi]["answers"] as $ai => $answer) {
                                        if (strcmp($answer["answertext"], $equals) == 0) {
                                            $gaps[$gi]["answers"][$ai]["action"] = $setvar->getAction();
                                            $gaps[$gi]["answers"][$ai]["points"] = $setvar->getContent();
                                        }
                                    }
                                } elseif ($g["type"] == assClozeGap::TYPE_TEXT) {
                                    array_push($gaps[$gi]["answers"], [
                                        "answertext" => $equals,
                                        "points" => $setvar->getContent(),
                                        "answerorder" => count($gaps[$gi]["answers"]),
                                        "action" => $setvar->getAction()

                                    ]);
                                } elseif ($g["type"] == assClozeGap::TYPE_NUMERIC) {
                                    array_push($gaps[$gi]["answers"], [
                                        "answertext" => $equals,
                                        "points" => $setvar->getContent(),
                                        "answerorder" => count($gaps[$gi]["answers"]),
                                        "action" => $setvar->getAction()
                                    ]);
                                }
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

        $this->addGeneralMetadata($item);
        $this->object->setTitle($item->getTitle());
        $this->object->setNrOfTries((int) $item->getMaxattempts());
        $this->object->setComment($item->getComment());
        $this->object->setAuthor($item->getAuthor());
        $this->object->setOwner($user_id);
        $this->object->setObjId($questionpool_id);
        $textgap_rating = $item->getMetadataEntry("textgaprating");
        $this->object->setFixedTextLength((int) $item->getMetadataEntry("fixedTextLength"));
        $this->object->setIdenticalScoring((bool) $item->getMetadataEntry("identicalScoring"));
        $this->object->setFeedbackMode(
            strlen($item->getMetadataEntry("feedback_mode")) ?
            $item->getMetadataEntry("feedback_mode") : ilAssClozeTestFeedback::FB_MODE_GAP_QUESTION
        );
        $combinations = json_decode(base64_decode($item->getMetadataEntry("combinations")));
        if (strlen($textgap_rating) == 0) {
            $textgap_rating = "ci";
        }
        $this->object->setTextgapRating($textgap_rating);
        $gaptext = [];
        foreach ($gaps as $gapidx => $gap) {
            $gapcontent = [];
            $clozegap = new assClozeGap($gap["type"]);
            foreach ($gap["answers"] as $index => $answer) {
                $gapanswer = new assAnswerCloze($answer["answertext"], $answer["points"], $answer["answerorder"]);
                $gapanswer->setGapSize((int) ($gap["gap_size"] ?? 0));
                switch ($clozegap->getType()) {
                    case assClozeGap::TYPE_SELECT:
                        $clozegap->setShuffle($answer["shuffle"]);
                        break;
                    case assClozeGap::TYPE_NUMERIC:
                        $gapanswer->setLowerBound($gap["minnumber"]);
                        $gapanswer->setUpperBound($gap["maxnumber"]);
                        break;
                }
                $clozegap->setGapSize((int) ($gap["gap_size"] ?? 0));
                $clozegap->addItem($gapanswer);
                array_push($gapcontent, $answer["answertext"]);
            }
            $this->object->addGapAtIndex($clozegap, $gapidx);
            $gaptext[$gap["ident"]] = "[gap]" . join(",", $gapcontent) . "[/gap]";
        }

        $this->object->setQuestion($questiontext);
        $clozetext = join("", $clozetext_array);

        foreach ($gaptext as $idx => $val) {
            $clozetext = str_replace("<<" . $idx . ">>", $val, $clozetext);
        }
        $this->object->setClozeTextValue($clozetext);

        // additional content editing mode information
        $this->object->setAdditionalContentEditingMode(
            $this->fetchAdditionalContentEditingModeInformation($item)
        );
        $this->object->saveToDb();

        if (is_array($combinations) && count($combinations) > 0) {
            assClozeGapCombination::clearGapCombinationsFromDb($this->object->getId());
            assClozeGapCombination::importGapCombinationToDb($this->object->getId(), $combinations);
            $gap_combinations = new assClozeGapCombination();
            $gap_combinations->loadFromDb($this->object->getId());
            $this->object->setGapCombinations($gap_combinations);
            $this->object->setGapCombinationsExists(true);
        }

        // handle the import of media objects in XHTML code
        foreach ($feedbacks as $ident => $material) {
            $m = $this->QTIMaterialToString($material);
            $feedbacks[$ident] = $m;
        }
        foreach ($feedbacksgeneric as $correctness => $material) {
            $m = $this->QTIMaterialToString($material);
            $feedbacksgeneric[$correctness] = $m;
        }
        if (is_array(ilSession::get("import_mob_xhtml"))) {
            foreach (ilSession::get("import_mob_xhtml") as $mob) {
                $importfile = $importdirectory . DIRECTORY_SEPARATOR . $mob["uri"];
                global $DIC; /* @var ILIAS\DI\Container $DIC */
                $DIC['ilLog']->write(__METHOD__ . ': import mob from dir: ' . $importfile);

                $media_object = ilObjMediaObject::_saveTempFileAsMediaObject(basename($importfile), $importfile, false);
                $questiontext = str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $questiontext);
                $clozetext = str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $clozetext);
                foreach ($feedbacks as $ident => $material) {
                    $feedbacks[$ident] = str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $material);
                }
                foreach ($feedbacksgeneric as $correctness => $material) {
                    $feedbacksgeneric[$correctness] = str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $material);
                }
            }
        }
        $this->object->setQuestion(ilRTE::_replaceMediaObjectImageSrc($questiontext, 1));
        $this->object->setClozeTextValue(ilRTE::_replaceMediaObjectImageSrc($clozetext, 1));
        foreach ($feedbacks as $ident => $material) {
            $fbIdentifier = $this->buildFeedbackIdentifier($ident);
            $this->object->feedbackOBJ->importSpecificAnswerFeedback(
                $this->object->getId(),
                $fbIdentifier->getQuestionIndex(),
                $fbIdentifier->getAnswerIndex(),
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
                $this->importSuggestedSolution(
                    $this->object->getId(),
                    $suggested_solution["solution"]->getContent(),
                    $suggested_solution["gap_index"]
                );
            }
        }
        if (isset($tst_id) && $tst_id !== $questionpool_id) {
            $qpl_qid = $this->object->getId();
            $tst_qid = $this->object->duplicate(true, "", "", -1, $tst_id);
            $tst_object->questions[$question_counter++] = $tst_qid;
            $import_mapping[$item->getIdent()] = ["pool" => $qpl_qid, "test" => $tst_qid];
            return $import_mapping;
        }

        if (isset($tst_id)) {
            $tst_object->questions[$question_counter++] = $this->object->getId();
            $import_mapping[$item->getIdent()] = ["pool" => 0, "test" => $this->object->getId()];
            return $import_mapping;
        }

        $import_mapping[$item->getIdent()] = ["pool" => $this->object->getId(), "test" => 0];
        return $import_mapping;
    }

    /**
     * @param string $ident
     * @return ilAssSpecificFeedbackIdentifier
     */
    protected function buildFeedbackIdentifier($ident): ilAssSpecificFeedbackIdentifier
    {
        $fbIdentifier = new ilAssSpecificFeedbackIdentifier();

        $ident = explode('_', $ident);

        if (count($ident) > 1) {
            $fbIdentifier->setQuestionIndex($ident[0]);
            $fbIdentifier->setAnswerIndex($ident[1]);
        } else {
            $fbIdentifier->setQuestionIndex($ident[0]);
            $fbIdentifier->setAnswerIndex(0);
        }

        return $fbIdentifier;
    }
}

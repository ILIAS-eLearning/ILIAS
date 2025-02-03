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

class assLongMenuImport extends assQuestionImport
{
    public $object;

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
        ilSession::clear('import_mob_xhtml');

        $answers = [];
        $correct_answers = [];
        $presentation = $item->getPresentation();
        $gap_types = json_decode($item->getMetadataEntry("gapTypes"));
        foreach ($presentation->order as $entry) {
            switch ($entry["type"]) {
                case "material":

                    $material = $presentation->material[$entry["index"]];
                    if (preg_match('/\[Longmenu \d\]/', $this->QTIMaterialToString($material))) {
                        $this->object->setLongMenuTextValue($this->QTIMaterialToString($material));
                    } else {
                        $this->object->setQuestion($this->QTIMaterialToString($material));
                    }


                    break;
            }
        }

        // fixLongMenuImageImport - process images in question and long menu text when question is imported
        $questiontext = $this->object->getQuestion();
        $longmenutext = $this->object->getLongMenuTextValue();
        if (is_array(ilSession::get("import_mob_xhtml"))) {
            foreach (ilSession::get("import_mob_xhtml") as $mob) {
                $importfile = $importdirectory . DIRECTORY_SEPARATOR . $mob["uri"];

                global $DIC; /* @var ILIAS\DI\Container $DIC */
                $DIC['ilLog']->write(__METHOD__ . ': import mob from dir: ' . $importfile);

                $media_object = ilObjMediaObject::_saveTempFileAsMediaObject(basename($importfile), $importfile, false);
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
                    switch ($order['field']) {
                        case 'varequal':
                            $equals = $conditionvar->varequal[$order["index"]]->getContent();
                            $gapident = $conditionvar->varequal[$order["index"]]->getRespident();
                            $id = $this->getIdFromGapIdent($gapident);
                            if (!isset($answers[$id]) || !in_array($equals, $answers[$id])) {
                                $answers[$id][] = $equals;
                            }
                            break;
                    }
                }
                foreach ($respcondition->setvar as $setvar) {
                    if ($gapident !== '') {
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
                foreach ($respcondition->displayfeedback as $feedbackpointer) {
                    if (strlen($feedbackpointer->getLinkrefid())) {
                        foreach ($item->itemfeedback as $ifb) {
                            if ($ifb->getIdent() === 'response_allcorrect') {
                                foreach ($ifb->material as $material) {
                                    $feedbacksgeneric[1] = $material;
                                }
                                foreach ($ifb->flow_mat as $fmat) {
                                    if (count($fmat->material)) {
                                        foreach ($fmat->material as $material) {
                                            $feedbacksgeneric[1] = $material;
                                        }
                                    }
                                }
                            } elseif ($ifb->getIdent() === 'response_onenotcorrect') {
                                foreach ($ifb->material as $material) {
                                    $feedbacksgeneric[0] = $material;
                                }
                                foreach ($ifb->flow_mat as $fmat) {
                                    if (count($fmat->material)) {
                                        foreach ($fmat->material as $material) {
                                            $feedbacksgeneric[0] = $material;
                                        }
                                    }
                                }
                            } else {
                                foreach ($ifb->material as $material) {
                                    $feedbacks[$ifb->getIdent()] = $material;
                                }
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

        $sum = 0;
        foreach ($correct_answers as $row) {
            $sum += $row[1];
        }
        $this->object->setAnswers($answers);
        // handle the import of media objects in XHTML code
        if (isset($feedbacks) && count($feedbacks) > 0) {
            foreach ($feedbacks as $ident => $material) {
                $m = $this->QTIMaterialToString($material);
                $feedbacks[$ident] = $m;
            }
        }
        if (isset($feedbacksgeneric) && is_array($feedbacksgeneric) && count($feedbacksgeneric) > 0) {
            foreach ($feedbacksgeneric as $correctness => $material) {
                $m = $this->QTIMaterialToString($material);
                $feedbacksgeneric[$correctness] = $m;
            }
        }

        $this->addGeneralMetadata($item);
        $this->object->setTitle($item->getTitle());
        $this->object->setNrOfTries((int) $item->getMaxattempts());
        $this->object->setComment($item->getComment());
        $this->object->setAuthor($item->getAuthor());
        $this->object->setOwner($user_id);
        $this->object->setObjId($questionpool_id);
        $this->object->setMinAutoComplete((int) ($item->getMetadataEntry('minAutoCompleteLength') ?? assLongMenu::MIN_LENGTH_AUTOCOMPLETE));
        $this->object->setIdenticalscoring((int) $item->getMetadataEntry('identical_scoring'));
        $this->object->setCorrectAnswers($correct_answers);
        $this->object->setPoints($sum);
        // additional content editing mode information
        $this->object->setAdditionalContentEditingMode(
            $this->fetchAdditionalContentEditingModeInformation($item)
        );
        $this->object->saveToDb();
        $this->importSuggestedSolutions($this->object->getId(), $item->suggested_solutions);
        if (isset($feedbacks) && count($feedbacks) > 0) {
            foreach ($feedbacks as $ident => $material) {
                $this->object->feedbackOBJ->importSpecificAnswerFeedback(
                    $this->object->getId(),
                    0,
                    $ident,
                    ilRTE::_replaceMediaObjectImageSrc($material, 1)
                );
            }
        }
        if (isset($feedbacksgeneric) && is_array($feedbacksgeneric) && count($feedbacksgeneric) > 0) {
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
            $question_id = $this->object->duplicate(true, "", "", -1, $tst_id);
            $tst_object->questions[$question_counter++] = $question_id;
            $import_mapping[$item->getIdent()] = ["pool" => $q_1_id, "test" => $question_id];
        } else {
            $import_mapping[$item->getIdent()] = ["pool" => $this->object->getId(), "test" => 0];
        }
        return $import_mapping;
    }

    private function getIdFromGapIdent($ident)
    {
        $id = preg_split('/_/', $ident);
        return $id[1] - 1;
    }
}

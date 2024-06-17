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
* Class for matching question imports
*
* assMatchingQuestionImport is a class for matching question imports
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup components\ILIASTestQuestionPool
*/
class assMatchingQuestionImport extends assQuestionImport
{
    public function saveImage($data, $filename): void
    {
        $image = base64_decode($data);
        $imagepath = $this->object->getImagePath();
        if (!file_exists($imagepath)) {
            ilFileUtils::makeDirParents($imagepath);
        }
        $imagepath .= $filename;
        $fh = fopen($imagepath, "wb");
        if ($fh == false) {
        } else {
            $imagefile = fwrite($fh, $image);
            fclose($fh);
        }
    }

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
        $shuffle = 0;
        $definitions = [];
        $terms = [];
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
                                $answerimage = [];
                                foreach ($response_label->material as $mat) {
                                    for ($m = 0; $m < $mat->getMaterialCount(); $m++) {
                                        $foundmat = $mat->getMaterial($m);
                                        if (strcmp($foundmat["type"], "mattext") == 0) {
                                            $answertext .= $foundmat["material"]->getContent();
                                        }
                                        if (strcmp($foundmat["type"], "matimage") == 0) {
                                            $foundimage = true;
                                            $answerimage = [
                                                "imagetype" => $foundmat["material"]->getImageType(),
                                                "label" => $foundmat["material"]->getLabel(),
                                                "content" => $foundmat["material"]->getContent()
                                            ];
                                        }
                                    }
                                }
                                if (($response_label->getMatchMax() == 1) && (strlen($response_label->getMatchGroup()))) {
                                    $definitions[$ident] = [
                                        "answertext" => $answertext,
                                        "answerimage" => $answerimage,
                                        "points" => 0,
                                        "answerorder" => $ident,
                                        "action" => ""
                                    ];
                                } else {
                                    $terms[$ident] = [
                                        "term" => $answertext,
                                        "answerimage" => $answerimage,
                                        "points" => 0,
                                        "ident" => $ident,
                                        "action" => ""
                                    ];
                                }
                            }
                            break;
                    }
                    break;
            }
        }
        $responses = [];
        $feedbacksgeneric = [];
        foreach ($item->resprocessing as $resprocessing) {
            foreach ($resprocessing->respcondition as $respcondition) {
                $subset = [];
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
                    array_push($responses, ["subset" => $subset, "action" => $setvar->getAction(), "points" => $setvar->getContent()]);
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

        $this->object->createNewQuestion();
        $this->addGeneralMetadata($item);
        $this->object->setTitle($item->getTitle());
        $this->object->setNrOfTries((int) $item->getMaxattempts());
        $this->object->setComment($item->getComment());
        $this->object->setAuthor($item->getAuthor());
        $this->object->setOwner($user_id);
        $this->object->setQuestion($this->QTIMaterialToString($item->getQuestiontext()));
        $this->object->setObjId($questionpool_id);
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
            // @PHP8-CR: If you look above, how $this->object->addDefinition does in fact take an object, I take this
            // issue as an indicator for a bigger issue and won't suppress / "quickfix" this but postpone further
            // analysis, eventually involving T&A TechSquad (see also remark in assMatchingQuestionGUI
            $this->object->addTerm(new assAnswerMatchingTerm($term["term"], $term['answerimage']['label'] ?? '', $term["ident"]));
        }
        foreach ($definitions as $definitionindex => $definition) {
            $this->object->addDefinition(new assAnswerMatchingDefinition($definition["answertext"], $definition['answerimage']['label'] ?? '', $definition["answerorder"]));
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
            $this->object->addMatchingPair(new assAnswerMatchingTerm('', '', (float) $term["ident"]), new assAnswerMatchingDefinition('', '', (int) $definition["answerorder"]), (float) $response['points']);
        }
        // additional content editing mode information
        $this->object->setAdditionalContentEditingMode(
            $this->fetchAdditionalContentEditingModeInformation($item)
        );
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
            $m = $this->QTIMaterialToString($material);
            $feedbacksgeneric[$correctness] = $m;
        }

        $feedbacks = $this->getFeedbackAnswerSpecific($item, 'correct_');

        // handle the import of media objects in XHTML code
        $questiontext = $this->object->getQuestion();
        if (is_array(ilSession::get("import_mob_xhtml"))) {
            foreach (ilSession::get("import_mob_xhtml") as $mob) {
                $importfile = $importdirectory . DIRECTORY_SEPARATOR . $mob["uri"];

                global $DIC; /* @var ILIAS\DI\Container $DIC */
                $DIC['ilLog']->write(__METHOD__ . ': import mob from dir: ' . $importfile);

                $media_object = ilObjMediaObject::_saveTempFileAsMediaObject(basename($importfile), $importfile, false);
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
            $question_id = $this->object->duplicate(true, "", "", -1, $tst_id);
            $tst_object->questions[$question_counter++] = $question_id;
            $import_mapping[$item->getIdent()] = ["pool" => $q_1_id, "test" => $question_id];
        } else {
            $import_mapping[$item->getIdent()] = ["pool" => $this->object->getId(), "test" => 0];
        }
        return $import_mapping;
    }

    /**
     * @param $feedbackIdent
     * @param string $prefix
     * @return int
     */
    protected function fetchIndexFromFeedbackIdent($feedbackIdent, $prefix = 'response_'): int
    {
        list($termId, $definitionId) = explode('_', str_replace($prefix, '', $feedbackIdent));

        foreach ($this->object->getMatchingPairs() as $index => $pair) {
            /* @var assAnswerMatchingPair $pair */

            if ($pair->getTerm()->getIdentifier() != $termId) {
                continue;
            }

            if ($pair->getDefinition()->getIdentifier() != $definitionId) {
                continue;
            }

            return (int) $index;
        }

        return -1;
    }
}

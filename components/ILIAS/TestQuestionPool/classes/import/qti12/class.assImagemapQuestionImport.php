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
* Class for imagemap question imports
*
* assImagemapQuestionImport is a class for imagemap question imports
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup components\ILIASTestQuestionPool
*/
class assImagemapQuestionImport extends assQuestionImport
{
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
        $questionimage = [];
        $answers = [];
        foreach ($presentation->order as $entry) {
            switch ($entry["type"]) {
                case "response":
                    $response = $presentation->response[$entry["index"]];
                    $rendertype = $response->getRenderType();
                    switch (strtolower(get_class($rendertype))) {
                        case "ilqtirenderhotspot":
                            foreach ($rendertype->material as $mat) {
                                for ($i = 0; $i < $mat->getMaterialCount(); $i++) {
                                    $m = $mat->getMaterial($i);
                                    if (strcmp($m["type"], "matimage") == 0) {
                                        $questionimage = [
                                            "imagetype" => $m["material"]->getImageType(),
                                            "label" => $m["material"]->getLabel(),
                                            "content" => $m["material"]->getContent()
                                        ];
                                    }
                                }
                            }
                            foreach ($rendertype->response_labels as $response_label) {
                                $ident = $response_label->getIdent();
                                $answerhint = "";
                                foreach ($response_label->material as $mat) {
                                    $answerhint .= $this->QTIMaterialToString($mat);
                                }
                                $answers[$ident] = [
                                    "answerhint" => $answerhint,
                                    "areatype" => $response_label->getRarea(),
                                    "coordinates" => $response_label->getContent(),
                                    "points" => 0,
                                    "answerorder" => $response_label->getIdent(),
                                    "correctness" => "1",
                                    "action" => "",
                                    "points_unchecked" => 0
                                ];
                            }
                            break;
                    }
                    break;
            }
        }
        $responses = [];
        $feedbacks = [];
        $feedbacksgeneric = [];
        foreach ($item->resprocessing as $resprocessing) {
            foreach ($resprocessing->respcondition as $respcondition) {
                $coordinates = "";
                $correctness = 1;
                $conditionvar = $respcondition->getConditionvar();
                foreach ($conditionvar->order as $order) {
                    switch ($order["field"]) {
                        case "arr_not":
                            $correctness = 0;
                            break;
                        case "varinside":
                            $coordinates = $conditionvar->varinside[$order["index"]]->getContent();
                            break;
                        case "varequal":
                            $coordinates = $conditionvar->varequal[$order["index"]]->getContent();
                            break;
                    }
                }
                foreach ($respcondition->setvar as $setvar) {
                    foreach ($answers as $ident => $answer) {
                        if (strcmp($answer["coordinates"], $coordinates) == 0) {
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
        }

        $this->addGeneralMetadata($item);
        $this->object->setTitle($item->getTitle());
        $this->object->setNrOfTries((int) $item->getMaxattempts());
        $this->object->setComment($item->getComment());
        $this->object->setAuthor($item->getAuthor());
        $this->object->setOwner($user_id);
        $this->object->setQuestion($this->QTIMaterialToString($item->getQuestiontext()));
        $this->object->setObjId($questionpool_id);
        $this->object->setIsMultipleChoice($item->getMetadataEntry("IS_MULTIPLE_CHOICE"));
        $areas = ["2" => "rect", "1" => "circle", "3" => "poly"];
        $this->object->setImageFilename($questionimage["label"]);
        foreach ($answers as $answer) {
            $this->object->addAnswer($answer["answerhint"], $answer["points"], $answer["answerorder"], $answer["coordinates"], $areas[$answer["areatype"]], $answer["points_unchecked"]);
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
        $image = base64_decode($questionimage["content"]);
        $imagepath = $this->object->getImagePath();
        if (!file_exists($imagepath)) {
            ilFileUtils::makeDirParents($imagepath);
        }
        $imagepath .= $questionimage["label"];
        $fh = fopen($imagepath, "wb");
        if ($fh == true) {
            $imagefile = fwrite($fh, $image);
            fclose($fh);
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
}

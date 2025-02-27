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
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package components\ILIAS/Test
 */
class assKprimChoiceImport extends assQuestionImport
{
    /**
     * @var assKprimChoice
     */
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

        $shuffle = 0;
        $answers = [];

        $presentation = $item->getPresentation();
        foreach ($presentation->order as $entry) {
            if ($entry['type'] !== 'response') {
                continue;
            }

            $response = $presentation->response[$entry['index']];
            $rendertype = $response->getRenderType();
            if (strtolower(get_class($response->getRenderType())) !== 'ilqtirenderchoice') {
                continue;
            }

            $shuffle = $rendertype->getShuffle();
            $foundimage = false;
            foreach ($rendertype->response_labels as $response_label) {
                $ident = $response_label->getIdent();
                $answertext = '';
                $answerimage = [];
                foreach ($response_label->material as $mat) {
                    $embedded = false;
                    for ($m = 0; $m < $mat->getMaterialCount(); $m++) {
                        $foundmat = $mat->getMaterial($m);
                        if ($foundmat['type'] === 'matimage'
                            && $foundmat['material']->getEmbedded() !== '') {
                            $embedded = true;
                        }
                    }
                    if (!$embedded) {
                        $answertext = $this->QTIMaterialToString($mat);
                        continue;
                    }

                    for ($m = 0; $m < $mat->getMaterialCount(); $m++) {
                        $foundmat = $mat->getMaterial($m);
                        if ($foundmat['type'] === 'mattext') {
                            $answertext .= $foundmat['material']->getContent();
                        }
                        if ($foundmat['type'] === 'matimage') {
                            $foundimage = true;
                            $answerimage = [
                                'imagetype' => $foundmat['material']->getImageType(),
                                'label' => $foundmat['material']->getLabel(),
                                'content' => $foundmat['material']->getContent()
                            ];
                        }
                    }
                }

                $answers[$ident] = [
                    'answertext' => $answertext,
                    'imagefile' => $answerimage,
                    'answerorder' => $ident
                ];
            }
        }

        $feedbacks = [];
        $feedbacksgeneric = [];

        foreach ($item->resprocessing as $resprocessing) {
            foreach ($resprocessing->outcomes->decvar as $decvar) {
                if ($decvar->getVarname() == 'SCORE') {
                    $this->object->setPoints($decvar->getMaxvalue());
                    $this->object->setScorePartialSolutionEnabled(false);
                    if ($decvar->getMinvalue() > 0) {
                        $this->object->setScorePartialSolutionEnabled(true);
                    }
                }
            }

            foreach ($resprocessing->respcondition as $respcondition) {
                if ($respcondition->setvar === []) {
                    foreach ($respcondition->getConditionvar()->varequal as $varequal) {
                        $ident = $varequal->respident;
                        $answers[$ident]['correctness'] = (bool) $varequal->getContent();
                        break;
                    }

                    foreach ($respcondition->displayfeedback as $feedbackpointer) {
                        if ($feedbackpointer->getLinkrefid() === '') {
                            continue;
                        }
                        foreach ($item->itemfeedback as $ifb) {
                            if ($ifb->getIdent() !== $feedbackpointer->getLinkrefid()) {
                                continue;
                            }

                            foreach ($ifb->material as $material) {
                                $feedbacks[$ident] = $material;
                            }
                            foreach ($ifb->flow_mat as $fmat) {
                                foreach ($fmat->material as $material) {
                                    $feedbacks[$ident] = $material;
                                }
                            }
                        }
                    }

                    continue;
                }

                foreach ($respcondition->displayfeedback as $feedbackpointer) {
                    if ($feedbackpointer->getLinkrefid() === '') {
                        continue;
                    }

                    foreach ($item->itemfeedback as $ifb) {
                        if ($ifb->getIdent() === 'response_allcorrect') {
                            foreach ($ifb->material as $material) {
                                $feedbacksgeneric[1] = $material;
                            }
                            foreach ($ifb->flow_mat as $fmat) {
                                foreach ($fmat->material as $material) {
                                    $feedbacksgeneric[1] = $material;
                                }
                            }
                            continue;
                        }

                        if ($ifb->getIdent() === 'response_onenotcorrect') {
                            // found a feedback for the identifier
                            foreach ($ifb->material as $material) {
                                $feedbacksgeneric[0] = $material;
                            }
                            foreach ($ifb->flow_mat as $fmat) {
                                foreach ($fmat->material as $material) {
                                    $feedbacksgeneric[0] = $material;
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
        $this->object->setQuestion($this->QTIMaterialToString($item->getQuestiontext()));
        $this->object->setObjId($questionpool_id);
        $this->object->setShuffleAnswersEnabled($shuffle);
        $this->object->setAnswerType($item->getMetadataEntry('answer_type'));
        $this->object->setOptionLabel($item->getMetadataEntry('option_label_setting'));
        $this->object->setCustomTrueOptionLabel($item->getMetadataEntry('custom_true_option_label'));
        $this->object->setCustomFalseOptionLabel($item->getMetadataEntry('custom_false_option_label'));
        $this->object->setThumbSize(
            $this->deduceThumbSizeFromImportValue((int) $item->getMetadataEntry('thumb_size'))
        );

        $this->object->saveToDb();

        $answer_objects = [];
        foreach ($answers as $answer_data) {
            $answer = new ilAssKprimChoiceAnswer();
            $answer->setImageFsDir($this->object->getImagePath());
            $answer->setImageWebDir($this->object->getImagePathWeb());
            $answer->setPosition($answer_data['answerorder']);
            $answer->setAnswertext($answer_data['answertext']);
            $answer->setCorrectness($answer_data['correctness']);
            if (isset($answer_data['imagefile']['label'])) {
                $answer->setImageFile($answer_data['imagefile']['label']);
            }
            $answer_objects[] = $answer;
        }
        $this->object->setAnswers($answer_objects);
        // additional content editing mode information
        $this->object->setAdditionalContentEditingMode(
            $this->fetchAdditionalContentEditingModeInformation($item)
        );

        $this->object->saveToDb();

        foreach ($answers as $answer) {
            if (!is_array($answer['imagefile']) || $answer['imagefile'] === []) {
                continue;
            }
            $image = base64_decode($answer['imagefile']['content']);
            $imagepath = $this->object->getImagePath();
            if (!file_exists($imagepath)) {
                ilFileUtils::makeDirParents($imagepath);
            }
            $imagepath .= $answer['imagefile']['label'];
            if ($fh = fopen($imagepath, 'wb')) {
                $imagefile = fwrite($fh, $image);
                fclose($fh);
                $this->object->generateThumbForFile(
                    $answer['imagefile']['label'],
                    $this->object->getImagePath(),
                    $this->object->getThumbSize()
                );
            }
        }

        $feedback_setting = $item->getMetadataEntry('feedback_setting');
        if (!is_null($feedback_setting)) {
            $this->object->feedbackOBJ->saveSpecificFeedbackSetting($this->object->getId(), $feedback_setting);
            $this->object->setSpecificFeedbackSetting($feedback_setting);
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
        $answers = $this->object->getAnswers();
        if (is_array(ilSession::get("import_mob_xhtml"))) {
            foreach (ilSession::get("import_mob_xhtml") as $mob) {
                $importfile = $importdirectory . DIRECTORY_SEPARATOR . $mob["uri"];

                global $DIC; /* @var ILIAS\DI\Container $DIC */
                $DIC['ilLog']->write(__METHOD__ . ': import mob from dir: ' . $importfile);

                $media_object = ilObjMediaObject::_saveTempFileAsMediaObject(basename($importfile), $importfile, false);
                ilObjMediaObject::_saveUsage($media_object->getId(), "qpl:html", $this->object->getId());
                $questiontext = str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $questiontext);
                foreach ($answers as $answer_obj) {
                    if ($answer_obj->getAnswertext() === null) {
                        continue;
                    }
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
        foreach ($answers as $answer_obj) {
            if ($answer_obj->getAnswertext() === null) {
                continue;
            }
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
        $this->importSuggestedSolutions($this->object->getId(), $item->suggested_solutions);
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

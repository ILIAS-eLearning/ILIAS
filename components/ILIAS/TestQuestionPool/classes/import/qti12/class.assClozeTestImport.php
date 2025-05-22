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
    private ilDBInterface $db;

    public function __construct($a_object)
    {
        /** @var ILIAS\DI\Container $DIC */
        global $DIC;
        $this->db = $DIC['ilDB'];

        parent::__construct($a_object);
    }

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
            $item->getMetadataEntry('question') ?? '&nbsp;',
            $item->getIliasSourceNic()
        );

        $clozetext_array = [];
        $shuffle = 0;
        $gaps = [];
        foreach ($presentation->order as $entry) {
            switch ($entry['type']) {
                case 'material':

                    $material_string = $this->QTIMaterialToString(
                        $presentation->material[$entry['index']]
                    );

                    if ($questiontext === '&nbsp;') {
                        /**
                         * 2024-11-06, sk: This is needed because the question-text
                         * is actually saved as the first entry in the material-
                         * node.
                         */
                        $questiontext = $material_string;
                    } else {
                        array_push($clozetext_array, $material_string);
                    }

                    break;
                case 'response':
                    $response = $presentation->response[$entry['index']];
                    $rendertype = $response->getRenderType();
                    array_push($clozetext_array, '<<' . $response->getIdent() . '>>');

                    switch (strtolower(get_class($response->getRenderType()))) {
                        case 'ilqtirenderfib':
                            switch ($response->getRenderType()->getFibtype()) {
                                case ilQTIRenderFib::FIBTYPE_DECIMAL:
                                case ilQTIRenderFib::FIBTYPE_INTEGER:
                                    array_push(
                                        $gaps,
                                        [
                                            'ident' => $response->getIdent(),
                                            'type' => assClozeGap::TYPE_NUMERIC,
                                            'answers' => [],
                                            'minnumber' => $response->getRenderType()->getMinnumber(),
                                            'maxnumber' => $response->getRenderType()->getMaxnumber(),
                                            'gap_size' => $response->getRenderType()->getMaxchars()
                                        ]
                                    );
                                    break;
                                default:
                                case ilQTIRenderFib::FIBTYPE_STRING:
                                    array_push(
                                        $gaps,
                                        [
                                            'ident' => $response->getIdent(),
                                            'type' => assClozeGap::TYPE_TEXT,
                                            'answers' => [],
                                            'gap_size' => $response->getRenderType()->getMaxchars()
                                        ]
                                    );
                                    break;
                            }
                            break;
                        case 'ilqtirenderchoice':
                            $answers = [];
                            $shuffle = $rendertype->getShuffle();
                            $answerorder = 0;
                            foreach ($rendertype->response_labels as $response_label) {
                                $ident = $response_label->getIdent();
                                $answertext = '';
                                foreach ($response_label->material as $mat) {
                                    $answertext .= $this->QTIMaterialToString($mat);
                                }
                                $answers[$ident] = [
                                    'answertext' => $answertext,
                                    'points' => 0,
                                    'answerorder' => $answerorder++,
                                    'action' => '',
                                    'shuffle' => $rendertype->getShuffle()
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
                $ident = '';
                $correctness = 1;
                $conditionvar = $respcondition->getConditionvar();
                $equals = '';
                $gapident = '';
                foreach ($conditionvar->order as $order) {
                    if ($order['field'] === 'varequal') {
                        $equals = $conditionvar->varequal[$order['index']]->getContent();
                        $gapident = $conditionvar->varequal[$order['index']]->getRespident();
                    }
                }
                if ($gapident === '') {
                    continue;
                }
                foreach ($respcondition->setvar as $setvar) {
                    foreach ($gaps as $gi => $g) {
                        if ($g['ident'] !== $gapident) {
                            continue;
                        }
                        switch ($g['type']) {
                            case assClozeGap::TYPE_SELECT:
                                foreach ($gaps[$gi]['answers'] as $ai => $answer) {
                                    if ($answer['answertext'] === $equals) {
                                        $gaps[$gi]['answers'][$ai]['action'] = $setvar->getAction();
                                        $gaps[$gi]['answers'][$ai]['points'] = $setvar->getContent();
                                    }
                                }
                                break;
                            case assClozeGap::TYPE_TEXT:
                            case assClozeGap::TYPE_NUMERIC:
                                $gaps[$gi]['answers'][] = [
                                    'answertext' => $equals,
                                    'points' => $setvar->getContent(),
                                    'answerorder' => count($gaps[$gi]['answers']),
                                    'action' => $setvar->getAction()

                                ];
                                break;
                        }
                    }
                }

                if ($respcondition->displayfeedback === []) {
                    continue;
                }

                foreach ($respcondition->displayfeedback as $feedbackpointer) {
                    if ($feedbackpointer->getLinkrefid() === '') {
                        continue;
                    }
                    foreach ($item->itemfeedback as $ifb) {
                        switch ($ifb->getIdent()) {
                            case 'response_allcorrect':
                                foreach ($ifb->material as $material) {
                                    $feedbacksgeneric[1] = $material;
                                }
                                foreach ($ifb->flow_mat as $fmat) {
                                    foreach ($fmat->material as $material) {
                                        $feedbacksgeneric[1] = $material;
                                    }
                                }
                                break;
                            case 'response_onenotcorrect':
                                foreach ($ifb->material as $material) {
                                    $feedbacksgeneric[0] = $material;
                                }
                                foreach ($ifb->flow_mat as $fmat) {
                                    foreach ($fmat->material as $material) {
                                        $feedbacksgeneric[0] = $material;
                                    }
                                }
                                break;
                            default:
                                foreach ($ifb->material as $material) {
                                    $feedbacks[$ifb->getIdent()] = $material;
                                }
                                foreach ($ifb->flow_mat as $fmat) {
                                    foreach ($fmat->material as $material) {
                                        $feedbacks[$ifb->getIdent()] = $material;
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
        $textgap_rating = $item->getMetadataEntry('textgaprating') ?? '';
        $this->object->setFixedTextLength((int) $item->getMetadataEntry('fixedTextLength'));
        $this->object->setIdenticalScoring((bool) $item->getMetadataEntry('identicalScoring'));
        $this->object->setFeedbackMode(
            ($item->getMetadataEntry('feedback_mode') ?? '') !== '' ?
                $item->getMetadataEntry('feedback_mode') : ilAssClozeTestFeedback::FB_MODE_GAP_QUESTION
        );
        $combinations = json_decode(base64_decode($item->getMetadataEntry('combinations') ?? ''));
        if ($textgap_rating === '') {
            $textgap_rating = 'ci';
        }
        $this->object->setTextgapRating($textgap_rating);
        $gaptext = [];
        foreach ($gaps as $gapidx => $gap) {
            $gapcontent = [];
            $clozegap = new assClozeGap($gap['type']);
            foreach ($gap['answers'] as $answer) {
                $gapanswer = new assAnswerCloze($answer['answertext'], $answer['points'], $answer['answerorder']);
                $gapanswer->setGapSize((int) ($gap['gap_size'] ?? 0));
                switch ($clozegap->getType()) {
                    case assClozeGap::TYPE_SELECT:
                        $clozegap->setShuffle($answer['shuffle']);
                        break;
                    case assClozeGap::TYPE_NUMERIC:
                        $gapanswer->setLowerBound($gap['minnumber']);
                        $gapanswer->setUpperBound($gap['maxnumber']);
                        break;
                }
                $clozegap->setGapSize((int) ($gap['gap_size'] ?? 0));
                $clozegap->addItem($gapanswer);
                array_push($gapcontent, $answer['answertext']);
            }
            $this->object->addGapAtIndex($clozegap, $gapidx);
            $gaptext[$gap['ident']] = '[gap]' . join(',', $gapcontent) . '[/gap]';
        }

        $this->object->setQuestion($questiontext);
        $clozetext = join('', $clozetext_array);

        foreach ($gaptext as $idx => $val) {
            $clozetext = str_replace('<<' . $idx . '>>', $val, $clozetext);
        }
        $this->object->setClozeTextValue($clozetext);

        // additional content editing mode information
        $this->object->setAdditionalContentEditingMode(
            $this->fetchAdditionalContentEditingModeInformation($item)
        );
        $this->object->saveToDb();

        if (is_array($combinations) && count($combinations) > 0) {
            $gap_combinations = new assClozeGapCombination($this->db);
            $gap_combinations->clearGapCombinationsFromDb($this->object->getId());
            $gap_combinations->importGapCombinationToDb($this->object->getId(), $combinations);
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
        if (is_array(ilSession::get('import_mob_xhtml'))) {
            foreach (ilSession::get('import_mob_xhtml') as $mob) {
                $importfile = $importdirectory . DIRECTORY_SEPARATOR . $mob['uri'];
                global $DIC;
                $DIC['ilLog']->write(__METHOD__ . ': import mob from dir: ' . $importfile);

                $media_object = ilObjMediaObject::_saveTempFileAsMediaObject(basename($importfile), $importfile, false);
                $questiontext = str_replace('src="' . $mob['mob'] . '"', 'src="' . 'il_' . IL_INST_ID . '_mob_' . $media_object->getId() . '"', $questiontext);
                $clozetext = str_replace('src="' . $mob['mob'] . '"', 'src="' . 'il_' . IL_INST_ID . '_mob_' . $media_object->getId() . '"', $clozetext);
                foreach ($feedbacks as $ident => $material) {
                    $feedbacks[$ident] = str_replace('src="' . $mob['mob'] . '"', 'src="' . 'il_' . IL_INST_ID . '_mob_' . $media_object->getId() . '"', $material);
                }
                foreach ($feedbacksgeneric as $correctness => $material) {
                    $feedbacksgeneric[$correctness] = str_replace('src="' . $mob['mob'] . '"', 'src="' . 'il_' . IL_INST_ID . '_mob_' . $media_object->getId() . '"', $material);
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

        $this->importSuggestedSolutions($this->object->getId(), $item->suggested_solutions);
        $import_mapping[$item->getIdent()] = $this->addQuestionToParentObjectAndBuildMappingEntry(
            $questionpool_id,
            $tst_id,
            $question_counter,
            $tst_object
        );
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

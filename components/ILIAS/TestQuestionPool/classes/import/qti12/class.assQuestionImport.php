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

use ILIAS\TestQuestionPool\Questions\SuggestedSolution\SuggestedSolution;
use ILIAS\TestQuestionPool\Questions\SuggestedSolution\SuggestedSolutionsDatabaseRepository;
use ILIAS\TestQuestionPool\QuestionPoolDIC;

/**
* Class for question imports
*
* assQuestionImport is a basis class question imports
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup components\ILIASTestQuestionPool
*/
class assQuestionImport
{
    /**
    * @var assQuestion
    */
    public $object;

    /**
    * assQuestionImport constructor
    *
    * @param object $a_object The question object
    * @access public
    */
    public function __construct($a_object)
    {
        $this->object = $a_object;
    }

    public function getQuestionId(): int
    {
        return (int) $this->object->getId();
    }

    public function getFeedbackGeneric($item): array
    {
        $feedbacksgeneric = [];
        foreach ($item->resprocessing as $resprocessing) {
            foreach ($resprocessing->respcondition as $respcondition) {
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
        // handle the import of media objects in XHTML code
        foreach ($feedbacksgeneric as $correctness => $material) {
            $m = $this->QTIMaterialToString($material);
            $feedbacksgeneric[$correctness] = $m;
        }
        return $feedbacksgeneric;
    }

    /**
     * @param $feedbackIdent
     * @param string $prefix
     * @return int
     */
    protected function fetchIndexFromFeedbackIdent($feedbackIdent, $prefix = 'response_'): int
    {
        return (int) str_replace($prefix, '', $feedbackIdent);
    }

    /**
     * @param ilQTIItem $item
     * @param string $prefix
     * @return array
     */
    protected function getFeedbackAnswerSpecific(ilQTIItem $item, $prefix = 'response_'): array
    {
        $feedbacks = [];

        foreach ($item->itemfeedback as $ifb) {
            if ($ifb->getIdent() == 'response_allcorrect' || $ifb->getIdent() == 'response_onenotcorrect') {
                continue;
            }

            if ($ifb->getIdent() == $prefix . 'allcorrect' || $ifb->getIdent() == $prefix . 'onenotcorrect') {
                continue;
            }

            if (substr($ifb->getIdent(), 0, strlen($prefix)) != $prefix) {
                continue;
            }

            $ident = $ifb->getIdent();

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

        foreach ($feedbacks as $ident => $material) {
            $m = $this->QTIMaterialToString($material);
            $feedbacks[$ident] = $m;
        }

        return $feedbacks;
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
        return [];
    }

    /**
     * @param ilQTIItem $item
     */
    protected function addGeneralMetadata(ilQTIItem $item): void
    {
        if ($item->getMetadataEntry('externalID')) {
            $this->object->setExternalId($item->getMetadataEntry('externalID'));
        } else {
            $this->object->setExternalId($item->getMetadataEntry('externalId'));
        }

        $this->object->setLifecycle($this->fetchLifecycle($item));
    }

    /**
     * @param ilQTIItem $item
     * @return ilAssQuestionLifecycle
     */
    protected function fetchLifecycle(ilQTIItem $item): ilAssQuestionLifecycle
    {
        try {
            $lifecycle = ilAssQuestionLifecycle::getInstance(
                $item->getMetadataEntry('ilias_lifecycle')
            );
        } catch (ilTestQuestionPoolInvalidArgumentException $e) {
            try {
                $lomLifecycle = new ilAssQuestionLomLifecycle(
                    $item->getMetadataEntry('lifecycle')
                );

                $lifecycle = ilAssQuestionLifecycle::getInstance(
                    $lomLifecycle->getMappedIliasLifecycleIdentifer()
                );
            } catch (ilTestQuestionPoolInvalidArgumentException $e) {
                $lifecycle = ilAssQuestionLifecycle::getDraftInstance();
            }
        }

        return $lifecycle;
    }

    protected function processNonAbstractedImageReferences($text, $sourceNic): string
    {
        $reg = '/<img.*src=".*\\/mm_(\\d+)\\/(.*?)".*>/m';
        $matches = null;

        if (preg_match_all($reg, $text, $matches)) {
            $mobs = [];
            for ($i = 0, $max = count($matches[1]); $i < $max; $i++) {
                $mobSrcId = $matches[1][$i];
                $mobSrcName = $matches[2][$i];
                $mobSrcLabel = 'il_' . $sourceNic . '_mob_' . $mobSrcId;
                $mobs[] = [
                    "mob" => $mobSrcLabel, "uri" => 'objects/' . $mobSrcLabel . '/' . $mobSrcName
                ];
            }
            ilSession::set('import_mob_xhtml', $mobs);
        }

        return ilRTE::_replaceMediaObjectImageSrc($text, 0, $sourceNic);
    }

    /**
     * fetches the "additional content editing mode" information from qti item
     * and falls back to ADDITIONAL_CONTENT_EDITING_MODE_RTE when no or invalid information is given
     *
     * @final
     * @access protected
     * @return string $additionalContentEditingMode
     */
    final protected function fetchAdditionalContentEditingModeInformation($qtiItem): string
    {
        $additionalContentEditingMode = $qtiItem->getMetadataEntry('additional_cont_edit_mode');

        if (!$this->object->isValidAdditionalContentEditingMode($additionalContentEditingMode ?? '')) {
            $additionalContentEditingMode = assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_RTE;
        }

        return $additionalContentEditingMode;
    }

    public function importSuggestedSolution(
        int $question_id,
        string $value = "",
        int $subquestion_index = 0
    ): void {
        $type = $this->findSolutionTypeByValue($value);
        if (!$type) {
            return;
        }

        $repo = $this->getSuggestedSolutionsRepo();

        $nu_value = $this->object->resolveInternalLink($value);
        $solution = $repo->create($question_id, $type)
            ->withInternalLink($nu_value)
            ->withImportId($value);
        $repo->update([$solution]);
    }

    protected function findSolutionTypeByValue(string $value): ?string
    {
        foreach (array_keys(SuggestedSolution::TYPES) as $type) {
            $search_type = '_' . $type . '_';
            if (strpos($value, $search_type) !== false) {
                return $type;
            }
        }
        return null;
    }


    protected ?SuggestedSolutionsDatabaseRepository $suggestedsolution_repo = null;
    protected function getSuggestedSolutionsRepo(): SuggestedSolutionsDatabaseRepository
    {
        if (is_null($this->suggestedsolution_repo)) {
            $dic = QuestionPoolDIC::dic();
            $this->suggestedsolution_repo = $dic['question.repo.suggestedsolutions'];
        }
        return $this->suggestedsolution_repo;
    }

    /**
     * Reads an QTI material tag and creates a text or XHTML string
     * @return string text or xhtml string
     */
    public function QTIMaterialToString(ilQTIMaterial $a_material): string
    {
        $result = "";
        $mobs = [];
        for ($i = 0; $i < $a_material->getMaterialCount(); $i++) {
            $material = $a_material->getMaterial($i);
            if (strcmp($material["type"], "mattext") === 0) {
                $result .= $material["material"]->getContent();
            }
            if (strcmp($material["type"], "matimage") === 0) {
                $matimage = $material["material"];
                if (preg_match("/(il_([0-9]+)_mob_([0-9]+))/", $matimage->getLabel(), $matches)) {
                    $mobs[] = ["mob" => $matimage->getLabel(),
                                    "uri" => $matimage->getUri()
                    ];
                }
            }
        }
        ilSession::set('import_mob_xhtml', $mobs);
        return $result;
    }

}

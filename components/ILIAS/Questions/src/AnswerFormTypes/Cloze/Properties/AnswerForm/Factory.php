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

declare(strict_types=1);

namespace ILIAS\Questions\AnswerFormTypes\Cloze\Properties\AnswerForm;

use ILIAS\Questions\AnswerForm\TypeGenericProperties;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\ClozeText\Factory as ClozeTextFactory;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\ClozeText\Text as ClozeText;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Definitions\ScoringIdentical;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Gaps\Factory as GapsFactory;

class Factory
{
    public function __construct(
        private readonly ClozeTextFactory $cloze_text_factory,
        private readonly GapsFactory $gaps_factory
    ) {
    }

    public function fromData(
        TypeGenericProperties $type_generic_properties,
        array $type_specific_data
    ): Properties {
        if ($type_specific_data === []) {
            return new Properties(
                $type_generic_properties->getAnswerFormId(),
                $type_generic_properties->getQuestionId(),
                $this->cloze_text_factory->buildFromTextString(
                    $type_generic_properties->getAdditionalText()
                ),
                $type_generic_properties->getAdditionalTextLegacy(),
                $this->gaps_factory->getEmptyGapsObject()
            );
        }

        return new Properties(
            $type_generic_properties->getAnswerFormId(),
            $type_generic_properties->getQuestionId(),
            $this->cloze_text_factory->buildFromTextString(
                $type_generic_properties->getAdditionalText()
            ),
            $type_generic_properties->getAdditionalTextLegacy(),
            $type_specific_data['gaps'],
            $type_specific_data['identical_scoring'],
            $type_specific_data['combinations_enabled']
        );
    }

    public function fromForm(
        Properties $properties,
        ClozeText $cloze_text,
        ScoringIdentical $scoring_of_identical_responses,
        bool $combinations_enabled
    ): Properties {
        $updated_gaps = $cloze_text->updateGapsFromMarkdown($properties->getGaps());
        return $properties
            ->withClozeText(
                $cloze_text->withIdsOfNewGapsInClozeText($updated_gaps->getUndefinedGaps())
            )->withGaps($updated_gaps)
            ->withScoringOfIdenticalResponses($scoring_of_identical_responses)
            ->withCombinationsEnabled($combinations_enabled);
    }
}

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
use ILIAS\Questions\AnswerFormTypes\Cloze\Definition;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\ClozeText\Factory as ClozeTextFactory;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\ClozeText\Text as ClozeText;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Definitions\ScoringIdentical;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Gaps\Factory as GapsFactory;
use ILIAS\Questions\Persistence\Query;
use ILIAS\Questions\Persistence\TableTypes;

class Factory
{
    public function __construct(
        private readonly ClozeTextFactory $cloze_text_factory,
        private readonly GapsFactory $gaps_factory
    ) {
    }

    public function fromData(
        TypeGenericProperties $type_generic_properties,
        ?Query $query
    ): Properties {
        if ($query === null) {
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

        [
            'scoring_identical_responses' => $scoring_identical_responses,
            'combinations_activated' => $combinations_activated
        ] = $query->retrieveCurrentRecord(
            TableTypes::TypeSpecificAnswerForms->getTable(
                $query->getTableNameBuilder(Definition::class)
            ),
            $query->getRefinery()->custom()->transformation(
                fn(array $vs): array => [
                    'scoring_identical_responses' => ScoringIdentical::tryFrom($vs[0]['scoring_identical_responses']),
                    'combinations_activated' => $vs[0]['combinations_activated'] === 1
                ]
            )
        );

        return new Properties(
            $type_generic_properties->getAnswerFormId(),
            $type_generic_properties->getQuestionId(),
            $this->cloze_text_factory->buildFromTextString(
                $type_generic_properties->getAdditionalText()
            ),
            $type_generic_properties->getAdditionalTextLegacy(),
            $this->gaps_factory->fromDatabase($query),
            $scoring_identical_responses,
            $combinations_activated
        );
    }

    public function fromForm(
        Properties $properties,
        ClozeText $cloze_text,
        ScoringIdentical $scoring_of_identical_responses,
        bool $combinations_enabled
    ): Properties {
        $updated_gaps = $cloze_text->updateGapsFromMarkdown(
            $properties->getAnswerFormId(),
            $properties->getGaps()
        );

        return $properties
            ->withClozeText(
                $cloze_text->withIdsOfNewGapsInClozeText($updated_gaps->getUndefinedGaps())
            )->withGaps($updated_gaps)
            ->withScoringOfIdenticalResponses($scoring_of_identical_responses)
            ->withCombinationsEnabled($combinations_enabled);
    }
}

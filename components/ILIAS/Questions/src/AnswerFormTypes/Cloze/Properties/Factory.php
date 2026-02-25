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

namespace ILIAS\Questions\AnswerFormTypes\Cloze\Properties;

use ILIAS\Questions\AnswerForm\Persistence\AnswerFormSpecificTableTypes;
use ILIAS\Questions\AnswerForm\TypeGenericProperties;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\ClozeText\Factory as ClozeTextFactory;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\ClozeText\Text as ClozeText;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Combinations\Factory as CombinationsFactory;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Definitions\ScoringIdentical;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Gaps\Factory as GapsFactory;
use ILIAS\Questions\Persistence\Factory as PersistenceFactory;
use ILIAS\Questions\Persistence\Query;

class Factory
{
    public function __construct(
        private readonly PersistenceFactory $persistence_factory,
        private readonly ClozeTextFactory $cloze_text_factory,
        private readonly GapsFactory $gaps_factory,
        private readonly CombinationsFactory $combinations_factory
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
                $type_generic_properties->getDefinition(),
                $this->cloze_text_factory->buildFromTextString(
                    $type_generic_properties->getAdditionalText()
                ),
                $type_generic_properties->getAdditionalTextLegacy(),
                ScoringIdentical::ScoreAll,
                $this->gaps_factory->getEmptyGapsObject(
                    $type_generic_properties->getAnswerFormId()
                ),
                $this->combinations_factory->getCombinations(
                    $type_generic_properties,
                    false
                )
            );
        }

        [
            'scoring_identical_responses' => $scoring_identical_responses,
            'combinations_enabled' => $combinations_enabled
        ] = $query->retrieveCurrentRecord(
            $this->persistence_factory->table(
                $query->getTableNameBuilder(
                    $type_generic_properties
                        ->getDefinition()
                        ->getTableDefinitions()
                        ->getTableSubNameSpace()
                ),
                AnswerFormSpecificTableTypes::TypeSpecificAnswerForms
            ),
            $query->getRefinery()->custom()->transformation(
                function (array $vs) use ($type_generic_properties): array {
                    $values = $this->retrieveFirstMatchingRowFromDBRecords(
                        $type_generic_properties->getAnswerFormId()->toString(),
                        $vs
                    );
                    return [
                        'scoring_identical_responses' => ScoringIdentical::tryFrom($values['scoring_identical_responses']),
                        'combinations_enabled' => $values['combinations_enabled'] === 1
                    ];
                }
            )
        );

        $gaps = $this->gaps_factory->fromDatabase(
            $this->persistence_factory,
            $type_generic_properties
                ->getDefinition()
                ->getTableDefinitions()
                ->getTableSubNameSpace(),
            $type_generic_properties->getAnswerFormId(),
            $query
        );

        return new Properties(
            $type_generic_properties->getAnswerFormId(),
            $type_generic_properties->getQuestionId(),
            $type_generic_properties->getDefinition(),
            $this->cloze_text_factory->buildFromTextString(
                $type_generic_properties->getAdditionalText()
            ),
            $type_generic_properties->getAdditionalTextLegacy(),
            $scoring_identical_responses,
            $gaps,
            $this->combinations_factory->getCombinations(
                $type_generic_properties,
                $combinations_enabled,
                $gaps,
                $query
            )
        );
    }

    public function fromBasicEditingForm(
        Properties $properties,
        ClozeText $cloze_text,
        ScoringIdentical $scoring_of_identical_responses,
        bool $combinations_enabled
    ): Properties {
        $updated_properties = $properties
            ->withScoringOfIdenticalResponses($scoring_of_identical_responses)
            ->withCombinations(
                $properties->getCombinations()->withCombinationsEnabled(
                    $combinations_enabled
                )
            );

        if ($updated_properties->getLegacyClozeText() !== ''
            && $cloze_text->getRawRepresentation() === '') {
            return $updated_properties;
        }

        $updated_gaps = $cloze_text->updateGapsFromMarkdown(
            $properties->getAnswerFormId(),
            $properties->getGaps()
        );

        return $updated_properties
            ->withClozeText(
                $cloze_text->withIdsOfNewGapsInClozeText(
                    $updated_gaps->getIncompleteGaps()
                )
            )->withGaps($updated_gaps);
    }

    public function fromCarry(
        Properties $properties,
        ?string $carry
    ): Properties {
        if ($carry === null
           || !is_array(
               ($carry_array = json_decode($carry, true))
           )) {
            return $properties;
        }

        return $properties->withValuesFromCarry(
            $this->cloze_text_factory,
            $carry_array
        );
    }

    private function retrieveFirstMatchingRowFromDBRecords(
        string $answer_form_id,
        array $vs
    ): ?array {
        foreach ($vs as $row) {
            if ($row['answer_form_id'] === $answer_form_id) {
                return $row;
            }
        }
        return null;
    }
}

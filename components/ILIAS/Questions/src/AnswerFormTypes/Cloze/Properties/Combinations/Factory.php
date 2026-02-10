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

namespace ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Combinations;

use ILIAS\Questions\AnswerForm\TypeGenericProperties;
use ILIAS\Questions\AnswerFormTypes\Cloze\Persistence;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Gaps\Gaps;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Properties;
use ILIAS\Questions\Persistence\Query;
use ILIAS\Questions\Persistence\TableTypes;
use ILIAS\Data\UUID\Factory as UuidFactory;
use ILIAS\Data\UUID\Uuid;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Custom\Transformation as CustomTransformation;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Field\Group;

class Factory
{
    public function __construct(
        private readonly UuidFactory $uuid_factory
    ) {
    }

    public function getCombinations(
        TypeGenericProperties $type_generic_properties,
        bool $combinations_enabled,
        ?Gaps $gaps = null,
        ?Query $query = null
    ): Combinations {
        return new Combinations(
            $this,
            $type_generic_properties->getAnswerFormId(),
            $combinations_enabled,
            !$combinations_enabled || $gaps === null || $query === null
                ? []
                : $this->retrieveMatchingValuesFromQuery(
                    $type_generic_properties,
                    $gaps,
                    $this->retrieveCombinationsFromQuery(
                        $type_generic_properties,
                        $query
                    ),
                    $query
                )
        );
    }

    /**
     * @param array<string> $gap_ids
     */
    public function buildNewCombination(
        Gaps $gaps,
        array $gap_ids,
    ): Combination {
        $combination_id = $this->uuid_factory->uuid4();
        return $this->buildCombination(
            $combination_id,
            null,
            array_map(
                fn(string $v): MatchingValue => new MatchingValue(
                    $combination_id,
                    $gaps->getGapById(
                        $this->uuid_factory->fromString($v)
                    )
                ),
                $gap_ids
            )
        );
    }

    /**
     * @param array<\ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Combinations\MatchingValue> $matching_values
     */
    public function buildCombination(
        Uuid $combination_id,
        ?float $points,
        array $matching_values
    ): Combination {
        return new Combination(
            $combination_id,
            $points,
            $matching_values
        );
    }

    /**
     * @param array<string, mixed> $values_array
     */
    public function buildMatchingValuesFromForm(
        Properties $properties,
        Uuid $combination_id,
        array $values_array
    ): array {
        return array_reduce(
            array_keys($values_array),
            function (array $c, string $v) use (
                $properties,
                $values_array,
                $combination_id
            ): array {
                $gap = $properties->getGaps()->getGapById(
                    $this->uuid_factory->fromString($v)
                );
                $answer_option =
                    $gap->getAnswerOptions()
                    ->getAnswerOptionById(
                        $this->uuid_factory->fromString($values_array[$v])
                    );

                if ($answer_option === null) {
                    return $c;
                }

                $c[] = new MatchingValue(
                    $combination_id,
                    $gap,
                    $answer_option,
                    null
                );
                return $c;
            },
            []
        );
    }

    public function buildCombinationFromCarryValue(
        string $carry,
        Properties $properties
    ): Combination {
        $values_array = json_decode($carry, true);
        $combination_id = $this->uuid_factory->fromString(
            array_key_first($values_array)
        );

        return new Combination(
            $combination_id,
            null,
            array_map(
                fn(string $v): MatchingValue => new MatchingValue(
                    $combination_id,
                    $properties->getGaps()->getGapById(
                        $this->uuid_factory->fromString($v)
                    )
                ),
                $values_array[$combination_id->toString()]
            )
        );
    }

    private function retrieveCombinationsFromQuery(
        TypeGenericProperties $type_generic_properties,
        Query $query
    ): array {
        return $query->retrieveCurrentRecord(
            TableTypes::Additional->getTable(
                $query->getPersistenceFactory(),
                $query->getTableNameBuilder(
                    $type_generic_properties->getDefinition()::class
                ),
                Persistence::COMBINATION_TABLE_IDENTIFIER
            ),
            $query->getRefinery()->custom()->transformation(
                fn(array $vs): array => $this->buildCombinationsFromQuery(
                    array_filter(
                        $vs,
                        fn(array $v): bool => $v['answer_form_id'] !== null
                    )
                )
            )
        );
    }

    private function buildCombinationsFromQuery(
        array $values
    ): array {
        if ($values === []) {
            return [];
        }

        return array_reduce(
            $values,
            function (array $c, array $v): array {
                if (array_key_exists($v['id'], $c)) {
                    return $c;
                }

                $c[$v['id']] = new Combination(
                    $this->uuid_factory->fromString($v['id']),
                    $v['points']
                );

                return $c;
            },
            []
        );
    }

    private function retrieveMatchingValuesFromQuery(
        TypeGenericProperties $type_generic_properties,
        Gaps $gaps,
        array $combinations,
        Query $query
    ): array {
        return $query->retrieveCurrentRecord(
            TableTypes::Additional->getTable(
                $query->getPersistenceFactory(),
                $query->getTableNameBuilder(
                    $type_generic_properties->getDefinition()::class
                ),
                Persistence::COMBINATION_TO_ANSWER_OPTIONS_TABLE_IDENTIFIER
            ),
            $query->getRefinery()->custom()->transformation(
                function (array $vs) use (
                    $gaps,
                    $combinations
                ): array {
                    $already_added = [];
                    foreach ($vs as $v) {
                        if (!array_key_exists($v['combination_id'], $combinations)
                            || in_array(
                                $v['combination_id'] . $v['gap_id'],
                                $already_added
                            )
                        ) {
                            continue;
                        }

                        $already_added[] = $v['combination_id'] . $v['gap_id'];

                        $gap = $gaps->getGapById(
                            $this->uuid_factory->fromString($v['gap_id'])
                        );

                        $combinations[$v['combination_id']] = $combinations[$v['combination_id']]
                            ->withAdditionalMatchingValue(
                                new MatchingValue(
                                    $this->uuid_factory->fromString($v['combination_id']),
                                    $gap,
                                    $gap->getAnswerOptions()
                                        ->getAnswerOptionById(
                                            $this->uuid_factory->fromString($v['answer_option_id'])
                                        )
                                )
                            );
                    }

                    return array_values($combinations);
                }
            )
        );
    }
}

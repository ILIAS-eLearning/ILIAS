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

use ILIAS\Questions\AnswerFormTypes\Cloze\Persistence;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Gaps\Gaps;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Properties;
use ILIAS\Questions\Persistence\Delete;
use ILIAS\Questions\Persistence\Manipulate;
use ILIAS\Questions\Persistence\Replace;
use ILIAS\Questions\Persistence\Table;
use ILIAS\Questions\Persistence\TableNameBuilder;
use ILIAS\Questions\Persistence\TableTypes;
use ILIAS\Questions\Persistence\Value;
use ILIAS\Questions\Persistence\Where;
use ILIAS\Data\UUID\Uuid;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Field\Group;
use ILIAS\UI\Component\Table\DataRow;
use ILIAS\UI\Component\Table\DataRowBuilder;

class Combination
{
    /**
     * @param Uuid $id
     * @param array<ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Combinations\MatchingValue> $matching_values
     */
    public function __construct(
        private readonly Uuid $id,
        private ?float $available_points,
        private array $matching_values = []
    ) {
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getAvailablePoints(): ?float
    {
        return $this->available_points;
    }

    public function withAdditionalMatchingValue(
        MatchingValue $matching_value
    ): self {
        $clone = clone $this;
        $clone->matching_values[] = $matching_value;
        return $clone;
    }

    public function getValuePresentation(
        Language $lng
    ): string {
        return implode(
            '<br>',
            array_map(
                fn(MatchingValue $v): string => $v->buildPresentationString($lng),
                $this->matching_values
            )
        );
    }

    public function containsAnswerOptionsExactly(
        array $vs
    ): bool {
        return array_diff(
            $vs,
            array_map(
                fn(MatchingValue $v): string => $v->getAnswerOption()->getAnswerOptionId()->toString(),
                $this->matching_values
            )
        ) === [];
    }

    public function toStorage(
        Uuid $answer_form_id,
        Persistence $persistence,
        TableNameBuilder $table_name_builder,
        Manipulate $manipulate
    ): Manipulate {
        if ($this->matching_values === []) {
            throw new \UnexpectedValueException(
                'A Combination without MatchingValues cannot be stored.'
            );
        }

        return array_reduce(
            $this->matching_values,
            fn(Manipulate $c, MatchingValue $v): Manipulate => $c->withAdditionalStatement(
                $v->toStorage(
                    $persistence,
                    $table_name_builder
                )
            ),
            $manipulate->withAdditionalStatement(
                $this->buildReplace(
                    $answer_form_id,
                    $persistence,
                    $table_name_builder
                )
            )
        );
    }

    public function toDelete(
        Persistence $persistence,
        TableNameBuilder $table_name_builder,
        Manipulate $manipulate
    ): Manipulate {
        return $manipulate->withAdditionalStatement(
            $this->buildDelete($persistence, $table_name_builder)
        )->withAdditionalStatement(
            $this->buildDeleteForLinkedValues(
                $persistence,
                $table_name_builder
            )
        );
    }

    private function buildReplace(
        Uuid $answer_form_id,
        Persistence $persistence,
        TableNameBuilder $table_name_builder
    ): Replace {
        $table_definition = TableTypes::Additional;
        return new Replace(
            $persistence->getColumns(
                $table_name_builder,
                $table_definition,
                $persistence->getCombinationsTableIdentifier()
            ),
            [
                new Value(\ilDBConstants::T_TEXT, $this->id->toString()),
                new Value(\ilDBConstants::T_TEXT, $answer_form_id->toString()),
                new Value(\ilDBConstants::T_FLOAT, $this->available_points)
            ]
        );
    }

    private function buildDelete(
        Persistence $persistence,
        TableNameBuilder $table_name_builder
    ): Delete {
        $table_definition = TableTypes::Additional;
        return new Delete(
            new Table(
                $table_definition,
                $table_name_builder,
                $persistence->getCombinationsTableIdentifier()
            ),
            [
                new Where(
                    $persistence->getIdColumn(
                        $table_name_builder,
                        $table_definition,
                        $persistence->getCombinationsTableIdentifier()
                    ),
                    new Value(\ilDBConstants::T_TEXT, $this->id->toString())
                )
            ]
        );
    }

    private function buildDeleteForLinkedValues(
        Persistence $persistence,
        TableNameBuilder $table_name_builder
    ): Delete {
        $table_definition = TableTypes::Additional;
        return new Delete(
            new Table(
                $table_definition,
                $table_name_builder,
                $persistence->getCombinationToAnswerOptionsTableIdentifier()
            ),
            [
                new Where(
                    $persistence->getIdColumn(
                        $table_name_builder,
                        $table_definition,
                        $persistence->getCombinationToAnswerOptionsTableIdentifier()
                    ),
                    new Value(\ilDBConstants::T_TEXT, $this->id->toString())
                )
            ]
        );
    }

    public function toTableRow(
        Language $lng,
        DataRowBuilder $data_row_builder
    ): DataRow {
        return $data_row_builder->buildDataRow(
            $this->id->toString(),
            [
                'gaps' => $this->buildGapsString(),
                'values' => $this->getValuePresentation($lng),
                'available_points' => $this->getAvailablePoints()
            ]
        );
    }

    public function buildGapsString(): string
    {
        return implode(
            '<br>',
            array_map(
                fn(MatchingValue $v): string => $v->getGap()->buildShortenedGapRepresentation(),
                $this->matching_values
            )
        );
    }

    public function buildPointsInputs(
        FieldFactory $field_factory,
        Refinery $refinery,
        Language $lng,
        Factory $combinations_factory,
        Properties $properties
    ): Group {
        return $field_factory->section(
            [
                'values' => $this->buildValuesInputs(
                    $field_factory,
                    $properties
                ),
                'points' => $field_factory->numeric(
                    $lng->txt('points')
                )->withStepSize(0.01)
                ->withRequired(true)
                ->withValue($this->getAvailablePoints())
            ],
            $lng->txt('values')
        )->withAdditionalTransformation(
            $refinery->custom()->constraint(
                fn(array $vs): bool => !$properties->getCombinations()
                    ->hasMatchingCombinationForAnswerOptionIds($vs['values']),
                $lng->txt('combination_already_exists')
            )
        )->withAdditionalTransformation(
            $refinery->custom()->transformation(
                fn(array $v): Properties => $properties->withCombinations(
                    $properties->getCombinations()->withAdditionalCombination(
                        $combinations_factory->buildCombination(
                            $this->id,
                            $v['points'],
                            $combinations_factory->buildMatchingValuesFromForm(
                                $properties,
                                $this->id,
                                $v['values']
                            )
                        )
                    )
                )
            )
        );
    }

    public function buildCarryString(): string
    {
        return json_encode([
            $this->id->toString() => array_map(
                fn(MatchingValue $v) => $v->getGap()->getAnswerInputId()->toString(),
                $this->matching_values
            )
        ]);
    }

    private function buildValuesInputs(
        FieldFactory $field_factory,
        Properties $properties
    ): Group {
        return $field_factory->group(
            array_reduce(
                $this->matching_values,
                function (array $c, MatchingValue $v) use ($field_factory, $properties): array {
                    $gap_id = $v->getGap()->getAnswerInputId();
                    $gap = $properties->getGaps()->getGapById($gap_id);
                    $c[$gap_id] = $field_factory->select(
                        $gap->buildShortenedGapName(),
                        $gap->getType()->getCombinationsSelectValues($gap)
                    )->withRequired(true)
                    ->withValue(
                        $v->getAnswerOption()?->getAnswerOptionId()->toString()
                    );
                    return $c;
                },
                []
            )
        );
    }
}

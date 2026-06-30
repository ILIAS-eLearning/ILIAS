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

namespace ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Gaps\AnswerOptions;

use ILIAS\Questions\AnswerForm\Persistence\AnswerFormSpecificTableTypes;
use ILIAS\Questions\Definitions\Clonable;
use ILIAS\Questions\Persistence\Delete;
use ILIAS\Questions\Persistence\Factory as PersistenceFactory;
use ILIAS\Questions\Persistence\Replace;
use ILIAS\Questions\Persistence\TableNameBuilder;
use ILIAS\Questions\AnswerFormTypes\Cloze\TableDefinitions;
use ILIAS\Data\UUID\Factory as UuidFactory;
use ILIAS\Data\UUID\Uuid;
use ILIAS\Database\FieldDefinition;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Transformation;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;

class AnswerOptions implements Clonable
{
    private const string KEY_IS_INCOMPLETE = 'is_incomplete';
    private const string KEY_ANSWER_OPIONS = 'answer_options';
    private const string KEY_ANSWER_OPTIONS_AWARDING_POINTS = 'answer_options_awarding_points';

    private array $answer_options_awarding_points;

    private bool $is_incomplete = false;

    public function __construct(
        private readonly Factory $factory,
        private Uuid $answer_input_id,
        private array $answer_options
    ) {
        $this->answer_options_awarding_points = $this->buildAnswerOptionsAwardingPointsFromAnswerOptions($answer_options);
    }

    public function getMaxAvailablePoints(): float
    {
        return array_reduce(
            $this->answer_options_awarding_points,
            function (?float $c, AnswerOption $v): ?float {
                if ($v->getAvailablePoints() === null) {
                    return $c;
                }

                if ($c === null) {
                    return $v->getAvailablePoints();
                }

                return max($c, $v->getAvailablePoints());
            }
        );
    }

    public function isIncomplete(): bool
    {
        return $this->is_incomplete
            || $this->answer_options === []
            || $this->answer_options_awarding_points === [];
    }

    public function withIsIncomplete(
        bool $is_incomplete
    ): self {
        $clone = clone $this;
        $clone->is_incomplete = $is_incomplete;
        return $clone;
    }

    public function getAnswerOptionById(
        Uuid $answer_option_id
    ): ?AnswerOption {
        $answer_option_id_string = $answer_option_id->toString();
        return array_find(
            $this->answer_options,
            function (AnswerOption $v) use ($answer_option_id_string): bool {
                $id_string = $v->getAnswerOptionId()->toString();
                return $id_string === $answer_option_id_string;
            }
        );
    }

    public function getAnswerOptionByTextValue(
        string $text_value
    ): ?AnswerOption {
        return array_find(
            $this->answer_options,
            fn(AnswerOption $v): bool => $v->getTextValue() === $text_value
        );
    }

    public function getAnswerOptionForPositionOrNew(
        int $position
    ): AnswerOption {
        return $this->answer_options[$position]
            ?? $this->factory->getDefaultAnswerOptionForPosition(
                $this->answer_input_id,
                $position
            );
    }

    public function getBestAnswerOption(): ?AnswerOption
    {
        return array_reduce(
            $this->answer_options_awarding_points,
            fn(?AnswerOption $c, AnswerOption $v): ?AnswerOption
                => $v->getAvailablePoints() > $c?->getAvailablePoints()
                    ? $v
                    : $c
        );
    }

    public function getTagsArrayFromAnswerOptions(): array
    {
        return array_reduce(
            $this->answer_options,
            function (array $c, AnswerOption $v): array {
                if ($v->getTextValue() === '') {
                    return $c;
                }
                $c[] = $v->getTextValue();
                return $c;
            },
            []
        );
    }

    public function getAnswerOptionsAwardingPoints(): array
    {
        return $this->answer_options_awarding_points;
    }

    public function withAnswerOptionsAwardingPoints(
        array $options
    ): self {
        $clone = clone $this;
        $clone->answer_options_awarding_points = array_reduce(
            $options,
            function (array $c, string $v): array {
                $answer_option = $this->retrieveAnswerOptionByTextValue($v);
                if ($answer_option !== null) {
                    $c[] = $answer_option;
                }
                return $c;
            },
            []
        );
        return $clone;
    }

    public function withAnswerOptions(
        array $answer_options
    ): self {
        $clone = clone $this;
        $clone->answer_options = $answer_options;
        return $clone;
    }

    public function withAnswerOptionsFromTags(
        array $tags
    ): self {
        $clone = clone $this;
        $position = 0;
        $clone->answer_options = array_map(
            function (string $v) use (&$position): AnswerOption {
                return $this->buildAnswerOptionFromTag(
                    $position++,
                    $v
                );
            },
            $tags
        );
        return $clone;
    }

    public function withAnswerOptionsWithAddedPointsFromForm(
        Refinery $refinery,
        array $values_from_form
    ): self {
        $clone = clone $this;
        $clone->answer_options = array_map(
            function (AnswerOption $v) use ($refinery, $values_from_form): AnswerOption {
                $answer_option_id = $v->getAnswerOptionId()->toString();
                if (array_key_exists($answer_option_id, $values_from_form)) {
                    return $v->withAvailablePoints(
                        $refinery->byTrying([
                            $refinery->kindlyTo()->float(),
                            $refinery->always(null)
                        ])->transform($values_from_form[$answer_option_id])
                    );
                }

                return $v;
            },
            $clone->answer_options
        );
        $clone->answer_options_awarding_points = $clone
            ->buildAnswerOptionsAwardingPointsFromAnswerOptions($clone->answer_options);
        return $clone;
    }

    public function buildArrayForSelectInput(
        Transformation $shuffle_transformation
    ): array {
        return array_reduce(
            $shuffle_transformation->transform($this->answer_options),
            function (array $c, AnswerOption $v): array {
                $c[$v->getAnswerOptionId()->toString()] = $v->getTextValue();
                return $c;
            },
            []
        );
    }

    public function toCarry(): array
    {
        return [
            self::KEY_IS_INCOMPLETE => $this->is_incomplete ? 1 : 0,
            self::KEY_ANSWER_OPIONS => array_map(
                fn(AnswerOption $v): array => $v->toCarry(),
                $this->answer_options
            ),
            self::KEY_ANSWER_OPTIONS_AWARDING_POINTS => array_map(
                fn(AnswerOption $v): string => $v->getAnswerOptionId()->toString(),
                $this->answer_options_awarding_points
            )
        ];
    }

    public function withValuesFromCarry(
        array $carry
    ): self {
        $clone = clone $this;
        $clone->is_incomplete = $carry[self::KEY_IS_INCOMPLETE] === 1;
        $clone->answer_options = array_map(
            fn(array $vs): AnswerOption => $this->factory->buildAnswerOption(
                $vs[AnswerOption::FORM_KEY_ID],
                $this->answer_input_id,
                (int) $vs[AnswerOption::FORM_KEY_POSITION],
                $vs[AnswerOption::FORM_KEY_TEXT_VALUE],
                $vs[AnswerOption::FORM_KEY_LOWER_LIMIT] ?? null,
                $vs[AnswerOption::FORM_KEY_UPPER_LIMIT] ?? null,
                $vs[AnswerOption::FORM_KEY_AVAILABLE_POINTS] ?? null
            ),
            $carry[self::KEY_ANSWER_OPIONS] ?? []
        );

        $clone->answer_options_awarding_points = array_filter(
            $clone->answer_options,
            fn(AnswerOption $v): bool => in_array(
                $v->getAnswerOptionId()->toString(),
                $carry[self::KEY_ANSWER_OPTIONS_AWARDING_POINTS] ?? []
            )
        );
        return $clone;
    }

    public function getEditPointsInputs(
        FieldFactory $ff,
        \Closure $build_label,
        ?array $answer_options_awarding_points = null
    ): array {
        return array_reduce(
            $answer_options_awarding_points ?? $this->answer_options,
            function (array $c, AnswerOption $v) use ($ff, $build_label): array {
                $c[$v->getAnswerOptionId()->toString()] = $ff->numeric($build_label($v))
                    ->withStepSize(0.01)
                    ->withValue($v->getAvailablePoints());
                return $c;
            },
            []
        );
    }

    #[\Override]
    public function clone(
        UuidFactory $uuid_factory,
        array $environment = []
    ): static {
        $clone = clone $this;
        $clone->answer_input_id = $environment['answer_input_id'];
        $clone->answer_options = array_map(
            fn(AnswerOption $v): AnswerOption => $v->clone(
                $uuid_factory,
                $environment
            ),
            $this->answer_options
        );
        return $clone;
    }

    public function buildReplace(
        ?Replace $replace,
        TableDefinitions $table_definitions,
        PersistenceFactory $persistence_factory,
        TableNameBuilder $table_names_builder
    ): Replace {
        return array_reduce(
            $this->answer_options,
            fn(?Replace $c, AnswerOption $v): Replace => $v->buildReplace(
                $persistence_factory,
                $c,
                $table_definitions->getColumns(
                    $table_names_builder,
                    AnswerFormSpecificTableTypes::AnswerOptions
                )
            ),
            $replace
        );
    }

    public function buildDelete(
        TableDefinitions $table_definitions,
        PersistenceFactory $persistence_factory,
        TableNameBuilder $table_names_builder
    ): Delete {
        $table_type = AnswerFormSpecificTableTypes::AnswerOptions;

        return $persistence_factory->delete(
            $persistence_factory->table(
                $table_names_builder,
                $table_type
            ),
            [
                $persistence_factory->where(
                    $table_definitions->getForeignKeyColumn(
                        $table_names_builder,
                        $table_type
                    ),
                    $persistence_factory->value(
                        FieldDefinition::T_TEXT,
                        $this->answer_input_id->toString()
                    )
                )
            ]
        );
    }

    private function buildAnswerOptionsAwardingPointsFromAnswerOptions(
        array $answer_options
    ): array {
        return array_filter(
            $answer_options,
            fn(AnswerOption $v): bool => $v->getAvailablePoints() > 0.0
        );
    }

    private function buildAnswerOptionFromTag(
        int $position,
        string $text_value
    ): AnswerOption {
        $answer_option = $this->retrieveAnswerOptionByTextValue($text_value)
            ?? $this->factory->getDefaultAnswerOptionForPosition(
                $this->answer_input_id,
                $position
            );

        return $answer_option
            ->withPosition($position)
            ->withTextValue($text_value);
    }

    private function retrieveAnswerOptionByTextValue(
        string $value
    ): ?AnswerOption {
        $filtered_array = array_filter(
            $this->answer_options,
            fn(AnswerOption $v): bool => $v->getTextValue() === $value
        );
        return array_shift($filtered_array) ?? null;
    }
}
